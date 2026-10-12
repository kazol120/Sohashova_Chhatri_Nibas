<?php

use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\FloorController;
use App\Http\Controllers\Frontend\WelcomeCotroller;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Backend\RoomController;
use App\Http\Controllers\Backend\StaffsController;
use App\Http\Controllers\Frontend\NoticeController;
use App\Http\Controllers\Backend\RoomBookingHistoryController;
use App\Http\Controllers\Frontend\HelpDeskController;
use App\Http\Controllers\Frontend\ResidenceOverviewController;
use App\Http\Controllers\Frontend\GalleryController;
use App\Http\Controllers\Frontend\SliderController;
use App\Http\Controllers\Backend\HostelRuleController;
use App\Http\Controllers\Backend\SpecialMealController;
use App\Http\Controllers\Backend\GuestMealController;
use App\Http\Controllers\Backend\StaffAttendanceController;
use App\Http\Controllers\Backend\StaffSalaryController;
use App\Http\Controllers\Backend\ExpenseController;
use App\Http\Controllers\Backend\ExpenseTypeController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\SupplierContoller;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\BrandController;
use App\Http\Controllers\Backend\BrandCategoryController;
use App\Http\Controllers\Backend\PurchaseController;
use App\Http\Controllers\Backend\ProductStockController;
use App\Http\Controllers\Backend\ManageSaleController;
use App\Http\Controllers\Backend\ProductDistributionController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\CustomerReportController;
use App\Http\Controllers\Backend\HotelManagementController;
use App\Http\Controllers\Backend\MonthlyPaymentController;
use App\Http\Controllers\Frontend\MyPaymentController;
use App\Http\Controllers\Backend\MealController;
use App\Http\Controllers\Backend\DepositController;
use App\Http\Controllers\Backend\FineController;
use App\Http\Controllers\Backend\ResidentCredentialController;
use App\Http\Controllers\Backend\ResidentHistoryController;
use App\Http\Controllers\Frontend\ResidentAuthController;
use App\Http\Controllers\Backend\ResidentNoticeController;
use App\Http\Controllers\Backend\ElectricityBillController;

/*
|--------------------------------------------------------------------------
| Authentication & Public Security Routes
|--------------------------------------------------------------------------
*/
Auth::routes();
Route::match(['get', 'post'], 'logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// CSRF Token Refresh endpoints
Route::get('csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});
Route::get('/refresh-csrf', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
        'status' => 'success'
    ]);
})->name('refresh-csrf');

/*
|--------------------------------------------------------------------------
| Public Frontend Routes (Homepage, Booking, Helpdesk, Resident OTP)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'homepage'])->name('home');

Route::controller(PasswordResetController::class)->group(function () {
    Route::get('send-otp', 'sendOtp')->name('send-otp');
    Route::post('verify-otp', 'checkOtp')->name('verify-otp');
    Route::get('verify-token/{token}', 'validateToken')->name('verify-token');
    Route::post('update-password', 'updatePassword')->name('update-password');
});

Route::get('booking-now', [HomeController::class, 'bookingpage'])->name('booking-now.bookingpage');
Route::get('/get-live-booked-seats', [HomeController::class, 'getLiveBookedSeats']);
Route::get('guest-support', [HomeController::class, 'HelpDesk'])->name('guest-support.HelpDesk');
Route::get('floor/{id}/rooms', [HomeController::class, 'floorRooms']);
Route::post('/helpdesk-send', [HelpDeskController::class, 'sendHelpDeskEmail'])->name('helpdesk.send');

// Public Geo & Guest lookup endpoints used by Frontend Booking form
Route::get('divisions', [StaffsController::class, 'getDivisions']);
Route::get('divisions/{division}/districts', [StaffsController::class, 'getDistrictsByDivision']);
Route::get('districts/{district}/thanas', [StaffsController::class, 'getThanasByDistrict']);
Route::post('room-booking-history/store', [RoomBookingHistoryController::class, 'store'])->name('room-booking-history.store');
Route::get('/guest/by-phone/{phone}', [RoomBookingHistoryController::class, 'getGuestByPhone']);
Route::get('/get-select-guet', [RoomBookingHistoryController::class, 'getNameguet']);

// Public Resident Search & Multi-Method Auth Routes
Route::get('/resident-overview', [ResidentAuthController::class, 'publicOverview'])->name('resident.overview');
Route::prefix('resident-auth')->group(function () {
    Route::post('/search-phone', [ResidentAuthController::class, 'searchPhone'])->name('resident.search-phone');
    Route::post('/login-password', [ResidentAuthController::class, 'loginPassword'])->name('resident.login-password');
    Route::post('/send-login-otp', [ResidentAuthController::class, 'sendLoginOtp'])->name('resident.send-login-otp');
    Route::post('/verify-login-otp', [ResidentAuthController::class, 'verifyLoginOtp'])->name('resident.verify-login-otp');
    Route::post('/send-forgot-otp', [ResidentAuthController::class, 'sendForgotOtp'])->name('resident.send-forgot-otp');
    Route::post('/reset-password-otp', [ResidentAuthController::class, 'resetPasswordWithOtp'])->name('resident.reset-password-otp');
});

// Resident Notice (জরুরি বিজ্ঞপ্তি) Public Board & Alert APIs
Route::get('/resident-notices-board', [ResidentNoticeController::class, 'residentNoticeBoardPage'])->name('resident.notice-board');
Route::get('api/resident-notices/latest-alert', [ResidentNoticeController::class, 'getLatestAlert']);
Route::get('api/resident-notices/board', [ResidentNoticeController::class, 'getResidentNoticeBoard']);
Route::post('api/resident-notices/verify', [ResidentNoticeController::class, 'verifyResidentAndGetNotices']);

// Resident Portal Check In / Out
Route::post('/api/resident/check-out', [\App\Http\Controllers\Backend\ResidentCheckInOutController::class, 'storeResident'])->name('resident.check-out');
Route::post('/api/resident/check-in', [\App\Http\Controllers\Backend\ResidentCheckInOutController::class, 'checkInResident'])->name('resident.check-in');
Route::get('/api/resident/check-in-out-status', [\App\Http\Controllers\Backend\ResidentCheckInOutController::class, 'getResidentStatus'])->name('resident.check-in-out-status');

/*
|--------------------------------------------------------------------------
| Protected Backend / Admin Routes (Requires Authentication & Active Status)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'checkUserStatus'])->group(function () {

    // User Role lookup
    Route::get('/get-user-role', function () {
        return response()->json([
            'role' => auth()->user()->roles->first()->name ?? ''
        ]);
    });

    // Resident Credentials & Password Mail Dispatch
    Route::prefix('resident-credentials')->group(function () {
        Route::get('/', [ResidentCredentialController::class, 'index'])->name('resident-credentials.index');
        Route::post('/send-single', [ResidentCredentialController::class, 'sendSingleEmail'])->name('resident-credentials.send-single');
        Route::post('/send-bulk', [ResidentCredentialController::class, 'sendBulkEmail'])->name('resident-credentials.send-bulk');
        Route::post('/update-password', [ResidentCredentialController::class, 'updatePassword'])->name('resident-credentials.update-password');
    });

    // Resident 360 Lifetime Profile & History
    Route::prefix('resident-history')->group(function () {
        Route::get('/', [ResidentHistoryController::class, 'index'])->name('resident-history.index');
        Route::get('/{id}', [ResidentHistoryController::class, 'show'])->name('resident-history.show');
        Route::get('/{id}/print', [ResidentHistoryController::class, 'printStatement'])->name('resident-history.print');
    });

    // Staffs Management
    Route::get('staffs', [StaffsController::class, 'index'])->name('staffs.index');
    Route::post('staffs/store', [StaffsController::class, 'store'])->name('staffs.store');
    Route::get('/staffs-get', [StaffsController::class, 'getstaffsGet'])->name('staffs-get.getstaffsGet'); 
    Route::post('/staffs-update/{id}', [StaffsController::class, 'update']);
    Route::delete('/staffs-delete/{id}', [StaffsController::class, 'destroy']);

    // Room Booking History
    Route::get('room-booking-history', [RoomBookingHistoryController::class, 'getroombookinghistory']);
    Route::get('get-room-booking', [RoomBookingHistoryController::class, 'getbookinghistory'])->name('get-room-booking.getbookinghistory');
    Route::get('today-room-booking-history', [RoomBookingHistoryController::class, 'todaygetroombookinghistory']);
    Route::get('today-get-room-booking', [RoomBookingHistoryController::class, 'todaygetbookinghistory'])->name('today-get-room-booking.todaygetbookinghistory');
    Route::get('/room-booking/find-guest', [RoomBookingHistoryController::class, 'findGuestByPhone'])->name('room.booking.findGuest');

    // Floor Management
    Route::get('floor', [FloorController::class, 'index'])->name('floor.index');
    Route::post('floors-create', [FloorController::class, 'store'])->name('floors.store');
    Route::get('floors-get', [FloorController::class, 'floorget'])->name('floors-get.floorget');
    Route::post('floors-update/{id}', [FloorController::class, 'update']);
    Route::delete('floors-delete/{id}', [FloorController::class, 'destroy'])->name('floors.delete');
    Route::post('/rooms-store-multiple', [FloorController::class, 'storeMultipleRooms']);
    Route::get('/floors/all', [FloorController::class, 'all']); 

    // Room Management
    Route::get('room-list', [RoomController::class, 'index'])->name('room-list.index');
    Route::get('/rooms-get', [RoomController::class, 'getRooms']); 
    Route::get('/rooms/by-floor/{floor}', [RoomController::class, 'roomsByFloor'])->name('roomsByFloor'); 
    Route::post('/room-store', [RoomController::class, 'store']);
    Route::post('/room-update/{room}', [RoomController::class, 'update']);
    Route::delete('/room-delete/{room}', [RoomController::class, 'destroy']);

    // Notice Management
    Route::get('notice', [NoticeController::class, 'index'])->name('notice.index');
    Route::get('/notice-get', [NoticeController::class, 'get']);
    Route::post('/notice-store', [NoticeController::class, 'store']);
    Route::post('/notice-update/{id}', [NoticeController::class, 'update']);
    Route::delete('/notice-delete/{id}', [NoticeController::class, 'destroy']);

    // Resident Notice Management
    Route::get('resident-notices-manage', [ResidentNoticeController::class, 'index'])->name('resident-notices.index');
    Route::get('resident-notices-get', [ResidentNoticeController::class, 'get']);
    Route::post('resident-notices-store', [ResidentNoticeController::class, 'store']);
    Route::post('resident-notices-update/{id}', [ResidentNoticeController::class, 'update']);
    Route::post('resident-notices-toggle-status/{id}', [ResidentNoticeController::class, 'toggleStatus']);
    Route::delete('resident-notices-delete/{id}', [ResidentNoticeController::class, 'destroy']);

    // Residence Overview Management
    Route::get('residence-overview', [ResidenceOverviewController::class, 'index'])->name('residence-overview.index');
    Route::post('residence-overview-create', [ResidenceOverviewController::class, 'store']);
    Route::get('residence-overview-get', [ResidenceOverviewController::class, 'getrelaxplace']);
    Route::post('/residence-overview-update/{id}', [ResidenceOverviewController::class, 'update']);
    Route::delete('/residence-overview-delete/{id}', [ResidenceOverviewController::class, 'destroy']);

    // Gallery Management
    Route::get('gallery', [GalleryController::class, 'index'])->name('gallery.index');
    Route::post('gallery-store', [GalleryController::class, 'store']);
    Route::get('gallery-get', [GalleryController::class, 'getluxuryroom'])->name('gallery-get.getluxuryroom');
    Route::post('gallery-update/{id}', [GalleryController::class, 'update']);
    Route::delete('gallery-delete/{id}', [GalleryController::class, 'destroy']);

    // Slider Management
    Route::get('sliders', [SliderController::class, 'index'])->name('slider.index');
    Route::post('slider-store', [SliderController::class, 'store']);
    Route::get('slider-get', [SliderController::class, 'getSliders'])->name('slider-get.getSliders');
    Route::post('slider-update/{id}', [SliderController::class, 'update']);
    Route::delete('slider-delete/{id}', [SliderController::class, 'destroy']);

    // Hostel Rules Management
    Route::get('hostel-rules', [HostelRuleController::class, 'index'])->name('hostel-rules.index');
    Route::get('hostel-rules-get', [HostelRuleController::class, 'getRules'])->name('hostel-rules.get');
    Route::post('hostel-rules-store', [HostelRuleController::class, 'store'])->name('hostel-rules.store');
    Route::post('hostel-rules-update/{id}', [HostelRuleController::class, 'update'])->name('hostel-rules.update');
    Route::delete('hostel-rules-delete/{id}', [HostelRuleController::class, 'destroy'])->name('hostel-rules.destroy');

    // Special Meals Management
    Route::get('special-meals', [SpecialMealController::class, 'index'])->name('special-meals.index');
    Route::post('special-meals', [SpecialMealController::class, 'store'])->name('special-meals.store');
    Route::delete('special-meals/{id}', [SpecialMealController::class, 'destroy'])->name('special-meals.destroy');
    Route::post('special-meals/{id}/toggle-status', [SpecialMealController::class, 'toggleStatus'])->name('special-meals.toggle-status');

    // Guest Meals Management
    Route::resource('guest-meals', GuestMealController::class);

    // Staff Attendance
    Route::get('staffs-attendance', [StaffAttendanceController::class, 'index'])->name('staffs-attendance.index');
    Route::get('staffs/search', [StaffAttendanceController::class, 'search']);
    Route::post('staff-attendance', [StaffAttendanceController::class, 'store']);
    Route::get('/staffs-get-attendance', [StaffAttendanceController::class, 'GetAttendance']);
    Route::put('staffs-attendance/{id}', [StaffAttendanceController::class, 'update']);
    Route::get('get-select-staff', [StaffAttendanceController::class, 'getstaffname']);

    // Staff Salary Management
    Route::get('/staff-salary', [StaffSalaryController::class, 'index'])->name('staff-salary.index');
    Route::get('/search-staff', [StaffSalaryController::class, 'searchStaff'])->name('search-staff.searchStaff');
    Route::get('/summary/{staffId}', [StaffSalaryController::class, 'summary'])->name('staff-salary.summary');
    Route::get('/history/{staffId}', [StaffSalaryController::class, 'history'])->name('staff-salary.history');
    Route::post('/advance', [StaffSalaryController::class, 'storeAdvance'])->name('staff-salary.advance');
    Route::post('/full', [StaffSalaryController::class, 'storeFull'])->name('staff-salary.full');
    Route::get('/staff-salary-get', [StaffSalaryController::class, 'getstaffsalary'])->name('staff-salary-get.getstaffsalary');

    // Expense Types & Categories
    Route::get('/expense-type', [ExpenseTypeController::class, 'index'])->name('expense-type.index');
    Route::post('expense-categories', [ExpenseTypeController::class, 'store']);
    Route::get('/expense-categories', [ExpenseTypeController::class, 'getexpensetype'])->name('expense-categories.getexpensetype');
    Route::put('/expense-categories/{id}', [ExpenseTypeController::class, 'update'])->name('expense-categories.update');
    Route::delete('/expense-categories/{id}', [ExpenseTypeController::class, 'destroy'])->name('expense-categories.destroy');
    Route::post('/expense-type-create', [ExpenseTypeController::class, 'store']);
    Route::delete('/expense-type-delete/{id}', [ExpenseTypeController::class, 'destroy']);

    // Expenses Management
    Route::get('/expense', [ExpenseController::class, 'index'])->name('expense.index');
    Route::get('/today-expense', [ExpenseController::class, 'index'])->name('today-expense.index');
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::get('/expense-categories-list', [ExpenseController::class, 'getAll'])->name('expense-categories-list.getAll');
    Route::put('/expense-update/{id}', [ExpenseController::class, 'update'])->name('expense.update');
    Route::delete('/expense-delete/{id}', [ExpenseController::class, 'destroy'])->name('expense.destroy');
    Route::get('/expense-type-list', [ExpenseController::class, 'getAll'])->name('expense-type-list.getAll');
    Route::get('/get-select-expense', [ExpenseController::class, 'getexpensecategory'])->name('get-select-expense.getexpensecategory');
    Route::get('/expense-list', [ExpenseController::class, 'getexpenselilst'])->name('expense-list.getexpenselilst');
    Route::get('/today-expense-list', [ExpenseController::class, 'getTodayExpenses'])->name('today-expense-list.getTodayExpenses');

    // Suppliers Management
    Route::get('/supplier', [SupplierContoller::class, 'index'])->name('supplier.index');
    Route::post('/supplier-create', [SupplierContoller::class, 'store']);
    Route::get('/get-supplier', [SupplierContoller::class, 'getsupplier']);
    Route::post('/supplier-update-data/{id}', [SupplierContoller::class, 'update']);
    Route::delete('/supplier-delete/{id}', [SupplierContoller::class, 'destroy']);

    // Brand Management
    Route::get('/brand', [BrandController::class, 'index'])->name('brand.index');
    Route::get('/get-select-brand-category', [BrandController::class, 'getselectbrandCategory']);
    Route::post('/new-brand-create', [BrandController::class, 'store']);
    Route::get('/get-brand', [BrandController::class, 'getBrand'])->name('get-brand.getBrand');
    Route::post('/brand-update-data/{id}', [BrandController::class, 'update']);
    Route::delete('/brand-delete/{id}', [BrandController::class, 'destroy']);

    // Brand Categories
    Route::get('/brand-category', [BrandCategoryController::class, 'index'])->name('brand-category.index');
    Route::post('/brand-category-create', [BrandCategoryController::class, 'store']);
    Route::get('/get-brand-category', [BrandCategoryController::class, 'GetBrandCategory'])->name('get-brand-category.GetBrandCategory');
    Route::post('/brand-category-update-data/{id}', [BrandCategoryController::class, 'update']);
    Route::delete('/brand-category-delete/{id}', [BrandCategoryController::class, 'destroy']);

    // Products Management
    Route::get('/product', [ProductController::class, 'index'])->name('product.index');
    Route::get('/get-select-brand', [ProductController::class, 'SelectGetBrand']);
    Route::get('/select-brand-category', [ProductController::class, 'SelectGetBrandcategory']);
    Route::post('/product-store', [ProductController::class, 'store']);
    Route::get('/get-product', [ProductController::class, 'getproductlist']);
    Route::post('/product-update-data/{id}', [ProductController::class, 'update']);
    Route::delete('/product-delete/{id}', [ProductController::class, 'destroy']);

    // Product Stock & Purchase
    Route::get('/product-stock', [ProductStockController::class, 'index'])->name('product-stock.index');
    Route::get('/select-supplier', [ProductStockController::class, 'getselectsupplier']);
    Route::get('/get-select-product', [ProductStockController::class, 'getselectproduct']);
    Route::post('/product-purchase-stock-store', [ProductStockController::class, 'store']);
    Route::get('/get-select-supplier', [ProductStockController::class, 'getsupplier']);
    Route::get('/product_purchase_list', [ProductStockController::class, 'Productpurchaselist']);
    Route::delete('/product-purchase-delete/{id}', [ProductStockController::class, 'destroy']);
    Route::post('/product-quantity-add', [ProductStockController::class, 'QuantityStore']);

    // Product Distribution & Sales
    Route::get('/product-distribution', [ProductDistributionController::class, 'index'])->name('product-distribution.index');
    Route::get('/today-product-distribution', [ProductDistributionController::class, 'index'])->name('today-product-distribution.index');
    Route::get('get-select-product-sale', [ProductDistributionController::class, 'getselectproductSale']);
    Route::get('/product-districbution-list', [ProductDistributionController::class, 'customerdistributionlist']);
    Route::get('/today-product-distribution-list', [ProductDistributionController::class, 'todayDistributionList']);
    Route::get('/get-select-customer', [ProductDistributionController::class, 'getcustomer']);
    Route::delete('/customerlist-delete/{id}', [ProductDistributionController::class, 'destroy']);
    Route::post('product-sale-store', [ProductDistributionController::class, 'store']);
    Route::get('floor-select', [ProductDistributionController::class, 'floorSelect']);
    Route::get('floor-room-select/{floor_id}', [ProductDistributionController::class, 'floorRoomSelect']);
    Route::get('room-seats-select/{room_id}', [ProductDistributionController::class, 'roomSeatsSelect']);
    Route::get('room-customers-select/{room_id}', [ProductDistributionController::class, 'roomCustomersSelect']);
    Route::get('seat-customers-select/{seat_id}', [ProductDistributionController::class, 'seatCustomersSelect']);
    Route::get('room-customer-auto-load/{room_number}', [ProductDistributionController::class, 'roomCustomerAutoLoad']);

    // Reports & Analytics
    Route::get('report', [ReportController::class, 'index'])->name('report.index');
    Route::get('get-profit-loss-report', [ReportController::class, 'profitLossReport']);
    Route::get('get-monthly-detail-report', [ReportController::class, 'getMonthlyDetailReport']);
    Route::get('get-daily-detail-report', [ReportController::class, 'getDailyDetailReport']);
    Route::get('get-available-years', [ReportController::class, 'availableYears']);
    Route::get('get-productstock', [ReportController::class, 'getproductStock']);

    // Customer Reports
    Route::get('customer-report', [CustomerReportController::class, 'index'])->name('customer-report.index');
    Route::get('get-roombooking', [CustomerReportController::class, 'getroombooing']);
    Route::get('get-productdistribution', [CustomerReportController::class, 'getproductdistributton']);
    Route::post('rooms/active', [CustomerReportController::class, 'activeRoom']);

    // Room Release Management
    Route::get('rooms/release-manager', [RoomBookingHistoryController::class, 'releaseManagerIndex'])->name('room-release.index');
    Route::get('active-bookings', [RoomBookingHistoryController::class, 'getActiveBookings']);
    Route::post('bookings/{id}/schedule-leave', [RoomBookingHistoryController::class, 'scheduleLeave']);
    Route::post('bookings/{id}/cancel-leave', [RoomBookingHistoryController::class, 'cancelLeave']);
    Route::post('bookings/{id}/instant-release', [RoomBookingHistoryController::class, 'instantRelease']);
    Route::get('rooms/release-history', [RoomBookingHistoryController::class, 'releaseHistoryIndex'])->name('room-release.history');
    Route::get('released-bookings', [RoomBookingHistoryController::class, 'getReleaseHistory']);

    // Deleted Booking History
    Route::get('rooms/deleted-history', [RoomBookingHistoryController::class, 'deletedHistoryIndex'])->name('room-deleted.history');
    Route::get('deleted-bookings', [RoomBookingHistoryController::class, 'getDeletedBookings']);
    Route::post('bookings/{id}/restore', [RoomBookingHistoryController::class, 'restoreBooking']);
    Route::delete('bookings/{id}/force-delete', [RoomBookingHistoryController::class, 'forceDeleteBooking']);

    // Room Change / Seat Transfer
    Route::get('rooms/change-manager', [RoomBookingHistoryController::class, 'changeManagerIndex'])->name('room-change.index');
    Route::get('available-seats-tree', [RoomBookingHistoryController::class, 'getAvailableSeatsTree']);
    Route::post('bookings/{id}/change-room-seat', [RoomBookingHistoryController::class, 'changeRoomSeat']);
    Route::get('room-change-histories', [RoomBookingHistoryController::class, 'roomChangeHistories']);

    // Development Fees
    Route::get('development-fees', [RoomBookingHistoryController::class, 'devFeeIndex'])->name('development-fee.index');
    Route::get('development-fees/get', [RoomBookingHistoryController::class, 'getDevFeeList']);
    Route::post('development-fees/{id}/pay', [RoomBookingHistoryController::class, 'payDevFee']);

    // Advance Fees
    Route::get('advance-fees', [RoomBookingHistoryController::class, 'advanceFeeIndex'])->name('advance-fee.index');
    Route::get('advance-fees/get', [RoomBookingHistoryController::class, 'getAdvanceFeeList']);
    Route::post('advance-fees/{id}/pay', [RoomBookingHistoryController::class, 'payAdvanceFee']);
    Route::post('bookings/{id}/activate-account', [RoomBookingHistoryController::class, 'activateBookingAccount']);
    Route::post('bookings/{id}/deactivate-account', [RoomBookingHistoryController::class, 'deactivateBookingAccount']);
    Route::delete('bookings/{id}', [RoomBookingHistoryController::class, 'destroyBooking']);

    // Booking Button Status Setting
    Route::get('booking-button-status', [RoomBookingHistoryController::class, 'bookingButtonIndex'])->name('booking-button.index');
    Route::post('booking-button-status', [RoomBookingHistoryController::class, 'updateBookingButtonStatus'])->name('booking-button.update');

    // Monthly Payments
    Route::get('monthly-payments', [MonthlyPaymentController::class, 'index'])->name('monthly-payments.index');
    Route::get('monthly-payments/get', [MonthlyPaymentController::class, 'getPayments']);
    Route::post('monthly-payments/generate', [MonthlyPaymentController::class, 'generateBills']);
    Route::post('monthly-payments/collect', [MonthlyPaymentController::class, 'collectPayment']);

    // Electricity Bills
    Route::get('electricity-bills', [ElectricityBillController::class, 'index'])->name('electricity-bills.index');
    Route::post('electricity-bills/update-single', [ElectricityBillController::class, 'updateSingle'])->name('electricity-bills.update-single');
    Route::get('electricity-bills/{id}', [ElectricityBillController::class, 'show'])->name('electricity-bills.show');
    Route::get('electricity-bills/{id}/print', [ElectricityBillController::class, 'printStatement'])->name('electricity-bills.print');
    Route::get('electricity-bills-bulk/residents', [ElectricityBillController::class, 'getResidentsForBilling'])->name('electricity-bills.residents-for-billing');
    Route::post('electricity-bills-bulk/create', [ElectricityBillController::class, 'bulkCreate'])->name('electricity-bills.bulk-create');

    // Meal Management
    Route::get('meal-requests', [MealController::class, 'mealRequestsIndex'])->name('meal-requests.index');
    Route::post('meal-requests/{id}/approve', [MealController::class, 'approveMealRequest'])->name('meal-requests.approve');
    Route::post('meal-requests/{id}/reject', [MealController::class, 'rejectMealRequest'])->name('meal-requests.reject');
    Route::get('api/pending-meal-requests-check', [MealController::class, 'checkPendingMealRequests'])->name('meal-requests.check-pending');
    Route::get('api/user-meal-notifs-check', [MealController::class, 'checkUserMealNotifs'])->name('meal-requests.check-user-notifs');
    Route::post('meal-requests/{id}/dismiss-user-notif', [MealController::class, 'dismissUserMealNotification'])->name('meal-requests.dismiss-notif');
    Route::post('meals/toggle-off', [MealController::class, 'toggleMealOff'])->name('meals.toggle-off');
    Route::get('meals/live-status', [MealController::class, 'getLiveMealStatus'])->name('meals.live-status');
    Route::post('meals/update-status', [MealController::class, 'updateMealStatus'])->name('meals.update-status');
    Route::post('meals/bulk-update-status', [MealController::class, 'bulkUpdateMealStatus'])->name('meals.bulk-update-status');
    Route::get('meals-print', [MealController::class, 'printDailyMeals'])->name('meals.print');
    Route::resource('meals', MealController::class);
    Route::get('meal-history', [MealController::class, 'mealHistory'])->name('meal-history');
    Route::get('meal-history/detail', [MealController::class, 'userMealHistoryDetail'])->name('meal-history.detail');
    Route::get('meal-history/resident-detail/{userId}', [MealController::class, 'residentMealDetailView'])->name('meal-history.resident-detail');
    
    // Deposits & Fines
    Route::resource('deposits', DepositController::class);
    Route::get('deposit-history', [DepositController::class, 'depositHistory'])->name('deposit.history');
    Route::get('deposit-refunds', [\App\Http\Controllers\Backend\DepositRefundController::class, 'index'])->name('deposit-refunds.index');
    Route::post('deposit-refunds', [\App\Http\Controllers\Backend\DepositRefundController::class, 'store'])->name('deposit-refunds.store');
    Route::get('deposit-refunds/user-balance/{id}', [\App\Http\Controllers\Backend\DepositRefundController::class, 'getUserBalance'])->name('deposit-refunds.user-balance');
    Route::resource('fines', FineController::class);
    Route::get('my-payment', [MyPaymentController::class, 'index'])->name('my-payment.index');

    // Hotel Management
    Route::get('/management', [HotelManagementController::class, 'index'])->name('management.index');
    Route::post('/management-store', [HotelManagementController::class, 'store']);
    Route::get('get-management', [HotelManagementController::class, 'getmanagement'])->name('get-management.getmanagement');
    Route::post('management-update/{id}', [HotelManagementController::class, 'update']);
    Route::delete('management-delete/{id}', [HotelManagementController::class, 'destroy']);

    // Complaint System
    Route::get('/complaints', [\App\Http\Controllers\Backend\ComplaintController::class, 'index'])->name('complaints.index');
    Route::post('/complaints/store', [\App\Http\Controllers\Backend\ComplaintController::class, 'store'])->name('complaints.store');
    Route::post('/complaints/update-status/{id}', [\App\Http\Controllers\Backend\ComplaintController::class, 'updateStatus'])->name('complaints.update-status');
    Route::get('/api/pending-complaints-check', [\App\Http\Controllers\Backend\ComplaintController::class, 'checkPendingComplaints'])->name('complaints.check-pending');
    Route::get('/api/user-complaints-check', [\App\Http\Controllers\Backend\ComplaintController::class, 'checkUserComplaints'])->name('complaints.user-check');
    Route::get('/api/pending-bookings-check', [RoomBookingHistoryController::class, 'checkPendingBookings'])->name('bookings.check-pending');
    Route::get('/api/room-booking/{id}/mark-seen', [RoomBookingHistoryController::class, 'markBookingSeen'])->name('bookings.mark-seen');

    // Resident Check In / Check Out Admin Routes
    Route::get('/check-in-out', [\App\Http\Controllers\Backend\ResidentCheckInOutController::class, 'index'])->name('check-in-out.index');
    Route::get('/check-in-out/{id}/history', [\App\Http\Controllers\Backend\ResidentCheckInOutController::class, 'getResidentHistory'])->name('check-in-out.resident-history');
    Route::post('/check-in-out/store-admin', [\App\Http\Controllers\Backend\ResidentCheckInOutController::class, 'storeAdmin'])->name('check-in-out.store-admin');
    Route::post('/check-in-out/{id}/check-in-admin', [\App\Http\Controllers\Backend\ResidentCheckInOutController::class, 'checkInAdmin'])->name('check-in-out.check-in-admin');
    Route::delete('/check-in-out/{id}', [\App\Http\Controllers\Backend\ResidentCheckInOutController::class, 'destroy'])->name('check-in-out.destroy');
    Route::get('/api/check-in-out/pending-check', [\App\Http\Controllers\Backend\ResidentCheckInOutController::class, 'apiCheckPending'])->name('check-in-out.check-pending');
    Route::post('/api/check-in-out/mark-seen/{id?}', [\App\Http\Controllers\Backend\ResidentCheckInOutController::class, 'markSeen'])->name('check-in-out.mark-seen');

});
