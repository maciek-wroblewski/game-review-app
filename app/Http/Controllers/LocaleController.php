<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function setLocale($locale)
    {
        // Ograniczamy do tych dwóch języków
        if (in_array($locale, ['en', 'pl'])) {
            Session::put('locale', $locale);
        }
        
        return redirect()->back();
    }
}