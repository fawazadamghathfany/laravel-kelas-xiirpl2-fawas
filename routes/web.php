<?php

use App\Http\Controllers\AdminFilmController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\KritikController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', fn () => redirect()->route('home')->with('open_register', true))->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminFilmController::class, 'index'])->name('admin');
    Route::get('/admin/films/create', [AdminFilmController::class, 'create'])->name('admin.films.create');
    Route::post('/admin/films', [AdminFilmController::class, 'store'])->name('admin.films.store');
    Route::get('/admin/films/{film}/edit', [AdminFilmController::class, 'edit'])->name('admin.films.edit');
    Route::put('/admin/films/{film}', [AdminFilmController::class, 'update'])->name('admin.films.update');
    Route::delete('/admin/films/{film}', [AdminFilmController::class, 'destroy'])->name('admin.films.destroy');
});
Route::post('/movies/{film}/comments', [KritikController::class, 'store'])
    ->middleware('auth')
    ->name('movies.comments.store');

Route::post('/', function (Request $request) {
    $keyword = trim((string) $request->input('search', $request->input('Search', '')));

    return redirect()->route('home', $keyword === '' ? [] : ['search' => $keyword]);
})->withoutMiddleware(PreventRequestForgery::class)->name('home.search.legacy');

Route::get('/', [FilmController::class, 'movieHome'])->name('home');
Route::get('/movies', [FilmController::class, 'movies'])->name('movies');
Route::get('/movies/{film}', [FilmController::class, 'show'])->name('movies.show');
Route::get('/movies/genre/{genre}', [FilmController::class, 'moviesByGenre'])->name('genre');
