<?php
namespace App\Services;

use GuzzleHttp\Client;

class ParsGreenService
{
    protected $url;
    protected $apiKey;
    protected $smsNumber;

    public function __construct()
    {
        $this->url = 'https://sms.parsgreen.ir/Apiv2/Message/SendSms';
        $this->apiKey = '66FB518F-FA7A-41D3-84EC-6F653FDA1BB8';
        $this->smsNumber = '10001983';  // شماره ارسال پیامک
    }

    public function sendSms($mobile, $txt)
    {
        $client = new Client();

        $data = [
            'SmsBody' => $txt,
            'Mobiles' => [$mobile],
            'SmsNumber' => $this->smsNumber,
        ];

        try {
            $response = $client->post($this->url, [
                'json' => $data,
                'headers' => [
                    'Authorization' => 'BASIC APIKEY:' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ]
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            return null;
        }
    }

    public function sendOtp($mobile, $otp)
    {
        $sms = 'آموزشگاه آی تک، کد یکبارمصرف: ' . $otp;
        return $this->sendSms($mobile, $sms);
    }

    public function sendPassword($mobile, $user_name, $pass)
    {
        $sms = 'آی تک، خوش آمدید،' . "\n";
        $sms .= 'نام کاربری: ' . $user_name . "\n";
        $sms .= 'کلمه عبور: ' . $pass;
        return $this->sendSms($mobile, $sms);
    }

    public function sendResetPassword($mobile, $user_name, $pass)
    {
        $sms = 'آی تک،' . "\n";
        $sms .= 'نام کاربری: ' . $user_name . "\n";
        $sms .= 'کلمه عبور جدید: ' . $pass;
        return $this->sendSms($mobile, $sms);
    }
}
