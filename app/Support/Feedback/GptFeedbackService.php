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
     * Generate suggested business reply
     */
    public function generateReply(FeedbackEntry $feedback): array
    {
        $systemPrompt = "You are a polite customer recovery assistant. Input: customer name (if provided), rating, comment, category, urgency. Output a short reply (1-3 sentences) that apologizes, acknowledges the issue, offers one specific remedy (refund/discount/appointment/reschedule), and asks for preferred contact time. Keep tone professional and concise. Provide reply text and a 1-line suggested next step for staff. Return JSON: {\"reply\":\"...\",\"next_step\":\"...\"}";

        $userPrompt = sprintf(
            "Rating: %d/5\nComment: %s\nCategory: %s\nUrgency: %s",
            $feedback->rating,
            $feedback->comment ?? 'No comment',
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

            return [
                'reply' => $json['reply'] ?? 'We apologize for the inconvenience. A manager will review this and follow up with you shortly.',
                'next_step' => $json['next_step'] ?? 'Review feedback and contact customer within 24 hours',
            ];

        } catch (\Exception $e) {
            Log::error('GPT reply generation error: ' . $e->getMessage());
            
            return [
                'reply' => 'We apologize for the inconvenience. A manager will review this and follow up with you shortly.',
                'next_step' => 'Review feedback and contact customer within 24 hours',
            ];
        }
    }

    /**
     * Generate recovery plan actions
     */
    public function generateRecoveryPlan(FeedbackEntry $feedback): array
    {
        $systemPrompt = "You are an operations advisor. Input: category and comment. Output JSON list of 3 recommended actions staff should take (priority, who, ETA). Keep it actionable. Return JSON array: [{\"priority\":\"high|medium|low\",\"action\":\"...\",\"assigned_to\":\"...\",\"eta\":\"...\"}, ...]";

        $userPrompt = sprintf(
            "Category: %s\nComment: %s",
            $feedback->category ?? 'other',
            $feedback->comment ?? 'No comment'
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
                $actions[] = [
                    'priority' => 'medium',
                    'action' => 'Follow up with customer',
                    'assigned_to' => 'Manager',
                    'eta' => '24 hours',
                ];
            }

            return $actions;

        } catch (\Exception $e) {
            Log::error('GPT recovery plan error: ' . $e->getMessage());
            
            // Fallback actions
            return [
                [
                    'priority' => $feedback->urgency === 'high' ? 'high' : 'medium',
                    'action' => 'Contact customer to discuss issue',
                    'assigned_to' => 'Manager',
                    'eta' => '24 hours',
                ],
                [
                    'priority' => 'medium',
                    'action' => 'Review and document feedback',
                    'assigned_to' => 'Staff',
                    'eta' => '48 hours',
                ],
                [
                    'priority' => 'low',
                    'action' => 'Implement preventive measures',
                    'assigned_to' => 'Operations',
                    'eta' => '1 week',
                ],
            ];
        }
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

