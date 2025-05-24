<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TestDataController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;

// Redirect root to login for guests
Route::get('/', function() {
    return redirect()->route('login');
})->middleware('guest')->name('root');

// Home and all other pages (require authentication and email verification)
Route::middleware(['auth', 'verified'])->group(function () {
    // Home page
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // Web Pages (protected access)
    Route::get('/about', [App\Http\Controllers\AboutController::class, 'index'])->name('about');
    Route::view('/blog', 'webfront.blog')->name('blog');
    Route::view('/blog-details', 'webfront.blog-details')->name('blog.details');
    Route::get('/shop', [ProductController::class, 'shop'])->name('shop');
    Route::get('/shop-details/{id}', [ProductController::class, 'showDetails'])->name('shop.details');
    Route::view('/contact', 'webfront.contact')->name('contact');
    Route::view('/contactform', 'webfront.contactform')->name('contactform');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');
});

// Email verification routes
Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
    ->middleware('auth')
    ->name('verification.notice');

Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// Password confirmation routes
Route::middleware('auth')->group(function () {
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

// Socialite Routes
Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirectToProvider'])->name('socialite.redirect');
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'handleProviderCallback'])->name('socialite.callback');

// Customer Routes (requires authentication and email verification)
Route::middleware(['auth', 'verified'])->group(function () {
    // Shopping Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::put('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    
    // Checkout Routes
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
    
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/account/delete', [AuthController::class, 'deleteAccount'])->name('account.delete');
    
    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// Staff Routes (requires staff role)
Route::middleware(['auth', \App\Http\Middleware\CheckRole::class.':staff'])->group(function () {
    Route::get('/staff/dashboard', function () {
        return view('staff.dashboard');
    })->name('staff.dashboard');
    
    Route::get('/staff/orders', [OrderController::class, 'staffIndex'])->name('staff.orders.index');
    Route::put('/staff/orders/{order}', [OrderController::class, 'updateStatus'])->name('staff.orders.update');
});

// Manager Routes (requires manager role)
Route::middleware(['auth', \App\Http\Middleware\CheckRole::class.':manager'])->group(function () {
    Route::get('/manager/dashboard', function () {
        return view('manager.dashboard');
    })->name('manager.dashboard');
    
    // Product Management
    Route::resource('products', ProductController::class);
    
    // Staff Management
    Route::get('/staff', [ProfileController::class, 'staffIndex'])->name('staff.index');
    Route::put('/staff/{user}/role', [RoleController::class, 'assignRole'])->name('staff.role.update');
    
    // Reports
    Route::get('/reports/sales', [OrderController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/inventory', [ProductController::class, 'inventoryReport'])->name('reports.inventory');
});

// Admin Routes (requires admin role)
Route::middleware(['auth', \App\Http\Middleware\CheckRole::class.':admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    // User Management
    Route::get('/users', [ProfileController::class, 'index'])->name('users.index');
    Route::get('/users/create', [ProfileController::class, 'create'])->name('users.create');
    Route::post('/users', [ProfileController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [ProfileController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [ProfileController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [ProfileController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [ProfileController::class, 'destroy'])->name('users.destroy');
    
    // Role Management
    Route::resource('roles', RoleController::class);
    
    // System Settings
    Route::get('/settings', function () {
        return view('admin.settings');
    })->name('settings');
    Route::put('/settings', [ProfileController::class, 'updateSettings'])->name('settings.update');
});

// Admin routes
Route::middleware(['auth', \App\Http\Middleware\CheckRole::class.':admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('roles', RoleController::class);
    Route::resource('products', ProductController::class);
    // Route::resource('users', UserController::class); // Removed: No UserController exists, ProfileController handles users
    Route::get('/settings', function () {
        return view('admin.settings');
    })->name('settings');
});

// Manager routes
Route::middleware(['auth', \App\Http\Middleware\CheckRole::class.':manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/sales-data', [ManagerDashboardController::class, 'getSalesDataForPeriod'])->name('dashboard.sales-data');
    Route::resource('products', ProductController::class);
    Route::resource('staff', StaffController::class);
    Route::get('/reports/sales', [OrderController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/inventory', [ProductController::class, 'inventoryReport'])->name('reports.inventory');
    
    // Comment out PDF export routes until they're properly implemented
    // Route::get('/reports/sales/pdf', [OrderController::class, 'salesReportPdf'])->name('reports.sales.pdf');
    // Route::get('/reports/inventory/pdf', [ProductController::class, 'inventoryReportPdf'])->name('reports.inventory.pdf');
});

// Staff routes
Route::middleware(['auth', \App\Http\Middleware\CheckRole::class.':staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [OrderController::class, 'staffIndex'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}', [OrderController::class, 'updateStatus'])->name('orders.update');
    
    // Customer Management
    Route::get('/customers', [UserController::class, 'staffCustomersIndex'])->name('customers.index');
    Route::get('/customers/{user}', [UserController::class, 'staffCustomerShow'])->name('customers.show');
    Route::get('/customers/{user}/orders', [UserController::class, 'staffCustomerOrders'])->name('customers.orders');
    
    // Inventory Management
    Route::get('/inventory', [ProductController::class, 'staffInventory'])->name('inventory');
});

Route::post('/products/{id}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

// Test Data routes - FOR DEVELOPMENT ONLY
Route::get('/test-data', [TestDataController::class, 'index'])->name('test-data');
Route::get('/generate-test-data', [TestDataController::class, 'generateData'])->name('generate-test-data');

// Add this route near the end of the file, before any route groups
Route::get('/test-charts', function() {
    return view('test-charts');
});

require __DIR__.'/auth.php';