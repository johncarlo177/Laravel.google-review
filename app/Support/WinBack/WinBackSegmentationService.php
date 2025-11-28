<?php

namespace App\Support\WinBack;

use App\Models\WinBackCustomer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WinBackSegmentationService
{
    /**
     * Automatically segment all customers for a user
     */
    public function segmentCustomers(User $user): array
    {
        $customers = WinBackCustomer::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $segments = [
            WinBackCustomer::SEGMENT_LOST => [],
            WinBackCustomer::SEGMENT_DORMANT => [],
            WinBackCustomer::SEGMENT_VIP => [],
            WinBackCustomer::SEGMENT_ONE_TIME => [],
            WinBackCustomer::SEGMENT_FAILED_LEAD => [],
        ];

        foreach ($customers as $customer) {
            // Calculate days since last visit
            if ($customer->last_visit_date) {
                $customer->days_since_last_visit = Carbon::parse($customer->last_visit_date)->diffInDays(now());
                $customer->save();
            }

            $segment = $this->determineSegment($customer);
            $customer->segment = $segment;
            $customer->save();

            $segments[$segment][] = $customer->id;
        }

        return [
            'total_customers' => $customers->count(),
            'segments' => array_map('count', $segments),
            'segment_details' => $segments,
        ];
    }

    /**
     * Determine customer segment based on behavior
     */
    protected function determineSegment(WinBackCustomer $customer): string
    {
        $daysSinceVisit = $customer->days_since_last_visit ?? 999;

        // Failed Lead: Never visited but in system
        if (!$customer->last_visit_date && $customer->visit_count === 0) {
            return WinBackCustomer::SEGMENT_FAILED_LEAD;
        }

        // Lost Customer: 60+ days no visit
        if ($daysSinceVisit >= 60) {
            return WinBackCustomer::SEGMENT_LOST;
        }

        // Dormant Customer: 30-59 days
        if ($daysSinceVisit >= 30 && $daysSinceVisit < 60) {
            return WinBackCustomer::SEGMENT_DORMANT;
        }

        // VIP: High value or frequent visitor
        if ($customer->lifetime_value >= 500 || $customer->visit_count >= 10) {
            return WinBackCustomer::SEGMENT_VIP;
        }

        // One-Time Visitor: Only visited once
        if ($customer->visit_count === 1) {
            return WinBackCustomer::SEGMENT_ONE_TIME;
        }

        // Default to dormant if between visits but not quite lost
        if ($daysSinceVisit >= 15) {
            return WinBackCustomer::SEGMENT_DORMANT;
        }

        // Active customer (recent visit) - don't segment for win-back
        return WinBackCustomer::SEGMENT_DORMANT;
    }

    /**
     * Get customers by segment
     */
    public function getCustomersBySegment(User $user, string $segment): \Illuminate\Database\Eloquent\Collection
    {
        return WinBackCustomer::where('user_id', $user->id)
            ->where('segment', $segment)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get segment statistics
     */
    public function getSegmentStats(User $user): array
    {
        $stats = WinBackCustomer::where('user_id', $user->id)
            ->where('is_active', true)
            ->select('segment', DB::raw('count(*) as count'), DB::raw('sum(lifetime_value) as total_value'))
            ->groupBy('segment')
            ->get()
            ->keyBy('segment');

        return [
            'lost' => [
                'count' => $stats[WinBackCustomer::SEGMENT_LOST]->count ?? 0,
                'value' => $stats[WinBackCustomer::SEGMENT_LOST]->total_value ?? 0,
            ],
            'dormant' => [
                'count' => $stats[WinBackCustomer::SEGMENT_DORMANT]->count ?? 0,
                'value' => $stats[WinBackCustomer::SEGMENT_DORMANT]->total_value ?? 0,
            ],
            'vip' => [
                'count' => $stats[WinBackCustomer::SEGMENT_VIP]->count ?? 0,
                'value' => $stats[WinBackCustomer::SEGMENT_VIP]->total_value ?? 0,
            ],
            'one_time' => [
                'count' => $stats[WinBackCustomer::SEGMENT_ONE_TIME]->count ?? 0,
                'value' => $stats[WinBackCustomer::SEGMENT_ONE_TIME]->total_value ?? 0,
            ],
            'failed_lead' => [
                'count' => $stats[WinBackCustomer::SEGMENT_FAILED_LEAD]->count ?? 0,
                'value' => 0,
            ],
        ];
    }
}

