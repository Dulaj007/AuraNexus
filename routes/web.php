<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\ForumController;

Route::get('/', function () {
    return view('home');
})->name('home');


// -------------------- AUTH --------------------
// Register routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate']);
});

// Logout route (only accessible for logged-in users)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Email verification
Route::get('/verify/{token}', [VerifyController::class, 'verify'])->name('verify.email');






Route::prefix('admin10nexus')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');


Route::get('/users/{user:username}', [AdminController::class, 'showUser'])->name('admin.users.show');
Route::put('/users/{user:username}', [AdminController::class, 'updateUser'])->name('admin.users.update');
Route::delete('/users/{user:username}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');


    Route::get('/customization', [AdminController::class, 'customization'])->name('admin.customization');

    // Categories
    Route::post('/customization/categories', [AdminController::class, 'storeCategory'])->name('admin.categories.store');
    Route::put('/customization/categories/{category}', [AdminController::class, 'updateCategory'])->name('admin.categories.update');
    Route::delete('/customization/categories/{category}', [AdminController::class, 'destroyCategory'])->name('admin.categories.destroy');

    // Forums
    Route::post('/customization/forums', [AdminController::class, 'storeForum'])->name('admin.forums.store');
    Route::put('/customization/forums/{forum}', [AdminController::class, 'updateForum'])->name('admin.forums.update');
    Route::delete('/customization/forums/{forum}', [AdminController::class, 'destroyForum'])->name('admin.forums.destroy');

    Route::get('/theme', [AdminController::class, 'theme'])->name('admin.theme');
});
// Public - Categories
Route::prefix('category')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
});

// Public - Forums
Route::prefix('forum')->group(function () {
    Route::get('/', [ForumController::class, 'index'])->name('forums.index');
    Route::get('/{forum:slug}', [ForumController::class, 'show'])->name('forums.show');
});

Route::put('/users/{user:username}/permissions', [AdminController::class, 'updateUserPermissionOverrides'])
    ->name('admin.users.permissions.update');
// roles
Route::put('/users/{user:username}/role', [AdminController::class, 'updateUserRole'])
    ->name('admin.users.role.update');
