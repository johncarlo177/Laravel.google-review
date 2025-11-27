<?php

namespace App\Http\Controllers;

use App\Models\FeedbackEntry;
use App\Models\FeedbackAuditLog;
use App\Models\QRCode;
use App\Support\Sms\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StaffFeedbackController extends Controller
{
    /**
     * Show list of feedback entries
     */
    public function index(Request $request)
    {
        // Use sanctum guard since the app uses cookie-based Sanctum auth
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }
        
        // Get QR codes owned by user
        $qrcodeIds = QRCode::where('user_id', $user->id)
            ->where('type', 'business-review')
            ->pluck('id');

        // Debug: Log for troubleshooting
        \Log::info('Staff Feedback Index', [
            'user_id' => $user->id,
            'qrcode_ids_count' => $qrcodeIds->count(),
            'qrcode_ids' => $qrcodeIds->toArray(),
        ]);

        $query = FeedbackEntry::whereIn('qrcode_id', $qrcodeIds)
            ->with('qrcode')
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('urgency') && $request->urgency !== 'all') {
            $query->where('urgency', $request->urgency);
        }

        if ($request->has('rating') && $request->rating !== 'all') {
            $query->where('rating', $request->rating);
        }

        $feedbacks = $query->paginate(20);

        // Analytics
        $analytics = $this->getAnalytics($qrcodeIds);

        return view('staff.feedback.index', [
            'feedbacks' => $feedbacks,
            'analytics' => $analytics,
            'filters' => $request->only(['status', 'urgency', 'rating']),
        ]);
    }

    /**
     * Show single feedback for review
     */
    public function show($id)
    {
        $user = Auth::guard('sanctum')->user();
        
        $feedback = FeedbackEntry::with(['qrcode', 'auditLogs'])
            ->whereHas('qrcode', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($id);

        return view('staff.feedback.show', [
            'feedback' => $feedback,
        ]);
    }

    /**
     * Approve and send reply to customer
     */
    public function approveReply(Request $request, $id)
    {
        $user = Auth::guard('sanctum')->user();
        
        $feedback = FeedbackEntry::whereHas('qrcode', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($id);

        $request->validate([
            'reply' => 'required|string|max:1000',
        ]);

        try {
            $reply = $request->input('reply');
            
            // Update feedback with approved reply
            $feedback->update([
                'gpt_reply' => $reply,
                'status' => FeedbackEntry::STATUS_RESOLVED,
            ]);

            // Log action
            FeedbackAuditLog::create([
                'feedback_entry_id' => $feedback->id,
                'action' => 'reply_approved',
                'details' => ['reply' => $reply],
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);

            // Send reply to customer if contact info provided
            if ($feedback->contact) {
                $this->sendReplyToCustomer($feedback, $reply);
            }

            return redirect()->route('staff.feedback.show', $id)
                ->with('success', 'Reply sent to customer successfully.');

        } catch (\Exception $e) {
            Log::error('Error approving reply: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to send reply. Please try again.']);
        }
    }

    /**
     * Mark feedback as resolved
     */
    public function markResolved($id)
    {
        $user = Auth::guard('sanctum')->user();
        
        $feedback = FeedbackEntry::whereHas('qrcode', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($id);

        $feedback->update(['status' => FeedbackEntry::STATUS_RESOLVED]);

        FeedbackAuditLog::create([
            'feedback_entry_id' => $feedback->id,
            'action' => 'marked_resolved',
            'details' => [],
            'user_id' => $user->id,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Feedback marked as resolved.');
    }

    /**
     * Export feedback data
     */
    public function export(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        
        $qrcodeIds = QRCode::where('user_id', $user->id)
            ->where('type', 'business-review')
            ->pluck('id');

        $feedbacks = FeedbackEntry::whereIn('qrcode_id', $qrcodeIds)
            ->with('qrcode')
            ->orderBy('created_at', 'desc')
            ->get();

        $csv = "ID,Rating,Comment,Contact,Sentiment,Urgency,Category,Status,Created At,Business\n";

        foreach ($feedbacks as $feedback) {
            $csv .= sprintf(
                "%d,%d,\"%s\",\"%s\",%s,%s,%s,%s,%s,\"%s\"\n",
                $feedback->id,
                $feedback->rating,
                str_replace('"', '""', $feedback->comment ?? ''),
                $feedback->contact ?? '',
                $feedback->sentiment ?? '',
                $feedback->urgency ?? '',
                $feedback->category ?? '',
                $feedback->status,
                $feedback->created_at->format('Y-m-d H:i:s'),
                str_replace('"', '""', $feedback->qrcode->name ?? '')
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="feedback-export-' . date('Y-m-d') . '.csv"');
    }

    /**
     * Get analytics data
     */
    private function getAnalytics($qrcodeIds)
    {
        $total = FeedbackEntry::whereIn('qrcode_id', $qrcodeIds)->count();
        $resolved = FeedbackEntry::whereIn('qrcode_id', $qrcodeIds)
            ->where('status', FeedbackEntry::STATUS_RESOLVED)
            ->count();
        $escalated = FeedbackEntry::whereIn('qrcode_id', $qrcodeIds)
            ->where('status', FeedbackEntry::STATUS_ESCALATED)
            ->count();

        $avgRating = FeedbackEntry::whereIn('qrcode_id', $qrcodeIds)->avg('rating');

        $byCategory = FeedbackEntry::whereIn('qrcode_id', $qrcodeIds)
            ->whereNotNull('category')
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category');

        $byUrgency = FeedbackEntry::whereIn('qrcode_id', $qrcodeIds)
            ->whereNotNull('urgency')
            ->selectRaw('urgency, count(*) as count')
            ->groupBy('urgency')
            ->get()
            ->pluck('count', 'urgency');

        $recentResolved = FeedbackEntry::whereIn('qrcode_id', $qrcodeIds)
            ->where('status', FeedbackEntry::STATUS_RESOLVED)
            ->where('updated_at', '>=', now()->subDays(7))
            ->count();

        return [
            'total' => $total,
            'resolved' => $resolved,
            'escalated' => $escalated,
            'avg_rating' => round($avgRating, 2),
            'recovery_rate' => $total > 0 ? round(($recentResolved / $total) * 100, 2) : 0,
            'by_category' => $byCategory,
            'by_urgency' => $byUrgency,
        ];
    }

    /**
     * Send reply to customer
     */
    private function sendReplyToCustomer(FeedbackEntry $feedback, string $reply)
    {
        $contact = $feedback->contact;
        
        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            // Send email
            try {
                Mail::raw($reply, function ($message) use ($contact, $feedback) {
                    $message->to($contact)
                        ->subject('Response to your feedback');
                });
            } catch (\Exception $e) {
                Log::error('Error sending email reply: ' . $e->getMessage());
            }
        } elseif (preg_match('/^\+?[1-9]\d{1,14}$/', $contact)) {
            // Send SMS using the SMS service
            try {
                $smsService = new SmsService();
                $result = $smsService->send($contact, $reply);
                
                if ($result) {
                    Log::info('SMS reply sent successfully', ['to' => $contact]);
                } else {
                    Log::warning('SMS reply failed to send', ['to' => $contact]);
                }
            } catch (\Exception $e) {
                Log::error('Error sending SMS reply: ' . $e->getMessage());
            }
        }
    }
}
