<?php

namespace App\Helpers;

class SmsHelper
{
    public static function sendSms($phone, $message)
    {
        $url = env('TEXTSMS_URL');
        $postData = [
            'apikey' => env('TEXTSMS_API_KEY'),
            'partnerID' => env('TEXTSMS_PARTNER_ID'),
            'message' => $message,
            'shortcode' => env('TEXTSMS_SENDER_ID'),
            'mobile' => $phone,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);

        $output = curl_exec($ch);

        if (curl_errno($ch)) {
            $output = curl_error($ch);
        }

        curl_close($ch);

        return $output;
    }
}
