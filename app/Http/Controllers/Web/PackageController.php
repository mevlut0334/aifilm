<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(): View
    {
        return view('web.packages.index');
    }
}
