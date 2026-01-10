<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyController;

use App\Http\Controllers\User\PostController;
use App\Http\Controllers\User\PostingController;
use App\Http\Controllers\User\PostReactionController;
use App\Http\Controllers\User\PostReportController;
use App\Http\Controllers\User\PostCommentController;
use App\Http\Controllers\User\PostRemoveController;
use App\Http\Controllers\User\CommentRemoveController;
use App\Http\Controllers\User\PostUpdateController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\RemovalReportController;

use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\ForumController;
use App\Http\Controllers\Public\TagController;

Route::get('/', function () {
    return view('home');
})->name('home');


// -------------------- AUTH --------------------
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

Route::get('/verify/{token}', [VerifyController::class, 'verify'])->name('verify.email');


// -------------------- ADMIN --------------------
Route::prefix('admin10nexus')->middleware(['auth', 'admin'])->group(function () {

    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');

    // Users
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');

    // MUST be before /users/{user}
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('admin.users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');

    Route::get('/users/{user:username}', [AdminController::class, 'showUser'])->name('admin.users.show');
    Route::put('/users/{user:username}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{user:username}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    // Role + overrides
    Route::put('/users/{user:username}/role', [AdminController::class, 'updateUserRole'])
        ->name('admin.users.role.update');

    Route::put('/users/{user:username}/permissions', [AdminController::class, 'updateUserPermissionOverrides'])
        ->name('admin.users.permissions.update');

    // Customization
    Route::get('/customization', [AdminController::class, 'customization'])->name('admin.customization');

    // Categories
    Route::post('/customization/categories', [AdminController::class, 'storeCategory'])->name('admin.categories.store');
    Route::put('/customization/categories/{category}', [AdminController::class, 'updateCategory'])->name('admin.categories.update');
    Route::delete('/customization/categories/{category}', [AdminController::class, 'destroyCategory'])->name('admin.categories.destroy');

    // Forums
    Route::post('/customization/forums', [AdminController::class, 'storeForum'])->name('admin.forums.store');
    Route::put('/customization/forums/{forum}', [AdminController::class, 'updateForum'])->name('admin.forums.update');
    Route::delete('/customization/forums/{forum}', [AdminController::class, 'destroyForum'])->name('admin.forums.destroy');

    // Paragraph templates (SEO)
    Route::post('/customization/paragraph-templates', [AdminController::class, 'storeParagraphTemplate'])
        ->name('admin.paragraph_templates.store');

    Route::put('/customization/paragraph-templates/{paragraph_template}', [AdminController::class, 'updateParagraphTemplate'])
        ->name('admin.paragraph_templates.update');

    Route::delete('/customization/paragraph-templates/{paragraph_template}', [AdminController::class, 'destroyParagraphTemplate'])
        ->name('admin.paragraph_templates.destroy');

    // Reports (user reports)
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::post('/reports/message', [AdminController::class, 'updateReportMessage'])->name('admin.reports.message');

    // ✅ Removed content reports (separate page)
    Route::get('/reports/removals', [RemovalReportController::class, 'index'])
        ->name('admin.reports.removals');

    // Theme
    Route::get('/theme', [AdminController::class, 'theme'])->name('admin.theme');
});


// -------------------- PUBLIC --------------------
Route::prefix('category')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
});

Route::prefix('forum')->group(function () {
    Route::get('/', [ForumController::class, 'index'])->name('forums.index');

    // ✅ page is optional, but if present must be a number
    Route::get('/{forum:slug}/{page?}', [ForumController::class, 'show'])
        ->whereNumber('page')
        ->name('forums.show');
});


Route::prefix('tag')->group(function () {
    Route::get('/{tag:slug}', [TagController::class, 'show'])->name('tags.show');
});


// -------------------- POSTING --------------------
Route::middleware(['auth', 'perm:create_post'])->group(function () {
    Route::get('/posting', [PostingController::class, 'create'])->name('posting.create');
    Route::post('/posting', [PostingController::class, 'store'])->name('posting.store');
});


// -------------------- POST SHOW --------------------
Route::get('/post/{post:slug}', [PostController::class, 'show'])->name('post.show');

Route::post('/post/{post:slug}/approve', [PostController::class, 'approve'])
    ->middleware(['auth', 'perm:approve_post'])
    ->name('post.approve');


// -------------------- POST INTERACTIONS --------------------
Route::middleware('auth')->group(function () {

    Route::post('/post/{post}/react', [PostReactionController::class, 'toggle'])
        ->name('post.react.toggle');

    Route::post('/post/{post}/report', [PostReportController::class, 'store'])
        ->name('post.report.store');

    Route::post('/post/{post}/comments', [PostCommentController::class, 'store'])
        ->name('post.comment.store');

    Route::post('/comments/{comment}/approve', [PostCommentController::class, 'approve'])
        ->middleware('perm:approve_post')
        ->name('comment.approve');

    // ✅ Remove post/comment (requires auth; controller should check permission delete_post)
    Route::post('/posts/{post}/remove', [PostRemoveController::class, 'store'])
        ->name('post.remove');

    Route::post('/comments/{comment}/remove', [CommentRemoveController::class, 'store'])
        ->name('comment.remove');

         Route::get('/update/{post:slug}', [PostUpdateController::class, 'edit'])
        ->name('post.edit');

    Route::put('/update/{post:slug}', [PostUpdateController::class, 'update'])
        ->name('post.update');
});




// /update alone should go home
Route::get('/update', function () {
    return redirect()->route('home');
});

