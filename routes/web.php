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
Route::get('/eventos', [HomeController::class, 'eventsPage'])->name('eventsPage');
Route::post('/login', [LoginController::class, 'login'])->name('login')->middleware('web');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('web');
Route::post('/cadastro', [RegisterController::class, 'register'])->name('register');
Route::get('/cadastro', [RegisterController::class, 'registerPage'])->name('registerPage');
Route::get('/downloads', [HomeController::class, 'downloads'])->name('downloads');
Route::get('/admin', [AdminController::class,'index'])->name('admin');
Route::post('/admin', [AdminController::class,'upload'])->name('upload');
Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
Route::post('/admin/players/disconnect', [AdminController::class, 'disconnectPlayer'])->name('admin.players.disconnect');
Route::get('/removeDownloadFile/{download}', [AdminController::class,"removeDownloadFile"])->name('removeDownloadFile');
Route::get('/loadEvents', [HomeController::class, 'loadEvents'])->name('loadEvents');
Route::get('/rankings', [RankingsController::class, 'index'])->name('rankings');
Route::post('/rankings', [RankingsController::class, 'searchRankings'])->name('searchRankings');
Route::get('/loja', [VipController::class, 'index'])->name('shop');
Route::get('/vip', function() { return redirect()->route('shop'); });
Route::match(['get', 'post'], '/loja/checkout', [VipController::class, 'checkout'])->name('shop.checkout');
Route::post('/loja/pagamento', [VipController::class, 'processPayment'])->name('shop.payment.process');
Route::get('/loja/pedido/{externalReference}', [VipController::class, 'showOrder'])->name('shop.order');
Route::get('/loja/pedido/{externalReference}/status', [VipController::class, 'checkOrderStatus'])->name('shop.order.check');
Route::post('/loja/pedido/{externalReference}/cancelar', [VipController::class, 'cancelOrder'])->name('shop.order.cancel');

// Webhook Mercado Pago (API de Orders)
Route::any('/api/webhooks/mercadopago', [\App\Http\Controllers\MercadoPagoWebhookController::class, 'handle'])->name('webhook.mercadopago');

Route::post('/admin/shop/settings', [AdminController::class, 'updateShopSettings'])->name('admin.shop.settings');
Route::post('/admin/registration/bonus', [AdminController::class, 'updateRegistrationBonus'])->name('admin.registration.bonus');
Route::post('/admin/shop/packages', [AdminController::class, 'storePackage'])->name('admin.shop.package.store');
Route::put('/admin/shop/packages/{id}', [AdminController::class, 'updatePackage'])->name('admin.shop.package.update');
Route::post('/admin/shop/packages/{id}/toggle', [AdminController::class, 'togglePackage'])->name('admin.shop.package.toggle');
Route::delete('/admin/shop/packages/{id}', [AdminController::class, 'deletePackage'])->name('admin.shop.package.delete');

Route::get('/account', [AccountController::class, 'index'])->name('account');
Route::post('/account/disconnect', [AccountController::class, 'disconnect'])->name('account.disconnect');
Route::get('/account/settings', [AccountController::class, 'settings'])->name('account.settings');
Route::put('/account/settings', [AccountController::class, 'updateSettings'])->name('account.settings.update');

// Módulo de Notícias
Route::get('/noticias', [\App\Http\Controllers\NewsController::class, 'index'])->name('news.index');
Route::get('/noticias/{slug}', [\App\Http\Controllers\NewsController::class, 'show'])->name('news.show');

// Administração de Notícias
Route::post('/admin/news', [\App\Http\Controllers\NewsController::class, 'store'])->name('admin.news.store');
Route::put('/admin/news/{id}', [\App\Http\Controllers\NewsController::class, 'update'])->name('admin.news.update');
Route::post('/admin/news/{id}/toggle', [\App\Http\Controllers\NewsController::class, 'toggle'])->name('admin.news.toggle');
Route::delete('/admin/news/{id}', [\App\Http\Controllers\NewsController::class, 'destroy'])->name('admin.news.destroy');
