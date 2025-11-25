<?php

namespace App\Http\Controllers;

use App\Models\QRCode;
use App\Support\QRCodeTypes\BusinessReview\RedirectManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required_if:rating,1,2,3|string|max:5000',
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

            // Save feedback to database
            $feedback = DB::table('feedback_entries')->insert([
                'qrcode_id' => $qrcode->id,
                'rating' => $request->input('rating'),
                'comment' => $request->input('comment'),
                'contact' => $request->input('contact'),
                'status' => 'new',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Get Google review URL
            $googleReviewUrl = $this->getGoogleReviewUrl($qrcode);

            // Show success page
            return view('feedback.success', [
                'qrcode' => $qrcode,
                'rating' => $request->input('rating'),
                'googleReviewUrl' => $googleReviewUrl,
            ]);

        } catch (\Exception $e) {
            Log::error('Feedback submission error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred. Please try again.'])->withInput();
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

