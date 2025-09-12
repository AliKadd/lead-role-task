<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/tasks', function () {
    return view('tasks.index');
})->name('tasks.index');
