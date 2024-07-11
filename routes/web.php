<?php

use App\Http\Controllers\Auth\ActivityLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ForgetPasswordController;


Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']) ->middleware('throttle:5,1'); // Allow 5 registration attempts per minute;
Route::get('/verify-email/{token}', [RegisterController::class, 'verifyEmail'])->name('verify.email');


Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::get('/2fa', [TwoFactorController::class, 'show2faForm'])->name('2fa.index');
Route::post('/2fa', [TwoFactorController::class, 'verify2fa'])->name('2fa.verify');
Route::post('/2fa/resend', [App\Http\Controllers\Auth\TwoFactorController::class, 'resendOtp'])->name('2fa.resend');

Route::get("/home", [HomeController::class, 'index'])->name('home')->middleware('auth');

Route::get('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get("/activitylog", [ActivityLogController::class, 'activitylog'])->name('activitylog')->middleware('auth');

Route::get('/searchresults', [SearchController::class, 'search'])->middleware('auth')->name('searchresults');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'loadProfile'])->name('profile.edit');
    Route::post('/profile/edit', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/delete', [ProfileController::class, 'destroyProfile'])->name('profile.delete');
    Route::post('/profile/toggle-2fa', [ProfileController::class, 'toggle2FA'])->name('profile.toggle-2fa');
    Route::get('/profile/change-password', [ProfileController::class, 'showChangePasswordForm'])->name('profile.change-password');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/chat/create', [ChatController::class, 'createChat'])->name('chat.create');
    Route::get('/chat/{token}', [ChatController::class, 'show'])->name('chat.chatwindow');
    Route::get('/chat/{token}/new-messages-count', [ChatController::class, 'getNewMessagesCount'])->name('chat.newMessagesCount');
    Route::post('/chat/{token}/reset-new-messages-count', [ChatController::class, 'resetNewMessagesCount'])->name('chat.resetNewMessagesCount');
    Route::delete('/chat/{token}', [ChatController::class, 'deleteChat'])->name('chat.delete');
    Route::post('/chat/{token}/message', [MessageController::class, 'sendMessage'])->name('chat.message');
    Route::get('/chat/messages/{token}', [ChatController::class, 'fetchMessages'])->name('chat.messages');
});


Route::get('/forget-password',[ForgetPasswordController::class, 'forgetPassword'])->name("forget.password");
Route::post('/forget-password',[ForgetPasswordController::class, 'forgetPasswordPost'])->name("forget.password.post");
Route::get('/reset-password{token}',[ForgetPasswordController::class, 'resetPassword'])->name("reset.password");
Route::post('reset-password',[ForgetPasswordController::class,'resetPasswordPost'])->name('reset.password.post');

URL::forceScheme('https');
