<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Artisan;

class CacheController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth:admin');
  }

  public function clear() {
      news_cache()->invalidateAll(); // not cache:clear: on Redis that flushes the whole database
      Artisan::call('config:clear');
      Artisan::call('route:clear');
      Artisan::call('view:clear');
      Toastr::success('Cache, route, view, config cleared successfully!');
      return back();
    }
}
