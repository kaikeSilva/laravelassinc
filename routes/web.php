<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ColorController;
use Illuminate\Support\Facades\Cache;

// CSV import routes
Route::get('colors/import', [ColorController::class, 'showImportForm'])->name('colors.importForm');
Route::post('colors/import', [ColorController::class, 'import'])->name('colors.import');
Route::resource('colors', ColorController::class);

Route::get('/import-progress/{cacheKey}', function ($cacheKey) {
    return response()->json([
        'progress' => Cache::get('import_progress_' . $cacheKey, 0),
        'status' => Cache::get('import_status_' . $cacheKey, 'pending')
    ]);
})->name('colors.importProgress');

