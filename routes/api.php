<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\V1\ProductsController;
use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\BookingController;
use App\Http\Controllers\V1\MaintenanceController;
use App\Http\Controllers\V1\ActivityLogController;
use App\Http\Controllers\V1\PaymentsController;
use App\Http\Controllers\V1\PmsController;
use Carbon\Carbon;


Route::prefix('v1')->group(function () {

	

	  Route::group(['prefix' => 'payments'], function () {

        Route::get('time', function(){ 

        	$o = '2023-01-12T15:23:48.104Z';

        	$date = new DateTime($o);
return $date->format('Y-m-d H:i:s');

        	//return Carbon::now()->format('Y-m-d H:i:s');

        	//return Carbon::parse('2022-01-01 06:00:00')->format('Y-m-d H:i:s');
        });


      


	 	Route::get('culqui', [PaymentsController::class, 'culqui']);

		Route::post('culquiPayment', [PaymentsController::class, 'culquiPayment']);

		Route::get('paypal', [PaymentsController::class, 'paypal']);


		
    });
	

	 Route::group(['prefix' => 'booking'], function () {

	 	Route::get('search', [BookingController::class, 'search']);

	 	Route::get('viewAll', [BookingController::class, 'viewAll']);

	 	Route::get('coupon', [BookingController::class, 'coupon']);

	 	Route::post('create', [BookingController::class, 'create']);

	 	Route::put('confirm', [BookingController::class, 'confirm']);

	 	Route::get('getIgv', [BookingController::class, 'getIgv']);

	 	
		
    });


	  Route::group(['prefix' => 'maintenance','middleware' => ['jwt.verify']], function () {

        
	 	Route::get('options', [MaintenanceController::class, 'options']);


	 	 Route::prefix('seeker')->group(function () {
	
	   			Route::get('products', [MaintenanceController::class, 'seekerProducts']);
	   			Route::get('guest', [MaintenanceController::class, 'seekerGuest']);

	
			});


	 	 Route::prefix('roomsType')->group(function () {
	
	   			
	   			Route::get('get', [MaintenanceController::class, 'getRoomTypes']);
	   			Route::post('save', [MaintenanceController::class, 'saveRoomType']);
	   			

	
			});


	 	  Route::prefix('rates')->group(function () {
	
	   			Route::get('get', [MaintenanceController::class, 'getRates']);
	   			Route::post('save', [MaintenanceController::class, 'saveRates']);
	   			

	
			});


	 	   Route::prefix('room')->group(function () {
	
	   			Route::get('get', [MaintenanceController::class, 'getRooms']);
	   			Route::post('save', [MaintenanceController::class, 'saveRooms']);
	   			

	
			});

	 	    Route::prefix('coupon')->group(function () {
	
	   			Route::get('get', [MaintenanceController::class, 'getCoupon']);
	   			Route::post('save', [MaintenanceController::class, 'saveCoupon']);
	   			

	
			});


			Route::prefix('user')->group(function () {
	
	   			Route::get('get', [MaintenanceController::class, 'getUser']);
	   			Route::post('save', [MaintenanceController::class, 'saveUser']);
	   			

	
			});


			Route::prefix('permissions')->group(function () {
	
	   			Route::get('get', [MaintenanceController::class, 'getPermissions']);

			});
		
    });



	   Route::group(['prefix' => 'pms','middleware' => ['jwt.verify']], function () {

	   		Route::prefix('dashboard')->group(function () {
	
	   			Route::get('today', [PmsController::class, 'dashboardToday']);

	 			Route::get('indicators', [PmsController::class, 'dashboardIndicators']);

	
			});


			Route::prefix('reservation')->group(function () {
	
	   			Route::get('all', [PmsController::class, 'getAllReservation']);

	 			

	
			});


			Route::prefix('activitylog')->group(function () {
	
	   			Route::get('get', [ActivityLogController::class, 'getActivityLog']);

			});



			

	   		

			Route::prefix('booking')->group(function () {
	
	   			

	 			
	 			Route::prefix('edit')->group(function () {
	
	   				Route::get('suggestNewHistory', [PmsController::class, 'suggestNewHistory']);

	   				Route::post('saveEditDates', [PmsController::class, 'saveEditDates']);
	 			
	   				Route::post('savePayments', [PmsController::class, 'savePayments']);

	   				Route::post('saveProducts', [PmsController::class, 'saveProducts']);

	   				Route::post('saveNotes', [PmsController::class, 'saveNotes']);

	   				Route::put('confirmDiscount', [PmsController::class, 'confirmDiscount']);

	   				Route::get('getHistoryPrices', [PmsController::class, 'getHistoryPrices']);

	   				Route::get('getHistoryProducts', [PmsController::class, 'getHistoryProducts']);

	   				Route::get('getHistoryPayments', [PmsController::class, 'getHistoryPayments']);

	   				Route::get('getHistoryNotes', [PmsController::class, 'getHistoryNotes']);

	   				Route::get('getInfoDetail', [PmsController::class, 'getInfoDetail']);

	   				Route::put('changeToIgv', [PmsController::class, 'changeToIgv']);

	   				Route::put('assignGuest', [PmsController::class, 'assignGuest']);

	   				Route::put('inactiveItemProduct', [PmsController::class, 'inactiveItemProduct']);

	   				Route::put('updateState', [PmsController::class, 'updateState']);

	   				Route::get('viewSameRooms', [PmsController::class, 'viewSameRooms']);

	   				Route::put('reassignRoom', [PmsController::class, 'reassignRoom']);

	   				Route::put('changeDateArrival', [PmsController::class, 'changeDateArrival']);

	   				

	   				
	
				});


				Route::prefix('create')->group(function () {
	

					Route::get('viewAvailability', [PmsController::class, 'viewAvailability']);

					Route::post('booking', [PmsController::class, 'createBooking']);

					Route::get('getDetailsReservation', [PmsController::class, 'getDetailsReservation']);
					

					
				});

	
			});
       
	 	
		
    	});

	 

	



    Route::post('login', [AuthController::class, 'authenticate']);
    
    Route::post('register', [AuthController::class, 'register']);
    

    Route::group(['middleware' => ['jwt.verify']], function() {
      
      //verificación 
        
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('get-user', [AuthController::class, 'getUser']);
      
    });
});