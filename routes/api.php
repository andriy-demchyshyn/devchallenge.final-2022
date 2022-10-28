<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;

Route::post('/image-input', [ImageController::class, 'process']);
