<?php

namespace App\Support;

class Locale
{
    public static function code(): string
    {
        return app()->getLocale();
    }

    public static function isRtl(?string $locale = null): bool
    {
        $locale ??= static::code();

        return in_array($locale, config('locales.rtl', []), true);
    }

    public static function direction(?string $locale = null): string
    {
        return static::isRtl($locale) ? 'rtl' : 'ltr';
    }

    public static function htmlLang(?string $locale = null): string
    {
        return str_replace('_', '-', $locale ?? static::code());
    }
}
