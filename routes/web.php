<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RankingsController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\VipController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/login', [LoginController::class, 'login'])->name('login')->middleware('web');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('web');
Route::post('/cadastro', [RegisterController::class, 'register'])->name('register');
Route::get('/cadastro', [RegisterController::class, 'registerPage'])->name('registerPage');
Route::get('/downloads', [HomeController::class, 'downloads'])->name('downloads');
Route::get('/admin', [AdminController::class,'index'])->name('admin');
Route::post('/admin', [AdminController::class,'upload'])->name('upload');
Route::get('/removeDownloadFile/{download}', [AdminController::class,"removeDownloadFile"])->name('removeDownloadFile');
Route::get('/loadEvents', [HomeController::class, 'loadEvents'])->name('loadEvents');
Route::get('/rankings', [RankingsController::class, 'index'])->name('rankings');
Route::post('/rankings', [RankingsController::class, 'searchRankings'])->name('searchRankings');
Route::get('/vip', [VipController::class, 'index'])->name('vip');
Route::get('/account', [AccountController::class, 'index'])->name('account');



