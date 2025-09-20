<?php

namespace V3\App\Common\Utilities;

class BirthdayMessenger
{
    public static function send()
    {
        $apiKey = "TLgCGRozUXzaMdIfenydqBUyFVBEoWwdhunSqNmlfPtYKHqEDBYGYSqjixVuMu";
        $phone = "2349131613125";
        $message = "Happy Birthday, Mr Vee 🎉! From all of us at LinkSkool.";

        $url = "https://v3.api.termii.com/api/sms/send";

        $data = [
            "to" => $phone,
            "from" => "Digital Dreams Limited",
            "sms" => $message,
            "type" => "plain",
            "channel" => "generic",
            "api_key" => $apiKey
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

        $response = curl_exec($ch);
        curl_close($ch);

        echo $response;
    }
}
