<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Apis\v1\AuthController;
use App\Http\Controllers\Apis\v1\ArticleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix("v1")->group(function () {
    Route::controller(AuthController::class)
        ->group(function () {
            Route::post('login', 'login');
            Route::post('register', 'register');
        });

    Route::middleware('auth:api')->group(function () {
        Route::controller(AuthController::class)
            ->group(function () {
                Route::get('logout', 'logout');
                Route::post('user/set-preferences', 'setUserPreferences');
                Route::post('change-password', 'passwordReset');
            });

        Route::controller(ArticleController::class)
            ->group(function () {
                Route::get('articles', 'getArticles');
                Route::get('article/{slug}', 'showArticle');
                Route::get('categories', 'getCategories');
                Route::get('sources', 'getSources');
                Route::get('authors', 'getAuthors');
            });
    });
});
