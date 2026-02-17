<?php

use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EnrollmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('courses', CourseController::class);

// ✅ taruh di atas resource
Route::get('enrollments/export', [EnrollmentController::class, 'export']);

// ✅ resource tanpa show
Route::apiResource('enrollments', EnrollmentController::class)->except(['show']);
