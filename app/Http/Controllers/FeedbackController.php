<?php

namespace App\Http\Controllers;

use App\Models\QRCode;
use App\Models\FeedbackEntry;
use App\Models\FeedbackAuditLog;
use App\Support\QRCodeTypes\BusinessReview\RedirectManager;
use App\Support\Feedback\GptFeedbackService;
use App\Notifications\FeedbackEscalated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class FeedbackController extends Controller
{
    /**
     * Show the feedback rating page
     * Route: GET /feedback/{token}
     */
    public function showRatingPage($token)
    {
        try {
            // Decode token to get QRCode ID
            $qrcodeId = $this->decodeToken($token);
            
            if (!$qrcodeId) {
                abort(404, 'Invalid feedback link');
            }

            $qrcode = QRCode::find($qrcodeId);

            if (!$qrcode || $qrcode->type !== 'business-review') {
                abort(404, 'QR Code not found or invalid type');
            }

            // Check if QR code is active
            if ($qrcode->archived || $qrcode->status === QRCode::STATUS_DISABLED) {
                abort(404, 'This feedback link is no longer available');
            }

            // Get Google review URL if available
            $googleReviewUrl = $this->getGoogleReviewUrl($qrcode);

            return view('feedback.rating', [
                'qrcode' => $qrcode,
                'token' => $token,
                'googleReviewUrl' => $googleReviewUrl,
                'businessName' => $qrcode->data->businessName ?? 'Business',
            ]);

        } catch (\Exception $e) {
            Log::error('Feedback rating page error: ' . $e->getMessage());
            abort(404, 'Invalid feedback link');
        }
    }

    /**
     * Submit feedback form
     * Route: POST /feedback/{token}/submit
     */
    public function submit(Request $request, $token)
    {
        // Rate limiting
        $key = 'feedback:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return back()->withErrors(['error' => 'Too many submissions. Please try again later.'])->withInput();
        }
        RateLimiter::hit($key, 3600); // 10 attempts per hour

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:5000',
            'contact' => 'nullable|string|max:255',
        ]);

        try {
            // Decode token to get QRCode ID
            $qrcodeId = $this->decodeToken($token);
            
            if (!$qrcodeId) {
                return back()->withErrors(['error' => 'Invalid feedback link'])->withInput();
            }

            $qrcode = QRCode::find($qrcodeId);

            if (!$qrcode || $qrcode->type !== 'business-review') {
                return back()->withErrors(['error' => 'QR Code not found'])->withInput();
            }

            $rating = $request->input('rating');
            $comment = $request->input('comment');
            $contact = $request->input('contact');

            // Create feedback entry
            $feedback = FeedbackEntry::create([
                'qrcode_id' => $qrcode->id,
                'rating' => $rating,
                'comment' => $comment ?: null,
                'contact' => $contact ?: null,
                'status' => FeedbackEntry::STATUS_NEW,
            ]);

            // Log feedback creation
            $this->logAudit($feedback, 'feedback_submitted', [
                'rating' => $rating,
                'has_comment' => !empty($comment),
                'has_contact' => !empty($contact),
            ], $request->ip());

            // GPT Classification (for all ratings)
            $gptService = new GptFeedbackService();
            $classification = $gptService->classifyFeedback($rating, $comment);

            // Update feedback with classification
            $feedback->update([
                'sentiment' => $classification['sentiment'],
                'urgency' => $classification['urgency'],
                'category' => $classification['category'],
                'escalate' => $classification['escalate'],
            ]);

            // Log classification
            $this->logAudit($feedback, 'classified', $classification, $request->ip());

            // Handle 4-5 star feedback (positive)
            if ($rating >= 4) {
                $googleReviewUrl = $this->getGoogleReviewUrl($qrcode);
                
                return view('feedback.success_positive', [
                    'qrcode' => $qrcode,
                    'rating' => $rating,
                    'googleReviewUrl' => $googleReviewUrl,
                ]);
            }

            // Handle 1-3 star feedback (negative)
            // Generate GPT reply and recovery plan
            $replyData = $gptService->generateReply($feedback);
            $recoveryPlan = $gptService->generateRecoveryPlan($feedback);

            $feedback->update([
                'gpt_reply' => $replyData['reply'],
                'gpt_next_step' => $replyData['next_step'],
                'gpt_actions' => $recoveryPlan,
            ]);

            // Log reply generation
            $this->logAudit($feedback, 'reply_generated', [
                'reply' => $replyData['reply'],
                'next_step' => $replyData['next_step'],
            ], $request->ip());

            // Check for escalation
            if ($feedback->needsEscalation()) {
                $this->handleEscalation($feedback);
            }

            $googleReviewUrl = $this->getGoogleReviewUrl($qrcode);

            return view('feedback.success_negative', [
                'qrcode' => $qrcode,
                'rating' => $rating,
                'googleReviewUrl' => $googleReviewUrl,
                'feedback' => $feedback,
            ]);

        } catch (\Exception $e) {
            Log::error('Feedback submission error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred. Please try again.'])->withInput();
        }
    }

    /**
     * Handle escalation for urgent feedback
     */
    private function handleEscalation(FeedbackEntry $feedback)
    {
        try {
            $feedback->update(['status' => FeedbackEntry::STATUS_ESCALATED]);

            // Log escalation
            $this->logAudit($feedback, 'escalated', [
                'reason' => $feedback->escalate ? 'escalate flag set' : 'high urgency',
            ]);

            // Notify business owner/manager
            $qrcode = $feedback->qrcode;
            if ($qrcode && $qrcode->user) {
                $qrcode->user->notify(new FeedbackEscalated($feedback));
            }

        } catch (\Exception $e) {
            Log::error('Escalation handling error: ' . $e->getMessage());
        }
    }

    /**
     * Log audit action
     */
    private function logAudit(FeedbackEntry $feedback, string $action, array $details = [], ?string $ipAddress = null)
    {
        try {
            FeedbackAuditLog::create([
                'feedback_entry_id' => $feedback->id,
                'action' => $action,
                'details' => $details,
                'user_id' => auth()->id(),
                'ip_address' => $ipAddress ?? request()->ip(),
            ]);
        } catch (\Exception $e) {
            Log::error('Audit log error: ' . $e->getMessage());
        }
    }

    /**
     * Decode token to get QRCode ID
     */
    private function decodeToken($token)
    {
        try {
            $decrypted = Crypt::decryptString($token);
            return (int) $decrypted;
        } catch (\Exception $e) {
            // Try to use token as slug first (for backward compatibility)
            $redirect = \App\Models\QRCodeRedirect::whereSlug($token)->first();
            if ($redirect && $redirect->qrcode) {
                return $redirect->qrcode->id;
            }
            return null;
        }
    }

    /**
     * Get Google review URL for the QR code
     */
    private function getGoogleReviewUrl(QRCode $qrcode)
    {
        try {
            return RedirectManager::withQRCode($qrcode)->getFinalReviewUrl();
        } catch (\Exception $e) {
            Log::warning('Could not generate Google review URL: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate a token for a QR code (helper method for generating links)
     */
    public static function generateToken(QRCode $qrcode)
    {
        return Crypt::encryptString($qrcode->id);
    }

    /**
     * Generate feedback URL for a QR code
     */
    public static function generateFeedbackUrl(QRCode $qrcode)
    {
        $token = self::generateToken($qrcode);
        return url("/feedback/{$token}");
    }
}

