<?php

  use App\Http\Controllers\Auth\PasswordResetController;
  use Illuminate\Support\Facades\Route;
  use  App\Http\Controllers\Backend\FloorController;
  use  App\Http\Controllers\Frontend\WelcomeCotroller;
  use  App\Http\Controllers\Frontend\HomeController;
  use  App\Http\Controllers\Backend\RoomController;
  use  App\Http\Controllers\Backend\StaffsController;
  use  App\Http\Controllers\Frontend\NoticeController;
  use  App\Http\Controllers\Backend\RoomBookingHistoryController;
  use  App\Http\Controllers\Frontend\HelpDeskController;
  use  App\Http\Controllers\Frontend\ResidenceOverviewController;
  use  App\Http\Controllers\Frontend\GalleryController;
  use  App\Http\Controllers\Backend\StaffAttendanceController;
  use  App\Http\Controllers\Backend\StaffSalaryController;
  use  App\Http\Controllers\Backend\ExpenseController;
  use  App\Http\Controllers\Backend\ExpenseTypeController;
  use  App\Http\Controllers\Backend\DashboardController;
  use  App\Http\Controllers\Backend\SupplierContoller;
  use  App\Http\Controllers\Backend\ProductController;
  use  App\Http\Controllers\Backend\BrandController;
  use  App\Http\Controllers\Backend\BrandCategoryController;
  use  App\Http\Controllers\Backend\PurchaseController;
  use  App\Http\Controllers\Backend\ProductStockController;
  use  App\Http\Controllers\Backend\ManageSaleController;
  use  App\Http\Controllers\Backend\ProductDistributionController;
  use  App\Http\Controllers\Backend\ReportController;
  use  App\Http\Controllers\Backend\CustomerReportController;
  use  App\Http\Controllers\Backend\HotelManagementController;
  use  App\Http\Controllers\Backend\MonthlyPaymentController;
  use  App\Http\Controllers\Frontend\MyPaymentController;
  use  App\Http\Controllers\Backend\MealController;
  use  App\Http\Controllers\Backend\DepositController;
  use  App\Http\Controllers\Backend\FineController;

    Auth::routes();

    Route::get('/',[HomeController::class,'homepage'])->name('home');
    Route::controller(PasswordResetController::class)->group(function () {
    Route::get('send-otp', 'sendOtp')->name('send-otp');
    Route::post('verify-otp', 'checkOtp')->name('verify-otp');
    Route::get('verify-token/{token}', 'validateToken')->name('verify-token');
    Route::post('update-password', 'updatePassword')->name('update-password');
    });

    Route::get('booking-now',[HomeController::class, 'bookingpage'])->name('booking-now.bookingpage');
    Route::get('guest-support',[HomeController::class, 'HelpDesk'])->name('guest-support.HelpDesk');
    Route::get('floor/{id}/rooms', [HomeController::class, 'floorRooms']);

    Route::get('staffs', [StaffsController::class, 'index'])->name('staffs.index');
    Route::post('staffs/store', [StaffsController::class, 'store'])->name('staffs.store');
    Route::get('divisions', [StaffsController::class, 'getDivisions']);
    Route::get('divisions/{division}/districts', [StaffsController::class, 'getDistrictsByDivision']);
    Route::get('districts/{district}/thanas', [StaffsController::class, 'getThanasByDistrict']);
    Route::get('/staffs-get', [StaffsController::class, 'getstaffsGet'])->name('staffs-get.getstaffsGet'); 
    Route::post('/staffs-update/{id}',     [StaffsController::class, 'update']);
    Route::delete('/staffs-delete/{id}',   [StaffsController::class, 'destroy']);

    Route::get('/get-user-role', function () {
    return response()->json([
        'role' => auth()->user()->roles->first()->name ?? '']);
  });


    // Route::get('/', function () {
    //     return view('auth.login');
    // })->name('home');
    //     Route::controller(PasswordResetController::class)->group(function () {
    //     Route::get('send-otp', 'sendOtp')->name('send-otp');
    //     Route::post('verify-otp', 'checkOtp')->name('verify-otp');
    //     Route::get('verify-token/{token}', 'validateToken')->name('verify-token');
    //     Route::post('update-password', 'updatePassword')->name('update-password');
    // });


    //roombooking controller //
    Route::post('room-booking-history/store', [RoomBookingHistoryController::class, 'store'])
    ->name('room-booking-history.store');
    Route::get('divisions', [RoomBookingHistoryController::class, 'getDivisions']);
    Route::get('divisions/{division}/districts', [RoomBookingHistoryController::class, 'getDistrictsByDivision']);
    Route::get('districts/{district}/thanas', [RoomBookingHistoryController::class, 'getThanasByDistrict']);
    Route::get('room-booking-history', [RoomBookingHistoryController::class, 'getroombookinghistory']);
    Route::get('get-room-booking', [RoomBookingHistoryController::class, 'getbookinghistory'])->name('get-room-booking.getbookinghistory');
    Route::get('today-room-booking-history', [RoomBookingHistoryController::class, 'todaygetroombookinghistory']);
    Route::get('today-get-room-booking', [RoomBookingHistoryController::class, 'todaygetbookinghistory'])->name('today-get-room-booking.todaygetbookinghistory');
    Route::get('/room-booking/find-guest', [RoomBookingHistoryController::class, 'findGuestByPhone'])
    ->name('room.booking.findGuest');
    Route::get('/guest/by-phone/{phone}', [RoomBookingHistoryController::class, 'getGuestByPhone']);
    Route::get('/get-select-guet', [RoomBookingHistoryController::class, 'getNameguet']);

    //floor controller //
    Route::get('floor', [FloorController::class, 'index'])->name('floor.index');
    Route::post('floors-create', [FloorController::class, 'store'])->name('floors.store');
    Route::get('floors-get', [FloorController::class, 'floorget'])->name('floors-get.floorget');
    Route::post('floors-update/{id}', [FloorController::class, 'update']);
    Route::delete('floors-delete/{id}', [FloorController::class, 'destroy'])->name('floors.delete');
    Route::post('/rooms-store-multiple', [FloorController::class, 'storeMultipleRooms']);
    Route::get('/floors/all', [FloorController::class, 'all']); 

    //room controller//
    Route::get('room-list', [RoomController::class, 'index'])->name('room-list.index');
    Route::get('/rooms-get', [RoomController::class, 'getRooms']); 
    Route::get('/rooms/by-floor/{floor}', [RoomController::class, 'roomsByFloor'])->name('roomsByFloor'); 
    Route::post('/room-store', [RoomController::class, 'store']);
    Route::post('/room-update/{room}', [RoomController::class, 'update']);
    Route::delete('/room-delete/{room}', [RoomController::class, 'destroy']);

    //notice controller //
    Route::get('notice', [NoticeController::class, 'index'])->name('notice.index');
    Route::get('/notice-get', [NoticeController::class, 'get']);
    Route::post('/notice-store', [NoticeController::class, 'store']);
    Route::post('/notice-update/{id}', [NoticeController::class, 'update']);
    Route::delete('/notice-delete/{id}', [NoticeController::class, 'destroy']);

    Route::post('/helpdesk-send', [HelpDeskController::class, 'sendHelpDeskEmail'])->name('helpdesk.send');

    // residence overview controller //
    Route::get('residence-overview', [ResidenceOverviewController::class, 'index'])->name('residence-overview.index');
    Route::post('residence-overview-create', [ResidenceOverviewController::class, 'store']);
    Route::get('residence-overview-get', [ResidenceOverviewController::class, 'getrelaxplace']);
    Route::post('/residence-overview-update/{id}', [ResidenceOverviewController::class, 'update']);
    Route::delete('/residence-overview-delete/{id}', [ResidenceOverviewController::class, 'destroy']);

    // gallery controller //
    Route::get('gallery', [GalleryController::class, 'index'])->name('gallery.index');
    Route::post('gallery-store', [GalleryController::class, 'store']);
    Route::get('gallery-get', [GalleryController::class, 'getluxuryroom'])->name('gallery-get.getluxuryroom');
    Route::post('gallery-update/{id}', [GalleryController::class, 'update']);
    Route::delete('gallery-delete/{id}', [GalleryController::class, 'destroy']);



    //staffattendance  controller //
    Route::get('staffs-attendance', [StaffAttendanceController::class, 'index'])->name('staffs-attendance.index');
    Route::get('staffs/search', [StaffAttendanceController::class, 'search']);
    Route::post('staff-attendance', [StaffAttendanceController::class, 'store']);
    Route::get('/staffs-get-attendance', [StaffAttendanceController::class, 'GetAttendance']);
    Route::put('staffs-attendance/{id}', [StaffAttendanceController::class, 'update']);
    Route::get('get-select-staff', [StaffAttendanceController::class, 'getstaffname']);

    //staffsalary controller//
    Route::get('/staff-salary', [StaffSalaryController::class, 'index'])->name('staff-salary.index');
    Route::get('/search-staff', [StaffSalaryController::class, 'searchStaff'])->name('search-staff.searchStaff');
    Route::get('/summary/{staffId}', [StaffSalaryController::class, 'summary'])->name('staff-salary.summary');
    Route::get('/history/{staffId}', [StaffSalaryController::class, 'history'])->name('staff-salary.history');
    Route::post('/advance', [StaffSalaryController::class, 'storeAdvance'])->name('staff-salary.advance');
    Route::post('/full', [StaffSalaryController::class, 'storeFull'])->name('staff-salary.full');
    Route::get('/staff-salary-get', [StaffSalaryController::class, 'getstaffsalary'])->name('staff-salary-get.getstaffsalary');

    //expensetype controller //
    Route::get('/expense-type', [ExpenseTypeController::class, 'index'])->name('expense-type.index');
    Route::post('expense-categories', [ExpenseTypeController::class, 'store']);
    Route::get('/expense-categories', [ExpenseTypeController::class, 'getexpensetype'])->name('expense-categories.getexpensetype');
    Route::put('/expense-categories/{id}', [ExpenseTypeController::class, 'update'])->name('expense-categories.update');
    Route::delete('/expense-categories/{id}', [ExpenseTypeController::class, 'destroy'])->name('expense-categories.destroy');
    Route::post('/expense-type-create', [ExpenseTypeController::class, 'store']);
    Route::delete('/expense-type-delete/{id}', [ExpenseTypeController::class, 'destroy']);

    //expense controller //
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

    // spplider controller //
    Route::get('/supplier', [SupplierContoller::class, 'index'])->name('supplier.index');
    Route::post('/supplier-create', [SupplierContoller::class, 'store']);
    Route::get('/get-supplier', [SupplierContoller::class, 'getsupplier']);
    Route::post('/supplier-update-data/{id}', [SupplierContoller::class, 'update']);
    Route::delete('/supplier-delete/{id}', [SupplierContoller::class, 'destroy']);

    // brand controller //
    Route::get('/brand', [BrandController::class, 'index'])->name('brand.index');
    Route::get('/get-select-brand-category', [BrandController::class, 'getselectbrandCategory']);
    Route::post('/new-brand-create', [BrandController::class, 'store']);
    Route::get('/get-brand', [BrandController::class, 'getBrand'])->name('get-brand.getBrand');
    Route::post('/brand-update-data/{id}', [BrandController::class, 'update']);
    Route::delete('/brand-delete/{id}', [BrandController::class, 'destroy']);

    //brandcategory controller //
    Route::get('/brand-category', [BrandCategoryController::class, 'index'])->name('brand-category.index');
    Route::post('/brand-category-create', [BrandCategoryController::class, 'store']);
    Route::get('/get-brand-category', [BrandCategoryController::class, 'GetBrandCategory'])->name('get-brand-category.GetBrandCategory');
    Route::post('/brand-category-update-data/{id}', [BrandCategoryController::class, 'update']);
    Route::delete('/brand-category-delete/{id}', [BrandCategoryController::class, 'destroy']);

    // product controller //
    Route::get('/product', [ProductController::class, 'index'])->name('product.index');
    Route::get('/get-select-brand', [ProductController::class, 'SelectGetBrand']);
    Route::get('/select-brand-category', [ProductController::class, 'SelectGetBrandcategory']);
    Route::post('/product-store', [ProductController::class, 'store']);
    Route::get('/get-product', [ProductController::class, 'getproductlist']);
    Route::post('/product-update-data/{id}', [ProductController::class, 'update']);
    Route::delete('/product-delete/{id}', [ProductController::class, 'destroy']);

    //product stock controller //
    Route::get('/product-stock', [ProductStockController::class, 'index'])->name('product-stock.index');
    Route::get('/select-supplier',[ProductStockController::class, 'getselectsupplier']);
    Route::get('/get-select-product', [ProductStockController::class, 'getselectproduct']);
    Route::post('/product-purchase-stock-store', [ProductStockController::class, 'store']);
    Route::get('/get-select-supplier', [ProductStockController::class, 'getsupplier']);
    Route::get('/product_purchase_list', [ProductStockController::class, 'Productpurchaselist']);
    Route::delete('/product-purchase-delete/{id}', [ProductStockController::class, 'destroy']);
    Route::post('/product-quantity-add', [ProductStockController::class, 'QuantityStore']);

    //product distribution controller  //
    Route::get('/product-distribution', [ProductDistributionController::class, 'index'])->name('product-distribution.index');
    Route::get('/today-product-distribution', [ProductDistributionController::class, 'index'])->name('today-product-distribution.index');
    Route::get('get-select-product-sale', [ProductDistributionController::class, 'getselectproductSale']);
    Route::get('/product-districbution-list', [ProductDistributionController::class, 'customerdistributionlist']);
    Route::get('/today-product-distribution-list', [ProductDistributionController::class, 'todayDistributionList']);
    Route::get('/get-select-customer',      [ProductDistributionController::class, 'getcustomer']);
    Route::delete('/customerlist-delete/{id}', [ProductDistributionController::class, 'destroy']);
    Route::post('product-sale-store', [ProductDistributionController::class, 'store']);
    Route::get('floor-select', [ProductDistributionController::class, 'floorSelect']);
    Route::get('floor-room-select/{floor_id}', [ProductDistributionController::class, 'floorRoomSelect']);
    Route::get('room-customer-auto-load/{room_number}', [ProductDistributionController::class, 'roomCustomerAutoLoad']);

    // report controller //
    Route::get('report', [ReportController::class, 'index'])->name('report.index');
    Route::get('get-profit-loss-report', [ReportController::class, 'profitLossReport']);
    Route::get('get-available-years', [ReportController::class, 'availableYears']);
    Route::get('get-productstock', [ReportController::class, 'getproductStock']);

    //customer report controller //
    Route::get('customer-report', [CustomerReportController::class, 'index'])->name('customer-report.index');
    Route::get('get-roombooking', [CustomerReportController::class, 'getroombooing']);
    Route::get('get-productdistribution', [CustomerReportController::class, 'getproductdistributton']);
    Route::post('/admin/rooms/active', [CustomerReportController::class, 'activeRoom']);

    // room release manager routes
    Route::get('admin/rooms/release-manager', [RoomBookingHistoryController::class, 'releaseManagerIndex'])->name('room-release.index');
    Route::get('admin/active-bookings', [RoomBookingHistoryController::class, 'getActiveBookings']);
    Route::post('admin/bookings/{id}/schedule-leave', [RoomBookingHistoryController::class, 'scheduleLeave']);
    Route::post('admin/bookings/{id}/cancel-leave', [RoomBookingHistoryController::class, 'cancelLeave']);
    Route::post('admin/bookings/{id}/instant-release', [RoomBookingHistoryController::class, 'instantRelease']);
    Route::get('admin/rooms/release-history', [RoomBookingHistoryController::class, 'releaseHistoryIndex'])->name('room-release.history');
    Route::get('admin/released-bookings', [RoomBookingHistoryController::class, 'getReleaseHistory']);

    // monthly payment controller //
    Route::get('monthly-payments', [MonthlyPaymentController::class, 'index'])->name('monthly-payments.index');
    Route::get('monthly-payments/get', [MonthlyPaymentController::class, 'getPayments']);
    Route::post('monthly-payments/generate', [MonthlyPaymentController::class, 'generateBills']);
    Route::post('monthly-payments/collect', [MonthlyPaymentController::class, 'collectPayment']);

    // meal management routes //
    Route::resource('meals', MealController::class);
    Route::get('meal-history', [MealController::class, 'mealHistory'])->name('meal-history');
    Route::resource('deposits', DepositController::class);
    Route::get('deposit-history', [DepositController::class, 'depositHistory'])->name('deposit.history');
    Route::resource('fines', FineController::class);
    Route::get('my-payment', [MyPaymentController::class, 'index'])->name('my-payment.index')->middleware('auth');

    //
    Route::get('/management', [HotelManagementController::class, 'index'])->name('management.index');
    Route::post('/management-store', [HotelManagementController::class, 'store']);
    Route::get('get-management', [HotelManagementController::class, 'getmanagement'])->name('get-management.getmanagement');
    Route::post('management-update/{id}', [HotelManagementController::class, 'update']);
    Route::delete('management-delete/{id}', [HotelManagementController::class, 'destroy']);






















    
