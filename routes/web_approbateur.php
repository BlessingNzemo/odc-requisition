<?php
use App\Http\Controllers\approbateurController;
use Illuminate\Support\Facades\Route;
    Route::post('/update-levels', [ApprobateurController::class, 'updateLevels'])->name('update.levels');
    Route::resource('approbateurs', ApprobateurController::class);
require __DIR__.'/auth.php';