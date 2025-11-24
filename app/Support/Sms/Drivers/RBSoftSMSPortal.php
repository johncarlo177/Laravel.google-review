<?php

namespace App\Support\Sms\Drivers;

use Exception;

class RBSoftSMSPortal extends BaseDriver
{
    private string $server;
    private string $apiKey;

    public function slug()
    {
        return 'rbsoft-sms-portal';
    }

    public function doSend(string $to, string $text)
    {
        if (!$this->credentialsArePresent()) return;

        return $this->sendSingleMessage($to, $text);
    }

    private function credentialsArePresent()
    {
        return !empty($this->config('server')) && !empty($this->config('api_key'));
    }

    public function __construct()
    {
        if ($this->credentialsArePresent()) {
            $this->server = $this->config('server');
            $this->apiKey = $this->config('api_key');
        }
    }

    /**
     * @param string     $number      The mobile number where you want to send message.
     * @param string     $message     The message you want to send.
     * @param int|string $device      The ID of a device you want to use to send this message.
     * @param int        $schedule    Set it to timestamp when you want to send this message.
     * @param bool       $isMMS       Set it to true if you want to send MMS message instead of SMS.
     * @param string     $attachments Comma separated list of image links you want to attach to the message. Only works for MMS messages.
     * @param bool       $prioritize  Set it to true if you want to prioritize this message.
     *
     * @return array     Returns The array containing information about the message.
     * @throws Exception If there is an error while sending a message.
     */
    function sendSingleMessage($number, $message, $device = 0, $schedule = null, $isMMS = false, $attachments = null, $prioritize = false)
    {
        $url = $this->server . "/services/send.php";

        $postData = array(
            'number' => $number,
            'message' => $message,
            'schedule' => $schedule,
            'key' => $this->apiKey,
            'devices' => $device,
            'type' => $isMMS ? "mms" : "sms",
            'attachments' => $attachments,
            'prioritize' => $prioritize ? 1 : 0
        );

        return $this->sendRequest($url, $postData)["messages"][0];
    }


    function sendRequest($url, $postData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);
        if ($httpCode == 200) {
            $json = json_decode($response, true);
            if ($json == false) {
                if (empty($response)) {
                    throw new Exception("Missing data in request. Please provide all the required information to send messages.");
                } else {
                    throw new Exception($response);
                }
            } else {
                if ($json["success"]) {
                    return $json["data"];
                } else {
                    throw new Exception($json["error"]["message"]);
                }
            }
        } else {
            throw new Exception("HTTP Error Code : {$httpCode}");
        }
    }
}
