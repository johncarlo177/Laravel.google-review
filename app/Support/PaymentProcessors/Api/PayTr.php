<?php

namespace App\Support\PaymentProcessors\Api;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayTr
{
    private $products = [];

    private $merchantId;
    private $merchantSalt;
    private $merchantKey;
    private bool $testMode;

    public function __construct($merchantId, $merchantSalt, $merchantKey, $testMode)
    {
        $this->merchantId = $merchantId;
        $this->merchantSalt = $merchantSalt;
        $this->merchantKey = $merchantKey;
        $this->testMode = $testMode;
    }

    public function withProduct($name, $price, $quantity)
    {
        $this->products[] = [
            $name, $price, $quantity
        ];

        return $this;
    }

    /**
     * @param string $merchantOid Custom id to consume in webhook
     */
    public function createPaymentToken(
        $customId,
        $userIp,
        $userEmail,
        $userName,
        $userPhone,
        $productDescription,
        $amount,
        $currency,
        $successUrl,
        $failUrl
    ) {
        $merchant_id     = $this->merchantId;
        $merchant_key     = $this->merchantKey;
        $merchant_salt    = $this->merchantSalt;

        $payment_amount    = $amount * 100;
        $merchant_oid = $customId;
        $user_address = "Turkey";
        $merchant_ok_url = $successUrl;
        $merchant_fail_url = $failUrl;
        $user_basket = "";
        #
        /* EXAMPLE $user_basket creation - You can duplicate arrays per each product
	$user_basket = base64_encode(json_encode(array(
		array("Sample Product 1", "18.00", 1), // 1st Product (Product Name - Unit Price - Piece)
		array("Sample Product 2", "33.25", 2), // 2nd Product (Product Name - Unit Price - Piece)
    	array("Sample Product 3", "45.42", 1)  // 3rd Product (Product Name - Unit Price - Piece)
	)));
	 */

        $user_basket = base64_encode(json_encode(array(
            array($productDescription, $payment_amount, 1), // 1st Product (Product Name - Unit Price - Piece)
        )));

        $user_ip = $userIp;
        $timeout_limit = "30";
        $debug_on = 1;
        $test_mode = $this->testMode;
        $no_installment    = 0;
        $max_installment = 0;

        $hash_str = $merchant_id . $user_ip . $merchant_oid . $userEmail . $payment_amount . $user_basket . $no_installment . $max_installment . $currency . $test_mode;

        $paytr_token = base64_encode(hash_hmac('sha256', $hash_str . $merchant_salt, $merchant_key, true));

        $post_vals = array(
            'merchant_id' => $merchant_id,
            'user_ip' => $user_ip,
            'merchant_oid' => $merchant_oid,
            'email' => $userEmail,
            'payment_amount' => $payment_amount,
            'paytr_token' => $paytr_token,
            'user_basket' => $user_basket,
            'debug_on' => $debug_on,
            'no_installment' => $no_installment,
            'max_installment' => $max_installment,
            'user_name' => $userName,
            'user_address' => $user_address,
            'user_phone' => $userPhone,
            'merchant_ok_url' => $merchant_ok_url,
            'merchant_fail_url' => $merchant_fail_url,
            'timeout_limit' => $timeout_limit,
            'currency' => $currency,
            'test_mode' => $test_mode
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        //XXX: DİKKAT: lokal makinanızda "SSL certificate problem: unable to get local issuer certificate" uyarısı alırsanız eğer
        //aşağıdaki kodu açıp deneyebilirsiniz. ANCAK, güvenlik nedeniyle sunucunuzda (gerçek ortamınızda) bu kodun kapalı kalması çok önemlidir!
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = @curl_exec($ch);

        if (curl_errno($ch))
            die("PAYTR IFRAME connection error. err:" . curl_error($ch));

        curl_close($ch);

        $result = json_decode($result, 1);

        if ($result['status'] == 'success')
            return $result;
        else
            return $result;
    }

    public function verifyWebhook($postData)
    {
        $post = $postData;

        $merchant_key     = $this->merchantKey;
        $merchant_salt    = $this->merchantSalt;

        $hash = base64_encode(hash_hmac('sha256', $post['merchant_oid'] . $merchant_salt . $post['status'] . $post['total_amount'], $merchant_key, true));

        if ($hash != $post['hash'])
            return false;

        return true;
    }
}
