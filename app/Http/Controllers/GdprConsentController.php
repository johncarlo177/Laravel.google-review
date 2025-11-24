<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Input;
use DateTime;

class GdprConsentController extends Controller
{
    //
    public function setCookieConsent(Request $request)
    {

        $clientIp = $request->getClientIp(); //getVisIpAddr();
        //$geo = geoip($clientIp);
        $data = $request->all();

        $cookie_consent_name = $data['cookie_consent_name'];
        $consent_id = $data['consent_id'];
        $cookie_consent_value = $data['cookie_consent_value'];
        $consent_necessary = $data['consent_necessary'];
        $consent_preferences = $data['consent_preferences'];
        $consent_statistics = $data['consent_statistics'];
        $consent_marketing = $data['consent_marketing'];
        $consent_unclassified = $data['consent_unclassified'];
        $consent_url = $data['consent_url'];
        $cookie_consent_lifetime = $data['cookie_consent_lifetime'];
        $consent_client_datetime = $data['consent_client_datetime'];

        $consent_client_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $consent_client_datetime);

        // Encrypt
        $consent_ip_anonymized = openssl_encrypt($clientIp, 'AES-256-ECB', '3103');

        // Decrypt
        //$consent_ip_anonymized = openssl_decrypt($consent_ip_anonymized, 'AES-256-ECB', '3103');

        $consent_user_agent = $_SERVER['HTTP_USER_AGENT'];
        $consent_country_isocode =  'N/A'; //$geo['iso_code']; // we do not need the country
        $consent_server_datetime = new DateTime();

        $id = DB::table('gdpr_consents')->insertGetId(
            [
                'cookie_consent_name' => $cookie_consent_name,
                'consent_id' => $consent_id,
                'cookie_consent_value' => $cookie_consent_value,
                'consent_necessary' => $consent_necessary,
                'consent_preferences' => $consent_preferences,
                'consent_statistics' => $consent_statistics,
                'consent_marketing' => $consent_marketing,
                'consent_unclassified' => $consent_unclassified,
                'consent_url' => $consent_url,
                'consent_ip_anonymized' => $consent_ip_anonymized,
                'consent_country_isocode' => $consent_country_isocode,
                'consent_user_agent' => $consent_user_agent,
                'cookie_consent_lifetime' => $cookie_consent_lifetime,
                'consent_client_datetime' => $consent_client_datetime,
                'consent_server_datetime' => $consent_server_datetime,
                'created_at' => $consent_server_datetime,
            ]
        );


        return response()->json(array(
            'cookie_consent_name' => $cookie_consent_name,
            'consent_id' => $consent_id,
            'cookie_consent_value' => $cookie_consent_value,
            'consent_necessary' => $consent_necessary,
            'consent_preferences' => $consent_preferences,
            'consent_statistics' => $consent_statistics,
            'consent_marketing' => $consent_marketing,
            'consent_unclassified' => $consent_unclassified,
            'consent_url' => $consent_url,
            'consent_ip_anonymized' => $consent_ip_anonymized,
            'consent_country_isocode' => $consent_country_isocode,
            'consent_user_agent' => $consent_user_agent,
            'cookie_consent_lifetime' => $cookie_consent_lifetime,
            'consent_client_datetime' => $consent_client_datetime,
            'consent_server_datetime' => $consent_server_datetime,
            'id' => $id,
            //'decrypt' => Crypt::decryptString($consent_ip_anonymized)

        )/*array($data)*/, 200);
    }
}
