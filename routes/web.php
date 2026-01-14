<?php

use Illuminate\Support\Facades\Route;

// AUTH
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyController;

// PUBLIC
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\ForumController;
use App\Http\Controllers\Public\TagController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\ProfileController;
use App\Http\Controllers\Public\LatestController;
use App\Http\Controllers\Public\PopularController;
use App\Http\Controllers\Public\PostPinController;
use App\Http\Controllers\Public\PagesController as PublicPagesController;

// USER
use App\Http\Controllers\User\PostController;
use App\Http\Controllers\User\PostingController;
use App\Http\Controllers\User\PostReactionController;
use App\Http\Controllers\User\PostReportController;
use App\Http\Controllers\User\PostCommentController;
use App\Http\Controllers\User\PostRemoveController;
use App\Http\Controllers\User\CommentRemoveController;
use App\Http\Controllers\User\PostUpdateController;
use App\Http\Controllers\User\PostSaveController;
use App\Http\Controllers\User\PostShareController;
use App\Http\Controllers\User\SavedPostsController;

// ADMIN
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\CustomizationController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\RemovalReportController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\PagesController as AdminPagesController;

/*
|---------------------------------------------------------------------------
| HOME
|---------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('home');
})->name('home');

/*
|---------------------------------------------------------------------------
| AUTH
|---------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/verify/{token}', [VerifyController::class, 'verify'])
    ->name('verify.email');

/*
|---------------------------------------------------------------------------
| ADMIN
|---------------------------------------------------------------------------
*/
Route::prefix('admin10nexus')
    ->as('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Users
        Route::prefix('users')->group(function () {
            Route::get('/', [UsersController::class, 'index'])->name('users');

            Route::get('/create', [UsersController::class, 'create'])->name('users.create');
            Route::post('/', [UsersController::class, 'store'])->name('users.store');

            Route::get('/{user:username}', [UsersController::class, 'show'])->name('users.show');
            Route::put('/{user:username}', [UsersController::class, 'update'])->name('users.update');
            Route::delete('/{user:username}', [UsersController::class, 'destroy'])->name('users.destroy');

            Route::put('/{user:username}/role', [UsersController::class, 'updateRole'])->name('users.role.update');
            Route::put('/{user:username}/permissions', [UsersController::class, 'updatePermissionOverrides'])->name('users.permissions.update');
        });

        // Customization
        Route::get('/customization', [CustomizationController::class, 'index'])->name('customization');

        Route::prefix('customization')->group(function () {
            Route::post('/categories', [CustomizationController::class, 'storeCategory'])->name('categories.store');
            Route::put('/categories/{category}', [CustomizationController::class, 'updateCategory'])->name('categories.update');
            Route::delete('/categories/{category}', [CustomizationController::class, 'destroyCategory'])->name('categories.destroy');

            Route::post('/forums', [CustomizationController::class, 'storeForum'])->name('forums.store');
            Route::put('/forums/{forum}', [CustomizationController::class, 'updateForum'])->name('forums.update');
            Route::delete('/forums/{forum}', [CustomizationController::class, 'destroyForum'])->name('forums.destroy');

            Route::post('/paragraph-templates', [CustomizationController::class, 'storeParagraphTemplate'])->name('paragraph_templates.store');
            Route::put('/paragraph-templates/{paragraph_template}', [CustomizationController::class, 'updateParagraphTemplate'])->name('paragraph_templates.update');
            Route::delete('/paragraph-templates/{paragraph_template}', [CustomizationController::class, 'destroyParagraphTemplate'])->name('paragraph_templates.destroy');
        });

        // Reports
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
        Route::post('/reports/message', [ReportsController::class, 'updateReportMessage'])->name('reports.message');

        // Removed content reports
        Route::get('/reports/removals', [RemovalReportController::class, 'index'])->name('reports.removals');

        // Pages (ADMIN) ✅ FIXED: now routes are admin.pages.*
        Route::prefix('pages')->group(function () {
            Route::get('/', [AdminPagesController::class, 'index'])->name('pages.index');
            Route::get('/create', [AdminPagesController::class, 'create'])->name('pages.create');
            Route::post('/', [AdminPagesController::class, 'store'])->name('pages.store');

            Route::get('/{page:slug}/edit', [AdminPagesController::class, 'edit'])->name('pages.edit');
            Route::put('/{page:slug}', [AdminPagesController::class, 'update'])->name('pages.update');
            Route::delete('/{page:slug}', [AdminPagesController::class, 'destroy'])->name('pages.destroy');

            Route::post('/ensure-system', [AdminPagesController::class, 'ensureSystemPages'])->name('pages.ensureSystem');
        });

        // Theme
        Route::get('/theme', [ThemeController::class, 'edit'])->name('theme');
        Route::post('/theme', [ThemeController::class, 'update'])->name('theme.update');
        Route::post('/theme/reset-light', [ThemeController::class, 'resetLight'])->name('theme.resetLight');
    });

/*
|---------------------------------------------------------------------------
| PUBLIC: Categories / Forums / Tags
|---------------------------------------------------------------------------
*/
Route::prefix('category')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
});

Route::prefix('forum')->group(function () {
    Route::get('/', [ForumController::class, 'index'])->name('forums.index');

    Route::get('/{forum:slug}/{page?}', [ForumController::class, 'show'])
        ->whereNumber('page')
        ->name('forums.show');
});

Route::prefix('tag')->group(function () {
    // /tag/post-1
    Route::get('/{tag:slug}', [TagController::class, 'show'])->name('tags.show');

    // /tag/post-1/2
    Route::get('/{tag:slug}/{page}', [TagController::class, 'show'])
        ->whereNumber('page')
        ->name('tags.show.page');
});

/*
|---------------------------------------------------------------------------
| PUBLIC: Profile
|---------------------------------------------------------------------------
*/
Route::prefix('user')->group(function () {
    Route::get('/{user:username}', [ProfileController::class, 'show'])
        ->name('profile.show');

    Route::get('/{user:username}/{page}', [ProfileController::class, 'show'])
        ->whereNumber('page')
        ->name('profile.show.page');

    Route::post('/{user:username}/update', [ProfileController::class, 'update'])
        ->middleware('auth')
        ->name('profile.update');
});

/*
|---------------------------------------------------------------------------
| PUBLIC: Latest / Popular
|---------------------------------------------------------------------------
*/
Route::prefix('latest')->group(function () {
    Route::get('/', [LatestController::class, 'index'])->name('latest.index');
    Route::get('/{page}', [LatestController::class, 'index'])
        ->whereNumber('page')
        ->name('latest.index.page');
});

Route::prefix('popular')->group(function () {

    // default = week
    Route::get('/', [PopularController::class, 'index'])->name('popular.index');

    // /popular/2 (week pages)
    Route::get('/{page}', [PopularController::class, 'index'])
        ->whereNumber('page')
        ->name('popular.index.page');

    // /popular/month or /popular/all
    Route::get('/{period}', [PopularController::class, 'index'])
        ->whereIn('period', ['week', 'month', 'all'])
        ->name('popular.period');

    // /popular/month/2 or /popular/all/3
    Route::get('/{period}/{page}', [PopularController::class, 'index'])
        ->whereIn('period', ['week', 'month', 'all'])
        ->whereNumber('page')
        ->name('popular.period.page');
});

/*
|---------------------------------------------------------------------------
| SEARCH
|---------------------------------------------------------------------------
*/
Route::prefix('search')->group(function () {

    Route::get('/', [SearchController::class, 'home'])->name('search.home');

    Route::get('/go', [SearchController::class, 'go'])->name('search.go');

    Route::get('/{slug}', [SearchController::class, 'index'])->name('search.results');

    Route::get('/{slug}/{page}', [SearchController::class, 'index'])
        ->whereNumber('page')
        ->name('search.results.page');
});

/*
|---------------------------------------------------------------------------
| POSTING
|---------------------------------------------------------------------------
*/
Route::middleware(['auth', 'perm:create_post'])->group(function () {
    Route::get('/posting', [PostingController::class, 'create'])->name('posting.create');
    Route::post('/posting', [PostingController::class, 'store'])->name('posting.store');
});

/*
|---------------------------------------------------------------------------
| POST SHOW
|---------------------------------------------------------------------------
*/
Route::get('/post/{post:slug}', [PostController::class, 'show'])->name('post.show');

Route::post('/post/{post:slug}/approve', [PostController::class, 'approve'])
    ->middleware(['auth', 'perm:approve_post'])
    ->name('post.approve');

/*
|---------------------------------------------------------------------------
| POST UPDATE
|---------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/update/{post:slug}', [PostUpdateController::class, 'edit'])->name('post.edit');
    Route::put('/update/{post:slug}', [PostUpdateController::class, 'update'])->name('post.update');
});

Route::get('/update', function () {
    return redirect()->route('home');
})->name('post.edit.redirect');

/*
|---------------------------------------------------------------------------
| POST INTERACTIONS
|---------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::post('/post/{post:slug}/save', [PostSaveController::class, 'toggle'])
        ->name('post.save.toggle');

    Route::post('/post/{post:slug}/react', [PostReactionController::class, 'toggle'])
        ->name('post.react.toggle');

    Route::post('/post/{post:slug}/report', [PostReportController::class, 'store'])
        ->name('post.report.store');

    Route::post('/post/{post:slug}/comments', [PostCommentController::class, 'store'])
        ->name('post.comment.store');

    Route::post('/comments/{comment}/approve', [PostCommentController::class, 'approve'])
        ->middleware('perm:approve_post')
        ->name('comment.approve');

    Route::post('/post/{post:slug}/remove', [PostRemoveController::class, 'store'])
        ->name('post.remove');

    Route::post('/comments/{comment}/remove', [CommentRemoveController::class, 'store'])
        ->name('comment.remove');

    Route::post('/post/{post:slug}/share', [PostShareController::class, 'store'])
        ->name('post.share');
});

/*
|---------------------------------------------------------------------------
| PIN / UNPIN (approve permission)
|---------------------------------------------------------------------------
*/
Route::middleware(['auth', 'perm:approve_post'])->group(function () {
    Route::post('/forum/{forum:slug}/pin/{post:slug}', [PostPinController::class, 'pin'])
        ->name('forum.post.pin');

    Route::post('/forum/{forum:slug}/unpin/{post:slug}', [PostPinController::class, 'unpin'])
        ->name('forum.post.unpin');
});

/*
|---------------------------------------------------------------------------
| SAVED POSTS
|---------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/saved/{page?}', [SavedPostsController::class, 'index'])
        ->whereNumber('page')
        ->name('saved.index');
});

/*
|---------------------------------------------------------------------------
| PUBLIC PAGES
|---------------------------------------------------------------------------
| Recommended public URL:
|   /p/{slug}
|
| Optional "system pages on root":
|   /terms, /privacy, /dmca, /contact
|
| NOTE: keep these near the bottom to avoid catching other routes.
*/

// recommended
Route::get('/p/{page:slug}', [PublicPagesController::class, 'show'])
    ->name('pages.show');

// optional system slugs on root (only these exact slugs)
Route::get('/{page:slug}', [PublicPagesController::class, 'show'])
    ->whereIn('page', ['terms', 'privacy', 'dmca', 'contact']);

/*
|---------------------------------------------------------------------------
| FALLBACK (404)
|---------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
