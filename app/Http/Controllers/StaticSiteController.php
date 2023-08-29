<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaticSiteController extends Controller
{
    public function privacy(Request $request)
    {
        return view('front.saticsite.privacy');
    }
}
