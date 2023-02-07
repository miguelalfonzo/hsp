<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;
use JWTAuth;

class Pms extends Model
{
    use HasFactory;

   protected static function getStateBooking($bookingId){


      $rpta  = DB::select('SELECT BookingStatus FROM tblbookings WHERE Id=?', array($bookingId));

      return $rpta[0]->BookingStatus;


   }


   protected static function getNameStateBooking($id){


      $rpta  = DB::select('SELECT Name FROM tblbookingstatus WHERE Id=?', array($id));

      return $rpta[0]->Name;


   }


   protected static function validateIdBooking($bookingId){

      
      $rpta  = DB::select('SELECT count(*) AS Count FROM tblbookings WHERE Id=?', array($bookingId));

      $middleRtpa =  (isset($rpta[0]->Count))?$rpta[0]->Count:0;

      return ($middleRtpa==0)?false:true;
    }



   protected static function dashboardToday($request){


   		
   		$hotel  = JWTAuth::parseToken()->authenticate()->HotelId;
   		
   		$type  = $request->type;

    	$list  = DB::select('CALL sp_pms_get_list_bookings_today (?,?)', array($hotel,$type));

    	return $list;
    
    }

    protected static function dashboardIndicators(){


   		
   		$hotel  = JWTAuth::parseToken()->authenticate()->HotelId;
   		
   		

    	$list  = DB::select('CALL sp_pms_get_indicators_dashboard (?)', array($hotel));

    	return $list;
    
    }

    protected static function getListRatesHistoryOld($bookingId){


      
      

      $list  = DB::select('CALL sp_pms_get_list_rates_booking (?)', array($bookingId));

      return $list;
    
    }

   
    
    
    protected static function getListRatesHistoryNew($checkIn,$checkOut,$bookingId){


      
      $hotel  = JWTAuth::parseToken()->authenticate()->HotelId;
      
      $checkIn  = Carbon::parse($checkIn)->format('Y-m-d');

      $checkOut  = Carbon::parse($checkOut)->format('Y-m-d');

     

     
      
      $list  = DB::select('CALL sp_pms_temporal_dates_booking (?,?,?,?)', array($hotel,$checkIn,$checkOut,$bookingId));

      return $list;

    }

    protected static function savePayments($request){


      
      $hotel  = JWTAuth::parseToken()->authenticate()->HotelId;
      
      $user = JWTAuth::parseToken()->authenticate()->id;

      $booking = $request->bookingId;

      $paymentType   = $request->paymentType;

      $amount   = $request->amount;

      $description   = $request->description;
      
      $positive   = $request->positive;

      DB::insert('CALL sp_pms_insert_payment (?,?,?,?,?,?,?)', array($hotel,$user,$booking,$paymentType,$amount,$description,$positive));

      
    
    }

    protected static function saveProducts($bookingId,$idProduct,$quantity,$unitPrice,$subTotal,$discount,$igv,$total){


      $hotel= JWTAuth::parseToken()->authenticate()->HotelId;

      $user= JWTAuth::parseToken()->authenticate()->id;
     
      
      DB::insert('CALL sp_pms_insert_products (?,?,?,?,?,?,?,?,?,?)', array($hotel,$user,$bookingId,$idProduct,$quantity,$unitPrice,$subTotal,$discount,$igv,$total));

      
    
    }

    protected static function saveNotes($bookingId,$description){


      $hotel = JWTAuth::parseToken()->authenticate()->HotelId;

     $user = JWTAuth::parseToken()->authenticate()->id;

      
      $rpta = DB::insert('CALL sp_pms_insert_notes (?,?,?,?)', array($hotel,$bookingId,$description,$user));

      return $rpta;
      
    
    }


     protected static function confirmDiscount($bookingId,$description,$discount){

      $user = JWTAuth::parseToken()->authenticate()->id;
      
     
      
      DB::update('CALL sp_pms_update_discount (?,?,?,?)', array($user,$bookingId,$description,$discount));

     
      
    
    }

    protected static function alltotals($bookingId){


    	$list  = DB::select('CALL sp_pms_get_alltotals_booking (?)', array($bookingId));

    	return $list;

    }



    protected static function getHistoryProducts($bookingId){


      
      

      $list  = DB::select('CALL sp_pms_get_products_booking (?)', array($bookingId));

      return $list;
    
    }

    protected static function getHistoryPayments($bookingId){


      
      

      $list  = DB::select('CALL sp_pms_get_payments_booking (?)', array($bookingId));

      return $list;
    
    }



     protected static function changeToIgv($bookingId,$active){


      
     $user = JWTAuth::parseToken()->authenticate()->id;

     $hotel= JWTAuth::parseToken()->authenticate()->HotelId;

      
      DB::update('CALL sp_pms_change_igv_booking (?,?,?,?)', array($bookingId,$active,$user,$hotel));

      
      
    
    }


    protected static function getHistoryNotes($bookingId){


      
      

      $list  = DB::select('CALL sp_pms_get_notes_booking (?)', array($bookingId));

      return $list;
    
    }


    

    protected static function assignGuest($bookingId,$idGuest){


     $user = JWTAuth::parseToken()->authenticate()->id;
      
      DB::update('CALL sp_pms_assign_guest (?,?,?)', array($bookingId,$idGuest,$user));

      
      
    
    }
    

    protected static function inactiveItemProduct($id){

      $user = JWTAuth::parseToken()->authenticate()->id;
     
      
       DB::update('CALL sp_pms_delete_item_booking (?,?)', array($user,$id));

      
      
    
    }

     protected static function getInfoDetail($bookingId){

      $list  = DB::select('CALL sp_pms_get_details_booking (?)', array($bookingId));

      return $list;
    
    }

     protected static function getInfoDetailWithButtons($bookingId){


      $user =  JWTAuth::parseToken()->authenticate()->id;
      
      $list  = DB::select('CALL sp_pms_get_details_booking_wb (?,?)', array($bookingId,$user));

      return $list;
    
    }

    


    protected static function changeDateArrival($bookingId,$date){

      $date = Carbon::parse($date)->format('Y-m-d');

      $user = JWTAuth::parseToken()->authenticate()->id;

       DB::update('CALL sp_pms_update_arrival_booking (?,?,?)', array($bookingId,$date,$user));

    
    
    }


    protected static function updateState($bookingId,$state){

      $user = JWTAuth::parseToken()->authenticate()->id;

       DB::update('CALL sp_pms_update_state_booking (?,?,?)', array($bookingId,$user,$state));

    
    
    }




    protected static function createBooking($request,$ids,$holder){

        $hotel      = JWTAuth::parseToken()->authenticate()->HotelId;
        $user      = JWTAuth::parseToken()->authenticate()->id;
       
        $checkIn    = Carbon::parse($request->checkIn)->format('Y-m-d');
        $checkOut     = Carbon::parse($request->checkOut)->format('Y-m-d');
        $dateArrival  = Carbon::parse($request->dateArrival)->format('Y-m-d');
        $arrivalTime  = $request->arrivalTime ;
       
        $specialRequest = $request->specialRequest ;
        $origen     = $request->origen ;
        $temporary  = $request->temporary ;
        $statusBooking  = $request->statusBooking ;
        $country        = trim($request->country) ;


        $booking  = DB::select('CALL sp_web_insert_booking (?,?,?,?,?,?,?,?,?,?,?,?,?)', array(
        $hotel,$user,$checkIn,$checkOut,$dateArrival,$arrivalTime,$specialRequest,$origen ,$ids,$temporary,$holder,$statusBooking,$country));

      return $booking;

    }

    

    protected static function getTotalReservation($reservationId){



      
      $query = DB::select("SELECT SUM(Total) AS Total FROM tblbookings WHERE ReservationId=?" ,array($reservationId));

      return $query[0]->Total;

    }



    protected static function getCheckInBooking($bookingId){



      
      $query = DB::select("SELECT CheckIn FROM tblbookings WHERE Id=?" ,array($bookingId));

      return $query[0]->CheckIn;

    }


    protected static function getIgv(){

      $hotel = JWTAuth::parseToken()->authenticate()->HotelId;
      
      $query = DB::select("SELECT Igv FROM tblparameters WHERE HotelId=?" ,array($hotel));

      return (float)$query[0]->Igv;

    }


     protected static function getIgvBooking($bookingId){



      
      $query = DB::select("SELECT Igv FROM tblbookings WHERE Id=?" ,array($bookingId));

      if( $query[0]->Igv == 0){

        return self::getIgv();
      }

      return (float)$query[0]->Igv ;
    }

    protected static function getCheckOutBooking($bookingId){



      
        $query = DB::select("SELECT CheckOut FROM tblbookings WHERE Id=?" ,array($bookingId));

        return $query[0]->CheckOut;
      
    }

    protected static function getIdRoomBooking($bookingId){

       $query = DB::select("SELECT RoomBedId FROM tblbookings WHERE Id=?" ,array($bookingId));

        return $query[0]->RoomBedId;

    }


    protected static function getRoomTypeBooking($bookingId){

       $id = self::getIdRoomBooking($bookingId);

       return self::getTypeRoomById($id);

    }


    protected static function getTypeRoomById($id){



      $list  = DB::select('SELECT RoomType FROM tblrooms WHERE Id=?', array($id));

      return $list[0]->RoomType ; 
    
    }



    protected static function getEmailHolderReservation($id){



      $list  = DB::select('SELECT email FROM users WHERE id=(SELECT HolderId FROM tblreservation WHERE Id=?)', array($id));

      return $list[0]->email ; 
    
    }


    



    protected static function reassignRoom($bookingId,$newIdRoomOrBed){


      

      $user = JWTAuth::parseToken()->authenticate()->id;

       DB::update('CALL sp_pms_change_room (?,?,?)', array($bookingId,$newIdRoomOrBed,$user));

    }


    

    protected static function getDetailsReservation($reservationId){


      $list = DB::select('CALL sp_pms_get_details_reservation (?)', array($reservationId));

      return $list;

    }
    

    protected static function getDetailsReservationGroupByRoomsType($reservationId,$lang){

    
      $list = DB::select('CALL sp_pms_email_details_reservation (?,?)', array($reservationId,$lang));

      return $list;

    }


    


    

    protected static function getLangEsHolder($reservationId){

      $query  = DB::select("SELECT IF(LangEs = 1,'es','en') AS LangEs FROM tblcountries WHERE Id = (SELECT CountryId FROM users WHERE id = (SELECT HolderId FROM tblreservation WHERE Id=?))",array($reservationId));


       return $query[0]->LangEs ; 

    }



    protected static function getLangEsGuest($bookingId){

      $query  = DB::select("SELECT IF(LangEs = 1,'es','en') AS LangEs FROM tblcountries WHERE Id = (SELECT CountryId FROM users WHERE id = (SELECT GuestId FROM tblbookings WHERE Id=?))",array($bookingId));


       return $query[0]->LangEs ; 

    }


    

     protected static function getEmailGuest($bookingId){

      $query  = DB::select("SELECT email FROM users WHERE id = (SELECT GuestId FROM tblbookings WHERE Id=?)",array($bookingId));


       return $query[0]->email ; 

    }

    protected static function getAllReservation($from,$to){

      $hotel = JWTAuth::parseToken()->authenticate()->HotelId;

      $user = JWTAuth::parseToken()->authenticate()->id;

      $list = DB::select('CALL sp_pms_all_reservation (?,?,?,?)', array($from,$to,$hotel,$user));

      return $list;

    }

    
}
