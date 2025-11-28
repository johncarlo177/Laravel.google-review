<?php

namespace App\Support\WinBack;

use App\Models\WinBackCustomer;
use App\Models\WinBackSegment;
use App\Support\AI\OpenAi\Conversation;
use Illuminate\Support\Facades\Log;

class WinBackMessageGeneratorService
{
    /**
     * Generate personalized message for a customer
     */
    public function generateMessage(WinBackCustomer $customer, ?WinBackSegment $segment = null): string
    {
        $segmentName = $segment?->name ?? $customer->segment ?? 'dormant';
        
        $systemPrompt = $this->getSystemPrompt($segmentName);
        $userPrompt = $this->buildUserPrompt($customer, $segmentName);

        try {
            $conversation = Conversation::start($systemPrompt)
                ->record($userPrompt)
                ->send();

            $response = $conversation->get();
            $content = $response->output[0]->content[0]->text ?? '';

            // Clean up the message
            $message = trim($content);
            
            // Remove quotes if wrapped
            if (preg_match('/^["\'](.*)["\']$/s', $message, $matches)) {
                $message = $matches[1];
            }

            return $message;

        } catch (\Exception $e) {
            Log::error('Win-Back message generation error: ' . $e->getMessage());
            
            // Fallback to default message
            return $this->getDefaultMessage($customer, $segmentName);
        }
    }

    /**
     * Get system prompt based on segment
     */
    protected function getSystemPrompt(string $segment): string
    {
        $prompts = [
            WinBackCustomer::SEGMENT_LOST => <<<'PROMPT'
You are a friendly business owner reaching out to a customer who hasn't visited in 60+ days. 
Write a warm, genuine message that:
- Acknowledges it's been a while since their last visit
- Shows you genuinely care about them
- Offers a small thank-you gift or incentive (be specific but not pushy)
- Keeps it personal and human (2-3 sentences max)
- No sales-y language, no pressure
- Tone: friendly, appreciative, warm

Return ONLY the message text, no quotes, no JSON.
PROMPT,

            WinBackCustomer::SEGMENT_DORMANT => <<<'PROMPT'
You are a business owner checking in with a customer who hasn't visited in 30-60 days.
Write a genuine, caring message that:
- Shows you're thinking of them
- Asks if everything is okay or if they need anything
- Expresses appreciation
- Keeps it brief and personal (2 sentences max)
- No sales pitch, just genuine care
- Tone: friendly, caring, low-pressure

Return ONLY the message text, no quotes, no JSON.
PROMPT,

            WinBackCustomer::SEGMENT_VIP => <<<'PROMPT'
You are a business owner reaching out to a VIP customer (high value, frequent visitor).
Write an appreciative message that:
- Thanks them for being a valued customer
- Offers a VIP perk or exclusive benefit
- Shows genuine appreciation
- Keeps it personal and warm (2-3 sentences)
- Tone: appreciative, exclusive, warm

Return ONLY the message text, no quotes, no JSON.
PROMPT,

            WinBackCustomer::SEGMENT_ONE_TIME => <<<'PROMPT'
You are a business owner reaching out to someone who visited once but hasn't returned.
Write a friendly message that:
- Thanks them for their recent visit
- Expresses you'd love to see them again
- Keeps it brief and friendly (1-2 sentences)
- No pressure, just genuine invitation
- Tone: friendly, welcoming, casual

Return ONLY the message text, no quotes, no JSON.
PROMPT,

            WinBackCustomer::SEGMENT_FAILED_LEAD => <<<'PROMPT'
You are a business owner reaching out to someone who showed interest but never visited.
Write a gentle, no-pressure message that:
- Acknowledges their interest
- Offers to help if they're still interested
- Keeps it brief and friendly (1-2 sentences)
- No pressure, just genuine offer to help
- Tone: friendly, helpful, no-pressure

Return ONLY the message text, no quotes, no JSON.
PROMPT,
        ];

        return $prompts[$segment] ?? $prompts[WinBackCustomer::SEGMENT_DORMANT];
    }

    /**
     * Build user prompt with customer context
     */
    protected function buildUserPrompt(WinBackCustomer $customer, string $segment): string
    {
        $context = [];
        
        if ($customer->name) {
            $context[] = "Customer Name: {$customer->name}";
        }
        
        if ($customer->last_visit_date) {
            $daysAgo = \Carbon\Carbon::parse($customer->last_visit_date)->diffInDays(now());
            $context[] = "Last visit: {$daysAgo} days ago";
        }
        
        if ($customer->visit_count > 0) {
            $context[] = "Total visits: {$customer->visit_count}";
        }
        
        if ($customer->lifetime_value > 0) {
            $context[] = "Lifetime value: $" . number_format($customer->lifetime_value, 2);
        }
        
        if ($customer->total_spend > 0) {
            $context[] = "Total spend: $" . number_format($customer->total_spend, 2);
        }

        $contextString = implode("\n", $context);
        
        return "Generate a personalized win-back message for this customer:\n\n{$contextString}\n\nSegment: {$segment}\n\nMake it feel personal and human, not automated.";
    }

    /**
     * Get default fallback message
     */
    protected function getDefaultMessage(WinBackCustomer $customer, string $segment): string
    {
        $name = $customer->name ? "Hey {$customer->name}, " : "Hey, ";
        
        $messages = [
            WinBackCustomer::SEGMENT_LOST => "{$name}we haven't seen you in a while — hope everything's going great! If you ever want to swing back, I added a small thank-you gift for your next visit.",
            WinBackCustomer::SEGMENT_DORMANT => "{$name}just checking in to see if we can help with anything. We appreciate you!",
            WinBackCustomer::SEGMENT_VIP => "{$name}we appreciate you so much! Here's a VIP perk you earned.",
            WinBackCustomer::SEGMENT_ONE_TIME => "{$name}thanks again for coming by recently — we'd love to see you again!",
            WinBackCustomer::SEGMENT_FAILED_LEAD => "{$name}still interested? We'd be happy to help — no pressure.",
        ];

        return $messages[$segment] ?? $messages[WinBackCustomer::SEGMENT_DORMANT];
    }
}

