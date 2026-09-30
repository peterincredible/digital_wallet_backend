<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            ["id"=>1,"name"=>"naira","symbol"=>"₦","code"=>"NGN"],
            ["id"=>2,"name"=>"US Dollar","symbol"=>"$","code"=>"USD"],
            ["id"=>3,"name"=>"USDT","symbol"=>"₮","code"=>"USDT"],
        ];

        foreach ($currencies as  $currency) {
            $currency_temp = Currency::where('id', $currency["id"])->first();
            if ($currency_temp === null) {
                $currency_temp = new Currency();
                $currency_temp->id = $currency["id"];
                $currency_temp->name = $currency["name"];
                $currency_temp->symbol = $currency["symbol"];
                $currency_temp->code = $currency["code"];
                $currency_temp->save();
            }
        }
        //
    }
}
