<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\{BrandAssetController, PasswordController, ContactController, ProfileController, PackageController, PageController, ProfessionalRegistrationController, RecruitmentRequestController, SubscriptionPortalController};
use Illuminate\Support\Facades\Route;

/* ---------- Public website ---------- */
Route::get('/brand/{name}.svg', [BrandAssetController::class, 'svg'])->name('brand.svg');
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/how-it-works', [PageController::class, 'how'])->name('how');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms-and-conditions', [PageController::class, 'terms'])->name('terms');

// Professionals (independent of organisation subscriptions)
Route::get('/register/{type}', [ProfessionalRegistrationController::class, 'create'])->whereIn('type', ['researcher', 'enumerator'])->name('register.create');
Route::post('/register', [ProfessionalRegistrationController::class, 'store'])->middleware('throttle:10,1')->name('register.store');

// Professional self-service profile (signed-in professionals)
Route::middleware(['auth', 'role:professional'])->prefix('my')->name('profile.')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('update');
    Route::get('profile/cv', [ProfileController::class, 'cv'])->name('cv');
    Route::delete('profile/documents/{i}', [ProfileController::class, 'removeDocument'])->name('documents.destroy');
});

// Option A: monthly subscriptions
Route::get('/packages', [PackageController::class, 'index'])->name('packages');
Route::get('/packages/{package:slug}/subscribe', [PackageController::class, 'subscribe'])->name('packages.subscribe');
Route::post('/packages/{package:slug}/subscribe', [PackageController::class, 'store'])->middleware('throttle:10,1')->name('packages.store');
// Subscriber portal (signed-in organizations)
Route::middleware(['auth', 'role:organization'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [SubscriptionPortalController::class, 'show'])->name('show');
    Route::get('requests', [SubscriptionPortalController::class, 'requests'])->name('requests');
    Route::get('requests/{reference}', [SubscriptionPortalController::class, 'showRequest'])->name('requests.show');
    Route::get('requests/{reference}/edit', [SubscriptionPortalController::class, 'editRequest'])->name('requests.edit');
    Route::put('requests/{reference}', [SubscriptionPortalController::class, 'updateRequest'])->name('requests.update');
    Route::post('subscriptions/{subscription}/change', [SubscriptionPortalController::class, 'change'])->name('change');
    Route::post('subscriptions/{subscription}/cancel', [SubscriptionPortalController::class, 'cancel'])->name('cancel');
    Route::post('subscriptions/{subscription}/resume', [SubscriptionPortalController::class, 'resume'])->name('resume');
});

// Option B: public direct recruitment request (no account / login / subscription)
Route::get('/request-recruitment', [RecruitmentRequestController::class, 'create'])->name('request.create');
Route::post('/request-recruitment', [RecruitmentRequestController::class, 'store'])->middleware('throttle:10,1')->name('request.store');
Route::get('/request-recruitment/received', [RecruitmentRequestController::class, 'done'])->name('request.done');
Route::get('/request-recruitment/track', [RecruitmentRequestController::class, 'track'])->middleware('throttle:20,1')->name('request.track');

/* ---------- Accounts ---------- */
Route::get('/login', [Admin\AuthController::class, 'show'])->name('login');
Route::post('/login', [Admin\AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.post');
Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('logout');
Route::get('/forgot-password', [PasswordController::class, 'forgot'])->name('password.request');
Route::post('/forgot-password', [PasswordController::class, 'sendLink'])->middleware('throttle:5,1')->name('password.email');
Route::get('/reset-password/{token}', [PasswordController::class, 'reset'])->name('password.reset');
Route::post('/reset-password', [PasswordController::class, 'update'])->middleware('throttle:5,1')->name('password.update');

/* ---------- Administration ---------- */
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [Admin\AuthController::class, 'show'])->name('login');

    Route::middleware(['auth', 'role:admin,recruiter,finance'])->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::middleware('role:admin,recruiter')->group(function () {
            Route::get('messages', [Admin\MessageController::class, 'index'])->name('messages');
            Route::post('messages/{message}/toggle', [Admin\MessageController::class, 'handle'])->name('messages.toggle');
            Route::get('professionals', [Admin\ProfessionalController::class, 'index'])->name('professionals.index');
            Route::get('professionals/{professional}', [Admin\ProfessionalController::class, 'show'])->name('professionals.show');
            Route::put('professionals/{professional}', [Admin\ProfessionalController::class, 'update'])->name('professionals.update');
            Route::post('professionals/{professional}/recalculate', [Admin\ProfessionalController::class, 'recalculate'])->name('professionals.recalculate');
            Route::get('professionals/{professional}/cv', [Admin\ProfessionalController::class, 'cv'])->name('professionals.cv');
            Route::get('professionals/{professional}/documents/{i}', [Admin\ProfessionalController::class, 'document'])->name('professionals.document');

            Route::get('requests', [Admin\RequestAdminController::class, 'index'])->name('requests.index');
            Route::get('requests/{recruitmentRequest}', [Admin\RequestAdminController::class, 'show'])->name('requests.show');
            Route::put('requests/{recruitmentRequest}', [Admin\RequestAdminController::class, 'update'])->name('requests.update');
            Route::get('requests/{recruitmentRequest}/document', [Admin\RequestAdminController::class, 'document'])->name('requests.document');
            Route::post('requests/{recruitmentRequest}/shortlist', [Admin\RequestAdminController::class, 'shortlist'])->name('requests.shortlist');
            Route::put('shortlists/{shortlist}', [Admin\RequestAdminController::class, 'updateShortlist'])->name('shortlists.update');
            Route::delete('shortlists/{shortlist}', [Admin\RequestAdminController::class, 'removeShortlist'])->name('shortlists.destroy');
        });

        Route::middleware('role:admin,finance')->group(function () {
            Route::get('subscriptions', [Admin\SubscriptionAdminController::class, 'index'])->name('subscriptions.index');
            Route::post('subscriptions/run-lifecycle', [Admin\SubscriptionAdminController::class, 'runLifecycle'])->name('subscriptions.lifecycle');
            Route::get('subscriptions/{subscription}', [Admin\SubscriptionAdminController::class, 'show'])->name('subscriptions.show');
            Route::post('subscriptions/{subscription}/cancel', [Admin\SubscriptionAdminController::class, 'cancel'])->name('subscriptions.cancel');

            Route::get('invoices', [Admin\InvoiceController::class, 'index'])->name('invoices.index');
            Route::get('requests/{recruitmentRequest}/invoice/create', [Admin\InvoiceController::class, 'create'])->name('invoices.create');
            Route::post('requests/{recruitmentRequest}/invoice', [Admin\InvoiceController::class, 'store'])->name('invoices.store');
            Route::get('invoices/{invoice}', [Admin\InvoiceController::class, 'show'])->name('invoices.show');
            Route::get('invoices/{invoice}/edit', [Admin\InvoiceController::class, 'edit'])->name('invoices.edit');
            Route::put('invoices/{invoice}', [Admin\InvoiceController::class, 'update'])->name('invoices.update');
            Route::post('invoices/{invoice}/send', [Admin\InvoiceController::class, 'send'])->name('invoices.send');
            Route::post('invoices/{invoice}/void', [Admin\InvoiceController::class, 'void'])->name('invoices.void');
            Route::post('invoices/{invoice}/payments', [Admin\PaymentController::class, 'store'])->name('payments.store');

            Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports');
            Route::get('reports/invoices.csv', [Admin\ReportController::class, 'exportInvoices'])->name('reports.invoices');
            Route::get('reports/payments.csv', [Admin\ReportController::class, 'exportPayments'])->name('reports.payments');
        });

        Route::middleware('role:admin')->group(function () {
            Route::get('scoring', [Admin\ScoringController::class, 'edit'])->name('scoring');
            Route::put('scoring', [Admin\ScoringController::class, 'update'])->name('scoring.update');
            Route::get('packages', [Admin\PackageAdminController::class, 'index'])->name('packages.index');
            Route::get('packages/create', [Admin\PackageAdminController::class, 'create'])->name('packages.create');
            Route::post('packages', [Admin\PackageAdminController::class, 'store'])->name('packages.store');
            Route::get('packages/{package}/edit', [Admin\PackageAdminController::class, 'edit'])->name('packages.edit');
            Route::put('packages/{package}', [Admin\PackageAdminController::class, 'update'])->name('packages.update');
            Route::post('packages/{package}/toggle', [Admin\PackageAdminController::class, 'toggle'])->name('packages.toggle');
            Route::get('settings', [Admin\SettingsController::class, 'edit'])->name('settings');
            Route::put('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
            Route::put('settings/site', [Admin\SettingsController::class, 'updateSite'])->name('settings.site');
            Route::put('settings/brand', [Admin\SettingsController::class, 'updateBrand'])->name('settings.brand');
            Route::post('settings/brand/reset', [Admin\SettingsController::class, 'resetBrand'])->name('settings.brand.reset');
            Route::post('settings/tax', [Admin\SettingsController::class, 'storeTax'])->name('settings.tax.store');
            Route::delete('settings/tax/{taxRule}', [Admin\SettingsController::class, 'destroyTax'])->name('settings.tax.destroy');
        });

        Route::middleware('role:super_admin')->group(function () {
            Route::get('users', [Admin\UserController::class, 'index'])->name('users');
            Route::post('users', [Admin\UserController::class, 'store'])->name('users.store');
            Route::post('users/{user}/toggle', [Admin\UserController::class, 'toggle'])->name('users.toggle');
            Route::get('audit-log', Admin\AuditLogController::class)->name('audit');
        });
    });
});
