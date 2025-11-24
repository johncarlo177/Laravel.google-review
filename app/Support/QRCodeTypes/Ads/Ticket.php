<?php

namespace App\Support\QRCodeTypes\Ads;

use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class Ticket
{
    use WriteLogs;

    private Request $request;

    private $timeout;

    public function __construct()
    {
    }

    public static function withRequest(Request $request)
    {
        $instance = new static;

        $instance->request = $request;

        return $instance;
    }

    public function withTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function issue()
    {
        return redirect()->to(
            $this->request->path() . '?' . $this->build()
        );
    }

    public function shouldIssue()
    {
        return empty($this->time()) || !$this->timeIsInFuture();
    }

    public function allowsEntry()
    {
        return !empty($this->decode()) && $this->timeIsRecent();
    }

    private function build()
    {
        return http_build_query(array_merge($this->request->all(), [
            $this->paramName() => $this->encode()
        ]));
    }

    private function timeIsRecent()
    {
        $diff = time() - $this->time();

        return $diff < 30 && $diff > 0;
    }

    private function timeIsInFuture()
    {
        $diff = time() - $this->time();

        return $diff < 0;
    }

    private function encode()
    {
        $future = time() + $this->timeout;

        return Crypt::encryptString('ticket/' . $future);
    }

    private function time()
    {
        return @explode('/', $this->decode())[1];
    }

    private function decode()
    {
        $value = '';

        try {
            $value = Crypt::decryptString($this->getParam());
        } catch (Throwable $th) {
            // 
        }

        return $value;
    }

    private function getParam()
    {
        return $this->request->input($this->paramName());
    }

    private function paramName()
    {
        return substr(md5($this::class), 0, 5);
    }
}
