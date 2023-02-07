<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;
use JWTAuth;

class ActivityLog extends Model
{
    use HasFactory;

   
  protected static function insertActivity($bookingId,$description,$order){

     $now = Carbon::now()->format('Y-m-d H:i:s');

     $user = JWTAuth::parseToken()->authenticate()->id;
     
     $hotel = JWTAuth::parseToken()->authenticate()->HotelId;

     DB::insert("INSERT INTO tblactivitylog(HotelId,BookingId,Description,CreatedAt,CreatedBy,Hierarchy) VALUES(?,?,?,?,?,?);", array($hotel,$bookingId,$description,$now,$user,$order));
   

  }




    protected static function getActivityLog($request){

      $hotel = JWTAuth::parseToken()->authenticate()->HotelId;
      
      $bookingId   = $request->bookingId;
      $dateFrom  = (empty($request->dateFrom))?NULL:Carbon::parse($request->dateFrom)->format('Y-m-d');
      $dateTo    = (empty($request->dateTo))?NULL:Carbon::parse($request->dateTo)->format('Y-m-d');
      $activeDate = $request->activeDate;  
      
      

       $list  = DB::select('CALL sp_pms_get_activitylog (?,?,?,?,?)', array($hotel,$bookingId,$dateFrom,$dateTo,$activeDate));

      return $list;
   

  }




  protected static function insertLogEmail($rpta,$hotel,$asunto,$mensaje,$list,$user,$destinatarios,$bccEmails){

  		$success = ( $rpta > 0 )?'ok':'error';


         $values = array(

                "HotelId" => $hotel ,
                "Subject" => $asunto ,
                "SendDate" => Carbon::now()->format('Y-m-d H:i:s') ,
                "Sender" => $list['CORREO'] ,
                "Recipients" => json_encode($destinatarios) ,  
                "Cc" => $bccEmails ,
                "Message" => json_encode($mensaje) ,
              	"File" => null ,
                "Success" => $success,
                "CreatedAt" => Carbon::now()->format('Y-m-d H:i:s') ,
                "CreatedBy" => $user
               
            );


        DB::table('tbllogsmails')->insert($values);



    }
  
    
}
