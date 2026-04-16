<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language', config('app.locale'));

        if (in_array($locale, config('app.available_locales', ['uz', 'oz', 'ru', 'en']))) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
