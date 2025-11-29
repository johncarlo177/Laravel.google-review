<?php

namespace App\Http\Controllers;

use App\Models\FeedbackEntry;
use App\Models\QRCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserFeedbackController extends Controller
{
    /**
     * Show list of feedback entries for the logged-in user
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

        return view('user.feedback.index', [
            'feedbacks' => $feedbacks,
            'analytics' => $analytics,
            'filters' => $request->only(['status', 'urgency', 'rating']),
        ]);
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
        ];
    }
}

