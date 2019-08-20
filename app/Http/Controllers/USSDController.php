<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use App\Http\Controllers\MerchantController;
use Exception;
use Session;

class USSDController extends Controller
{
    private $credpalAPI;

    public function __construct( ApiContoller $credpalAPI)
    {
        $this->credpalAPI = $credpalAPI;
    }
    public function index (Request $request) {
        $sessionId   = $request["sessionId"];
        $serviceCode = $request["serviceCode"];
        $phoneNumber = $request["phoneNumber"];
        $text        = $request["text"];

        $response = "";

        // dd($text);
        $request->validate([
            'phoneNumber' => 'required'
        ]);
        // Gets correct phone number
        $phoneNumber = $this->formatNumber ($phoneNumber);
        try {

                // Verifies that user is a merchant
                $user = Session::has('merchant') ? Session::get('merchant') : $this->credpalAPI->getUser($phoneNumber);

                if ($user && $user->type == "merchant") {

                    $merchant = new MerchantController($sessionId, $user, $text);


                    $response = $merchant->index($user, $text);

                }else {
                    
                    $response = "END You are not allowed to perform this action";
                    
                }
            
        }catch (Exception $e) {
            // Session::forget('user');
            // Session::forget('merchant');
            $response = "END Oooops! An error occured, Try again later ".$e->getMessage();
        }

        return $this->returnResponse($response);


    }

    public function returnResponse ($response) {
        $substring = substr($response, 0, 3);
        if ($substring == 'CON' || $substring == "END") {
            header('Content-type: text/plain');
            echo $response;
        }
    }

    public function formatNumber ($phone) {
        $substring = substr($phone, 0, 4);
        if ($substring == "+234") {
            $phone = "0".substr($phone, 4);
        }
        return $phone;
    }
}
