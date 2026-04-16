<?php

namespace App\Http\Controllers;

use App\Traits\HasApiIndex;
use App\Traits\HasApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BaseController extends Controller
{
    use HasApiIndex, HasApiResponse, AuthorizesRequests;
}
