<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;
use JWTAuth;

class Maintenance extends Model
{
    use HasFactory;

   
   protected static function options($request){


   		
   		$type  = $request->type;
   		
    	$list  = DB::select('CALL sp_pms_get_select_maintenance (?)', array($type));

    	return $list;
    
    }

    
  protected static function getLangCountry($country){

    

    $countrys = ['ar','cl','co','cr','cu','do','ex','sv','gq','gt','hn','mx','ni','pa','py','pe','pr','es','uy','bo','ve'];


    return (in_array($country, $countrys))?'es':'en';

   

  }

    protected static function getMailUserLogin($user){

      $query = DB::select("SELECT email FROM users WHERE id=?" ,array($user));

      return $query[0]->email;

    }

    protected static function getNameStatusBooking($id){

      $query = DB::select("SELECT Name FROM tblbookingstatus WHERE Id=?" ,array($id));

      return $query[0]->Name;
    }

    protected static function getNameProductBooking($id){

      $query = DB::select("SELECT Name FROM tblproducts WHERE Id=?" ,array($id));

      
      return $query[0]->Name;
    }

    protected static function getNumberRoomBooking($id){

      

      $query = DB::select("SELECT Number  FROM tblrooms WHERE Id=?" ,array($id));

      
      return $query[0]->Number;
      
    }
    
    protected static function seekerProducts($request){



   		$hotel     = JWTAuth::parseToken()->authenticate()->HotelId;

   		$category  = $request->category;
   		$term   = trim($request->term);


    	$list  = DB::select('CALL sp_pms_seeker_products (?,?,?)', array($hotel,$category,$term));

    	return $list;
    
    }


    protected static function seekerGuest($request){


      $term   = trim($request->term);

      $list  = DB::select('CALL sp_pms_get_clientes (?)', array($term));

      return $list;
    
    }


    protected static function getPriceBaseTypeRoom($id){



      $list  = DB::select('SELECT Rate FROM tblrates  WHERE RoomTypeId = ? AND Active=1 AND Hierarchy=0 ', array($id));

      return (isset($list[0]->Rate)?$list[0]->Rate:'');
    
    }

    protected static function getNameTypeRoom($id){



      $list  = DB::select('SELECT Name FROM tblroomtypes WHERE   Id= ?', array($id));

      return $list[0]->Name ; 
    
    }

    

    //pms



    
     protected static function getRoomTypes($request){

      $hotel = JWTAuth::parseToken()->authenticate()->HotelId;

      $id = $request->id;

      $list  = DB::select('CALL sp_pms_main_rtype_all (?,?)', array($hotel,$id));

      return $list;
    
    }
    




     protected static function saveRoomType($request){

            $hotel = JWTAuth::parseToken()->authenticate()->HotelId;
            $id              = $request->id ;
            $name            = $request->name ;
            $nameEs          = $request->nameEs ;
            $description     = $request->description;
            $descriptionEs   = $request->descriptionEs;
            $descriptionBeds = $request->descriptionBeds;
            $maxPerson       = $request->maxPerson;
          
            $area          = $request->area;
            $private       = $request->private;
            $flagExternalBath = $request->flagExternalBath;
            $flagSharedBath   = $request->flagSharedBath;
            $flagBreakfast    = $request->flagBreakfast ;
            $flagTv           = $request->flagTv ;
            $flagWifi         = $request->flagWifi ;
            $flagFullDay      = $request->flagFullDay ;
            $flagFridge       = $request->flagFridge ;
            $flagBalcony      = $request->flagBalcony ;
            $flagHotTub       = $request->flagHotTub ;
            $flagRoomService  = $request->flagRoomService;
            $user = JWTAuth::parseToken()->authenticate()->id;


          $rpta  = DB::select('CALL sp_pms_main_rtype_save (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', array(

            $hotel,
            $id ,
            $name,
            $nameEs,
            $description  ,
            $descriptionEs ,
            $descriptionBeds,
            $maxPerson ,
            $area ,
            $private ,
            $flagExternalBath,
            $flagSharedBath ,
            $flagBreakfast ,
            $flagTv ,
            $flagWifi ,
            $flagFullDay  ,
            $flagFridge  ,
            $flagBalcony ,
            $flagHotTub   ,
            $flagRoomService ,
            $user
          ));

          return $rpta;
    
    }
    
    


    protected static function getRates($request){

      $hotel = JWTAuth::parseToken()->authenticate()->HotelId;

      $id = $request->id;

      $list  = DB::select('CALL sp_pms_main_rates_all (?,?)', array($hotel,$id));

      return $list;
    
    }
    

    protected static function saveRates($request){

      $hotel = JWTAuth::parseToken()->authenticate()->HotelId;
      $id           = $request->id;



      $description = $request->description;

      $hierarchy = $request->hierarchy;
      $objectTypes = $request->objectTypes;
      $rate = (float)$request->rate;
      $rateBooking = (float)$request->rateBooking;
      $dateFrom = (!empty($request->dateFrom))?Carbon::parse($request->dateFrom)->format('Y-m-d'):null;

      $dateTo =  (!empty($request->dateTo))?Carbon::parse($request->dateTo)->format('Y-m-d'):null;
      
      $user = JWTAuth::parseToken()->authenticate()->id;

      

      if($id == 0){

          foreach ($objectTypes as $value) {
            
            $roomTypeVal = $value["roomType"];

           $list  =  DB::select('CALL sp_pms_main_rates_save (?,?,?,?,?,?,?,?,?,?)', array($hotel,$id,$description,$hierarchy,$roomTypeVal,$rate,$rateBooking,$dateFrom,$dateTo,$user));

          }

      }else{

          $list  = DB::select('CALL sp_pms_main_rates_save (?,?,?,?,?,?,?,?,?,?)', array($hotel,$id,$description,$hierarchy,null,$rate,$rateBooking,$dateFrom,$dateTo,$user));

      }

      return $list;
    
    }


    


     protected static function getRooms($request){

      $hotel = JWTAuth::parseToken()->authenticate()->HotelId;

      $id = $request->id;

      $list  = DB::select('CALL sp_pms_main_room_all (?,?)', array($hotel,$id));

      return $list;
    
    }


    protected static function saveRooms($request){

      $hotel = JWTAuth::parseToken()->authenticate()->HotelId;
      $id        = $request->id;
      $number    = $request->number;
      $description = $request->description;
      $category = $request->category;
      $roomType = $request->roomType;
      $user = JWTAuth::parseToken()->authenticate()->id;


      $rpta  = DB::select('CALL sp_pms_main_room_save (?,?,?,?,?,?,?)', array($hotel,$id,$number,$description,$category,$roomType,$user));

      return $rpta;
    
    }
    

    
    
    protected static function getCoupon($request){

      $hotel = JWTAuth::parseToken()->authenticate()->HotelId;

      $id = $request->id;

      $list  = DB::select('CALL sp_pms_main_coupon_all (?,?)', array($hotel,$id));

      return $list;
    
    }

    

     protected static function saveCoupon($request){

      $hotel = JWTAuth::parseToken()->authenticate()->HotelId;
      $id        = $request->id;
      $code      = trim($request->code);
      $value     = $request->value;
      $count     = $request->count;
      $active       = $request->active;
      $expirationDate   = Carbon::parse($request->expirationDate)->format('Y-m-d');
      $user         = JWTAuth::parseToken()->authenticate()->id;


      $rpta  = DB::select('CALL sp_pms_main_coupon_save (?,?,?,?,?,?,?,?)', array($hotel,$id,$code,$value,$count,$active,$expirationDate,$user));

      return $rpta;
    
    }


    

    




     protected static function getUser($request){

      $hotel = JWTAuth::parseToken()->authenticate()->HotelId;

      $id = $request->id;

      $list  = DB::select('CALL sp_pms_main_user_all (?,?)', array($hotel,$id));

      return $list;
    
    }


    protected static function random_str_generator ($length){

        $string = "";

        $possible = "0123456789bcdfghjkmnpqrstvwxyz#$%&*";

        $i = 0;
        
        while ($i < $length) {

          $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
          $string .= $char;
          $i++;
        }

        return $string;
    }

    

    protected static function saveUser($request){

      $hotel = JWTAuth::parseToken()->authenticate()->HotelId;
      $id        = $request->id;
      $firstName = $request->firstName;
      $lastName  = $request->lastName;
      $email     = $request->email;
      $phone     = $request->phone;
      $phone2    = $request->phone2;
      $active    = $request->active;
      $address   = $request->address;
      $dni       = $request->dni;
      $userType  = $request->userType;
      $user         = JWTAuth::parseToken()->authenticate()->id;

      $randomStr = self::random_str_generator(8);

      

      $password = bcrypt($randomStr);

      $rpta  = DB::select('CALL sp_pms_main_user_save (?,?,?,?,?,?,?,?,?,?,?,?,?)', array($hotel,$id,$firstName,$lastName,$email,$phone,$phone2,$active,$address,$dni,$userType,$user,$password));

      return [$rpta,$randomStr];
    
    }





    protected static function getPermissions($user,$hotel){

     

      $list  = DB::select('CALL sp_pms_main_get_permissions (?,?)', array($hotel,$user));

      return $list;
    
    }
    

}
