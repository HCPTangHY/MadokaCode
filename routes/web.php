<?php

use App\Http\Controllers\Battle\MapController;
use App\Http\Controllers\Battle\MilitaryController;
use App\Http\Controllers\Battle\PlanetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('dashboard');
})->middleware(['web', 'verified'])->name('dashboard');
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/map', [MapController::class,"mapPage"])->middleware(['web', 'verified'])->name('map');
Route::get('/MapContent', [MapController::class,"getData"])->middleware(['web', 'verified'])->name('mapContent');
Route::get('/planet',[PlanetController::class,"planetPage"])->middleware(['web', 'verified'])->name('planet');
Route::get('/military',[MilitaryController::class,"militaryPage"])->middleware(['web','verified'])->name('military');

Route::prefix("Action")->group(function () {
    Route::post('/MapData',[MapController::class,"mapData"])->middleware(['web', 'verified']);
    Route::post('/ReadStar',[MapController::class,"readStar"])->middleware(['web', 'verified']);
});

require __DIR__.'/auth.php';
