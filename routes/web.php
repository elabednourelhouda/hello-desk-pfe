<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\PasswordController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SpaceController as AdminSpaceController;
use App\Http\Controllers\Admin\CommercialController as AdminCommercialController;
use App\Http\Controllers\Admin\ProspectController as AdminProspectController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\ProspectVisitController;
use App\Http\Controllers\Admin\ProspectRequestController;
use App\Http\Controllers\Admin\ProspectRequestController as AdminProspectRequestController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\InteractiveMapController;
use App\Http\Controllers\Admin\ContractController;

use App\Http\Controllers\Commercial\ProspectRequestController as CommercialProspectRequestController;
use App\Http\Controllers\Commercial\DashboardController as CommercialDashboardController;
use App\Http\Controllers\Commercial\ProspectController as CommercialProspectController;
use App\Http\Controllers\Commercial\ProspectVisitController as CommercialProspectVisitController;
use App\Http\Controllers\Admin\PaymentController;

use App\Http\Controllers\Client\DashboardController as ClientDashboardController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Auth routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/signin', function () {
    return redirect()->route('login');
})->name('signin');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Password routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/password/change', [PasswordController::class, 'edit'])
        ->name('password.change');

    Route::put('/password/change', [PasswordController::class, 'update'])
        ->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'password.changed', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // This allows you to open: http://127.0.0.1:8000/admin/
        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        })->name('home');

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('reservations', ReservationController::class)
            ->only(['index', 'create', 'store', 'show']);

        Route::resource('payments', PaymentController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

        /*
        |--------------------------------------------------------------------------
        | Spaces / Map
        |--------------------------------------------------------------------------
        */
        Route::get('/carte-interactive', [InteractiveMapController::class, 'index'])
            ->name('interactive-map.index');

        Route::get('/spaces', [AdminSpaceController::class, 'index'])
            ->name('spaces.index');

        Route::get('/map', [InteractiveMapController::class, 'index'])
            ->name('map.index');

        /*
        |--------------------------------------------------------------------------
        | Prospects
        |--------------------------------------------------------------------------
        */

        Route::resource('prospects', AdminProspectController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

        Route::post('/prospects/{prospect}/convert', [AdminProspectController::class, 'convert'])
            ->name('prospects.convert');

        Route::patch('/prospects/{prospect}/mark-lost', [AdminProspectController::class, 'markLost'])
            ->name('prospects.markLost');

        Route::patch('/prospects/{prospect}/reactivate', [AdminProspectController::class, 'reactivate'])
            ->name('prospects.reactivate');


        /*
        |--------------------------------------------------------------------------
        | Commercial staff
        |--------------------------------------------------------------------------
        */

        Route::resource('commercials', AdminCommercialController::class)
            ->only(['index', 'create', 'store', 'show']);

        Route::patch('/commercials/{commercial}/reset-password', [AdminCommercialController::class, 'resetPassword'])
            ->name('commercials.resetPassword');

        Route::post('/commercials/{commercial}/assignments', [AdminCommercialController::class, 'storeAssignment'])
            ->name('commercials.assignments.store');

        Route::delete('/commercials/{commercial}/assignments/{assignment}', [AdminCommercialController::class, 'destroyAssignment'])
            ->name('commercials.assignments.destroy');
            
        /*
        |--------------------------------------------------------------------------
        | Clients
        |--------------------------------------------------------------------------
        */

        Route::resource('clients', AdminClientController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

        Route::patch('/clients/{client}/deactivate', [AdminClientController::class, 'deactivate'])
            ->name('clients.deactivate');

        Route::patch('/clients/{client}/reactivate', [AdminClientController::class, 'reactivate'])
            ->name('clients.reactivate');

        Route::patch('/clients/{client}/reset-password', [AdminClientController::class, 'resetPassword'])
            ->name('clients.resetPassword');

        Route::post('/prospects/{prospect}/visits', [ProspectVisitController::class, 'store'])
            ->name('prospects.visits.store');

        Route::patch('/prospect-visits/{visit}/done', [ProspectVisitController::class, 'markDone'])
            ->name('prospects.visits.done');

        Route::patch('/prospect-visits/{visit}/cancel', [ProspectVisitController::class, 'cancel'])
            ->name('prospects.visits.cancel');

        Route::delete('/prospect-visits/{visit}', [ProspectVisitController::class, 'destroy'])
            ->name('prospects.visits.destroy');

        Route::post('/prospects/{prospect}/requests', [ProspectRequestController::class, 'store'])
            ->name('prospects.requests.store');

        Route::delete('/prospect-requests/{prospectRequest}', [ProspectRequestController::class, 'destroy'])
            ->name('prospects.requests.destroy');

        Route::post('/prospects/{prospect}/requests', [AdminProspectRequestController::class, 'store'])
            ->name('prospects.requests.store');

        Route::patch('/prospect-requests/{prospectRequest}', [AdminProspectRequestController::class, 'update'])
            ->name('prospects.requests.update');

        Route::delete('/prospect-requests/{prospectRequest}', [AdminProspectRequestController::class, 'destroy'])
            ->name('prospects.requests.destroy');

        Route::resource('contracts', ContractController::class)
            ->only(['index', 'show', 'edit', 'update']);
});
/*
|--------------------------------------------------------------------------
| Future CRM improvement: prospect need evolution
|--------------------------------------------------------------------------
| Disabled for now because the interface only shows "Besoin initial".
*/

// Route::post('/prospects/{prospect}/requests', [AdminProspectRequestController::class, 'store'])
//     ->name('prospects.requests.store');

// Route::patch('/prospect-requests/{prospectRequest}', [AdminProspectRequestController::class, 'update'])
//     ->name('prospects.requests.update');

// Route::delete('/prospect-requests/{prospectRequest}', [AdminProspectRequestController::class, 'destroy'])
//     ->name('prospects.requests.destroy');

/*
|--------------------------------------------------------------------------
| Commercial routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'password.changed', 'role:commercial'])
    ->prefix('commercial')
    ->name('commercial.')
    ->group(function () {

        Route::get('/', function () {
            return redirect()->route('commercial.dashboard');
        })->name('home');

        Route::get('/dashboard', [CommercialDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('prospects', CommercialProspectController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

        Route::post('/prospects/{prospect}/convert', [CommercialProspectController::class, 'convert'])
            ->name('prospects.convert');

        Route::patch('/prospects/{prospect}/mark-lost', [CommercialProspectController::class, 'markLost'])
            ->name('prospects.markLost');

        Route::patch('/prospects/{prospect}/reactivate', [CommercialProspectController::class, 'reactivate'])
            ->name('prospects.reactivate');

        Route::post('/prospects/{prospect}/visits', [CommercialProspectVisitController::class, 'store'])
            ->name('prospects.visits.store');

        Route::patch('/prospect-visits/{visit}/done', [CommercialProspectVisitController::class, 'markDone'])
            ->name('prospects.visits.done');

        Route::patch('/prospect-visits/{visit}/cancel', [CommercialProspectVisitController::class, 'cancel'])
            ->name('prospects.visits.cancel');

        Route::delete('/prospect-visits/{visit}', [CommercialProspectVisitController::class, 'destroy'])
            ->name('prospects.visits.destroy');

        Route::post('/prospects/{prospect}/requests', [CommercialProspectRequestController::class, 'store'])
            ->name('prospects.requests.store');

        Route::patch('/prospect-requests/{prospectRequest}', [CommercialProspectRequestController::class, 'update'])
            ->name('prospects.requests.update');

        Route::delete('/prospect-requests/{prospectRequest}', [CommercialProspectRequestController::class, 'destroy'])
            ->name('prospects.requests.destroy');
    });
    /*
|--------------------------------------------------------------------------
| Future CRM improvement: prospect need evolution
|--------------------------------------------------------------------------
| Disabled for now because the interface only shows "Besoin initial".
*/

// Route::post('/prospects/{prospect}/requests', [CommercialProspectRequestController::class, 'store'])
//     ->name('prospects.requests.store');

// Route::patch('/prospect-requests/{prospectRequest}', [CommercialProspectRequestController::class, 'update'])
//     ->name('prospects.requests.update');

// Route::delete('/prospect-requests/{prospectRequest}', [CommercialProspectRequestController::class, 'destroy'])
//     ->name('prospects.requests.destroy');

/*
|--------------------------------------------------------------------------
| Client routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:client', 'client.active', 'password.changed'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {

        // This allows: http://127.0.0.1:8000/client/
        Route::get('/', function () {
            return redirect()->route('client.dashboard');
        })->name('home');

        Route::get('/dashboard', [ClientDashboardController::class, 'index'])
            ->name('dashboard');
    });