<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\MatchingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyAndJobController;

// メインページ
Route::get('/', function () {
    return view('welcome');
});

// 登録するには
Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::get('/register', [RegisterController::class, 'register'])->name('register.get');

// メール確認ページへ
Route::get('/verify-email/{token}', [RegisterController::class, 'verifyEmail'])->name('verify.email');

// フルネームとパスワードの入力ページを表示する
Route::get('/set-password', [RegisterController::class, 'showSetPasswordForm'])->name('set-password');

// フルネームとパスワードを保存する
Route::post('/set-password', [RegisterController::class, 'setPassword'])->name('set-password.post');

// 認証後のユーザーページ
Route::middleware('auth')->group(function () {
    Route::get('/mypage', [MypageController::class, 'myPage'])->name('mypage');
    Route::get('/matchings/create', [MatchingsController::class, 'create'])->name('matchings.create');
    Route::post('/matchings', [MatchingsController::class, 'store'])->name('matchings.store');
    Route::get('/profile/profile', [ProfileController::class, 'profile'])->name('profile.profile');
});

// ログイン用
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [LoginController::class, 'login'])->name('login');

// ログアウト
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');



