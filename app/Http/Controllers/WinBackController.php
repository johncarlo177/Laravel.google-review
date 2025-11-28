<?php

namespace App\Http\Controllers;

use App\Models\WinBackCustomer;
use App\Models\WinBackCampaign;
use App\Models\WinBackSegment;
use App\Models\WinBackMessage;
use App\Models\WinBackResponse;
use App\Support\WinBack\WinBackSegmentationService;
use App\Support\WinBack\WinBackMessageGeneratorService;
use App\Support\WinBack\WinBackCustomerImportService;
use App\Support\WinBack\WinBackMessageSenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WinBackController extends Controller
{
    protected WinBackSegmentationService $segmentationService;
    protected WinBackMessageGeneratorService $messageGenerator;
    protected WinBackCustomerImportService $importService;
    protected WinBackMessageSenderService $senderService;

    public function __construct()
    {
        $this->segmentationService = new WinBackSegmentationService();
        $this->messageGenerator = new WinBackMessageGeneratorService();
        $this->importService = new WinBackCustomerImportService();
        $this->senderService = new WinBackMessageSenderService();
    }

    /**
     * Dashboard - Overview of win-back system
     */
    public function index(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // Get segment statistics
        $segmentStats = $this->segmentationService->getSegmentStats($user);

        // Get recent campaigns
        $campaigns = WinBackCampaign::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Calculate total revenue recovered
        $totalRevenue = WinBackCampaign::where('user_id', $user->id)
            ->sum('revenue_recovered');

        // Calculate total customers returned
        $totalReturned = WinBackCampaign::where('user_id', $user->id)
            ->sum('customers_returned');

        return view('winback.index', [
            'segmentStats' => $segmentStats,
            'campaigns' => $campaigns,
            'totalRevenue' => $totalRevenue,
            'totalReturned' => $totalReturned,
        ]);
    }

    /**
     * Import customers page
     */
    public function import()
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        return view('winback.import');
    }

    /**
     * Handle customer import
     */
    public function storeImport(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $request->validate([
            'import_type' => 'required|in:csv,text,email',
            'file' => 'required_if:import_type,csv|file|mimes:csv,txt',
            'text' => 'required_if:import_type,text|string',
            'emails' => 'required_if:import_type,email|string',
        ]);

        try {
            if ($request->import_type === 'csv' && $request->hasFile('file')) {
                $result = $this->importService->importFromCsv($user, $request->file('file'));
            } elseif ($request->import_type === 'text') {
                $result = $this->importService->importFromText($user, $request->text);
            } else {
                // Parse email list from textarea (comma or newline separated)
                $emailText = $request->emails ?? '';
                $emails = array_filter(array_map('trim', preg_split('/[,\n\r]+/', $emailText)));
                $result = $this->importService->importFromEmailList($user, $emails);
            }

            // Auto-segment after import
            $this->segmentationService->segmentCustomers($user);

            return redirect()->route('winback.index')
                ->with('success', "Successfully imported {$result['imported']} customers!");
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Show customers by segment
     */
    public function customers(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $segment = $request->get('segment', 'all');
        
        $query = WinBackCustomer::where('user_id', $user->id)
            ->where('is_active', true);

        if ($segment !== 'all') {
            $query->where('segment', $segment);
        }

        $customers = $query->orderBy('days_since_last_visit', 'desc')
            ->paginate(20);

        $segmentStats = $this->segmentationService->getSegmentStats($user);

        return view('winback.customers', [
            'customers' => $customers,
            'segmentStats' => $segmentStats,
            'currentSegment' => $segment,
        ]);
    }

    /**
     * Create campaign
     */
    public function createCampaign(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'segment' => 'required|string|in:lost,dormant,vip,one-time,failed-lead',
            'scheduled_at' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            // Get customers for segment
            $customers = $this->segmentationService->getCustomersBySegment($user, $request->segment);

            if ($customers->isEmpty()) {
                return back()->with('error', 'No customers found in this segment.');
            }

            // Create campaign
            $campaign = WinBackCampaign::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'segment_name' => $request->segment,
                'status' => $request->scheduled_at ? WinBackCampaign::STATUS_SCHEDULED : WinBackCampaign::STATUS_DRAFT,
                'scheduled_at' => $request->scheduled_at,
                'total_customers' => $customers->count(),
            ]);

            // Generate and queue messages
            $segment = WinBackSegment::where('user_id', $user->id)
                ->where('name', $request->segment)
                ->first();

            foreach ($customers as $customer) {
                // Generate AI message
                $messageContent = $this->messageGenerator->generateMessage($customer, $segment);
                
                // Determine channel
                $channel = $this->senderService->determineChannel($customer, $segment?->preferred_channel);

                // Create message
                $message = WinBackMessage::create([
                    'campaign_id' => $campaign->id,
                    'customer_id' => $customer->id,
                    'channel' => $channel,
                    'message_content' => $messageContent,
                    'recipient_email' => $customer->email,
                    'recipient_phone' => $customer->phone,
                    'status' => WinBackMessage::STATUS_PENDING,
                    'is_ai_generated' => true,
                ]);
            }

            DB::commit();

            return redirect()->route('winback.campaigns.show', $campaign->id)
                ->with('success', 'Campaign created! Messages are ready to send.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Campaign creation error: ' . $e->getMessage());
            return back()->with('error', 'Failed to create campaign: ' . $e->getMessage());
        }
    }

    /**
     * Send campaign messages
     */
    public function sendCampaign(WinBackCampaign $campaign)
    {
        $user = Auth::guard('sanctum')->user();
        
        if ($campaign->user_id !== $user->id) {
            abort(403);
        }

        $messages = WinBackMessage::where('campaign_id', $campaign->id)
            ->where('status', WinBackMessage::STATUS_PENDING)
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($messages as $message) {
            if ($this->senderService->send($message)) {
                $sent++;
            } else {
                $failed++;
            }
        }

        $campaign->update([
            'status' => WinBackCampaign::STATUS_ACTIVE,
            'started_at' => now(),
            'messages_sent' => $sent,
        ]);

        return back()->with('success', "Sent {$sent} messages. {$failed} failed.");
    }

    /**
     * Show campaign details
     */
    public function showCampaign(WinBackCampaign $campaign)
    {
        $user = Auth::guard('sanctum')->user();
        
        if ($campaign->user_id !== $user->id) {
            abort(403);
        }

        $messages = WinBackMessage::where('campaign_id', $campaign->id)
            ->with('customer', 'response')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('winback.campaign', [
            'campaign' => $campaign,
            'messages' => $messages,
        ]);
    }

    /**
     * Get revenue report
     */
    public function revenueReport(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $period = $request->get('period', 'month'); // month, week, year

        $query = WinBackCampaign::where('user_id', $user->id)
            ->where('status', WinBackCampaign::STATUS_COMPLETED);

        if ($period === 'week') {
            $query->where('completed_at', '>=', now()->subWeek());
        } elseif ($period === 'month') {
            $query->where('completed_at', '>=', now()->subMonth());
        } elseif ($period === 'year') {
            $query->where('completed_at', '>=', now()->subYear());
        }

        $campaigns = $query->get();

        $report = [
            'total_revenue' => $campaigns->sum('revenue_recovered'),
            'total_customers_returned' => $campaigns->sum('customers_returned'),
            'total_messages_sent' => $campaigns->sum('messages_sent'),
            'total_responses' => $campaigns->sum('responses_received'),
            'campaigns' => $campaigns,
        ];

        return view('winback.report', [
            'report' => $report,
            'period' => $period,
        ]);
    }

    /**
     * Run segmentation
     */
    public function segment()
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        try {
            $result = $this->segmentationService->segmentCustomers($user);
            
            return back()->with('success', "Segmented {$result['total_customers']} customers!");
        } catch (\Exception $e) {
            Log::error('Segmentation error: ' . $e->getMessage());
            return back()->with('error', 'Segmentation failed: ' . $e->getMessage());
        }
    }
}
