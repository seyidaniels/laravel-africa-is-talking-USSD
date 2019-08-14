<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MerchantController;
use Exception;

class USSDController extends Controller
{
    private $merchant;
    private $credpalAPI;

    public function __construct(MerchantController $merchant, ApiContoller $credpalAPI)
    {
        $this->merchant = $merchant;
        $this->credpalAPI = $credpalAPI;
    }
    public function index (Request $request) {
        $sessionId   = $request["sessionId"];
        $serviceCode = $request["serviceCode"];
        $phoneNumber = $request["phoneNumber"];
        $text        = $request["text"];

        $request->validate([
            'phoneNumber' => 'required'
        ]);

        


        try {
                // Verifies that user is a merchant
                $user = $this->credpalAPI->getUser($phoneNumber);

                if ($user && $user->type == "merchant") {

                    $response = $this->merchant->index($user, $text);

                }else {

                    $response = "END You are not allowed to perform this action";
                    
                }
            
        }catch (Exception $e) {
            $response = "Oooops! An error occured, Try again later";
        }

        return $this->returnResponse($response);


    }

    public function returnResponse ($response) {
        $substring = substr( $response, 0, 3 );
        if ($substring == 'CON' || $substring == "END") {
            header('Content-type: text/plain');
            echo $response;
        }
    }
}
