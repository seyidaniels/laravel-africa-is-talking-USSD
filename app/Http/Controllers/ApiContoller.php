<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiContoller extends Controller
{
    public function getUser($phone) {
        
    $response = $this->getRequest("get_user/phone?phone=".$phone);

    if ($response->success) {
        return $response->data;
    }

    return false;

        
    }

    public function sendOtp ($user_id) {
        $response = $this->postRequest("otp/request", [
            'user_id' => $user_id
        ]);
        return $response;
    }

    public function confirmOtp($data) {
        return $this->postRequest("otp/confirm", $data);
    }

    public function makePurchase ($data) {

        return $this->postRequest("order/store", $data);
    }

    public function getRequest($url, $data = [])
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => config('credpal.API_URL').$url,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer ".config("credpal.secret_key"),
    ),
    ));

    $response = curl_exec($curl);

    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
   // echo "cURL Error #:" . $err;
    } else {
    return json_decode($response);

    }
}

    public function postRequest ($url, $data) {

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL =>  config('credpal.API_URL').$url,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer CP_YUWW6hfKr4sfK6KvGeArDaQGTJRKdYwKMYjUO61BvHBURK6o9yHN45YSrIZh8JYnZEl2ct1xiR2S6r706pwJhFXXo0Ypb7niSGEUR0XNoQdN0XaskbU0J6h1bZVVdSsJ",
             "Content-Type: application/json",
        ),
        ));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  return "cURL Error #:" . $err;
} else {
  return json_decode($response);
}
        
    }

}
