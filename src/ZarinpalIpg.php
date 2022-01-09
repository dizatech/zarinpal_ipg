<?php

namespace Dizatech\ZarinpalIpg;

use \Exception;
use \stdClass;

class ZarinpalIpg{
    protected $merchant_id;
    protected $http_client;

    public function __construct($args=[])
    {
        $this->merchant_id = $args['merchantId'];
        $this->http_client = new \GuzzleHttp\Client();
    }

    public function getToken($amount, $description, $redirect_address)
    {
        $result = new stdClass();
        try{
            $response = $this->http_client->request(
                'POST',
                'https://api.zarinpal.com/pg/v4/payment/request.json',
                [
                    'form_params'   => [
                        'merchant_id'   => $this->merchant_id,
                        'amount'        => $amount,
                        'description'   => $description,
                        'callback_url'  => $redirect_address
                    ]
                ]
            );
            if( $response->getStatusCode() == 200 ){
                $body = $response->getBody();
                $contents = $body->getContents();
                $contents = json_decode( $contents );

                if(
                    isset( $contents->data ) &&
                    is_object( $contents->data ) &&
                    isset( $contents->data->code ) &&
                    $contents->data->code == 100 &&
                    isset( $contents->data->authority )
                ){
                    $result->status = 'success';
                    $result->token = $contents->data->authority;
                }
                else{
                    $message = 'خطا در اتصال به درگاه پرداخت!';
                    if(
                        isset( $contents->errors ) &&
                        is_object( $contents->errors ) &&
                        isset( $contents->errors->code )
                    ){
                        $message .= " کد خطا: {$contents->errors->code}";
                    }
                    $result->status = 'error';
                    $result->message = $message;
                }
            }
            else{
                $result->status = 'error';
                $result->message = 'خطا در اتصال به درگاه پرداخت!';
            }
        }
        catch(Exception $exception){
            $result->status = 'error';
            $result->message = 'خطا در اتصال به درگاه پرداخت!';
        }

        return $result;
    }

    public function verifyRequest($amount, $token)
    {
        $result = new stdClass();
        try{
            $response = $this->http_client->request(
                'POST',
                'https://api.zarinpal.com/pg/v4/payment/verify.json',
                [
                    'json'   => [
                        'merchant_id'   => $this->merchant_id,
                        'amount'        => $amount,
                        'authority'     => $token
                    ]
                ]
            );

            if( $response->getStatusCode() == 200 ){
                $body = $response->getBody();
                $contents = $body->getContents();
                $contents = json_decode( $contents );

                if(
                    isset( $contents->data ) &&
                    is_object( $contents->data ) &&
                    isset( $contents->data->code ) &&
                    in_array( $contents->data->code, [100, 101] )
                ){
                    $result->status = 'success';
                    $result->ref_id = $contents->data->ref_id;
                }
                else{
                    $message = 'خطا در تایید پرداخت!';
                    if(
                        isset( $contents->errors ) &&
                        is_object( $contents->errors ) &&
                        isset( $contents->errors->code )
                    ){
                        $message .= " کد خطا: {$contents->errors->code}";
                    }
                    $result->status = 'error';
                    $result->message = $message;
                }
            }
            else{
                $result->status = 'error';
                $result->message = 'خطا در تایید تراکنش!';
            }
        }
        catch(Exception $exception){
            $result->status = 'error';
            $result->message = 'خطا در تایید تراکنش!';
        }

        return $result;
    }

    public function refundRequest($authorization, $token)
    {
        $result = new stdClass();
        try{
            $response = $this->http_client->request(
                'POST',
                'https://api.zarinpal.com/pg/v4/payment/refund.json',
                [
                    'headers'       => [
                        'authorization' => 'Bearer ' . $authorization
                    ],
                    'json'   => [
                        'merchant_id'   => $this->merchant_id,
                        'authority'     => $token,
                    ]
                ]
            );
            if( $response->getStatusCode() == 200 ){
                $body = $response->getBody();
                $contents = $body->getContents();
                $contents = json_decode( $contents );

                if(
                    isset( $contents->data ) &&
                    is_object( $contents->data ) &&
                    isset( $contents->data->code ) &&
                    $contents->data->code == 100 &&
                    isset( $contents->data->authority )
                ){
                    $result->status = 'success';
                    $result->token = $contents->data->authority;
                }
                else{
                    $message = 'خطا در اتصال به درگاه پرداخت!';
                    if(
                        isset( $contents->errors ) &&
                        is_object( $contents->errors ) &&
                        isset( $contents->errors->code )
                    ){
                        $message .= " کد خطا: {$contents->errors->code}";
                    }
                    $result->status = 'error';
                    $result->message = $message;
                }
            }
            else{
                $result->status = 'error';
                $result->message = 'خطا در انجام درخواست!';
            }
        }
        catch(Exception $exception){
            echo $exception->getMessage();
            $result->status = 'error';
            $result->message = 'خطا در انجام درخواست!';
        }

        return $result;
    }
}