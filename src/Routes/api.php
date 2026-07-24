<?php

use Illuminate\Support\Facades\Route;
use Zerp\Recruitment\Http\Controllers\Api\DashboardApiController;
use Zerp\Recruitment\Http\Controllers\Api\JobPostingApiController;
use Zerp\Recruitment\Http\Controllers\Api\CandidateApiController;
use Zerp\Recruitment\Http\Controllers\Api\InterviewApiController;

Route::prefix('api')->middleware(['api.json'])->group(function () {
    Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'recruitment', 'as' => 'api.recruitment.'], function () {
        // Dashboard
        Route::get('dashboard', [DashboardApiController::class, 'index'])->name('dashboard');

        // Job Postings
        Route::apiResource('job-postings', JobPostingApiController::class);

        // Candidates
        Route::apiResource('candidates', CandidateApiController::class);

        // Interviews
        Route::apiResource('interviews', InterviewApiController::class);
    });
});
