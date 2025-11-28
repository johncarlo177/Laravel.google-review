<?php

namespace App\Support\WinBack;

use App\Models\WinBackCustomer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WinBackCustomerImportService
{
    /**
     * Import customers from CSV file
     */
    public function importFromCsv(User $user, UploadedFile $file): array
    {
        $imported = 0;
        $errors = [];

        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle); // Skip header row

        while (($row = fgetcsv($handle)) !== false) {
            try {
                $data = array_combine($headers, $row);
                $this->createCustomer($user, $data);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row error: " . $e->getMessage();
                Log::error('CSV import error: ' . $e->getMessage());
            }
        }

        fclose($handle);

        return [
            'imported' => $imported,
            'errors' => $errors,
        ];
    }

    /**
     * Import customers from text list (one per line: name, email/phone, [last_visit_date], [total_spend], [visit_count], [lifetime_value])
     */
    public function importFromText(User $user, string $text): array
    {
        $lines = explode("\n", $text);
        $imported = 0;
        $errors = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            try {
                // Parse line: "Name, email@example.com, 2024-10-15, 250, 5, 500"
                // Format: name, email/phone, last_visit_date (optional), total_spend (optional), visit_count (optional), lifetime_value (optional)
                $parts = array_map('trim', explode(',', $line));
                
                if (count($parts) < 2) {
                    $errors[] = "Line format invalid: {$line} (needs at least: Name, Email/Phone)";
                    continue;
                }

                $data = [
                    'name' => $parts[0] ?? '',
                ];

                // Parse contact (email or phone)
                $contact = $parts[1] ?? '';
                if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
                    $data['email'] = $contact;
                } elseif (preg_match('/^\+?[\d\s\-\(\)]+$/', $contact)) {
                    $data['phone'] = $contact;
                } else {
                    $errors[] = "Invalid contact format: {$contact}";
                    continue;
                }

                // Parse optional fields
                if (isset($parts[2]) && !empty($parts[2])) {
                    // Try to parse as date first
                    try {
                        $date = Carbon::parse($parts[2]);
                        $data['last_visit_date'] = $date->format('Y-m-d');
                    } catch (\Exception $e) {
                        // If not a date, might be spend amount
                        if (is_numeric($parts[2])) {
                            $data['total_spend'] = floatval($parts[2]);
                        }
                    }
                }

                if (isset($parts[3]) && !empty($parts[3]) && is_numeric($parts[3])) {
                    $data['total_spend'] = floatval($parts[3]);
                }

                if (isset($parts[4]) && !empty($parts[4]) && is_numeric($parts[4])) {
                    $data['visit_count'] = intval($parts[4]);
                }

                if (isset($parts[5]) && !empty($parts[5]) && is_numeric($parts[5])) {
                    $data['lifetime_value'] = floatval($parts[5]);
                }

                // If we have a date in parts[2] but it wasn't parsed, try again
                if (isset($parts[2]) && !empty($parts[2]) && !isset($data['last_visit_date'])) {
                    try {
                        $date = Carbon::parse($parts[2]);
                        $data['last_visit_date'] = $date->format('Y-m-d');
                    } catch (\Exception $e) {
                        // Ignore if not a valid date
                    }
                }

                $this->createCustomer($user, $data);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Line error: {$line} - " . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'errors' => $errors,
        ];
    }

    /**
     * Import from email list
     */
    public function importFromEmailList(User $user, array $emails): array
    {
        $imported = 0;

        foreach ($emails as $email) {
            try {
                $this->createCustomer($user, ['email' => $email]);
                $imported++;
            } catch (\Exception $e) {
                Log::error('Email import error: ' . $e->getMessage());
            }
        }

        return ['imported' => $imported];
    }

    /**
     * Create or update customer from data
     */
    protected function createCustomer(User $user, array $data): WinBackCustomer
    {
        // Find existing customer by email or phone
        $customer = WinBackCustomer::where('user_id', $user->id)
            ->where(function ($query) use ($data) {
                if (!empty($data['email'])) {
                    $query->where('email', $data['email']);
                }
                if (!empty($data['phone'])) {
                    $query->orWhere('phone', $data['phone']);
                }
            })
            ->first();

        if (!$customer) {
            $customer = new WinBackCustomer();
            $customer->user_id = $user->id;
        }

        // Update fields
        $customer->name = $data['name'] ?? $customer->name;
        $customer->email = $data['email'] ?? $customer->email;
        $customer->phone = $data['phone'] ?? $customer->phone;
        
        // Parse dates
        if (!empty($data['last_visit_date'])) {
            try {
                $customer->last_visit_date = Carbon::parse($data['last_visit_date']);
            } catch (\Exception $e) {
                // Ignore invalid dates
            }
        }

        // Parse numbers
        $customer->total_spend = floatval($data['total_spend'] ?? $data['spend'] ?? $customer->total_spend ?? 0);
        $customer->visit_count = intval($data['visit_count'] ?? $data['visits'] ?? $customer->visit_count ?? 0);
        $customer->lifetime_value = floatval($data['lifetime_value'] ?? $data['ltv'] ?? $customer->lifetime_value ?? 0);
        $customer->customer_type = $data['customer_type'] ?? $data['type'] ?? $customer->customer_type;

        // If last_visit_date is provided but visit_count is 0, assume at least 1 visit
        if ($customer->last_visit_date && $customer->visit_count === 0) {
            $customer->visit_count = 1;
        }

        // Calculate days since last visit
        if ($customer->last_visit_date) {
            $customer->days_since_last_visit = Carbon::parse($customer->last_visit_date)->diffInDays(now());
        }

        // Store metadata
        $metadata = $customer->metadata ?? [];
        foreach ($data as $key => $value) {
            if (!in_array($key, ['name', 'email', 'phone', 'last_visit_date', 'total_spend', 'visit_count', 'lifetime_value', 'customer_type'])) {
                $metadata[$key] = $value;
            }
        }
        $customer->metadata = $metadata;

        $customer->save();

        return $customer;
    }
}

