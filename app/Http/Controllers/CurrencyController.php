<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    //
    public function getAll(Request $request){
       $currencies = Currency::all();
       return response()->json($currencies);
    }
}
