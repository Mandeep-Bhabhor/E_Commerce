<?php

use App\Models\Cart;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



Schedule::call(function () {
    
   
    Cart::truncate(); 
    
    
   // Log::info('Cart is Cleared');

})->everyTwoMinutes();