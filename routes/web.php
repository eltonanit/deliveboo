<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\RestaurantController as RestaurantController;
use App\Http\Controllers\Admin\DishController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->name('admin.')->prefix('admin')->group(function () {
    Route::get('/', [RestaurantController::class, 'index'])->name('dashboard');
    Route::resource('restaurants', RestaurantController::class);
    Route::get('/restaurants/{restaurant}/dishes/create', [DishController::class, 'create'])->name('dishes.create');
    Route::resource('dishes', DishController::class);
});

// Route::post('/restaurants/{restaurant}/dishes', [DishController::class, 'store'])->name('admin.restaurants.dishes.store');

// Route::resource('restaurants', RestaurantController::class);
// Route::resource('dishes', DishController::class);

require __DIR__ . '/auth.php';
