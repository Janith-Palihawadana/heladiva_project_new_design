<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use PhpParser\JsonDecoder;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SMSServices
{
    public static function authenticationPhoneOtp($phoneNumber,$otp)
    {
        $msg = 'Your SELF MASTER OTP is ' . $otp->token;
        $response = self::sendSMS($phoneNumber, $msg);
        return json_decode($response, true);
    }

    public static function userValidate($phoneNumber,$otp)
    {
        $msg = 'Your SELF MASTER OTP is ' . $otp->token;
        $response = self::sendSMS($phoneNumber, $msg);
        return json_decode($response, true);
    }

    public static function studentPhoneOtp($phoneNumber,$otp)
    {
        $msg = 'Your SELF MASTER OTP is ' . $otp->token;
        $response = self::sendSMS($phoneNumber, $msg);
        return json_decode($response, true);
    }

    public static function deleteStudentSMS($phoneNumber)
    {
        $msg = 'Your SELF MASTER account has been deactivated. Please contact the administrator for more information.';
        $response = self::sendSMS($phoneNumber, $msg);
        return json_decode($response, true);
    }

    public static function studentPasswordChangeSMS($password,$phoneNumber)
    {
        $msg = 'Your SELF MASTER account password has been updated. Here is your new Password: ' . $password;
        $response = self::sendSMS($phoneNumber, $msg);
        return json_decode($response, true);
    }

    public static function verifyPhoneNumberOtp($request){
        return (new \Ichtrojan\Otp\Otp)->validate($request['phone_number'], $request['otp_code']);
    }

    private static function sendSMS($phone, $message)
    {
        $curl = curl_init();
        $api_token = 'jI0P1701173953';
        $api_key = 'xkFhUwfmpWQ8XPVssK47c9X6ShFvcVNj';
        $url = 'http://cloud.websms.lk/smsAPI?sendsms&apikey='.$api_key.'&apitoken='.$api_token.'&type=sms&from=Go4Eats&to='.$phone.'&text=' . urlencode($message);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: PHPSESSID=25e8db36facfe6126e71ad835ada878b'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

}
