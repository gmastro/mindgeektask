<?php

use App\Http\Controllers\ChirpController;
use App\Http\Controllers\ProfileController;
use App\Jobs\Common\CacheJob;
use App\Jobs\Common\DownloadJob;
use App\Jobs\Common\ThumbnailsJob;
use App\Models\DownloadedFiles;
use App\Models\Pornstars;
use App\Models\RemoteFeeds;
use App\Models\Thumbnails;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redirect;
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

Route::get('/dashboard', fn() => Inertia::render('Dashboard', [
    'products'  => RemoteFeeds::all()
]))->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('chirps', ChirpController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->middleware(['auth', 'verified']);

Route::get('/products', fn() => Inertia::render("Products/Feeds", [
    'products'  => RemoteFeeds::all(),
]))->name('products');

Route::name('products.')->prefix('products')->group(function () {
    Route::get('/pornstars', fn() => Inertia::render("Products/Index", [
        // default view
        // 'products'  => Pornstars::paginate(20),
        // 'type'      => 'pornstars',
        // 'source'    => RemoteFeeds::first()->source,
        // or view that will not displease me
        'products'  => Pornstars::with('thumbnails')
            ->has('thumbnails')
            ->whereNotIn('attributes->gender', ['male', 'm2f'])
            ->paginate(20),
        'type'      => 'pornstars',
        'source'    => RemoteFeeds::first()->source,
    ]))->name('pornstars');

    Route::post('/job', function () {
        $request = request();
        $data = $request->validate(['data.id' => 'required|integer']);
        $model = RemoteFeeds::find(intval($data['data']['id']));

        if ($model === null) {
            Redirect::back()->with(['status' => 404, 'message' => 'These are not the droids you are looking for']);
        }

        if ($model->chain->isEmpty() === false) {
            Bus::chain($model->chain)
                ->onQueue('downloads')
                ->dispatch();
        }

        return Redirect::back()->with(['status' => 200, 'message' => 'job dispatched']);
    })->middleware(['auth', 'verified'])->name('job');

    Route::post('/toggle', function () {
        $request = request();
        $data = $request->validate(['data.id' => 'required|integer']);
        $model = RemoteFeeds::find(intval($data['data']['id']));

        if ($model === null) {
            Redirect::back()->with(['status' => 404, 'message' => 'These are not the droids you are looking for']);
        }

        $model->is_active = !$model->is_active;
        $model->save();

        return Redirect::back()->with([
            'status'    => 200,
            'data'      => ['id' => $model->id, 'is_active' => $model->is_active],
        ]);
    })->middleware(['auth', 'verified'])->name('toggle');
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
