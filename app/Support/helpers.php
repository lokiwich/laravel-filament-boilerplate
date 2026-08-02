<?php

use App\Support\Locale;

if (! function_exists('locale_dir')) {
    function locale_dir(?string $locale = null): string
    {
        return Locale::direction($locale);
    }
}

if (! function_exists('locale_is_rtl')) {
    function locale_is_rtl(?string $locale = null): bool
    {
        return Locale::isRtl($locale);
    }
}
