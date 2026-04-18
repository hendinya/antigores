<?php

use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\MasterBrandController as AdminMasterBrandController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Admin\PhoneTypeController as AdminPhoneTypeController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MemberShowcaseController;
use App\Http\Controllers\ProductCatalogController;
use App\Http\Controllers\PublicProductController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');
Route::get('/offline-products', [PublicProductController::class, 'index'])->name('public.products.index');
Route::get('/offline-products/search', [PublicProductController::class, 'search'])->name('public.products.search');
Route::get('/offline-products/suggest', [PublicProductController::class, 'suggest'])->name('public.products.suggest');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/home', HomeController::class)->name('home');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/products', [ProductCatalogController::class, 'index'])->name('products.index');
    Route::get('/products/search', [ProductCatalogController::class, 'search'])->name('products.search');
    Route::get('/products/suggest', [ProductCatalogController::class, 'suggest'])->name('products.suggest');
    Route::get('/member/showcases', [MemberShowcaseController::class, 'edit'])->name('member.showcases.edit');
    Route::put('/member/showcases', [MemberShowcaseController::class, 'update'])->name('member.showcases.update');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('products/search', [AdminProductController::class, 'search'])->name('products.search');
        Route::get('products/template', [AdminProductController::class, 'template'])->name('products.template');
        Route::get('products/export-filtered', [AdminProductController::class, 'exportFiltered'])->name('products.export-filtered');
        Route::post('products/import', [AdminProductController::class, 'import'])->name('products.import');
        Route::post('products/bulk-delete', [AdminProductController::class, 'bulkDestroy'])->name('products.bulk-delete');
        Route::patch('products/bulk-visibility', [AdminProductController::class, 'bulkUpdateVisibility'])->name('products.bulk-visibility');
        Route::patch('products/{product}/visibility', [AdminProductController::class, 'updateVisibility'])->name('products.visibility');
        Route::resource('categories', AdminCategoryController::class)->except('show');
        Route::resource('master-brands', AdminMasterBrandController::class)->except('show');
        Route::resource('brands', AdminBrandController::class)->except('show');
        Route::get('phone-types/template', [AdminPhoneTypeController::class, 'template'])->name('phone-types.template');
        Route::get('phone-types/export-filtered', [AdminPhoneTypeController::class, 'exportFiltered'])->name('phone-types.export-filtered');
        Route::post('phone-types/import', [AdminPhoneTypeController::class, 'import'])->name('phone-types.import');
        Route::resource('phone-types', AdminPhoneTypeController::class)->except('show');
        Route::resource('products', AdminProductController::class)->except('show');
        Route::resource('members', AdminMemberController::class)->only(['index', 'edit', 'update']);
    });
});
