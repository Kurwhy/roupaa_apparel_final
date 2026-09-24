<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\AIController;
use App\Http\Controllers\Customer\CustomerNotificationController as CustomerNotifController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderChatController;
use App\Http\Controllers\OrderSpecificationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\PortofolioController;
use App\Http\Controllers\Owner\LaporanController;
use App\Http\Controllers\Owner\AdminManagementController;


// 1. ZONA PUBLIK & AUTH
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->middleware('throttle:10,1');
    Route::get('/register', 'showRegister')->name('register');
    Route::post('/register', 'register')->middleware('throttle:5,1');
    Route::post('/logout', 'logout')->name('logout');
});

// Lupa Password
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:5,1');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update')->middleware('throttle:5,1');

// Webhook Midtrans — di luar semua prefix, tanpa CSRF
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])
    ->name('payment.webhook')
    ->withoutMiddleware('App\Http\Middleware\VerifyCsrfToken');

Route::get('/api/portofolio-public', function () {
    $photos = \App\Models\Portofolio::whereNull('deleted_at')
        ->orderByDesc('created_at')
        ->get()
        ->map(fn($p) => [
            'image_url'  => \Illuminate\Support\Facades\Storage::url($p->image_path),
            'keterangan' => $p->keterangan ?? '',
        ]);
    return response()->json(['photos' => $photos]);
})->name('api.portofolio.public');

// ==========================================
// 2. ZONA PELANGGAN (Views & API)
// ==========================================
Route::prefix('customer')->group(function () {

    // --- TAMPILAN (DIBUKA) ---
    Route::controller(OrderController::class)->group(function () {
        Route::get('/custom-order', 'create')->name('customer.order.create');
        Route::get('/progress-pesanan', 'index')->name('customer.order.history');
        Route::get('/progress-pesanan/{id}', 'show')->name('customer.order.show');
    });

    Route::get('/ai-studio', [AIController::class, 'index'])->name('customer.ai.index');
    Route::get('/ai-studio/riwayat', [AIController::class, 'historyView'])->name('customer.ai.history');

    // --- PROSES DATA (DIKUNCI JWT) ---
    Route::middleware(['auth:api', 'role:pelanggan'])->group(function () {

        Route::get('/api/progress-pesanan', [OrderController::class, 'getOrdersData'])->name('customer.order.api.data');
        Route::get('/api/progress-pesanan/{id}', [OrderController::class, 'getOrderDetails'])->name('customer.order.api.show');

        Route::post('/custom-order', [OrderController::class, 'store'])->name('customer.order.store');
        Route::post('/order/{id}/approve-design', [OrderController::class, 'approveDesign'])->name('customer.order.design.approve');
        Route::post('/progress-pesanan/{id}/specs', [OrderSpecificationController::class, 'store'])->name('customer.order.specs.store');

        // Pembayaran Midtrans
        Route::post('/order/{id}/snap-token', [PaymentController::class, 'getSnapToken'])->name('customer.order.snap-token');
        Route::post('/order/{id}/confirm-payment', [PaymentController::class, 'confirmFromCallback'])
            ->name('customer.order.confirm-payment');

        // Chat Pelanggan
        Route::controller(OrderChatController::class)->group(function () {
            Route::post('/progress-pesanan/{id}/chat', 'store')->name('customer.order.chat.store');
            Route::post('/progress-pesanan/{id}/chat/read', 'markAsRead')->name('customer.order.chat.read');
        });

        Route::get('/api/notifications', [CustomerNotifController::class, 'index'])->name('customer.api.notifications');
        Route::post('/api/notifications/{id}/read', [CustomerNotifController::class, 'markRead'])->name('customer.api.notifications.read');
        Route::post('/api/notifications/read-all', [CustomerNotifController::class, 'markAllRead'])->name('customer.api.notifications.read-all');
        Route::post('/api/notifications/order/{orderId}/read', [CustomerNotifController::class, 'markReadByOrder'])->name('customer.api.notifications.read-by-order');
        // API AI Studio
        Route::prefix('ai-studio')->name('customer.ai.')->controller(AIController::class)->group(function () {
            Route::post('/generate', 'generate')->name('generate')->middleware('throttle:10,1');
            Route::post('/save', 'saveToLibrary')->name('save');
            Route::get('/history-data', 'historyData')->name('history.data');
            Route::get('/session-data', 'getSessionData')->name('session');
        });
    });
});

// ==========================================
// 3. ZONA OPS PABRIK (Admin & Owner)
// ==========================================
Route::prefix('ops')->name('ops.')->group(function () {

    // --- TAMPILAN (DIBUKA) ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pesanan', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::get('/pelanggan', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/pelanggan/{id}', [AdminCustomerController::class, 'show'])->name('customers.show');
    Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('inventory.index');
    Route::get('/profil', [AdminProfileController::class, 'index'])->name('profile.admin.index');


    // --- PROSES DATA (DIKUNCI JWT) ---
    Route::middleware(['auth:api', 'role:admin,owner'])->group(function () {

        Route::get('/api/dashboard', [DashboardController::class, 'getDashboardData'])->name('api.dashboard');

        // Notifikasi Admin
        Route::get('/api/notifications', [NotificationController::class, 'index'])
            ->name('api.notifications');
        Route::post('/api/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('api.notifications.read');
        Route::post('/api/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
            ->name('api.notifications.read-all');
        Route::post('/api/notifications/order/{orderId}/read', [NotificationController::class, 'markReadByOrder'])
            ->name('api.notifications.read-by-order');

        Route::get('/api/profil', [AdminProfileController::class, 'getData'])->name('profile.admin.data');
        Route::put('/api/profil', [AdminProfileController::class, 'update'])->name('profile.admin.update');
        Route::put('/api/profil/password', [AdminProfileController::class, 'updatePassword'])->name('profile.admin.password');

        Route::get('/api/pelanggan', [AdminCustomerController::class, 'getData'])->name('api.customers.data');
        Route::post('/api/pelanggan', [AdminCustomerController::class, 'store'])->name('api.customers.store');
        Route::put('/api/pelanggan/{id}', [AdminCustomerController::class, 'update'])->name('api.customers.update');
        Route::delete('/api/pelanggan/{id}', [AdminCustomerController::class, 'destroy'])->name('api.customers.destroy');
        Route::post('/api/pelanggan/{id}/restore', [AdminCustomerController::class, 'restore'])->name('api.customers.restore');

        Route::get('/api/portofolio', [PortofolioController::class, 'getData'])->name('api.portofolio.data');
        Route::post('/api/portofolio', [PortofolioController::class, 'store'])->name('api.portofolio.store');
        Route::put('/api/portofolio/{id}', [PortofolioController::class, 'update'])->name('api.portofolio.update');
        Route::delete('/api/portofolio/{id}', [PortofolioController::class, 'destroy'])->name('api.portofolio.destroy');
        Route::post('/api/portofolio/{id}/restore', [PortofolioController::class, 'restore'])->name('api.portofolio.restore');

        Route::controller(AdminOrderController::class)->group(function () {
            Route::post('/pesanan/{id}/mockup', 'uploadMockup')->name('orders.mockup.upload');
            Route::post('/pesanan/{id}/mockup/revisi', 'revisiMockup')->name('orders.mockup.revisi');
            Route::post('/pesanan/{id}/lanjutkan', 'lanjutkanPesanan')->name('orders.lanjutkan');
            Route::post('/pesanan/{id}/set-harga', 'setFinalPrice')->name('orders.set-harga');
            Route::post('/pesanan/{id}/selesai-produksi', 'markComplete')->name('orders.mark-complete');
            Route::post('/pesanan/{id}/selesai-kirim', 'markDelivered')->name('orders.mark-delivered');
            Route::get('/api/pesanan', 'getData')->name('api.orders.data');
        });

        Route::controller(OrderChatController::class)->group(function () {
            Route::post('/pesanan/{id}/chat', 'store')->name('orders.chat.store');
            Route::post('/pesanan/{id}/chat/read', 'markAsRead')->name('orders.chat.read');
        });

        Route::controller(AdminInventoryController::class)->group(function () {
            Route::post('/inventory/update-stock', 'updateStock')->name('inventory.update-stock');
            Route::post('/inventory/store-apparel', 'storeApparel')->name('inventory.store-apparel');
            Route::post('/inventory/print-restock', 'printRestock')->name('inventory.print-restock');
            Route::post('/inventory/apply-restock', 'applyRestock')->name('inventory.apply-restock');
            Route::delete('/inventory/apparel/{id}', 'destroyApparel')->name('inventory.destroy-apparel');
        });
    });

    // --- OWNER: TAMPILAN ---
    Route::get('/laporan', [LaporanController::class, 'index'])->name('owner.laporan.index');
    Route::get('/kelola-admin', [AdminManagementController::class, 'index'])->name('owner.admins.index');

    // --- OWNER: API (hanya role owner) ---
    Route::middleware(['auth:api', 'role:owner'])->group(function () {
        Route::get('/api/laporan', [LaporanController::class, 'getData'])->name('owner.api.laporan');
        Route::get('/api/kelola-admin', [AdminManagementController::class, 'getData'])->name('owner.api.admins.data');
        Route::post('/api/kelola-admin', [AdminManagementController::class, 'store'])->name('owner.api.admins.store');
        Route::put('/api/kelola-admin/{id}', [AdminManagementController::class, 'update'])->name('owner.api.admins.update');
        Route::delete('/api/kelola-admin/{id}', [AdminManagementController::class, 'destroy'])->name('owner.api.admins.destroy');
        Route::post('/api/kelola-admin/{id}/restore', [AdminManagementController::class, 'restore'])->name('owner.api.admins.restore');
    });

    Route::get('/portofolio', [PortofolioController::class, 'index'])->name('portofolio.index');
});

// ==========================================
// 4. ZONA UMUM LOGIN
// ==========================================
Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');

Route::middleware('auth:api')->group(function () {
    Route::get('/profile/data', [ProfileController::class, 'getData'])->name('profile.api.data');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});
