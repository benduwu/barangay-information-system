<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — v1
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api/v1/ via RouteServiceProvider.
| Auth routes are public; user management routes require admin role.
|
*/

// Public auth routes
Route::prefix('v1')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);

    // PDF route allows query string token auth, so it is outside standard auth middleware block
    Route::get('/documents/{id}/pdf', [\App\Http\Controllers\Api\V1\DocumentController::class, 'pdf']);

    // Protected routes (require Sanctum token)
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Purok & Resident Management (admin and staff)
        Route::get('/puroks', [\App\Http\Controllers\Api\V1\PurokController::class, 'index']);
        Route::apiResource('residents', \App\Http\Controllers\Api\V1\ResidentController::class);
        Route::post('/residents/{resident}/photo', [\App\Http\Controllers\Api\V1\ResidentController::class, 'uploadPhoto']);

        // Barangay Clearance & Certificates
        Route::get('/documents', [\App\Http\Controllers\Api\V1\DocumentController::class, 'index']);
        Route::post('/documents', [\App\Http\Controllers\Api\V1\DocumentController::class, 'store']);
        Route::get('/documents/{id}', [\App\Http\Controllers\Api\V1\DocumentController::class, 'show']);
        Route::put('/documents/{id}/approve', [\App\Http\Controllers\Api\V1\DocumentController::class, 'approve']);
        Route::put('/documents/{id}/reject', [\App\Http\Controllers\Api\V1\DocumentController::class, 'reject']);
        Route::put('/documents/{id}/release', [\App\Http\Controllers\Api\V1\DocumentController::class, 'release']);

        // Blotter / Incident Management
        Route::get('/blotters', [\App\Http\Controllers\Api\V1\BlotterController::class, 'index']);
        Route::post('/blotters', [\App\Http\Controllers\Api\V1\BlotterController::class, 'store']);
        Route::get('/blotters/{id}', [\App\Http\Controllers\Api\V1\BlotterController::class, 'show']);
        Route::put('/blotters/{id}/status', [\App\Http\Controllers\Api\V1\BlotterController::class, 'updateStatus']);
        Route::post('/blotters/{id}/parties', [\App\Http\Controllers\Api\V1\BlotterController::class, 'addParty']);
        Route::put('/blotters/{id}/assign', [\App\Http\Controllers\Api\V1\BlotterController::class, 'assignOfficer']);

        // User Management (admin only)
        Route::middleware('role:admin')->group(function () {
            Route::apiResource('users', UserController::class);
            Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
            
            // Workflow automation logs
            Route::get('/workflow-logs', [\App\Http\Controllers\Api\V1\WorkflowLogController::class, 'index']);
            Route::delete('/workflow-logs/clear', [\App\Http\Controllers\Api\V1\WorkflowLogController::class, 'clear']);
        });

        // Reports & Analytics (auth:sanctum)
        Route::get('/reports/residents', [\App\Http\Controllers\Api\V1\ReportController::class, 'residents']);
        Route::get('/reports/documents', [\App\Http\Controllers\Api\V1\ReportController::class, 'documents']);
        Route::get('/reports/blotters', [\App\Http\Controllers\Api\V1\ReportController::class, 'blotters']);
        Route::get('/reports/monthly', [\App\Http\Controllers\Api\V1\ReportController::class, 'monthly']);
        Route::post('/reports/export/pdf', [\App\Http\Controllers\Api\V1\ReportController::class, 'exportPdf']);
        Route::post('/reports/export/excel', [\App\Http\Controllers\Api\V1\ReportController::class, 'exportExcel']);

        // Announcement Management (admin & staff)
        Route::get('/admin/announcements', [\App\Http\Controllers\Api\V1\AnnouncementController::class, 'adminIndex']);
        Route::post('/admin/announcements', [\App\Http\Controllers\Api\V1\AnnouncementController::class, 'store']);
        Route::get('/admin/announcements/{id}', [\App\Http\Controllers\Api\V1\AnnouncementController::class, 'show']);
        Route::put('/admin/announcements/{id}', [\App\Http\Controllers\Api\V1\AnnouncementController::class, 'update']);
        Route::delete('/admin/announcements/{id}', [\App\Http\Controllers\Api\V1\AnnouncementController::class, 'destroy']);
        Route::post('/admin/announcements/{id}/publish', [\App\Http\Controllers\Api\V1\AnnouncementController::class, 'publish']);
    });

    // Public Announcements Feed
    Route::get('/announcements', [\App\Http\Controllers\Api\V1\AnnouncementController::class, 'index']);

    // Inbound Webhooks from n8n (protected by shared secret header)
    Route::prefix('webhooks')->group(function () {
        Route::post('/document-status', [\App\Http\Controllers\Api\V1\WebhookController::class, 'documentStatus']);
        Route::post('/blotter-status', [\App\Http\Controllers\Api\V1\WebhookController::class, 'blotterStatus']);
        Route::post('/announcement', [\App\Http\Controllers\Api\V1\WebhookController::class, 'announcement']);
        Route::post('/log-result', [\App\Http\Controllers\Api\V1\WebhookController::class, 'logResult']);
    });
});

