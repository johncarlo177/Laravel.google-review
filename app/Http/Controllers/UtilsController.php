<?php

namespace App\Http\Controllers;

use App\Support\MobileNumberManager;
use Illuminate\Http\Request;

class UtilsController extends Controller
{
    private MobileNumberManager $mobileNumberManager;

    public function __construct()
    {
        $this->mobileNumberManager = new MobileNumberManager;
    }

    public function listCallingCodes()
    {
        return $this->mobileNumberManager->list();
    }

    public function myCallingCode(Request $request)
    {
        return $this->mobileNumberManager->callingCodeByIP(
            $request->ip()
        );
    }
}
