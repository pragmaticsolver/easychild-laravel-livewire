<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ParentSignUpController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AutoSignOffRecheckController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ICalendarController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\InformationController;
use App\Http\Controllers\MealPlanController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OpeningTimeController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleApproveRejectController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ShortUrlController;
use App\Http\Controllers\SignedUrlMapper;
use App\Http\Controllers\TranslationUpdateController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::view('/', 'welcome')->name('home');
Route::view('offline', 'offline')->name('offline');

Route::middleware(['signed'])->group(function () {
    Route::get('ical/events/{token}', ICalendarController::class)->name('ical');

    Route::prefix('signed/schedule/{schedule:uuid}')->group(function () {
        Route::post('{type}', [ScheduleApproveRejectController::class, 'approval'])->name('signed.schedule.approval');
        Route::get('/', [ScheduleApproveRejectController::class, 'askConfirmation'])->name('signed.schedule.ask');
    });

    // Route::middleware('auth')->prefix('auto/signoff/check/{schedule}')->group(function () {
    //     Route::post('/', [AutoSignOffRecheckController::class, 'store'])->name('recheck.auto.signoff');
    //     Route::get('/', [AutoSignOffRecheckController::class, 'show'])->name('recheck.auto.signoff');
    // });

    Route::get('parent/signup/{token}', ParentSignUpController::class)->name('parent.signup');
});

Route::middleware(['auth', 'parent', 'group.assigned'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('calendar', CalendarController::class)->name('calendar');

    Route::middleware('hasaccess:messages')->group(function () {
        Route::middleware('hasrole:manager|principal|parent')->group(function () {
            Route::get('messages', MessageController::class)->name('messages.index');
            Route::get('attachment/{uuid}', [MessageController::class, 'attachmentURL']);
        });
    });

    Route::middleware('hasaccess:informations')->group(function () {
        Route::get('informations', [InformationController::class, 'index'])->name('informations.index');

        Route::middleware('hasrole:manager')->group(function () {
            Route::get('informations/create', [InformationController::class, 'create'])->name('informations.create');
        });
    });

    // Schedules
    Route::middleware('hasrole:admin|manager|principal|parent')->group(function () {
        Route::get('schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    });

    Route::get('users/profile', [UserController::class, 'profile'])->name('users.profile');

    Route::middleware('hasrole:manager')->group(function () {
        Route::get('contracts', ContractController::class)->name('contracts.index');
        Route::get('organizations/profile', [OrganizationController::class, 'profile'])->name('organizations.profile');
    });

    Route::middleware('hasrole:principal|manager')->group(function () {
        Route::get('reports', ReportController::class)->name('reports.index');
        Route::get('presence', PresenceController::class)->name('presence');
        Route::get('mealplan', MealPlanController::class)->name('mealplan');
    });

    Route::middleware('hasrole:admin|manager')->group(function () {
        Route::resource('users', UserController::class)->only('create');
    });

    Route::middleware('hasrole:admin|manager|principal')->group(function () {
        // Users Resource
        Route::resource('users', UserController::class)->only('index');
        Route::get('users/{user:uuid}/edit/{type?}', [UserController::class, 'edit'])->name('users.edit');
    });

    Route::middleware('hasrole:manager')->group(function () {
        Route::resource('groups', GroupController::class)->only('index', 'create', 'edit');

        // Schedules
        Route::get('schedules/{type?}/{uuid?}', [ScheduleController::class, 'index'])->name('schedules.type.index');
        Route::get('openingtimes', OpeningTimeController::class)->name('openingtimes.index');
    });

    Route::middleware('admin')->group(function () {
        // Org Resource
        Route::resource('organizations', OrganizationController::class)->only('index', 'create', 'edit');
        Route::get('translation/update', TranslationUpdateController::class)->name('translation.update');

        Route::prefix('import')->name('import.')->group(function () {
            Route::get('children', [ImportController::class, 'children'])
                ->name('children');
        });
    });

    Route::get('private-files', [SignedUrlMapper::class, 'signed'])
        ->middleware('signed')
        ->name('private-files');

    Route::view('pdf-viewer', 'layouts.pdf')->name('pdf-viewer');
});

Route::middleware(['guest', 'throttle:10,1'])->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');

    Route::get('password-reset/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::get('password-request', [PasswordResetController::class, 'request'])->name('password.request');
});

Route::get('url/{short_url:from}', [ShortUrlController::class, 'index'])->name('shorturl');

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
