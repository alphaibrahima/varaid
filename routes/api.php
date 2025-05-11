<?php

use Carbon\Carbon;
use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SlotController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



