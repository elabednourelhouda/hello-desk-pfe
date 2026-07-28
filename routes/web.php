<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ClientAttachmentController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SpaceController as AdminSpaceController;
use App\Http\Controllers\Admin\CommercialController as AdminCommercialController;
use App\Http\Controllers\Admin\ProspectController as AdminProspectController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\ProspectVisitController as AdminProspectVisitController;
use App\Http\Controllers\Admin\ProspectRequestController as AdminProspectRequestController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\InteractiveMapController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Admin\FloorController;

use App\Http\Controllers\Commercial\DashboardController as CommercialDashboardController;
use App\Http\Controllers\Commercial\ProspectController as CommercialProspectController;
use App\Http\Controllers\Commercial\ProspectVisitController as CommercialProspectVisitController;
use App\Http\Controllers\Commercial\ProspectRequestController as CommercialProspectRequestController;
use App\Http\Controllers\Commercial\ClientController as CommercialClientController;
use App\Http\Controllers\Commercial\ReservationController as CommercialReservationController;
use App\Http\Controllers\Commercial\ContractController as CommercialContractController;
use App\Http\Controllers\Commercial\PaymentController as CommercialPaymentController;
use App\Http\Controllers\Commercial\InteractiveMapController as CommercialInteractiveMapController;
use App\Http\Controllers\Commercial\ComplaintController as CommercialComplaintController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SpaceTypeController;
use App\Http\Controllers\Admin\SpaceStatusController;
use App\Http\Controllers\Admin\ReservationDurationTypeController;
use App\Http\Controllers\Admin\ProspectSourceController;
use App\Http\Controllers\Admin\ActivitySectorController;
use App\Http\Controllers\Admin\ContactTypeController;
use App\Http\Controllers\Admin\CampusController;

use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\ReservationController as ClientReservationController;
use App\Http\Controllers\Client\ContractController as ClientContractController;
use App\Http\Controllers\Client\PaymentController as ClientPaymentController;
use App\Http\Controllers\Client\ComplaintController as ClientComplaintController;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Authentication routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.post');

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

        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        })->name('home');

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Spaces and interactive map
        |--------------------------------------------------------------------------
        */

        Route::get('/interactive-map', [InteractiveMapController::class, 'index'])
            ->name('interactive-map.index');

        Route::get('/map', [InteractiveMapController::class, 'index'])
            ->name('map.index');

        Route::get('/spaces', [AdminSpaceController::class, 'index'])
            ->name('spaces.index');

        Route::get('/spaces/create', [AdminSpaceController::class, 'create'])
            ->name('spaces.create');

        Route::post('/spaces', [AdminSpaceController::class, 'store'])
            ->name('spaces.store');

        Route::get('/spaces/{space}', [AdminSpaceController::class, 'show'])
            ->name('spaces.show');

        Route::get('/spaces/{space}/edit', [AdminSpaceController::class, 'edit'])
            ->name('spaces.edit');

        Route::put('/spaces/{space}', [AdminSpaceController::class, 'update'])
            ->name('spaces.update');

        Route::delete('/spaces/{space}', [AdminSpaceController::class, 'destroy'])
            ->name('spaces.destroy');

        /*
        |--------------------------------------------------------------------------
        | Settings / configuration (configurable dropdown lists)
        |--------------------------------------------------------------------------
        */

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])
                ->name('index');

            Route::resource('space-types', SpaceTypeController::class)
                ->except(['show'])
                ->names('space-types');

            Route::patch('/space-types/{spaceType}/toggle-active', [SpaceTypeController::class, 'toggleActive'])
                ->name('space-types.toggleActive');

            Route::resource('space-statuses', SpaceStatusController::class)
                ->except(['show'])
                ->names('space-statuses');

            Route::patch('/space-statuses/{spaceStatus}/toggle-active', [SpaceStatusController::class, 'toggleActive'])
                ->name('space-statuses.toggleActive');

            Route::resource('reservation-duration-types', ReservationDurationTypeController::class)
                ->except(['show'])
                ->names('reservation-duration-types');

            Route::patch('/reservation-duration-types/{reservationDurationType}/toggle-active', [ReservationDurationTypeController::class, 'toggleActive'])
                ->name('reservation-duration-types.toggleActive');

            Route::patch('/reservation-duration-types/{reservationDurationType}/move-up', [ReservationDurationTypeController::class, 'moveUp'])
                ->name('reservation-duration-types.moveUp');

            Route::patch('/reservation-duration-types/{reservationDurationType}/move-down', [ReservationDurationTypeController::class, 'moveDown'])
                ->name('reservation-duration-types.moveDown');

            Route::resource('sites', CampusController::class)
                ->parameters(['sites' => 'campus'])
                ->names('sites');

            Route::patch('/sites/{campus}/toggle-active', [CampusController::class, 'toggleActive'])
                ->name('sites.toggleActive');

            Route::resource('sites.floors', FloorController::class)
                ->except(['index'])
                ->parameters(['sites' => 'campus', 'floors' => 'floor'])
                ->names('sites.floors');

            Route::patch('/sites/{campus}/floors/{floor}/toggle-active', [FloorController::class, 'toggleActive'])
                ->name('sites.floors.toggleActive');

            Route::resource('prospect-sources', ProspectSourceController::class)
                ->except(['show'])
                ->names('prospect-sources');

            Route::patch('/prospect-sources/{prospectSource}/toggle-active', [ProspectSourceController::class, 'toggleActive'])
                ->name('prospect-sources.toggleActive');

            Route::resource('activity-sectors', ActivitySectorController::class)
                ->except(['show'])
                ->names('activity-sectors');

            Route::patch('/activity-sectors/{activitySector}/toggle-active', [ActivitySectorController::class, 'toggleActive'])
                ->name('activity-sectors.toggleActive');

            Route::resource('contact-types', ContactTypeController::class)
                ->except(['show'])
                ->names('contact-types');

            Route::patch('/contact-types/{contactType}/toggle-active', [ContactTypeController::class, 'toggleActive'])
                ->name('contact-types.toggleActive');
        });

        /*
        |--------------------------------------------------------------------------
        | Commercial staff
        |--------------------------------------------------------------------------
        */

        Route::resource('commercials', AdminCommercialController::class)
            ->only(['index', 'create', 'store', 'show']);

        Route::patch('/commercials/{commercial}/reset-password', [AdminCommercialController::class, 'resetPassword'])
            ->name('commercials.resetPassword');

        Route::patch('/commercials/{commercial}/toggle-active', [AdminCommercialController::class, 'toggleActive'])
            ->name('commercials.toggleActive');

        Route::post('/commercials/{commercial}/assignments', [AdminCommercialController::class, 'storeAssignment'])
            ->name('commercials.assignments.store');

        Route::delete('/commercials/{commercial}/assignments/{assignment}', [AdminCommercialController::class, 'destroyAssignment'])
            ->name('commercials.assignments.destroy');

        /*
        |--------------------------------------------------------------------------
        | Prospects and CRM
        |--------------------------------------------------------------------------
        */

        Route::resource('prospects', AdminProspectController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

        Route::get('/prospects/{prospect}/crm', [AdminProspectController::class, 'crm'])
            ->name('prospects.crm');

        Route::post('/prospects/{prospect}/convert', [AdminProspectController::class, 'convert'])
            ->name('prospects.convert');

        Route::patch('/prospects/{prospect}/mark-lost', [AdminProspectController::class, 'markLost'])
            ->name('prospects.markLost');

        Route::patch('/prospects/{prospect}/reactivate', [AdminProspectController::class, 'reactivate'])
            ->name('prospects.reactivate');

        Route::post('/prospects/{prospect}/visits', [AdminProspectVisitController::class, 'store'])
            ->name('prospects.visits.store');

        Route::patch('/prospect-visits/{visit}/done', [AdminProspectVisitController::class, 'markDone'])
            ->name('prospects.visits.done');

        Route::patch('/prospect-visits/{visit}/cancel', [AdminProspectVisitController::class, 'cancel'])
            ->name('prospects.visits.cancel');

        Route::delete('/prospect-visits/{visit}', [AdminProspectVisitController::class, 'destroy'])
            ->name('prospects.visits.destroy');

        Route::post('/prospects/{prospect}/requests', [AdminProspectRequestController::class, 'store'])
            ->name('prospects.requests.store');

        Route::patch('/prospect-requests/{prospectRequest}', [AdminProspectRequestController::class, 'update'])
            ->name('prospects.requests.update');

        Route::delete('/prospect-requests/{prospectRequest}', [AdminProspectRequestController::class, 'destroy'])
            ->name('prospects.requests.destroy');

        /*
        |--------------------------------------------------------------------------
        | Clients
        |--------------------------------------------------------------------------
        */

        Route::resource('clients', AdminClientController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

        Route::post('/clients/analyze-risks', [AdminClientController::class, 'analyzeRisks'])
            ->name('clients.analyzeRisks');

        Route::post('/clients/{client}/attachments', [ClientAttachmentController::class, 'store'])
            ->name('clients.attachments.store');

        Route::get('/client-attachments/{attachment}/preview', [ClientAttachmentController::class, 'preview'])
            ->name('clients.attachments.preview');

        Route::get('/client-attachments/{attachment}/download', [ClientAttachmentController::class, 'download'])
            ->name('clients.attachments.download');

        Route::delete('/client-attachments/{attachment}', [ClientAttachmentController::class, 'destroy'])
            ->name('clients.attachments.destroy');

        Route::patch('/clients/{client}/deactivate', [AdminClientController::class, 'deactivate'])
            ->name('clients.deactivate');

        Route::patch('/clients/{client}/reactivate', [AdminClientController::class, 'reactivate'])
            ->name('clients.reactivate');

        Route::patch('/clients/{client}/reset-password', [AdminClientController::class, 'resetPassword'])
            ->name('clients.resetPassword');

        Route::patch('/clients/{client}/block-payment', [\App\Http\Controllers\Admin\ClientController::class, 'blockForPayment'])
            ->name('clients.block-payment');

        Route::patch('/clients/{client}/ban', [\App\Http\Controllers\Admin\ClientController::class, 'ban'])
            ->name('clients.ban');

        /*
        |--------------------------------------------------------------------------
        | Reservations
        |--------------------------------------------------------------------------
        */

        Route::resource('reservations', ReservationController::class)
            ->only(['index', 'create', 'store', 'show']);

        /*
        |--------------------------------------------------------------------------
        | Contracts
        |--------------------------------------------------------------------------
        */

        Route::get('/contracts/{contract}/document', [ContractController::class, 'document'])
            ->name('contracts.document');

        Route::resource('contracts', ContractController::class)
            ->only(['index', 'show', 'edit', 'update']);

        /*
        |--------------------------------------------------------------------------
        | Payments
        |--------------------------------------------------------------------------
        */

        Route::patch('/payments/{payment}/mark-as-paid', [PaymentController::class, 'markAsPaid'])
            ->name('payments.markAsPaid');

        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])
            ->name('payments.receipt');

        Route::get('/payments/contracts/{contract}/schedule', [PaymentController::class, 'schedule'])
            ->name('payments.schedule');

        Route::resource('payments', PaymentController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

        /*
        |--------------------------------------------------------------------------
        | Admin complaints
        |--------------------------------------------------------------------------
        */

        Route::resource('complaints', AdminComplaintController::class)
            ->only(['index', 'show', 'update']);

        /*
        |--------------------------------------------------------------------------
        | Admin notifications
        |--------------------------------------------------------------------------
        */

        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('notifications.index');

        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.readAll');

        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.read');
    });

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

        Route::get('/prospects/{prospect}/crm', [CommercialProspectController::class, 'crm'])
            ->name('prospects.crm');

        Route::resource('clients', CommercialClientController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

        Route::post('/clients/{client}/report-to-admin', [CommercialClientController::class, 'reportToAdmin'])
            ->name('clients.report-to-admin');

        Route::post('/clients/{client}/attachments', [ClientAttachmentController::class, 'store'])
            ->name('clients.attachments.store');

        Route::get('/client-attachments/{attachment}/preview', [ClientAttachmentController::class, 'preview'])
            ->name('clients.attachments.preview');

        Route::get('/client-attachments/{attachment}/download', [ClientAttachmentController::class, 'download'])
            ->name('clients.attachments.download');

        Route::delete('/client-attachments/{attachment}', [ClientAttachmentController::class, 'destroy'])
            ->name('clients.attachments.destroy');

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

        Route::get('/carte-interactive', [CommercialInteractiveMapController::class, 'index'])
            ->name('interactive-map.index');

        /*
        |--------------------------------------------------------------------------
        | Commercial reservations
        |--------------------------------------------------------------------------
        */

        Route::resource('reservations', CommercialReservationController::class)
            ->only(['index', 'create', 'store', 'show']);

        /*
        |--------------------------------------------------------------------------
        | Commercial contracts
        |--------------------------------------------------------------------------
        */

        Route::get('/contracts/{contract}/document', [CommercialContractController::class, 'document'])
            ->name('contracts.document');

        Route::resource('contracts', CommercialContractController::class)
            ->only(['index', 'show', 'edit', 'update']);

        /*
        |--------------------------------------------------------------------------
        | Commercial payments
        |--------------------------------------------------------------------------
        */

        Route::patch('/payments/{payment}/mark-as-paid', [CommercialPaymentController::class, 'markAsPaid'])
            ->name('payments.markAsPaid');

        Route::get('/payments/{payment}/receipt', [CommercialPaymentController::class, 'receipt'])
            ->name('payments.receipt');

        Route::get('/payments/contracts/{contract}/schedule', [CommercialPaymentController::class, 'schedule'])
            ->name('payments.schedule');

        Route::resource('payments', CommercialPaymentController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update']);

        /*
        |--------------------------------------------------------------------------
        | Commercial complaints
        |--------------------------------------------------------------------------
        */

        Route::resource('complaints', CommercialComplaintController::class)
            ->only(['index', 'show', 'update']);

        /*
        |--------------------------------------------------------------------------
        | Commercial notifications
        |--------------------------------------------------------------------------
        */

        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('notifications.index');

        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.readAll');

        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.read');
    });

/*
|--------------------------------------------------------------------------
| Client routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:client', 'client.active', 'password.changed'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {

        Route::get('/', function () {
            return redirect()->route('client.dashboard');
        })->name('home');

        Route::get('/dashboard', [ClientDashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Client reservations
        |--------------------------------------------------------------------------
        */

        Route::get('/reservations', [ClientReservationController::class, 'index'])
            ->name('reservations.index');

        Route::get('/reservations/{reservation}', [ClientReservationController::class, 'show'])
            ->name('reservations.show');

        /*
        |--------------------------------------------------------------------------
        | Client contracts
        |--------------------------------------------------------------------------
        */

        Route::get('/contracts', [ClientContractController::class, 'index'])
            ->name('contracts.index');

        Route::get('/contracts/{contract}', [ClientContractController::class, 'show'])
            ->name('contracts.show');

        Route::get('/contracts/{contract}/document', [ClientContractController::class, 'document'])
            ->name('contracts.document');

        /*
        |--------------------------------------------------------------------------
        | Client payments
        |--------------------------------------------------------------------------
        */

        Route::get('/payments', [ClientPaymentController::class, 'index'])
            ->name('payments.index');

        Route::get('/payments/contracts/{contract}/schedule', [ClientPaymentController::class, 'schedule'])
            ->name('payments.schedule');

        Route::get('/payments/{payment}', [ClientPaymentController::class, 'show'])
            ->name('payments.show');

        Route::get('/payments/{payment}/receipt', [ClientPaymentController::class, 'receipt'])
            ->name('payments.receipt');

        /*
        |--------------------------------------------------------------------------
        | Client notifications
        |--------------------------------------------------------------------------
        */

        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('notifications.index');

        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.readAll');

        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.read');

        /*
        |--------------------------------------------------------------------------
        | Client complaints
        |--------------------------------------------------------------------------
        */

        Route::get('/complaints', [ClientComplaintController::class, 'index'])
            ->name('complaints.index');

        Route::get('/complaints/create', [ClientComplaintController::class, 'create'])
            ->name('complaints.create');

        Route::post('/complaints', [ClientComplaintController::class, 'store'])
            ->name('complaints.store');

        Route::get('/complaints/{complaint}', [ClientComplaintController::class, 'show'])
            ->name('complaints.show');
    });