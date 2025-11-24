<?php

namespace App\Support\PaymentProcessors\Api;

use Illuminate\Support\Facades\Log;
use Throwable;
use Yansongda\Pay\Pay;

class AlipayChina
{
    private $config;

    public function __construct(
        $app_id,
        $app_secret_cert,
        $app_public_cert_path,
        $alipay_public_cert_path,
        $alipay_root_cert_path,
        $return_url,
        $notify_url,
        $app_auth_token,
        $mode
    ) {
        $this->config = [
            'alipay' => [
                'default' => [
                    // 必填-支付宝分配的 app_id
                    'app_id' => $app_id,
                    // 必填-应用私钥 字符串或路径
                    'app_secret_cert' => $app_secret_cert,
                    // 必填-应用公钥证书 路径
                    'app_public_cert_path' => $app_public_cert_path,
                    // 必填-支付宝公钥证书 路径
                    'alipay_public_cert_path' => $alipay_public_cert_path,
                    // 必填-支付宝根证书 路径
                    'alipay_root_cert_path' => $alipay_root_cert_path,
                    'return_url' => $return_url,
                    'notify_url' => $notify_url,
                    // 选填-第三方应用授权token
                    'app_auth_token' => $app_auth_token,
                    // 选填-服务商模式下的服务商 id，当 mode 为 Pay::MODE_SERVICE 时使用该参数
                    'service_provider_id' => '',
                    // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SANDBOX, MODE_SERVICE
                    'mode' => $mode == 'live' ? Pay::MODE_NORMAL : Pay::MODE_SANDBOX,
                ],
            ],
            'logger' => [ // optional
                'enable' => false,
                'file' => './logs/alipay.log',
                'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
                'type' => 'single', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],
            'http' => [ // optional
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
                // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
            ],
        ];
    }

    public function createOrder($out_trade_no, $total_amount, $subject)
    {
        $result = $this->alipay()->web([
            'out_trade_no' => $out_trade_no,
            'total_amount' => $total_amount,
            'subject' => $subject
        ]);

        Log::debug('AlipayChina - createOrder: result ' . json_encode($result, JSON_PRETTY_PRINT));

        return $result->getBody();
    }

    private function alipay()
    {
        return Pay::alipay($this->config);
    }

    public function getNotifyCallbackData()
    {
        try {
            $data = $this->alipay()->callback();

            return $data;
        } catch (Throwable $th) {
        }

        return null;
    }
}
