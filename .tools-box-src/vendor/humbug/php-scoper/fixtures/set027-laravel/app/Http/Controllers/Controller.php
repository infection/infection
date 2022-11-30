<?php

namespace _HumbugBoxb47773b41c19\App\Http\Controllers;

use _HumbugBoxb47773b41c19\Illuminate\Foundation\Bus\DispatchesJobs;
use _HumbugBoxb47773b41c19\Illuminate\Routing\Controller as BaseController;
use _HumbugBoxb47773b41c19\Illuminate\Foundation\Validation\ValidatesRequests;
use _HumbugBoxb47773b41c19\Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
