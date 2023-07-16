<?php

use App\Http\Controllers\ChirpController;
use App\Http\Controllers\ProfileController;
use App\Jobs\Common\CacheJob;
use App\Models\DownloadedFiles;
use App\Models\Pornstars;
use App\Models\RemoteFeeds;
use App\Models\Thumbnails;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

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

Route::get('/', fn() => Inertia::render('Welcome', [
    'canLogin'          => Route::has('login'),
    'canRegister'       => Route::has('register'),
    'laravelVersion'    => Application::VERSION,
    'phpVersion'        => PHP_VERSION,
]))->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('chirps', ChirpController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->middleware(['auth', 'verified']);

Route::get('/products', fn() => Inertia::render("Products/Index", [
    'products'  => Pornstars::with('thumbnails')
        ->has('thumbnails')
        ->whereNotIn('attributes->gender', ['male', 'm2f'])
        ->paginate(20),
    'type'      => 'pornstars',
    'source'    => RemoteFeeds::first()->source,
]))->name('products');

Route::name('products.')->prefix('products')->group(function () {
    Route::get('/pornstars', fn() => Inertia::render("Products/Index", [
        'products'  => Pornstars::paginate(20),
        'type'      => 'pornstars',
        'source'    => RemoteFeeds::first()->source,
    ]))->name('pornstars');
});

Route::get('/file/display/{code}', function(string $code) {
    $cached = DownloadedFiles::where('md5_hash', $code)->first();

    if($cached === null) {
        return abort(404, "These are not the droids you are looking for!");
    }
    
    $content = Redis::get("key:" . $cached->md5_hash);

    if ($content === null) {
        $content = Storage::disk($cached->disk)->get($cached->filename);
        CacheJob::dispatch();
    }

    return response($content, 200, ['Content-Type' => $cached->mime_type]);
})->where(['code' => '[a-f0-9]{32}'])->name('image-caching');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
