<?php

use App\Http\Controllers\Api\ShowcaseLookupController;
use Illuminate\Support\Facades\Route;

Route::get('/etalase/{sku}', [ShowcaseLookupController::class, 'show']);

