<?php

use App\Http\Controllers\Api\ArticleCommentController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\ContactLeadController;
use App\Http\Controllers\Api\HomeSlideController;
use App\Http\Controllers\Api\HouseDesignController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\WelcomePopupController;
use Illuminate\Support\Facades\Route;

Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::post('/contact-leads', [ContactLeadController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact-leads.store');
Route::get('/articles/{slug}/comments', [ArticleCommentController::class, 'index'])->name('article-comments.index');
Route::post('/articles/{slug}/comments', [ArticleCommentController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('article-comments.store');
Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/site-settings', SiteSettingController::class)->name('site-settings.show');
Route::get('/home-slides', HomeSlideController::class)->name('home-slides.index');
Route::get('/welcome-popup', WelcomePopupController::class)->name('welcome-popup.show');
Route::get('/house-designs', [HouseDesignController::class, 'index'])->name('house-designs.index');
Route::get('/house-designs/{slug}', [HouseDesignController::class, 'show'])->name('house-designs.show');
