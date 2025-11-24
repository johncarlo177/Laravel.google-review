<?php

namespace App\Http\Controllers;

class TroubleshootController
{
    public function home()
    {
        return view('troubleshoot');
    }

    /**
     * Usage example:
     * curl -i -H "Authorization: Bearer 827382737" https://yourweb
     */
    public function checkAuthHeader()
    {

        $headers = getallheaders();
        $token = null;

        if (isset($headers['Authorization'])) {
            $authorizationHeader = $headers['Authorization'];
            $matches = array();
            if (preg_match('/Bearer (.+)/', $authorizationHeader, $matches)) {
                if (isset($matches[1])) {
                    $token = $matches[1];
                }
            }
        }

        if ($token) {

            $message = 'Authurization header found';
        } else {

            $message = 'Authurization header is NOT FOUND';
        }

        return [
            'success' => !empty($token),
            'message' => $message
        ];
    }

    public function showSuccessIfReachable()
    {
        return [
            'success' => true
        ];
    }
}
