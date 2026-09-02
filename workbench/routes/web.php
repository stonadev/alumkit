<?php

use Alumkit\Alumkit\Facades\Alumkit;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/about', Alumkit::pageRoute('about'));

Route::get('/committee', function () {
    $members = Alumkit::committeeMembers()->get();

    return view('workbench::committee', compact('members'));
})->name('committee');
