<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Importación de los controladores (Asumiendo que estarán en App\Http\Controllers)
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ChecklistController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Owner\PosController;
use App\Http\Controllers\Inventory\GeneratorController;
use App\Http\Controllers\Inventory\SparePartController;
use App\Http\Controllers\Operations\RevisionController;
use App\Http\Controllers\Operations\WorkshopController;
use App\Http\Controllers\Logistics\ShipmentController;
use App\Http\Controllers\Client\ReservationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'technician') {
            return redirect()->route('operations.revisions.scan');
        } elseif (Auth::user()->role === 'client') {
            return redirect()->route('store.available'); // Assuming store root
        }
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

// Rutas de Autenticación (Generadas por laravel/ui)
Auth::routes();

// Grupo de rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PANEL ADMINISTRADOR GENERAL (Usuario 1)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware(['check.role:admin,owner'])->group(function () {
        
        // Dashboard Principal
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // CRUD Sucursales
        Route::resource('branches', BranchController::class);

        // CRUD Usuarios
        Route::resource('users', UserController::class);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset_password');

        // Configuración de Checklists Dinámicos
        Route::resource('checklists', ChecklistController::class);

        // Control de Banners Publicitarios
        Route::resource('banners', BannerController::class);

        // Registro de Pedidos Internacionales (EE.UU.)
        Route::get('/orders/usa', [GeneratorController::class, 'createOrder'])->name('orders.usa');
        Route::post('/orders/usa', [GeneratorController::class, 'storeOrder'])->name('orders.usa.store');
        Route::put('/orders/usa/{generator}', [GeneratorController::class, 'updateOrder'])->name('orders.usa.update');
        Route::delete('/orders/usa/{generator}', [GeneratorController::class, 'destroyOrder'])->name('orders.usa.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | GESTIÓN DE INVENTARIO Y MÁQUINAS
    |--------------------------------------------------------------------------
    */
    Route::prefix('inventory')->name('inventory.')->middleware(['check.role:admin,owner'])->group(function () {
        
        // Inventario Global y por Sucursal
        Route::get('/generators/export/excel', [GeneratorController::class, 'exportExcel'])->name('generators.export.excel');
        Route::post('/generators/import/excel', [GeneratorController::class, 'importExcel'])->name('generators.import.excel');
        Route::post('/generators/batch-status', [GeneratorController::class, 'batchUpdateStatus'])->name('generators.batch-status');
        Route::delete('/generators/batch-destroy', [GeneratorController::class, 'batchDestroy'])->name('generators.batch-destroy');
        Route::patch('/generators/{generator}/status', [GeneratorController::class, 'updateStatus'])->name('generators.update-status');
        Route::get('/generators/{generator}/qr', [GeneratorController::class, 'generateQRCode'])->name('generators.qr');
        Route::resource('generators', GeneratorController::class);
        Route::post('/generators/{generator}/receive', [GeneratorController::class, 'receiveInWarehouse'])->name('generators.receive');

        // Control de Refacciones
        Route::get('/spare-parts/export/excel', [SparePartController::class, 'exportExcel'])->name('spare-parts.export.excel');
        Route::resource('spare-parts', SparePartController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | OPERACIONES (Técnico / Taller - Usuario 3)
    |--------------------------------------------------------------------------
    */
    Route::prefix('operations')->name('operations.')->middleware(['check.role:admin,technician'])->group(function () {
        
        // Revisiones (Checklist)
        Route::get('/revisions/scan', [RevisionController::class, 'scan'])->name('revisions.scan');
        Route::post('/revisions/store', [RevisionController::class, 'store'])->name('revisions.store');

        // Taller de Reparación
        Route::resource('workshop', WorkshopController::class);
        Route::post('workshop/{workshop}/add-parts', [WorkshopController::class, 'addSpareParts'])->name('workshop.add_parts');
        Route::post('workshop/{workshop}/toggle-payment', [WorkshopController::class, 'togglePayment'])->name('workshop.toggle_payment');
    });

    /*
    |--------------------------------------------------------------------------
    | LOGÍSTICA Y ENVÍOS
    |--------------------------------------------------------------------------
    */
    Route::prefix('logistics')->name('logistics.')->middleware(['check.role:admin,owner'])->group(function () {
        
        // Envíos a Sucursal
        Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
        Route::post('/shipments/send', [ShipmentController::class, 'sendToBranch'])->name('shipments.send');
        Route::post('/shipments/{shipment}/receive', [ShipmentController::class, 'receiveAtBranch'])->name('shipments.receive');
    });

    /*
    |--------------------------------------------------------------------------
    | CLIENTES Y SEPARACIONES (Usuario 4)
    |--------------------------------------------------------------------------
    */
    Route::prefix('store')->name('store.')->middleware(['check.role:client'])->group(function () {
        
        // Catálogo de Sucursal
        Route::get('/available', [GeneratorController::class, 'availableInBranch'])->name('available');
        
        // Sistema de Separación (4 Horas)
        Route::post('/reserve/{generator}', [ReservationController::class, 'reserve'])->name('reserve');
        Route::get('/reservations', [ReservationController::class, 'myReservations'])->name('reservations');
    });

    /*
    |--------------------------------------------------------------------------
    | PUNTO DE VENTA (Owner)
    |--------------------------------------------------------------------------
    */
    Route::prefix('pos')->name('owner.pos.')->middleware(['check.role:owner,admin'])->group(function () {
        Route::get('/products', [PosController::class, 'products'])->name('products');
        Route::put('/products/{generator}/price', [PosController::class, 'updatePrice'])->name('update_price');
        
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/store', [PosController::class, 'store'])->name('store');
        Route::get('/sales', [PosController::class, 'sales'])->name('sales');
        Route::get('/sales/{sale}/ticket', [PosController::class, 'printTicket'])->name('print_ticket');
    });

});