<?php

use App\Livewire\Admin\Dashboard as AdminDashboard;
// Auth Routes
use App\Livewire\Admin\GlobalSearch;
use App\Livewire\Admin\Content\Index as AdminContentIndex;
use App\Livewire\Admin\Package\Index as AdminPackageIndex;
use App\Livewire\Admin\Package\RoadmapBuilder as AdminRoadmapBuilder;
use App\Livewire\Admin\Quiz\Index as AdminQuizIndex;
use App\Livewire\Admin\Quiz\Questions as AdminQuizQuestions;
use App\Livewire\Admin\User\Index as AdminUserIndex;
use App\Livewire\Admin\Withdrawal\Index as AdminWithdrawalIndex;
use App\Models\Package;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
// Email Verification Routes
use App\Livewire\Auth\Register;
// Route khusus untuk handle klik link dari email (Laravel Handle Otomatis)
use App\Livewire\Auth\ResetPassword;
// Settings Route
use App\Livewire\Auth\VerifyEmail;
use App\Livewire\Exam\QuizBriefing;
use App\Livewire\Exam\QuizEngine;
use App\Livewire\Exam\QuizResult;
// Admin Routes
use App\Livewire\Settings;
use App\Livewire\User\Affiliate as UserAffiliate;
use App\Livewire\User\Dashboard as UserDashboard;
use App\Livewire\User\Checkout as UserCheckout;
use App\Livewire\User\MaterialsJourney as UserMaterialsJourney;
use App\Models\RoadmapItem;
use App\Livewire\User\Onboarding as UserOnboarding;
use App\Livewire\User\Packages as UserPackages;
use App\Livewire\User\Transactions as UserTransactions;
use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
// User Routes
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Landing page: harga paket diambil langsung dari katalog aktif
    return view('welcome', [
        'packages' => Package::active()
            ->with(['plans' => fn ($q) => $q->orderBy('duration_days')])
            ->withCount(['quizItems as quizzes_count'])
            ->orderBy('name')
            ->get(),
    ]);
})->name('home');

// Webhook DOKU (CSRF-exempt via bootstrap/app.php; diverifikasi HMAC signature)
Route::post('/webhooks/doku', \App\Http\Controllers\Webhook\DokuWebhookController::class)
    ->name('webhooks.doku');

// Route khusus untuk halaman "Please Verify"
Route::get('/email/verify', VerifyEmail::class)
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    // User baru diarahkan ke onboarding dulu; user lama langsung ke dashboard
    return $request->user()->hasOnboarded()
        ? redirect(route('user.dashboard').'?verified=1')
        : redirect()->route('user.onboarding');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::middleware(['auth'])->group(function () {
    // ... route dashboard lainnya

    // Route Settings (Bisa diakses semua user yang login)
    Route::get('/settings', Settings::class)->name('settings');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');

    // Halaman Request Link
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');

    // Halaman Input Password Baru (Link dari Email akan mengarah kesini)
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');

    // Google OAuth (Socialite)
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/global-search', GlobalSearch::class)->name('global-search');

    Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
    Route::get('/users', AdminUserIndex::class)->name('users');

    // Modul Admin: CRUD Paket & Kuis (Phase 3)
    Route::get('/packages', AdminPackageIndex::class)->name('packages');
    Route::get('/packages/{package}/roadmap', AdminRoadmapBuilder::class)->name('packages.roadmap');
    Route::get('/quizzes', AdminQuizIndex::class)->name('quizzes');
    Route::get('/quizzes/import-template', \App\Http\Controllers\Admin\QuestionImportTemplateController::class)->name('quizzes.import-template');
    Route::get('/quizzes/{quiz}/questions', AdminQuizQuestions::class)->name('quizzes.questions');

    // Modul Admin: CRUD materi belajar (teks/PDF/video) untuk roadmap
    Route::get('/contents', AdminContentIndex::class)->name('contents');

    // Modul Admin: antrian persetujuan penarikan saldo (Phase 5)
    Route::get('/withdrawals', AdminWithdrawalIndex::class)->name('withdrawals');
});

Route::middleware(['auth', 'verified', 'role:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', UserDashboard::class)->name('dashboard');

    // Onboarding user baru: tutorial singkat + pilih paket
    Route::get('/onboarding', UserOnboarding::class)->name('onboarding');

    // CBT Engine (Phase 4): persiapan (aturan main) + ruang ujian + hasil
    Route::get('/exam/result/{attempt}', QuizResult::class)->name('exam.result');
    Route::get('/exam/{quiz}/prepare', QuizBriefing::class)->name('exam.prepare');
    Route::get('/exam/{quiz}', QuizEngine::class)->name('exam');

    // Ruang belajar: player dua kolom (sidebar roadmap + panel materi)
    Route::get('/journey/{package}', UserMaterialsJourney::class)->name('journey');

    // Kompat tautan lama /learn/{item} → buka player dengan item tersebut aktif
    Route::get('/learn/{item}', fn (RoadmapItem $item) => redirect()->route('user.journey', [
        'package' => $item->module->package_id,
        'item' => $item->id,
    ]))->name('learn');

    // Subscription & Affiliate (Phase 5)
    Route::get('/packages', UserPackages::class)->name('packages');
    Route::get('/affiliate', UserAffiliate::class)->name('affiliate');

    // Checkout, riwayat transaksi & invoice PDF
    Route::get('/checkout/{plan}', UserCheckout::class)->name('checkout');
    Route::get('/transactions', UserTransactions::class)->name('transactions');
    Route::get('/transactions/{payment}/invoice', \App\Http\Controllers\User\InvoiceController::class)->name('invoice.download');
});
