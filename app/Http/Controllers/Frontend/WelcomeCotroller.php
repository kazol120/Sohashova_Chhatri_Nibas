<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class WelcomeCotroller extends Controller

{

   public function index(){

    return view('Frontend.welcome');

   }

   public function bookingpage(){

      return view('Frontend.bookingnow');
   }

   public function HelpDesk(){

      return view('Frontend.helpdesk');
   }

}
