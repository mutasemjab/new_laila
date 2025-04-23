<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
    */
    public function boot()
    {
        Paginator::USeBootstrap();
        if(\App\Models\AttendanceLog::count() && !\App\Models\Day::where('is_open',true)->count()){
            $day = \App\Models\Day::Create(['is_open'=>true]);
            session()->put('day_no',$day->id);
        }
    }
}
