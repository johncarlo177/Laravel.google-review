<?php

namespace App\Support\BulkOperation\Support;

use App\Plugins\PluginManager;
use App\Support\System\Traits\WriteLogs;


class UrlSignature
{
    use WriteLogs;

    /**
     * Variable name in the bulk operation e.g. URL_SIGNATURE
     */
    private $variableName = '';

    private $originalUrl;

    public function __construct($variableName, $originalUrl)
    {
        $this->variableName = $variableName;

        $this->originalUrl = $originalUrl;
    }

    public function sign()
    {
        if (!$this->containsVariable()) return $this->originalUrl;

        $url = str_replace($this->variableName, '', $this->originalUrl);

        $secret = config('app.key');

        $signature = hash_hmac('sha256', $url, $secret);

        $separator = preg_match('/\?/', $url) ? '&' : '?';

        $url = sprintf('%s%ssignature=%s',  $url, $separator, $signature);

        $this->logDebug(
            'Signing %s secret = %s, signature = %s, final url = %s',
            $url,
            $secret,
            $signature,
            $url
        );

        return $url;
    }

    public static function validate($url)
    {
        $url = PluginManager::doFilter(
            PluginManager::FILTER_URL_SIGNATURE_VALIDATE_URL,
            $url
        );

        static::logDebug('Url = %s', $url);


        $query = parse_url($url, PHP_URL_QUERY);

        parse_str($query, $params);

        $userSignature = @$params['signature'];

        if (!$userSignature) return false;

        $unsignedUrl = preg_replace(
            sprintf('/.signature=%s/', $userSignature),
            '',
            $url
        );

        $secret = config('app.key');

        $expectedSignature = hash_hmac('sha256', $unsignedUrl, $secret);

        $valid = hash_equals($expectedSignature, $userSignature);

        if (!$valid) {
            static::logDebug(
                'Invalid URL signature: 
unsinged url = %s, 
expected = %s, 
actual = %s, 
secret = %s',
                $unsignedUrl,
                $expectedSignature,
                $userSignature,
                $secret,
            );
        }

        return $valid;
    }

    private function containsVariable()
    {
        return preg_match("/$this->variableName/", $this->originalUrl);
    }
}
