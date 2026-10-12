<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class WelcomeCotroller extends Controller
{
   public function index(){
      return view('frontend.page.home');
   }

   public function bookingpage(){
      return view('frontend.page.bookingnow');
   }

   public function HelpDesk(){
      return view('frontend.page.helpdesk');
   }
}
