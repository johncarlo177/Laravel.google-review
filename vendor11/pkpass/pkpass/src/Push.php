<?php

namespace PKPass;


class Push { 

    /**
     * Bundle ID PassKit
     *
     * @var string
     */
    protected string $pushBundleIdPush = '';

    /**
     * Key ID (из Apple Developer Portal)
     *
     * @var string
     */
    protected string $pushTokenId = '';

    /**
     * Team ID
     *
     * @var string
     */
    protected string $pushTeamId = '';

    /**
     * certificate p8
     *
     * @var string
     */
    protected string $pushAuthCertificatePush = '';
    /**
     * production push
     *
     * @var boolean
     */
    protected bool $pushProduction = true;

    public function __construct(
        ?string $pushBundleIdPush = null,
        ?string $pushTokenId = null,
        ?string $pushTeamId = null,
        ?string $pushAuthCertificatePush = null,
        ?bool $pushProduction = null
        )
    {
        if ($pushAuthCertificatePush) {
            $this->pushAuthCertificatePush = $pushAuthCertificatePush;
        }

        if ($pushBundleIdPush) {
            $this->pushBundleIdPush = $pushBundleIdPush;
        }

        if ($pushTokenId) {
            $this->pushTokenId = $pushTokenId;
        }

        if ($pushTeamId) { 
            $this->pushTeamId = $pushTeamId;
        }

        if ($pushProduction) {
            $this->pushProduction = $pushProduction;
        }
    }


    public function push(string $deviceToken, string $title, string $body): array
    {
        if (!$this->pushAuthCertificatePush || !$this->pushBundleIdPush || !$this->pushTokenId || !$this->pushTeamId) {
            throw new PKPassException("Push configuration is incomplete.");
        }

        $authKeyPath = $this->pushAuthCertificatePush;
        if (!str_starts_with($authKeyPath, '/')) {
            $authKeyPath = __DIR__ . '/Certificate/' . $authKeyPath;
        }

        if (!file_exists($authKeyPath)) {
            throw new PKPassException("Auth key file not found at: $authKeyPath");
        }

        $tokenKey = openssl_pkey_get_private(file_get_contents($authKeyPath));
        if (!$tokenKey) {
            throw new PKPassException("Invalid auth key provided.");
        }

        // JWT Header + Payload
        $jwtHeader = [
            'alg' => 'ES256',
            'kid' => $this->pushTokenId,   // Key ID (из Apple Developer Portal)
        ];
        $jwtPayload = [
            'iss' => $this->pushTeamId,    // Team ID
            'iat' => time(),
        ];

        // Base64Url helper
        $b64 = fn($raw, $json = false) => str_replace('=', '', strtr(base64_encode($json ? json_encode($raw) : $raw), '+/', '-_'));

        $rawTokenData = $b64($jwtHeader, true) . '.' . $b64($jwtPayload, true);

        if (!openssl_sign($rawTokenData, $signature, $tokenKey, OPENSSL_ALGO_SHA256)) {
            throw new PKPassException("Unable to sign JWT with provided key.");
        }

        $signature = '';
        openssl_sign($rawTokenData, $signature, $tokenKey, OPENSSL_ALGO_SHA256);
        $jwt = $rawTokenData . '.' . $b64($signature);

        // Notification Payload
        $notificationPayload = [
            'aps' => [
                'alert' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'sound' => 'default',
            ],
        ];

        $endpoint = $this->pushProduction
            ? 'https://api.push.apple.com/3/device'
            : 'https://api.development.push.apple.com/3/device';

        $url = "$endpoint/$deviceToken";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($notificationPayload),
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'content-type: application/json',
                "authorization: bearer $jwt",
                "apns-topic: {$this->pushBundleIdPush}", // Bundle ID PassKit
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new PKPassException("cURL error: $err");
        }

        if ($httpCode !== 200) {
            throw new PKPassException("APNs returned HTTP $httpCode: $response");
        }

        return [
            'status' => $httpCode,
            'response' => $response,
        ];
    }

}
