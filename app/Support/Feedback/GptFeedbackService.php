<?php

namespace App\Support\Feedback;

use App\Models\FeedbackEntry;
use App\Support\AI\OpenAi\Conversation;
use App\Support\AI\OpenAi\Models\Input;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Log;

class GptFeedbackService
{
    use WriteLogs;

    /**
     * Classify feedback sentiment, urgency, category, and escalation
     */
    public function classifyFeedback(int $rating, ?string $comment): array
    {
        $comment = $comment ?? 'No text provided';

        $systemPrompt = "You are a classifier. Input: customer rating (1-5) and short comment. Output JSON with fields:
- sentiment: positive | neutral | negative
- urgency: low | medium | high
- category: billing | service | staff | product | scheduling | other
- escalate: true|false (true if rating<=2 or contains words like 'sue', 'refund', 'danger', 'injury', 'harass', or urgent phrases)
Return only valid JSON.";

        $userPrompt = "Rating: {$rating}/5\nComment: {$comment}";

        try {
            $conversation = Conversation::start($systemPrompt)
                ->record($userPrompt)
                ->send();

            $response = $conversation->get();
            $content = $response->output[0]->content[0]->text ?? '';

            // Extract JSON from response
            $json = $this->extractJson($content);

            if (!$json) {
                throw new \Exception('Failed to extract JSON from GPT response');
            }

            // Validate and set defaults
            return [
                'sentiment' => $json['sentiment'] ?? ($rating >= 4 ? 'positive' : ($rating <= 2 ? 'negative' : 'neutral')),
                'urgency' => $json['urgency'] ?? ($rating <= 2 ? 'high' : ($rating == 3 ? 'medium' : 'low')),
                'category' => $json['category'] ?? 'other',
                'escalate' => $json['escalate'] ?? ($rating <= 2),
            ];

        } catch (\Exception $e) {
            Log::error('GPT classification error: ' . $e->getMessage());
            
            // Fallback to rule-based classification
            return $this->fallbackClassification($rating, $comment);
        }
    }

    /**
     * Generate suggested business reply with empathetic, solution-oriented response
     */
    public function generateReply(FeedbackEntry $feedback): array
    {
        $systemPrompt = "You are an AI agentic recovery assistant. Your responses must be concise, empathetic, and action-oriented (2-3 sentences maximum).

CRITICAL STYLE RULES:
- Start with brief acknowledgment: 'Thanks for letting us know' or 'Thank you for your honest feedback'
- Acknowledge the SPECIFIC issue mentioned in their comment
- Apologize sincerely (one sentence)
- Offer a CONCRETE remedy with specific details (discount %, replacement, etc.)
- End with a question to engage them
- Keep it SHORT - maximum 3 sentences total
- Be warm but professional
- DO NOT repeat information or use filler words

Category-specific response patterns:

SERVICE (food quality, wait times, cleanliness):
- Pattern: 'Thanks for letting us know. [Acknowledge specific issue]. We apologize. [Immediate action taken]. We'd like to offer [specific remedy] — would you prefer [option A] or [option B]?'
- Example: 'Thanks for letting us know. We're very sorry your meal arrived cold. We'd like to fix this for you right away — would you prefer a replacement or a credit toward your next order?'

STAFF (rude, unprofessional):
- Pattern: 'Thank you for your honest feedback. We're sorry about your experience — that's not the level of service we aim for. We're addressing this internally, and we'd like to offer you [specific remedy].'
- Example: 'Thank you for your honest feedback. We're sorry about your experience — that's not the level of service we aim for. We're addressing this internally, and we'd like to offer you a free coffee on your next visit.'

PRODUCT (wrong item, defective):
- Pattern: 'Sorry about that! [Immediate action]. Thank you for pointing this out so we can improve.'
- Example: 'Sorry about that! We'll send the correct item today — no return needed. Thank you for pointing this out so we can improve.'

SCHEDULING (long waits, missed appointments):
- Pattern: 'Thanks for letting us know. [Acknowledge frustration]. We want to make this right — we can offer [specific remedy].'
- Example: 'Thanks for letting us know. Long waits are frustrating, and we apologize. We want to make this right — we can offer 20% off your next appointment.'

BILLING (charges, pricing):
- Pattern: 'We're very sorry about the billing issue. We'd like to review this and make it right — would you prefer a refund or credit toward your next visit?'

Return JSON: {\"reply\":\"...\",\"next_step\":\"...\",\"suggested_remedy\":\"...\"}";

        $userPrompt = sprintf(
            "Customer Rating: %d/5 stars\n\nCustomer Feedback: \"%s\"\n\nIssue Category: %s\nUrgency Level: %s\n\nGenerate a concise, agentic recovery response (2-3 sentences max) following the exact pattern for this category. Reference their specific issue directly. Be direct and action-oriented.",
            $feedback->rating,
            $feedback->comment ?? 'No specific comment provided',
            $feedback->category ?? 'other',
            $feedback->urgency ?? 'medium'
        );

        try {
            $conversation = Conversation::start($systemPrompt)
                ->record($userPrompt)
                ->send();

            $response = $conversation->get();
            $content = $response->output[0]->content[0]->text ?? '';

            $json = $this->extractJson($content);

            if (!$json) {
                throw new \Exception('Failed to extract JSON from GPT response');
            }

            // Generate default if missing
            $reply = $json['reply'] ?? $this->generateDefaultReply($feedback);
            $nextStep = $json['next_step'] ?? $this->generateDefaultNextStep($feedback);
            $suggestedRemedy = $json['suggested_remedy'] ?? $this->getDefaultRemedy($feedback->category);

            return [
                'reply' => $reply,
                'next_step' => $nextStep,
                'suggested_remedy' => $suggestedRemedy,
            ];

        } catch (\Exception $e) {
            Log::error('GPT reply generation error: ' . $e->getMessage());
            
            return [
                'reply' => $this->generateDefaultReply($feedback),
                'next_step' => $this->generateDefaultNextStep($feedback),
                'suggested_remedy' => $this->getDefaultRemedy($feedback->category),
            ];
        }
    }

    /**
     * Generate default empathetic reply based on category
     */
    private function generateDefaultReply(FeedbackEntry $feedback): string
    {
        $category = $feedback->category ?? 'other';
        $comment = $feedback->comment ?? '';
        
        // Category-specific replies matching the agentic style (2-3 sentences, concise)
        $remedies = [
            'billing' => "We're very sorry about the billing issue. We'd like to review this and make it right — would you prefer a refund or credit toward your next visit?",
            'service' => function($comment) {
                $issue = !empty($comment) ? strtolower($comment) : 'your experience';
                if (stripos($issue, 'cold') !== false || stripos($issue, 'food') !== false) {
                    return "Thanks for letting us know. We're very sorry your meal arrived cold. We'd like to fix this for you right away — would you prefer a replacement or a credit toward your next order?";
                } elseif (stripos($issue, 'dirty') !== false || stripos($issue, 'clean') !== false) {
                    return "Thanks for telling us. Cleanliness is top priority and we apologize. We've alerted today's shift lead to address this immediately. We'd like to offer 15% off your next order.";
                }
                return "Thanks for letting us know. We're sorry your experience wasn't perfect. We'd like to fix this for you — would you prefer a replacement or a discount on your next order?";
            },
            'staff' => "Thank you for your honest feedback. We're sorry about your experience — that's not the level of service we aim for. We're addressing this internally, and we'd like to offer you a free item on your next visit.",
            'product' => "Sorry about that! We'll send the correct item today — no return needed. Thank you for pointing this out so we can improve.",
            'scheduling' => "Thanks for letting us know. Long waits are frustrating, and we apologize. We want to make this right — we can offer 20% off your next appointment.",
            'other' => "Thanks for letting us know. We're sorry your experience wasn't what you expected. We'd like to make this right — what would work best for you?",
        ];

        $baseRemedy = $remedies[$category] ?? $remedies['other'];
        
        // Handle service category with callable for dynamic responses
        if ($category === 'service' && is_callable($baseRemedy)) {
            return $baseRemedy($comment);
        }
        
        // For other categories, return the base reply directly
        return is_string($baseRemedy) ? $baseRemedy : $remedies['other'];
    }

    /**
     * Generate default next step
     */
    private function generateDefaultNextStep(FeedbackEntry $feedback): string
    {
        $urgency = $feedback->urgency ?? 'medium';
        $category = $feedback->category ?? 'other';
        
        if ($urgency === 'high') {
            return "Contact customer immediately (within 1 hour). Offer remedy and follow up within 24 hours.";
        }
        
        $steps = [
            'billing' => "Review billing records. Contact customer within 4 hours to resolve charge issue.",
            'service' => "Contact customer within 2 hours. Offer replacement or discount. Follow up after delivery.",
            'staff' => "Review with staff member. Contact customer within 4 hours. Offer discount and ensure issue addressed.",
            'product' => "Send replacement immediately. Contact customer within 2 hours. Follow up after delivery.",
            'scheduling' => "Review booking system. Contact customer within 4 hours. Offer priority booking or discount.",
            'other' => "Review feedback. Contact customer within 24 hours to discuss resolution.",
        ];
        
        return $steps[$category] ?? "Contact customer within 24 hours to discuss resolution.";
    }

    /**
     * Get default remedy suggestion
     */
    private function getDefaultRemedy(?string $category): string
    {
        $remedies = [
            'billing' => 'Refund or credit toward next visit',
            'service' => 'Replacement or 20% discount on next order',
            'staff' => 'Internal review + 15% discount on next visit',
            'product' => 'Replacement sent immediately',
            'scheduling' => 'Priority booking or 20% off next appointment',
            'other' => 'Discount or credit based on issue',
        ];
        
        return $remedies[$category] ?? 'Discount or credit based on specific issue';
    }

    /**
     * Generate operational recommendation based on category and frequency
     */
    public function generateOperationalRecommendation(FeedbackEntry $feedback, int $frequency = 0): ?string
    {
        $category = $feedback->category ?? 'other';
        $comment = $feedback->comment ?? '';
        
        $recommendations = [
            'service' => function($freq, $comment) {
                if (stripos($comment, 'wait') !== false || stripos($comment, 'time') !== false) {
                    return $freq > 10 
                        ? "Peak-time bookings exceed staff capacity. Recommendation: Add a text-based check-in system or increase staff during peak hours."
                        : "Consider optimizing scheduling to reduce wait times.";
                } elseif (stripos($comment, 'cold') !== false || stripos($comment, 'food') !== false) {
                    return "Food temperature issues detected. Recommendation: Review delivery timeframes and implement temperature monitoring.";
                } elseif (stripos($comment, 'dirty') !== false || stripos($comment, 'clean') !== false) {
                    return "Cleanliness issues reported. Recommendation: Implement hourly cleaning checklists and staff training on cleanliness standards.";
                }
                return "Service quality issues detected. Recommendation: Review service processes and implement quality checks.";
            },
            'staff' => function($freq) {
                return $freq > 5 
                    ? "Multiple staff-related complaints. Recommendation: Provide customer service training and coaching on interaction scripts."
                    : "Staff interaction issue. Recommendation: Review with team member and provide coaching.";
            },
            'product' => function($freq) {
                return $freq > 3
                    ? "Fulfillment errors occurring frequently. Recommendation: Implement double-check system before shipping."
                    : "Fulfillment error detected. Recommendation: Review fulfillment process.";
            },
            'scheduling' => function($freq) {
                return $freq > 10
                    ? "Scheduling issues exceed capacity. Recommendation: Optimize booking system and consider adding buffer time between appointments."
                    : "Scheduling issue detected. Recommendation: Review booking system.";
            },
            'billing' => function($freq) {
                return "Billing issue detected. Recommendation: Review billing process and implement verification steps.";
            },
            'other' => function() {
                return "Customer concern identified. Recommendation: Review and address root cause.";
            },
        ];
        
        $recommendation = $recommendations[$category] ?? $recommendations['other'];
        return is_callable($recommendation) ? $recommendation($frequency, $comment) : $recommendation;
    }

    /**
     * Generate follow-up message based on feedback status
     */
    public function generateFollowUpMessage(FeedbackEntry $feedback, string $type = 'check_in'): string
    {
        $messages = [
            'check_in' => "Just checking in — did our team address your concern?",
            'satisfaction' => "Was everything better this time?",
            'review_request' => function($category) {
                if ($category === 'service') {
                    return "Great! Would you mind sharing your updated experience on Google? It helps a small business a lot.";
                }
                return "Glad to help! If you're comfortable, we'd appreciate a quick review about your experience.";
            },
        ];
        
        if ($type === 'review_request' && is_callable($messages[$type])) {
            return $messages[$type]($feedback->category);
        }
        
        return $messages[$type] ?? $messages['check_in'];
    }

    /**
     * Generate recovery plan actions with specific, actionable recommendations
     */
    public function generateRecoveryPlan(FeedbackEntry $feedback): array
    {
        $systemPrompt = "You are an operations advisor providing actionable recovery steps. Based on the feedback category and comment, suggest 3 specific, measurable actions.

Guidelines:
- Actions should be specific and actionable (not vague)
- Include who should handle it and realistic timeframes
- First action should be immediate customer contact/remedy
- Second action should address the root cause
- Third action should be a preventive measure

Categories and typical actions:
- billing: Review charge, process refund/credit, update billing process
- service: Contact customer with remedy, review service process, implement quality check
- staff: Address with staff member, provide coaching, update training
- product: Send replacement, review fulfillment process, implement quality control
- scheduling: Contact customer, review booking system, optimize scheduling
- other: Contact customer, investigate issue, implement preventive measure

Return JSON array: [{\"priority\":\"high|medium|low\",\"action\":\"...\",\"assigned_to\":\"...\",\"eta\":\"...\"}, ...]";

        $userPrompt = sprintf(
            "Issue Category: %s\n\nCustomer Feedback: \"%s\"\n\nUrgency: %s\n\nGenerate 3 specific, actionable recovery steps:",
            $feedback->category ?? 'other',
            $feedback->comment ?? 'No specific comment',
            $feedback->urgency ?? 'medium'
        );

        try {
            $conversation = Conversation::start($systemPrompt)
                ->record($userPrompt)
                ->send();

            $response = $conversation->get();
            $content = $response->output[0]->content[0]->text ?? '';

            $json = $this->extractJson($content);

            if (!$json || !is_array($json)) {
                throw new \Exception('Failed to extract JSON array from GPT response');
            }

            // Ensure we have exactly 3 actions
            $actions = array_slice($json, 0, 3);
            while (count($actions) < 3) {
                $actions[] = $this->getDefaultAction(count($actions), $feedback);
            }

            return $actions;

        } catch (\Exception $e) {
            Log::error('GPT recovery plan error: ' . $e->getMessage());
            
            // Fallback to category-specific actions
            return $this->getDefaultRecoveryPlan($feedback);
        }
    }

    /**
     * Get default action based on position and feedback
     */
    private function getDefaultAction(int $position, FeedbackEntry $feedback): array
    {
        $urgency = $feedback->urgency ?? 'medium';
        $category = $feedback->category ?? 'other';
        
        $actions = [
            0 => [ // Immediate action
                'priority' => $urgency === 'high' ? 'high' : 'medium',
                'action' => 'Contact customer immediately with remedy offer',
                'assigned_to' => 'Manager',
                'eta' => $urgency === 'high' ? '1 hour' : '4 hours',
            ],
            1 => [ // Root cause
                'priority' => 'medium',
                'action' => 'Review and document the issue for process improvement',
                'assigned_to' => 'Operations',
                'eta' => '24 hours',
            ],
            2 => [ // Preventive
                'priority' => 'low',
                'action' => 'Implement preventive measures to avoid recurrence',
                'assigned_to' => 'Management',
                'eta' => '1 week',
            ],
        ];
        
        return $actions[$position] ?? $actions[0];
    }

    /**
     * Get default recovery plan based on category
     */
    private function getDefaultRecoveryPlan(FeedbackEntry $feedback): array
    {
        $category = $feedback->category ?? 'other';
        $urgency = $feedback->urgency ?? 'medium';
        
        $plans = [
            'billing' => [
                [
                    'priority' => $urgency === 'high' ? 'high' : 'medium',
                    'action' => 'Review billing records and contact customer to resolve charge',
                    'assigned_to' => 'Manager',
                    'eta' => $urgency === 'high' ? '1 hour' : '4 hours',
                ],
                [
                    'priority' => 'medium',
                    'action' => 'Process refund or credit as discussed with customer',
                    'assigned_to' => 'Finance',
                    'eta' => '24 hours',
                ],
                [
                    'priority' => 'low',
                    'action' => 'Review billing process to prevent similar issues',
                    'assigned_to' => 'Operations',
                    'eta' => '1 week',
                ],
            ],
            'service' => [
                [
                    'priority' => $urgency === 'high' ? 'high' : 'medium',
                    'action' => 'Contact customer and offer replacement or discount',
                    'assigned_to' => 'Manager',
                    'eta' => $urgency === 'high' ? '1 hour' : '2 hours',
                ],
                [
                    'priority' => 'medium',
                    'action' => 'Follow up after remedy delivery to ensure satisfaction',
                    'assigned_to' => 'Customer Service',
                    'eta' => '24 hours',
                ],
                [
                    'priority' => 'low',
                    'action' => 'Review service process and implement quality checks',
                    'assigned_to' => 'Operations',
                    'eta' => '1 week',
                ],
            ],
            'staff' => [
                [
                    'priority' => 'high',
                    'action' => 'Review incident with staff member and provide coaching',
                    'assigned_to' => 'Manager',
                    'eta' => '4 hours',
                ],
                [
                    'priority' => 'medium',
                    'action' => 'Contact customer to apologize and offer discount',
                    'assigned_to' => 'Manager',
                    'eta' => '24 hours',
                ],
                [
                    'priority' => 'low',
                    'action' => 'Update staff training on customer service standards',
                    'assigned_to' => 'HR',
                    'eta' => '2 weeks',
                ],
            ],
            'product' => [
                [
                    'priority' => $urgency === 'high' ? 'high' : 'medium',
                    'action' => 'Send replacement product immediately',
                    'assigned_to' => 'Fulfillment',
                    'eta' => 'Same day',
                ],
                [
                    'priority' => 'medium',
                    'action' => 'Follow up with customer after delivery',
                    'assigned_to' => 'Customer Service',
                    'eta' => '48 hours',
                ],
                [
                    'priority' => 'low',
                    'action' => 'Review fulfillment process and implement quality control',
                    'assigned_to' => 'Operations',
                    'eta' => '1 week',
                ],
            ],
            'scheduling' => [
                [
                    'priority' => $urgency === 'high' ? 'high' : 'medium',
                    'action' => 'Contact customer and offer priority booking or discount',
                    'assigned_to' => 'Manager',
                    'eta' => '4 hours',
                ],
                [
                    'priority' => 'medium',
                    'action' => 'Review booking system for capacity issues',
                    'assigned_to' => 'Operations',
                    'eta' => '24 hours',
                ],
                [
                    'priority' => 'low',
                    'action' => 'Optimize scheduling system to prevent overbooking',
                    'assigned_to' => 'Management',
                    'eta' => '2 weeks',
                ],
            ],
        ];
        
        if (isset($plans[$category])) {
            return $plans[$category];
        }
        
        // Default plan for 'other' category
        return [
            $this->getDefaultAction(0, $feedback),
            $this->getDefaultAction(1, $feedback),
            $this->getDefaultAction(2, $feedback),
        ];
    }

    /**
     * Detect fake/spam feedback (optional)
     */
    public function detectSpam(?string $comment, ?string $contact, $timestamp): array
    {
        $systemPrompt = "You are a fraud detector. Input: comment, email/phone (if provided), time of submission. Output: {\"likely_fake\": true|false, \"reason\":\"...\"}.";

        $userPrompt = sprintf(
            "Comment: %s\nContact: %s\nTimestamp: %s",
            $comment ?? 'No comment',
            $contact ?? 'No contact',
            $timestamp
        );

        try {
            $conversation = Conversation::start($systemPrompt)
                ->record($userPrompt)
                ->send();

            $response = $conversation->get();
            $content = $response->output[0]->content[0]->text ?? '';

            $json = $this->extractJson($content);

            return [
                'likely_fake' => $json['likely_fake'] ?? false,
                'reason' => $json['reason'] ?? 'No issues detected',
            ];

        } catch (\Exception $e) {
            Log::error('GPT spam detection error: ' . $e->getMessage());
            
            return [
                'likely_fake' => false,
                'reason' => 'Unable to analyze',
            ];
        }
    }

    /**
     * Extract JSON from text response
     */
    private function extractJson(string $text): ?array
    {
        // Try to find JSON in the response
        $jsonStart = strpos($text, '{');
        $jsonEnd = strrpos($text, '}');
        
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($text, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decoded = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Try array format
        $arrayStart = strpos($text, '[');
        $arrayEnd = strrpos($text, ']');
        
        if ($arrayStart !== false && $arrayEnd !== false) {
            $jsonString = substr($text, $arrayStart, $arrayEnd - $arrayStart + 1);
            $decoded = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Fallback classification when GPT fails
     */
    private function fallbackClassification(int $rating, string $comment): array
    {
        $commentLower = strtolower($comment);
        
        $escalate = $rating <= 2 || 
            preg_match('/\b(sue|refund|danger|injury|harass|legal|lawyer|attorney)\b/i', $comment);

        $urgency = 'low';
        if ($rating <= 2 || $escalate) {
            $urgency = 'high';
        } elseif ($rating == 3) {
            $urgency = 'medium';
        }

        $sentiment = 'neutral';
        if ($rating >= 4) {
            $sentiment = 'positive';
        } elseif ($rating <= 2) {
            $sentiment = 'negative';
        }

        $category = 'other';
        if (preg_match('/\b(bill|charge|price|cost|payment|invoice)\b/i', $comment)) {
            $category = 'billing';
        } elseif (preg_match('/\b(service|wait|slow|fast|quality)\b/i', $comment)) {
            $category = 'service';
        } elseif (preg_match('/\b(staff|employee|worker|person|rude|friendly)\b/i', $comment)) {
            $category = 'staff';
        } elseif (preg_match('/\b(product|item|quality|defect)\b/i', $comment)) {
            $category = 'product';
        } elseif (preg_match('/\b(appointment|schedule|time|date|booking)\b/i', $comment)) {
            $category = 'scheduling';
        }

        return [
            'sentiment' => $sentiment,
            'urgency' => $urgency,
            'category' => $category,
            'escalate' => $escalate,
        ];
    }
}

