<?php

namespace App\Http\Controllers;

use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Image;
use App\Models\ScannedCardDetail;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function analyze(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        try {

            //Scanner count Validation
            $user = $request->user();
            $subscriptions = $user->subscriptions;
            $subscriptionsPlans = SubscriptionPlan::whereIn('id', $subscriptions->pluck('subscription_plan_id')->toArray())->get();
            $cardScanCount = $subscriptionsPlans->sum('number_of_card_scans');
            $totalScansByUser = ScannedCardDetail::where('id', $user->id)->count();

            if($totalScansByUser >= $cardScanCount){
                return response()->json([
                    'success' => false,
                    'error' => "No more scan left with your plan",
                ], 500);
            }

            // Decode base64 image
            $imageData = base64_decode($request->image);
            $credentials = base_path("storage/app/google/quantum-theorem-474515-i6-bec25c20a92c.json");

            // Initialize Vision client
            $client = new ImageAnnotatorClient([
                'credentials' => $credentials,
            ]);

            // Create image object
            $image = (new Image())->setContent($imageData);

            // Create feature for text detection
            $feature = (new Feature())->setType(Feature\Type::TEXT_DETECTION);

            // Create AnnotateImageRequest
            $annotateRequest = (new AnnotateImageRequest())
                ->setImage($image)
                ->setFeatures([$feature]);

            // Wrap inside BatchAnnotateImagesRequest
            $batchRequest = (new BatchAnnotateImagesRequest())
                ->setRequests([$annotateRequest]);

            // Perform text detection
            $response = $client->batchAnnotateImages($batchRequest);
            $annotationResponses = $response->getResponses();

            $fullText = '';
            if (count($annotationResponses) > 0) {
                $textAnnotations = $annotationResponses[0]->getTextAnnotations();
                if (count($textAnnotations) > 0) {
                    $fullText = $textAnnotations[0]->getDescription();
                }
            }

            $client->close();

            // --- Extract structured data ---
            $structured = $this->extractDetails($fullText);

            return response()->json([
                'success' => true,
                'data' => [
                    'full_text' => $fullText,
                    'structured' => $structured,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function extractDetails(string $text)
    {
        $lines = array_filter(array_map('trim', explode("\n", $text)));
        $joined = implode(' ', $lines);

        // Extract phone numbers
        preg_match_all('/(\+91[\s-]?\d{10}|\b\d{10}\b)/', $text, $phones);
        $phones = array_unique($phones[0]);

        // Extract emails
        preg_match_all('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/', $text, $emails);
        $emails = array_unique($emails[0]);

        // Extract address-like text (basic heuristic)
        $addressCandidates = array_filter($lines, fn($line) =>
            preg_match('/(road|street|st\.|lane|block|nagar|tower|bhubaneswar|city|opp|near|plot|sector|vihar|guwahati|odisha|housing|board|mission)/i', $line)
        );

        // Guess person name (first line with mixed case, not all caps, no digits)
        $personName = '';
        foreach ($lines as $line) {
            if (!preg_match('/\d/', $line) && preg_match('/[A-Z][a-z]+/', $line) && !preg_match('/^[A-Z\s]+$/', $line)) {
                $personName = trim($line);
                break;
            }
        }

        // Guess business name - skip common false positives
        $ignore = ['PUSH', 'SELL', 'NOT', 'THE', 'FOR', 'PACK', 'CONTACT', 'SERVICE'];
        $businessName = '';
        foreach ($lines as $line) {
            if (
                preg_match('/^[A-Z0-9\s&\.\-]+$/', $line) &&
                strlen($line) > 3 &&
                !in_array(strtoupper(trim($line)), $ignore)
            ) {
                $businessName = trim($line);
                break;
            }
        }

        // If still not found, try a heuristic fallback
        if (!$businessName && count($lines) > 1) {
            $businessName = $lines[1];
        }

        return [
            'business_name' => $businessName ?: null,
            'person_name' => $personName ?: null,
            'phones' => array_values($phones),
            'emails' => array_values($emails),
            'address' => implode(', ', $addressCandidates),
        ];
    }

    public function get(Request $request)
    {
        try {
            $user = $request->user();
             $isAdmin = $user->roles->contains(fn($role) => strtolower($role->name) === 'admin');

            if ($isAdmin) {
                $scannedCardDetails = ScannedCardDetail::orderBy('id', 'DESC')->get();
            } else {
                $scannedCardDetails = ScannedCardDetail::where('user_id', $user->id)
                    ->orderBy('id', 'DESC')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data'    => $scannedCardDetails,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function store(Request $request){
        try {
            $scannedCardDetail                  = new ScannedCardDetail();
            $scannedCardDetail->user_id         = $request->user()->id;
            $scannedCardDetail->business_name   = $request->business_name;
            $scannedCardDetail->person_name     = $request->person_name;
            $scannedCardDetail->phones          = $request->phones;
            $scannedCardDetail->emails          = $request->emails;
            $scannedCardDetail->address         = $request->address;
            $scannedCardDetail->save();

            return response()->json([
                'success' => true,
                'data'    => $scannedCardDetail,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id){
        try {
            $scannedCardDetail                  = ScannedCardDetail::find($id);
            $scannedCardDetail->business_name   = $request->business_name;
            $scannedCardDetail->person_name     = $request->person_name;
            $scannedCardDetail->phones          = $request->phones;
            $scannedCardDetail->emails          = $request->emails;
            $scannedCardDetail->address         = $request->address;
            $scannedCardDetail->save();

            return response()->json([
                'success' => true,
                'data'    => $scannedCardDetail,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id){
        try {
            $scannedCardDetail = ScannedCardDetail::find($id);
            $scannedCardDetail->delete();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
