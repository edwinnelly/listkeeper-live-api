<?php

namespace App\Http\Controllers;

use App\Models\Business_list;
use App\Models\User;
// use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $total_users = user::get()->where('active_business_key', '');

        $business_active_account = Business_list::where('business_key', Auth::user()->active_business_key)->first();

        return view('home', [
            'business_actice_info' => $business_active_account,

        ]);
        // return view('home');

    }
}
