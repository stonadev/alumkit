<?php

use Alumkit\Alumkit\Facades\Alumkit;
use Alumkit\Alumkit\Models\Page;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/about', function () {
    $page = Page::where('slug', 'about')->published()->first();

    abort_unless($page, 404);

    $contents = Alumkit::getPageContent($page->slug)->keyBy('type');

    return view('workbench::about', [
        'hero' => $contents->get('hero')?->fields ?? [],
        'team' => $contents->get('team')?->fields ?? [],
    ]);
});
