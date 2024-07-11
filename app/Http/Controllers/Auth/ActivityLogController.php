<?php


namespace App\Http\Controllers\Auth;

use App\Http\Controllers\CookieController;
use Illuminate\Routing\Controller as BaseController;


class ActivityLogController extends CookieController{
    public function activitylog()
    {
        return view('activity_log.activitylog');
    }
}