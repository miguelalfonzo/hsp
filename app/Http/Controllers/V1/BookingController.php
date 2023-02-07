<?php
namespace App\Http\Controllers\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Booking;
use App\Models\Pms;
use App\Models\Maintenance;
use DB;
use App\Http\Controllers\V1\PaymentsController;
use App\Http\Controllers\V1\CorreoController;
use Carbon\Carbon;
class BookingController extends Controller
{
   

  
    //la functin viewAll se esta inactiando via postman

    protected function viewAll(Request $request){

        $data = $request->only('hotel','lang');

        $validator = Validator::make($data, [
            'hotel' => 'required|integer',
            'lang' => 'required|string|max:2'
            
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }
      
        
        $list = Booking::viewAll($request->hotel,$request->lang);

        $middleRpta = $this->setRpta('ok','success response',$list);

        return response()->json($middleRpta,Response::HTTP_OK);


    }


    protected function getIgv(Request $request)
    {
        


       
    
        $list = Pms::getIgv();

        $middleRpta = $this->setRpta('ok','success list',$list);

        return response()->json($middleRpta,Response::HTTP_OK);

    }

    protected function search(Request $request)
    {
        


        $data = $request->only('hotel','checkIn', 'checkOut','lang');

        $validator = Validator::make($data, [
            'hotel' => 'required|integer',
            'checkIn' => 'required|date|after_or_equal:today',
            'checkOut' => 'required|date|after:checkIn',
            'lang'=>'required|string|max:2'
            
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }
      
        
        $hotel = $request->hotel;

        $list = Booking::searchRooms($request,$hotel);

        $middleRpta = $this->setRpta('ok','success response',$list);

        return response()->json($middleRpta,Response::HTTP_OK);

    }

   

    public function validateInterferenceDatesNewBooking($hotel,$checkIn,$checkOut,$idRoomOrIdBed){

        

        $checkIn        = Carbon::parse($checkIn)->format('Y-m-d');

        $checkOut       = Carbon::parse($checkOut)->format('Y-m-d');

        $sql = DB::select("SELECT ReserveFromDate,ReserveToDate FROM tblbookings  
              WHERE HotelId = ? AND RoomBedId = ? AND ( (ReserveFromDate>=CURDATE() )OR (CURDATE() BETWEEN ReserveFromDate AND ReserveToDate));" ,array($hotel,$idRoomOrIdBed));
        
        $interference = 0;

        foreach($sql as $values){

            if($values->ReserveToDate > $checkIn AND $values->ReserveFromDate<$checkOut){

                $interference = $interference + 1;
            }
                

        }


        if($interference == 0){

              $activeQ = DB::select("SELECT Active FROM tblrooms WHERE Id=?",array($idRoomOrIdBed));

              $active = (isset($activeQ[0]->Active))?$activeQ[0]->Active:0;

              if(intval($active) == 0){

                return  $this->setRpta('error','the room or bed is not enabled : '. $idRoomOrIdBed,[]);

              }

            return  $this->setRpta('ok','success validate',[]);

        }else{

            return  $this->setRpta('error','there is no availability for the room Id: '. $idRoomOrIdBed,[]);
        }
        
    }
    


    public function validateBeforeInsertBooking($request){


        $hotel = $request->hotel;

        $checkIn = Carbon::parse($request->checkIn)->format('Y-m-d');

        $checkOut = Carbon::parse($request->checkOut)->format('Y-m-d');


        $availableList = Booking::searchRooms($request,$hotel);


        $itemsIdsAvailable = [];

        foreach($availableList as $list){

            $idTypeRoom = $list->IdTypeRoom;


            $itemsIdsAvailable[$idTypeRoom] = $list->ItemsIdsAvailable;

        }

       
        
        $roomsTypeCountAry = $request->roomsTypeCount;

        $idsRoomOrBed = '';

        foreach($roomsTypeCountAry as $list){


          
            
            $typeRoom = $list["roomType"];
            
            $nameType = Maintenance::getNameTypeRoom($typeRoom);

            if(empty($nameType)){

                return $this->setRpta('error','the type of room does not exist for: '.$typeRoom ,[]);
            }


            

            $countRequired = $list["countSelect"];

            if(empty($countRequired) || $countRequired < 0){

                return $this->setRpta('error','invalid quantity : '.$countRequired .' ,for type room : '.$nameType ,[]);
            }

            

            

           

            if(!isset($itemsIdsAvailable[$typeRoom])){

                return $this->setRpta('error','  there is no type of room:  '.$typeRoom ,[]);
            }



            $priceBase = Maintenance::getPriceBaseTypeRoom($typeRoom);

            if(empty($priceBase)){

                return $this->setRpta('error','  this type of room does not have a base price :  '.$nameType ,[]);
            }

            


            
            $listAvailable = $itemsIdsAvailable[$typeRoom];

            $listAvailable = explode(",", $listAvailable);

            $countAvailable = count($listAvailable);


            //adult y kids nums

            $adults = $list["adults"];

            $kids = $list["kids"];

            $strAdultsKids = $adults.'&'.$kids;

            if(intval($countRequired) <= $countAvailable){
                
                //obtener cantidad de cuartos dependiendo de lo solicitado

                // re validacion unitaria -agregar

               

                foreach($listAvailable as $idRoomOrIdBed){

                    $rptaPms = $this->validateInterferenceDatesNewBooking($hotel,$checkIn,$checkOut,$idRoomOrIdBed);

                   
                    if($rptaPms["status"] == "error"){

                        return $rptaPms;

                    }
                }


                $sliceIds = array_slice($listAvailable, 0, $countRequired);

                $strIds = implode(",", $sliceIds);

                $idsRoomOrBed .= $typeRoom.'|'.$strIds.'|'.$strAdultsKids.'-';



            }else{

                

                return $this->setRpta('error','there is no availability of rooms / beds of the type : '.$nameType .' , we only have : '.$countAvailable,[]);
            }

        }

        

        $insertIds = rtrim($idsRoomOrBed,"-");

        return $this->setRpta('ok','validate success',$insertIds);

        
    }

    protected function coupon(Request $request){

            $data = $request->only('coupon','hotel');

            $validator = Validator::make($data, [
                
                'coupon'=>'nullable|string|max:100',
                'hotel'=>'required|integer'
                
            ]);
        
            if ($validator->fails()) {

                $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

                return response()->json($middleRpta, 400);
            }


        $validate = Booking::validateCoupon($request->coupon,$request->hotel);

        $middleRpta = $this->setRpta('ok','success validation',floatval($validate));

        return response()->json($middleRpta,Response::HTTP_OK);


    }


    protected function confirm(Request $request){
        

       
        try {
            
            DB::beginTransaction();

            $data = $request->only('hotel','reservationId','user','typePayment','coupon','token','reference');

            $validator = Validator::make($data, [
                
                'hotel'=>'required|integer',
                'reservationId'=>'required|integer',
                'user'=>'required|integer',
                'typePayment'=>'required|integer',
                'coupon'=>'nullable|string|max:100',  
                'token'=>'required_if:typePayment,==,7|nullable|string|max:100',
                'reference'=>'nullable|string|max:100'
                
            ]);
        

            


            if ($validator->fails()) {

               

                return response()->json($this->setRpta('warning','validator fails',$validator->messages()), 400);
            }

            $reservationId = $request->reservationId;


            if(!Booking::validateIdReservation($reservationId)){
                
               return response()->json($this->setRpta('error','reservation id not found',[]), 400);
            }

            $hotel = $request->hotel;

            $user  = $request->user;

            
           
            $codeReference = $request->reference;

            $typePayment = $request->typePayment;

            $coupon = trim($request->coupon);

            $messagePayment = 'payment by web';

            $messagePaymentUser = 'successful payment';
            

            Booking::confirmPayBooking($hotel,
                                        $reservationId,
                                        $user,
                                        $coupon);

            
            
            if($typePayment == 7){

                $payments = new PaymentsController;

                $rptaPayment =  $payments->culquiPayment($request);

                $culqui = json_decode($rptaPayment);

                

                if($culqui->capture == true){

                    $codeReference =  $culqui->reference_code;
                    
                    $messagePayment = $culqui->outcome->type;

                    $messagePaymentUser = $culqui->outcome->user_message;

                    
                    

                }else{

                      DB::rollBack();

                     return response()->json($this->setRpta('error','transact culqui', $culqui), 400);
                }

            }


            
            Booking::insertPayment($reservationId,$codeReference,$messagePayment,$typePayment,$user);
               

                
                   
                $lang = Pms::getLangEsHolder($reservationId);

                $correo = new CorreoController();
                     
                $correo->sendEmailBookingSuccess($reservationId,$lang,$hotel,$user);


              DB::commit();

              return response()->json($this->setRpta('ok',$messagePaymentUser,[]),Response::HTTP_OK);

           

        } catch (\Exception $e) {
            
             DB::rollBack();

           
             return response()->json($this->setRpta('error','transact : '.$e->getMessage(),[]), 400);
        }
    }



    public function createUserBooking($request,$hotel,$user){


        $country = $request->country;
        $guestFirstName= trim($request->guestFirstName);
        $guestLastName = trim($request->guestLastName);
        $guestEmail= trim($request->guestEmail);
        $guestPhone = trim($request->guestPhone);
       

        $rpta = Booking::createUserBooking($hotel,$country,$guestFirstName,$guestLastName,$guestEmail,$guestPhone,$user);

       

        if(isset($rpta[0]->ID)){

            return $this->setRpta('ok','create user success ',$rpta[0]->ID);
        }

        return $this->setRpta('error','could not create user ',[]);
    }



    protected function create(Request $request)
    {
        
        try {
            
            DB::beginTransaction();


            


            $data = $request->only('hotel','agent', 'country', 'guestFirstName','guestLastName','guestEmail', 'guestPhone','checkIn', 'checkOut', 'dateArrival','arrivalTime', 'specialRequest','origen', 'roomsTypeCount','reservationId','temporary','statusBooking');

            $validator = Validator::make($data, [
                'hotel' => 'required|integer',
                'agent' => 'required|integer',
                'country' => 'required|string|max:2',
                'guestFirstName' => 'required|string|max:100',
                'guestLastName' => 'required|string|max:100',
                'guestEmail' => 'required|string|email|max:100',
                'guestPhone' => 'nullable|string|max:20',
                'checkIn' => 'required|date|after_or_equal:today',
                'checkOut' => 'required|date|after:checkIn',
            
                
                'dateArrival' => 'required_unless:country,pe|nullable|date|before_or_equal:checkIn',
                
                'arrivalTime' => 'nullable|string|max:50',
               
                'specialRequest' => 'nullable|string|max:250',
                'origen' => 'required|integer',
                'roomsTypeCount' =>'required',
                'reservationId'=>'required|integer',
                'temporary'=>'required|integer|in:0,1',
                'statusBooking'=>'required|integer',
                
                
            ]);
            

            


            if ($validator->fails()) {


                return response()->json($this->setRpta('warning','validator fails',$validator->messages()), 400);
            }
        
            
            
            if(!empty($request->reservationId)){

                

              Booking::deleteReservation($request->reservationId); 

            }
            
           
            $hotel = $request->hotel;

            $user  = $request->agent;


            $holderRpta = $this->createUserBooking($request,$hotel,$user);

            if($holderRpta["status"]=="ok"){

                $holderId = $holderRpta["data"];

            }else{

                  return response()->json($holderRpta,400);
            }



            $middleRpta = $this->validateBeforeInsertBooking($request);


           
            


            if($middleRpta["status"] == "ok"){

                $idsInsert = $middleRpta["data"] ;


                

                $resultId = Booking::create($request,$idsInsert,$holderId);

                
               

                if(isset($resultId[0]->ID)){

                
                   

                    DB::commit();

                    return response()->json($this->setRpta('ok','reservation created successfully ',intval($resultId[0]->ID)),201);

                }

             
                DB::rollBack();
           
                return response()->json($this->setRpta('error','could not generate reservation ',[]),400);



            }

            DB::rollBack();

           return response()->json($middleRpta,400);


        } catch (\Exception $e) {
             

             DB::rollBack();

           
             return response()->json($this->setRpta('error','transact : '.$e->getMessage(),[]), 400);
        }


        

    }
    
    
   
   
    
}