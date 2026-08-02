<?php

use App\Support\Locale;
use Illuminate\Support\Facades\App;

test('locale direction is ltr for english', function () {
    App::setLocale('en');

    expect(Locale::isRtl())->toBeFalse()
        ->and(Locale::direction())->toBe('ltr')
        ->and(locale_dir())->toBe('ltr');
});

test('locale direction is rtl for persian', function () {
    App::setLocale('fa');

    expect(Locale::isRtl())->toBeTrue()
        ->and(Locale::direction())->toBe('rtl')
        ->and(locale_dir())->toBe('rtl');
});

test('front layouts render rtl dir for persian locale', function () {
    App::setLocale('fa');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('dir="rtl"', false);
});

test('front layouts render ltr dir for english locale', function () {
    App::setLocale('en');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('dir="ltr"', false);
});
