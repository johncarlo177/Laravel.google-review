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
        $systemPrompt = "You are an empathetic customer recovery assistant. Your goal is to turn negative experiences into positive outcomes. 

Guidelines:
- Write ONE cohesive message (2-4 sentences) - do NOT repeat acknowledgments
- Start with a single acknowledgment of the specific issue mentioned
- Offer a concrete, relevant remedy based on the category
- Keep tone warm, professional, and conversational
- Make the customer feel heard and valued
- End with an open question to engage them
- DO NOT start with generic phrases like 'Thanks for your feedback' if you're going to acknowledge the issue again

Categories and typical remedies:
- billing: Offer to review/refund the charge or provide credit
- service: Offer replacement, discount (15-25%), or free item/service
- staff: Apologize, mention internal review, offer discount or free item
- product: Offer replacement, refund, or upgrade
- scheduling: Offer to reschedule, priority booking, or discount on next appointment
- other: Acknowledge issue, offer general discount or credit

Example good response: 'Thanks for letting us know about the dirty tables. Cleanliness is our top priority and we apologize. We've alerted today's shift lead to address this immediately. We'd like to offer 15% off your next order — would that work for you?'

Return JSON with: {\"reply\":\"...\",\"next_step\":\"...\",\"suggested_remedy\":\"...\"}";

        $userPrompt = sprintf(
            "Customer Rating: %d/5 stars\n\nCustomer Feedback: \"%s\"\n\nIssue Category: %s\nUrgency Level: %s\n\nGenerate a warm, empathetic response that:\n1. Acknowledges their specific issue\n2. Apologizes sincerely\n3. Offers a specific remedy appropriate for this category\n4. Asks how they'd like to proceed or what would make it right",
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
        
        // Category-specific replies that are complete and don't need prefix
        $remedies = [
            'billing' => "We're very sorry about the billing issue. We'd like to review this and make it right — would you prefer a refund or credit toward your next visit?",
            'service' => "Thanks for letting us know. We're sorry your experience wasn't perfect. We'd like to fix this for you — would you prefer a replacement or a discount on your next order?",
            'staff' => "Thank you for your honest feedback. We're sorry about your experience — that's not the level of service we aim for. We're addressing this internally, and we'd like to offer you a discount on your next visit.",
            'product' => "We're sorry the product wasn't right. We'll send a replacement right away — no return needed. Thank you for pointing this out so we can improve.",
            'scheduling' => "We apologize for the scheduling issue. Long waits are frustrating. We want to make this right — we can offer priority booking for your next appointment or a discount.",
            'other' => "Thanks for letting us know. We're sorry your experience wasn't what you expected. We'd like to make this right — what would work best for you?",
        ];

        $baseReply = $remedies[$category] ?? $remedies['other'];
        
        // If we have a specific comment, incorporate it naturally into the reply
        if (!empty($comment) && $comment !== 'No comment' && $comment !== 'your experience') {
            // For service category, reference the specific issue
            if ($category === 'service') {
                return "Thanks for letting us know about: {$comment}. We're sorry this happened. We'd like to fix this for you — would you prefer a replacement or a discount on your next order?";
            }
            // For other categories, just use the base reply (it's already empathetic)
        }
        
        // Return the base reply directly (no duplication)
        return $baseReply;
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

