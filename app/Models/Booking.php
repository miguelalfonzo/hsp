<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;


class Booking extends Model
{
    use HasFactory;


    protected static function viewAll($hotel,$lang){

   

      $list  = DB::select('CALL sp_web_get_top_rtypes (?,?)', array($hotel,$lang));

      return $list;

  }

     protected static function validateIdReservation($reservationId){

      
      $rpta  = DB::select('SELECT count(*) AS Count FROM tblreservation WHERE Id=?', array($reservationId));

      $middleRpta =  (isset($rpta[0]->Count))?$rpta[0]->Count:0;

      return ($middleRpta ==0 )?false:true;
    }


    protected static function validateCoupon($coupon,$hotel){

    	
      $text  = DB::select('SELECT fn_get_value_coupon (?,?) AS result', array($hotel,$coupon));

      return (isset($text[0]->result))?$text[0]->result:0;

    }


    protected static function update_temporary_book(){

    	DB::update('CALL sp_web_update_temporary_book', array());

    }


    

    protected static function insertPayment($reservationId,$codeReference,$messagePayment,$typePayment,$user){


      DB::statement('CALL sp_web_save_payment (?,?,?,?,?)', array($reservationId,$codeReference,$messagePayment,$typePayment,$user));


    }


    protected static function confirmPayBooking($hotel,$id,$user,$coupon){


    	DB::statement('CALL sp_web_confirm_pay_booking (?,?,?,?)', array($hotel,$id,$user,$coupon));


    }


   protected static function deleteReservation($id){


   

   	DB::delete('CALL sp_web_delete_reservation (?)', array($id));

   }

   protected static function searchRooms($request,$hotel){


   		
   		
   		$checkIn   = Carbon::parse($request->checkIn)->format('Y-m-d');
   		$checkOut  = Carbon::parse($request->checkOut)->format('Y-m-d');
   		$lang      = $request->lang;


    	$list  = DB::select('CALL sp_web_available_typeroom (?,?,?,?)', array($hotel,$checkIn,$checkOut,$lang));

    	return $list;
    
    }


  

    protected static function createUserBooking($hotel,$country,$guestFirstName,$guestLastName,$guestEmail,$guestPhone,$user){


        $list  = DB::select('CALL sp_web_create_user (?,?,?,?,?,?,?)', array($hotel,$country,$guestFirstName,$guestLastName,$guestEmail,$guestPhone,$user));

      return $list;

    }

    protected static function create($request,$ids,$holder){

    	  $hotel 			= $request->hotel ;
        $agent 			= $request->agent ;
        

        $country        = trim($request->country) ;

        $checkIn 		= Carbon::parse($request->checkIn)->format('Y-m-d');

        $checkOut 		= Carbon::parse($request->checkOut)->format('Y-m-d');

      
        $dateArrival 	= ($country == 'pe')?NULL:Carbon::parse($request->dateArrival)->format('Y-m-d');
        
        $arrivalTime 	= $request->arrivalTime ;

      
       

        


        $specialRequest = $request->specialRequest ;
        $origen 		= $request->origen ;
        $temporary 	= $request->temporary ;
        $statusBooking  = $request->statusBooking ;
        


        $booking  = DB::select('CALL sp_web_insert_booking (?,?,?,?,?,?,?,?,?,?,?,?,?)', array(
        $hotel,$agent,$checkIn,$checkOut,$dateArrival,$arrivalTime,$specialRequest,$origen ,$ids,$temporary,$holder,$statusBooking,$country));

    	return $booking;

    }

    
    
}
