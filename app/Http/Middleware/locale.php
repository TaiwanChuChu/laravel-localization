<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class locale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $locales = explode(',', $request->header('Accept-Language'));

        $locale = collect($locales)->map(function ($locale) {
            $localePart = explode(';', $locale);
            $language = $localePart[0];
            $factor = 1;

            if (isset($localePart[1])) {
                $factor = (float) explode('=', $localePart[1])[1];
            }

            return [
                'language' => $language,
                'factor' => $factor,
            ];
        })
        ->sortByDesc('factor');

        $locale = $this->mapLocale($locale->first()['language']);

        app()->setLocale($locale);

        return $next($request);
    }

    private function mapLocale(string $locale): string
    {
        $ownLocales = [
            'zh-TW' => ['zh', 'zh-TW', 'zh-Hant', 'zh-Hant-TW'],
            'en' => ['en', 'en-US'],
        ];

        foreach ($ownLocales as $lang => $langs) {
            if (in_array($locale, $langs)) {
                return $lang;
            }
        }

        return 'zh-TW';
    }
}
