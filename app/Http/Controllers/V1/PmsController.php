<?php
namespace App\Http\Controllers\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Pms;
use App\Models\Maintenance;


use App\Models\Booking;
use App\Models\ActivityLog;
use Carbon\Carbon;
use DB;
use App\Http\Controllers\V1\ActivityLogController;
use App\Http\Controllers\V1\BookingController;
use App\Http\Controllers\V1\CorreoController;

class PmsController extends Controller
{

 protected $userGlobal ;


  public function __construct(Request $request)
    {
        $token = $request->header('Authorization');

        if($token != '')
            
         

            $this->userGlobal = JWTAuth::parseToken()->authenticate();



           
    }


    

    protected function dashboardToday(Request $request)
    {
        

       
        $data = $request->only('type');

        $validator = Validator::make($data, [
            
            'type' => 'required|integer'
           
            
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }
      
    
        $list = Pms::dashboardToday($request);

        $middleRpta = $this->setRpta('ok','success list',$list);

        return response()->json($middleRpta,Response::HTTP_OK);

    }
    
    
   protected function dashboardIndicators(Request $request)
    {
        



    
        $list = Pms::dashboardIndicators();

        $middleRpta = $this->setRpta('ok','success list',$list);

        return response()->json($middleRpta,Response::HTTP_OK);

    }

    protected function validateNewDatesBooking($request){

       
        $bookingId      = $request->bookingId;

        $checkIn        = Carbon::parse($request->checkIn)->format('Y-m-d');
        $checkOut       = Carbon::parse($request->checkOut)->format('Y-m-d');

      

         $sql = DB::select("SELECT Id,ReserveFromDate,ReserveToDate FROM tblbookings  
              WHERE Id = ? AND ( (ReserveFromDate>=CURDATE() )OR (CURDATE() BETWEEN ReserveFromDate AND ReserveToDate));" ,array($bookingId));
        
        $interference = [];


        

        foreach($sql as $values){

            if($values->ReserveToDate > $checkIn AND $values->ReserveFromDate<$checkOut){

                $interference[] = $values->Id;
            }
                

        }


       


        if(count($interference)==0){

            return  $this->setRpta('ok','success validate',[]);

        }elseif(count($interference)==1){

            if(intval($interference[0]) == $bookingId ){

                return  $this->setRpta('ok','success validate',[]);

            }else{

                return  $this->setRpta('error','there is no availability for the room',[]);
            }

        }else{

            return  $this->setRpta('error','there is no availability for the room',[]);
        }
        
    }


    protected function suggestNewHistory(Request $request){

        $data = $request->only('checkIn','checkOut','bookingId');

        $validator = Validator::make($data, [
          
            'checkIn'=> 'required|date',
            'checkOut'=> 'required|date|after:checkIn',
            'bookingId'=>'required|integer',
          
           
            
        ]);

        if ($validator->fails()) {

            
            return response()->json($this->setRpta('warning','validator fails',$validator->messages()), 400);
        }

            $bookingId = $request->bookingId;

          if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }


        $middleRpta = $this->validateNewDatesBooking($request);
        

       

        if($middleRpta["status"]=="ok"){

            $list = $this->setTemporalListRatesDates($request);


           

            $rpta = $this->setRpta('ok','success response',$list);

            return response()->json($rpta,Response::HTTP_OK); 
        }
        
         return response()->json($middleRpta, 400);


    }

   


    protected function setTemporalListRatesDates($request){

        
         
         $oldList = [];

         $temporal =[];

         $dataSet = [] ;

         $listOld = Pms::getListRatesHistoryOld($request->bookingId);



         foreach($listOld as $list){

            $oldList[$list->Date]=$list->Price;

         }

       


         $listNew = Pms::getListRatesHistoryNew($request->checkIn,$request->checkOut,$request->bookingId);

         
        
        foreach($listNew as $list){

            $list = (array)$list;

            $date = $list["DateBooking"];
            
            $price_new = $list["PriceBooking"];

            $price = (isset($oldList[$date]))?$oldList[$date]:$price_new;

            $temporal[$date] = $price;

        }

        
         

         foreach($temporal as $key=>$val){

            $dataSet[] = array("date"=>$key,"price"=>$val);

         }

         return $dataSet;


    }
   


    
    


   protected  function validateStateBooking($bookingId){

        $statusBlocked = [2,3,4];

        $status = Pms::getStateBooking($bookingId);


        if (in_array($status, $statusBlocked))
        {
          
          return $this->setRpta('error','the booking has already been closed, you cannot modify',[]);

        }


        return $this->setRpta('ok','validate success',[]);

   }




    protected function saveEditDates(Request $request){

        try {

            DB::beginTransaction();
            
            $data = $request->only('bookingId','list');

            $validator = Validator::make($data, [
               
                'bookingId'=>'required|integer',
                'list'=>'required'
               
                
            ]);

            if ($validator->fails()) {

                $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

                return response()->json($middleRpta, 400);
            }

           
            $bookingId     = $request->bookingId;

             if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }


            $statusValidate = $this->validateStateBooking($bookingId);

            if ($statusValidate["status"]=="error") {
            
                return response()->json($statusValidate, 400);
            }


            $hotel         = $this->userGlobal->HotelId;
            $user          = $this->userGlobal->id;
            
           

            $now = Carbon::now()->format('Y-m-d H:i:s');

           
            DB::delete("DELETE FROM tblbookinghistoryprices WHERE BookingId=?",array($bookingId));

            $newPrice = 0;

            $minMaxDates = [];

            foreach($request->list  as $values ){

                $minMaxDates[] = $values["date"];

                $date = $values["date"];
                
                $price = (float)$values["price"];

                DB::insert("INSERT INTO tblbookinghistoryprices(HotelId,BookingId,Date,Price,CreatedAt,CreatedBy) VALUES(?,?,?,?,?,?)",array($hotel,$bookingId,$date,$price,$now,$user));

                $newPrice = $newPrice + $price;
            }


            //actualizamos el precio total en la cabecera y fechas de reservas

            $fromDate = current($minMaxDates); //primer valor a enviar

            $toDate = end($minMaxDates); //ultimo valor a enviar

            //agregar un dia mas a ultimo dia para checkout

            $toDate = Carbon::parse($toDate)->addDays(1)->format('Y-m-d');

           
            

           $query = DB::select("SELECT Igv,Discount FROM tblbookings WHERE Id=? ",array($bookingId));

                $igv = (!empty($query[0]->Igv))?(float)$query[0]->Igv:0;

                $discount = (!empty($query[0]->Discount))?(float)$query[0]->Discount:0;

                $igvVal = ($igv/100)*($newPrice-$discount) ;

                $total = $newPrice  - $discount + $igvVal;

                DB::update("UPDATE tblbookings SET PriceFinal =? , IgvVal=? ,Total =? ,ReserveFromDate =? ,ReserveToDate=?,CheckIn=?,CheckOut=?,UpdatedAt = ? ,UpdatedBy=?
                    WHERE  Id=? ",array($newPrice,$igvVal,$total,$fromDate,$toDate,$fromDate,$toDate,$now,$user,$bookingId));
            
            
             $action = 'se hizo la modificación de check-in y check-out para la reserva : '.$fromDate.' a '.$toDate;

             $logActivity = new ActivityLogController();

             $logActivity->insertActivity($bookingId,1,$action);

             

             $totals =  Pms::alltotals($bookingId);

             
             $dataSet = array_merge(array('CheckIn'=>$fromDate,'CheckOut'=>$toDate),(array)$totals[0]);

             //email change dates

             $correo = new CorreoController();

             $correo->sendEmailBookingChangeDates($bookingId,$hotel,$user);

             DB::commit();

             return response()->json($this->setRpta('ok','success created history ',$dataSet),201);

        } catch (\Exception $e) {
            
            DB::rollBack();

           
            return response()->json($this->setRpta('error','transact : '.$e->getMessage(),[]), 400);
        }



        

        

    }

    protected function validateDevolution($request){

        $positive = $request->positive;

        

        if($positive == 0){

             $bookingId = $request->bookingId;

             $totals =  Pms::alltotals($bookingId);

             $due = (isset($totals[0]->BalanceDue))?$totals[0]->BalanceDue:0;

             if($due<0){

                //aplica devolucion
                $amount = $request->amount;

                
                $duePositive = abs($due);

                if($duePositive >= floatval($amount) ) {

                     return $this->setRpta('ok','success validation',[]);

                }else{

                    return $this->setRpta('error','You cannot apply a refund greater than : '.$duePositive,[]);
                }


             }else{

                return $this->setRpta('error','refund cannot be applied',[]);
             }

        }

        return $this->setRpta('ok','success validation',[]);

    }


    protected  function savePayments(Request $request){


        try {

            DB::beginTransaction();
            
            $data = $request->only('bookingId','paymentType','amount','description','positive');

            $validator = Validator::make($data, [
               
                'bookingId'=>'required|integer',   
                'paymentType'=>'required|integer',
                'amount'=>'required|numeric',
                'description'=>'required|string|max:250',
                'positive'=>'required|integer|in:0,1' //devolucion , pago
               
                
            ]);

            if ($validator->fails()) {

                $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

                return response()->json($middleRpta, 400);
            }

             $bookingId = $request->bookingId;

            if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }



            $statusValidate = $this->validateStateBooking($bookingId);

            if ($statusValidate["status"]=="error") {
            
                return response()->json($statusValidate, 400);
            }


             $devolutionRpta = $this->validateDevolution($request);

             if($devolutionRpta["status"] == "ok"){

                Pms::savePayments($request);
             
                $desPayment = ($request->positive==0)?'devolución':'pago';

                $action = 'se aplicó '.$desPayment.' para la reserva , de : '.$request->amount .' por tal motivo : '. $request->description ;


                $logActivity = new ActivityLogController();

                $logActivity->insertActivity($bookingId,1,$action);


                DB::commit();

           
             
                $totals =  Pms::alltotals($bookingId);

                return response()->json($this->setRpta('ok','success created payment ',$totals),201);
             }

              DB::rollBack();

             return response()->json($devolutionRpta, 400);
             
             

        } catch (\Exception $e) {
            
            DB::rollBack();

           
            return response()->json($this->setRpta('error','transact : '.$e->getMessage(),[]), 400);
        }

    }


    protected  function saveProducts(Request $request){


        try {

            DB::beginTransaction();
            
            $data = $request->only('bookingId','applyIgv','list');

            $validator = Validator::make($data, [
              
                'bookingId'=>'required|integer',
                'applyIgv'=>'required|integer|in:0,1',
                'list'=>'required'
                
               
                
            ]);

            if ($validator->fails()) {

                $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

                return response()->json($middleRpta, 400);
            }

            
              $bookingId = $request->bookingId;
            
            if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }


            $statusValidate = $this->validateStateBooking($bookingId);

            if ($statusValidate["status"]=="error") {
            
                return response()->json($statusValidate, 400);
            }


              //$igv = ($request->applyIgv == 1 )?Pms::getIgv():0;

              $igv = ($request->applyIgv == 1 )?Pms::getIgvBooking($bookingId):0;

             foreach ($request->list as $value) {
                
                $idProduct = $value["idProduct"];
                $quantity  = (int)$value["quantity"];
                $unitPrice =(float) $value["unitPrice"];
                $subTotal = $quantity*$unitPrice;
                $discount = (float)$value["discount"];
                $igvVal = ($subTotal - $discount )*($igv/100) ;
                $total    = $subTotal - $discount + $igvVal ;
                


                Pms::saveProducts($bookingId,$idProduct,$quantity,$unitPrice,$subTotal,$discount,$igvVal,$total);

             }
             
             

             $action = 'se agregó la siguiente lista de productos : '.json_encode($request->list);

             $logActivity = new ActivityLogController();

             $logActivity->insertActivity($bookingId,1,$action);


             DB::commit();

             $totals =  Pms::alltotals($bookingId);

             return response()->json($this->setRpta('ok','success created product ',$totals),201);

        } catch (\Exception $e) {
            
            DB::rollBack();

           
            return response()->json($this->setRpta('error','transact : '.$e->getMessage(),[]), 400);
        }

    }


    protected function saveNotes(Request $request){

       

        $data = $request->only('bookingId','description');

            $validator = Validator::make($data, [
                
                'bookingId'=>'required|integer',
                'description'=>'required|string|max:250'
                
               
                
            ]);

            if ($validator->fails()) {

                $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

                return response()->json($middleRpta, 400);
            }

            $bookingId = $request->bookingId;

            if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }
           

            $statusValidate = $this->validateStateBooking($bookingId);

            if ($statusValidate["status"]=="error") {
            
                return response()->json($statusValidate, 400);
            }


            
            $description = $request->description;
            

            $rpta = Pms::saveNotes($bookingId,$description);


           


            if($rpta > 0){

                $action = 'se agregó una nota para la reserva : '.$description;

                $logActivity = new ActivityLogController();

                $logActivity->insertActivity($bookingId,1,$action);

              
              return response()->json($this->setRpta('ok','success created note ',[]),201);

            }

            return response()->json($this->setRpta('error','could not insert note',[]), 400);
            


    }


    protected function confirmDiscount(Request $request){

        try {
            
             DB::beginTransaction();


            $data = $request->only('bookingId','description','discount');

            $validator = Validator::make($data, [
               
                
                'bookingId'=>'required|integer',
                'description'=>'required|string|max:250',
                'discount'=>'required|numeric|min:1'
                
               
                
            ]);

            if ($validator->fails()) {

                $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

                return response()->json($middleRpta, 400);
            }


          
            
            $bookingId = $request->bookingId;

              if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }


            $statusValidate = $this->validateStateBooking($bookingId);

            if ($statusValidate["status"]=="error") {
            
                return response()->json($statusValidate, 400);
            }


            $description = $request->description;
            $discount    = $request->discount;

            Pms::confirmDiscount($bookingId,$description,$discount);

             $action = 'se agregó un descuento de : '.$discount .' por tal motivo : '.$description;

             $logActivity = new ActivityLogController();

             $logActivity->insertActivity($bookingId,1,$action);

             
              DB::commit();

              $totals =  Pms::alltotals($bookingId);

              return response()->json($this->setRpta('ok','added discount',$totals),200);
            

            



        } catch (\Exception $e) {
            
            DB::rollBack();

            return response()->json($this->setRpta('error','transact :'.$e->getMessage(),[]), 400);
        }


        

    }

    protected function getHistoryPrices(Request $request){


        $data = $request->only('bookingId');

            $validator = Validator::make($data, [
                
                'bookingId'=>'required|integer'
  
                
            ]);

            if ($validator->fails()) {

                $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

                return response()->json($middleRpta, 400);
            }

           
            $bookingId        = $request->bookingId;

              if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }

            $list = Pms::getListRatesHistoryOld($bookingId);

            $rpta = $this->setRpta('ok','success response',$list);

            return response()->json($rpta,Response::HTTP_OK); 


    }

    protected function getHistoryProducts(Request $request){


        $data = $request->only('bookingId');

            $validator = Validator::make($data, [
               
                'bookingId'=>'required|integer'
  
                
            ]);

            if ($validator->fails()) {

                $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

                return response()->json($middleRpta, 400);
            }

          
            $bookingId        = $request->bookingId;


            if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }

            $list = Pms::getHistoryProducts($bookingId);

            $rpta = $this->setRpta('ok','success response',$list);

            return response()->json($rpta,Response::HTTP_OK); 


    }

    protected function getHistoryPayments(Request $request){


            $data = $request->only('bookingId');

            $validator = Validator::make($data, [
                
                'bookingId'=>'required|integer'
  
                
            ]);

            if ($validator->fails()) {

                $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

                return response()->json($middleRpta, 400);
            }

          
            $bookingId        = $request->bookingId;

            if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }

            $list = Pms::getHistoryPayments($bookingId);

            $rpta = $this->setRpta('ok','success response',$list);

            return response()->json($rpta,Response::HTTP_OK); 


    }

    

    protected function getHistoryNotes(Request $request){


            $data = $request->only('bookingId');

            $validator = Validator::make($data, [
                
                'bookingId'=>'required|integer'
  
                
            ]);

            if ($validator->fails()) {

                $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

                return response()->json($middleRpta, 400);
            }

        
            $bookingId        = $request->bookingId;

            if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }

            $list = Pms::getHistoryNotes($bookingId);

            $rpta = $this->setRpta('ok','success response',$list);

            return response()->json($rpta,Response::HTTP_OK); 


    }
    
    protected function changeToIgv(Request $request){

            try {
                
                DB::beginTransaction();


                $data = $request->only('bookingId','active');

                $validator = Validator::make($data, [
                   
                    'bookingId'=>'required|integer',
                    'active'=>'required|integer|in:0,1' //aplica igv o no
      
                    
                ]);

                if ($validator->fails()) {

                    $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

                    return response()->json($middleRpta, 400);
                }

                
              
                $bookingId     = $request->bookingId;


                if (!Pms::validateIdBooking($bookingId)) {
            
                    return response()->json($this->setRpta('error','booking id not found',[]), 400);
                }


                $statusValidate = $this->validateStateBooking($bookingId);

                if ($statusValidate["status"]=="error") {
            
                    return response()->json($statusValidate, 400);
                }

                $active        = $request->active;

                
                Pms::changeToIgv($bookingId,$active);

                $igvType = ($active == 1)?'activó':'inactivó';

                $action = 'se '.$igvType.' igv a la reserva' ;

                $logActivity = new ActivityLogController();

                $logActivity->insertActivity($bookingId,1,$action);


                DB::commit();

                $totals =  Pms::alltotals($bookingId);

                return response()->json($this->setRpta('ok','change to igv success',$totals),200);

            } catch (\Exception $e) {
            
                DB::rollBack();

                return response()->json($this->setRpta('error','transact :'.$e->getMessage(),[]), 400);
            }


            



    }



    protected function assignGuest(Request $request){

        try {
             DB::beginTransaction();

             $data = $request->only('bookingId','country','name','lastName','email','phone');

            $validator = Validator::make($data, [
                
                'bookingId'=>'required|integer',
                'country'=>'required|string|max:2',
                'name'=>'required|string|max:50',
                'lastName'=>'required|string|max:50',
                'email'=>'required|string|email|max:50',
                'phone'=>'nullable|string|max:20',
  
                
            ]);

            if ($validator->fails()) {

                $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

                return response()->json($middleRpta, 400);
            }

            
          
            $bookingId     = $request->bookingId;

              if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }


            $statusValidate = $this->validateStateBooking($bookingId);

            if ($statusValidate["status"]=="error") {
            
                return response()->json($statusValidate, 400);
            }


            $country        = $request->country;
            $guestFirstName = $request->name;
            $guestLastName  = $request->lastName;
            $guestEmail     = $request->email;
            $guestPhone     = $request->phone;
           

            $hotel = $this->userGlobal->HotelId;

            $user = $this->userGlobal->id;



            
             $rpta = Booking::createUserBooking($hotel,$country,$guestFirstName,$guestLastName,$guestEmail,$guestPhone,$user);

            if(isset($rpta[0]->ID) ){

                    
                    $idGuest = $rpta[0]->ID;


                    Pms::assignGuest($bookingId,$idGuest);

                    $action = 'se asignó a la reserva el cliente con correo : '.$guestEmail ;

                    $logActivity = new ActivityLogController();

                    $logActivity->insertActivity($bookingId,1,$action);

                    DB::commit();

                    return response()->json($this->setRpta('ok','assign guest',[]),200);


            }

            DB::rollBack();

            return $this->setRpta('error','could not create user ',[]);

        } catch (\Exception $e) {
            
            DB::rollBack();

            return response()->json($this->setRpta('error','transact :'.$e->getMessage(),[]), 400);

        }
            



    }


    


    protected function changeDateArrival(Request $request){

        $data = $request->only('bookingId','date');

            $validator = Validator::make($data, [
                
                'bookingId'=> 'required|integer',
               
                'date'=>'required|date', 
               
  
                
            ]);

            if ($validator->fails()) {

                $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

                return response()->json($middleRpta, 400);
            }


    
            $bookingId = $request->bookingId;
           
            if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }

            
            $statusValidate = $this->validateStateBooking($bookingId);

            if ($statusValidate["status"]=="error") {
            
                return response()->json($statusValidate, 400);
            }


            $date = $request->date;

             Pms::changeDateArrival($bookingId,$date);

            
             $action = 'se cambió la fecha de arribo al pais a : '.$date;

             $logActivity = new ActivityLogController();

             $logActivity->insertActivity($bookingId,1,$action);

             DB::commit();

              return response()->json($this->setRpta('ok','change date arrival success',$date),200);


         

    }

    protected function inactiveItemProduct(Request $request){

        try {
            
            DB::beginTransaction();

            $data = $request->only('bookingId','id');

            $validator = Validator::make($data, [
                
                'bookingId'=> 'required|integer',
               
                'id'=>'required|integer', //id del item a inactivar
               
  
                
            ]);

            if ($validator->fails()) {

                $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

                return response()->json($middleRpta, 400);
            }


    
            $bookingId = $request->bookingId;
           
            if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }


            $statusValidate = $this->validateStateBooking($bookingId);

            if ($statusValidate["status"]=="error") {
            
                return response()->json($statusValidate, 400);
            }

            $id        = $request->id;

             Pms::inactiveItemProduct($id);

            
             $action = 'se inactivó el registro de un producto : '.Maintenance::getNameProductBooking($id);

             $logActivity = new ActivityLogController();

             $logActivity->insertActivity($bookingId,1,$action);

             DB::commit();

             $totals =  Pms::alltotals($bookingId);

              return response()->json($this->setRpta('ok','inactive item product success',$totals),200);

        } catch (\Exception $e) {
            
            DB::rollBack();

            return response()->json($this->setRpta('error','transact :'.$e->getMessage(),[]), 400);

        }


         

    }


    protected function setStatesByBooking($bookingId){

        $status = Pms::getStateBooking($bookingId);

        $nameStatus = Pms::getNameStateBooking($status);

        $blocked = [2,3,4];

        $data = [];

        if(in_array($status, $blocked)){

        
            $data[] = array('id'=> $status ,'text'=>$nameStatus);
        }

        $checkIn = [1];

        if(in_array($status, $checkIn)){

             $data[] = array('id'=> 2 ,'text'=>'Check Out');
        }


        $reserved = [5];

         if(in_array($status, $reserved)){

             $data = array(
                            array('id'=> 4 ,'text'=>'Canceled'),
                            array('id'=> 6 ,'text'=>'Maintenance')

                        );
             
        }


        return array('UniqueStates'=> $data );
                                    

                    
    }

     protected function getInfoDetail(Request $request){


            $data = $request->only('bookingId');

            $validator = Validator::make($data, [
               
                'bookingId'=>'required|integer'
  
                
            ]);

            if ($validator->fails()) {

                

                return response()->json($this->setRpta('warning','validator fails',$validator->messages()), 400);
            }

            
            $bookingId      = $request->bookingId;



            if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }


            //$list = Pms::getInfoDetail($bookingId);

            $list = Pms::getInfoDetailWithButtons($bookingId);
            
            $totals =  Pms::alltotals($bookingId);

            $listStatesByBooking = $this->setStatesByBooking($bookingId);

            $dataSet = array_merge((array)$list[0],(array)$totals[0],$listStatesByBooking);
            
            $rpta = $this->setRpta('ok','success response',$dataSet);

            return response()->json($rpta,Response::HTTP_OK); 


    }

    protected function validateBalanceDue($bookingId,$state){

        //para estado check out o cancelacion o no show valida

        //$statesDue = [2,3,4];

        $statesDue = [2]; //solo checkout

       if (in_array($state, $statesDue)) {
            
             $totals =  Pms::alltotals($bookingId);

           
             $due = (isset($totals[0]->BalanceDue))?$totals[0]->BalanceDue:0;

             if(floatval($due)>0){

                return $this->setRpta('error','cannot be modified due to debt : '.$due ,[]);
             }

             return $this->setRpta('ok','success validation',[]);
        }

        return $this->setRpta('ok','success validation',[]);

    }




    protected function updateState(Request $request){

        try {
            
            DB::beginTransaction();

            $data = $request->only('bookingId','state');

            $validator = Validator::make($data, [
               
                'bookingId'=>'required|integer',
             
                'state'=>'required|integer'
  
                
            ]);

            if ($validator->fails()) {

                return response()->json($this->setRpta('warning','validator fails',$validator->messages()), 400);
            }

            
          
            $bookingId      = $request->bookingId;
            

             $state          = $request->state;

            if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }



            $statusValidate = $this->validateStateBooking($bookingId);

            if ($statusValidate["status"]=="error") {
            
                return response()->json($statusValidate, 400);
            }


           

            $middleRpta = $this->validateBalanceDue($bookingId,$state);
            
            if($middleRpta["status"] == "ok"){

                Pms::updateState($bookingId,$state);

                $nameStatus = Maintenance::getNameStatusBooking($state);
                
                $action = 'se cambió el estado de la reserva a : '. $nameStatus;

                $logActivity = new ActivityLogController();

                $logActivity->insertActivity($bookingId,1,$action);

                //si se cancela la reserva enviar correo

                 if($state == 4){

                    $hotel = $this->userGlobal->HotelId;

                    $user = $this->userGlobal->id;

                    $correo = new CorreoController();

                    $correo->sendEmailBookingCancel($bookingId,$hotel,$user);
                 }
                  
             
                DB::commit();
              
              return response()->json($this->setRpta('ok','updated status booking success',$nameStatus),200);

            }


            return $middleRpta;
            

        } catch (\Exception $e) {
            
            DB::rollBack();

            return response()->json($this->setRpta('error','transact :'.$e->getMessage(),[]), 400);

        }
        

    }

    protected function viewSameRooms(Request $request){


        $data = $request->only('bookingId','same');

            $validator = Validator::make($data, [
               
                'bookingId'=> 'required|integer',
                'same'=> 'required|integer|in:0,1',
                
            
                
            ]);

            if ($validator->fails()) {

                return response()->json($this->setRpta('warning','validator fails',$validator->messages()), 400);
            }


        $bookingId = $request->bookingId;


          if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }


        $checkIn = Pms::getCheckInBooking($bookingId);

        $checkOut = Pms::getCheckOutBooking($bookingId);

        $request->request->set('checkIn', $checkIn);

        $request->request->set('checkOut', $checkOut);


        $viewAvailability = $this->viewAvailability($request);

        $jsonData = json_decode(json_encode($viewAvailability),true);
        
        
        $data = $jsonData["original"]["data"];

        

        $idRoomType = Pms::getRoomTypeBooking($bookingId);

        $same = $request->same;

        if($same == 1){

            $data = array_filter( $data, function( $e ) use ($idRoomType){

                return $e['IdTypeRoom'] == $idRoomType;
            });


        }
        

        $middleRpta = $this->setRpta('ok','success response',array_values($data));

        return response()->json($middleRpta,Response::HTTP_OK);

       

    }

     protected function viewAvailability(Request $request){

            
            $data = $request->only('checkIn','checkOut','lang');

            $validator = Validator::make($data, [
               
                'checkIn'=> 'required|date|after_or_equal:today',
                'checkOut'=> 'required|date|after:checkIn',
                'lang'=>'required|string|max:2'
                
  
                
            ]);

            if ($validator->fails()) {

                return response()->json($this->setRpta('warning','validator fails',$validator->messages()), 400);
            }

            
            $hotel = $this->userGlobal->HotelId;

            $parseToFloat = Booking::searchRooms($request,$hotel);

            $dataSet = [];



            foreach($parseToFloat as $values){


                $values = (array) $values;

                    $strToArray = explode(",",$values["ItemsIdsAvailable"]);


                   


                    foreach($strToArray as $list){


                        
                            $dataSet[] = array(
                                    "TypeSelect"      => $values["TypeSelect"],
                                    "IdTypeRoom"      => $values["IdTypeRoom"],
                                    "Name"            => $values["Name"],
                                    "Description"     => $values["Description"],
                                    "DescriptionBeds" => $values["DescriptionBeds"],
                                    "MaxPersonRoom"   => $values["MaxPersonRoom"],
                                    "Rate"            => $values["Rate"],
                                    "Images"          => $values["Images"],
                                    "Area"            => $values["Area"],
                                    "RoomIdOrBedId"   => $list,
                                    "Number"          => Maintenance::getNumberRoomBooking($list),
                                    
                                    "Private"         => $values["Private"],
                                    "FlagExternalBath" => $values["FlagExternalBath"],
                                    "FlagSharedBath"  => $values["FlagSharedBath"],
                                    "FlagBreakfast"   => $values["FlagBreakfast"],
                                    "FlagTv"          => $values["FlagTv"],
                                    "FlagWifi"        => $values["FlagWifi"],
                                    "FlagFullDay"     => $values["FlagFullDay"],
                                    "FlagBalcony"     => $values["FlagBalcony"],
                                    "FlagFridge"      => $values["FlagFridge"],
                                    "FlagHotTub"      => $values["FlagHotTub"],
                                    "FlagRoomService" => $values["FlagRoomService"]
                                );
                            
                    }

            }

            $middleRpta = $this->setRpta('ok','success response',$dataSet);

            return response()->json($middleRpta,Response::HTTP_OK);
    }



    


    protected function validateBeforeInsertBookingPms($request){


        $list = $request->roomsTypeCount;

        $hotel = $this->userGlobal->HotelId;

        $checkIn  = Carbon::parse($request->checkIn)->format('Y-m-d');
        
        $checkOut = Carbon::parse($request->checkOut)->format('Y-m-d');

        $groupTypes = [];

        $str = '';

        foreach($list as $values){


            $idRoomOrBed = $values["idRoomOrBed"];

            $typeRoom = Pms::getTypeRoomById($idRoomOrBed);

            


            $nameType = Maintenance::getNameTypeRoom($typeRoom);

            if(empty($nameType)){

                return $this->setRpta('error','the type of room does not exist for: '.$typeRoom ,[]);
            }


            $priceBase = Maintenance::getPriceBaseTypeRoom($typeRoom);

            if(empty($priceBase)){

                return $this->setRpta('error','  this type of room does not have a base price :  '.$nameType ,[]);
            }


            //disponiblidad unitaria x id de cuarto o cama

            $book = new BookingController();

            $middleRpta = $book->validateInterferenceDatesNewBooking($hotel,$checkIn,$checkOut,$idRoomOrBed);

            if($middleRpta["status"]=="error"){

                return $middleRpta;
            }


            $groupTypes[$typeRoom][] = $idRoomOrBed;





        }

        

        foreach($groupTypes as $key=>$list){

            $ids = implode(",",$list);
            
            $strAdultsKids = "0&0";

            $str .= $key.'|'.$ids.'|'.$strAdultsKids.'-' ;
        }

        $insertIds = rtrim($str,"-");

        return $this->setRpta('ok','validate success',$insertIds);
    }

   
    protected function updatedAdultsAndKids($reservationId,$request){

        $list = $request->roomsTypeCount;

        foreach($list as $values){

            $idRoomOrBed = $values["idRoomOrBed"];
            $adults      = $values["adults"];
            $kids        = $values["kids"];


            DB::update("UPDATE tblbookings SET Adults =?,Kids=?  WHERE RoomBedId =? AND ReservationId=?",array($adults,$kids,$idRoomOrBed,$reservationId));
        }

    }


    protected function createBooking(Request $request){


        try {
              
             DB::beginTransaction();

              $data = $request->only('country', 'guestFirstName','guestLastName','guestEmail', 'guestPhone','checkIn', 'checkOut', 'dateArrival','arrivalTime', 'specialRequest','origen', 'roomsTypeCount','temporary','statusBooking');

             $validator = Validator::make($data, [
               
                'country' => 'required|string|max:2',
                'guestFirstName' => 'required|string|max:100',
                'guestLastName' => 'required|string|max:100',
                'guestEmail' => 'required|string|email|max:100',
                'guestPhone' => 'nullable|string|max:20',
                'checkIn' => 'required|date|after_or_equal:today',
                'checkOut' => 'required|date|after:checkIn',
                'dateArrival' => 'required_unless:country,pe|nullable|date|after_or_equal:checkIn|before_or_equal:checkOut',
                'arrivalTime' => 'nullable|string|max:50',
                'specialRequest' => 'nullable|string|max:250',
                'origen' => 'required|integer',
                'roomsTypeCount' =>'required',
                'temporary'=>'required|integer|in:0,1',
                'statusBooking'=>'required|integer',
                
                
                
            ]);

            if ($validator->fails()) {

                return response()->json($this->setRpta('warning','validator fails',$validator->messages()), 400);
            }


                
                  $hotel = $this->userGlobal->HotelId;

                  $user  = $this->userGlobal->id;
              
                 $bookingC = new BookingController();

                 $holderRpta = $bookingC->createUserBooking($request,$hotel,$user);

                

                 if($holderRpta["status"]=="ok"){

                     $holderId = $holderRpta["data"];

                 }else{

                      return response()->json($holderRpta,400);
                 }


                $middleRpta = $this->validateBeforeInsertBookingPms($request);



                if($middleRpta["status"] == "ok"){

                    $idsInsert = $middleRpta["data"] ;

                    $resultId = Pms::createBooking($request,$idsInsert,$holderId);



                    if(isset($resultId[0]->ID)){

                         

                         $reservationId = $resultId[0]->ID;


                         $this->updatedAdultsAndKids($reservationId,$request);

                         $lang = Maintenance::getLangCountry($request->country) ;

                         $correo = new CorreoController();

                         $correo->sendEmailBookingSuccess($reservationId,$lang,$hotel,$user);
                

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

            return response()->json($this->setRpta('error','transact :'.$e->getMessage(),[]), 400);

        }


    

    }

    

    protected function reassignRoom(Request $request){



        try {


            DB::beginTransaction();

            $data = $request->only('bookingId','newIdRoomOrBed');

            $validator = Validator::make($data, [
              
                'bookingId' =>  'required|integer', 
                'newIdRoomOrBed' => 'required|integer'
                
                

                
            ]);

            if ($validator->fails()) {

                return response()->json($this->setRpta('warning','validator fails',$validator->messages()), 400);
            }


            $hotel =  $this->userGlobal->HotelId;

            $newIdRoomOrBed = $request->newIdRoomOrBed;

            $bookingId = $request->bookingId;

            if (!Pms::validateIdBooking($bookingId)) {
            
                return response()->json($this->setRpta('error','booking id not found',[]), 400);
            }


            $statusValidate = $this->validateStateBooking($bookingId);

            if ($statusValidate["status"]=="error") {
            
                return response()->json($statusValidate, 400);
            }

            $checkIn  = Pms::getCheckInBooking($bookingId);
        
            $checkOut = Pms::getCheckOutBooking($bookingId);

            $oldIdRoomBed = Pms::getIdRoomBooking($bookingId);

            $oldNumberRoomBed = Maintenance::getNumberRoomBooking($oldIdRoomBed);

            
            $newNumberOrBed = Maintenance::getNumberRoomBooking($newIdRoomOrBed);

            
            $book = new BookingController();

            $middleRpta = $book->validateInterferenceDatesNewBooking($hotel,$checkIn,$checkOut,$newIdRoomOrBed);



            if($middleRpta["status"]=="error"){

                return $middleRpta;
            }

                
            
               Pms::reassignRoom($bookingId,$newIdRoomOrBed);

             
                $action = 'se hizo un cambió de habitación de : '.$oldNumberRoomBed.' a ' .$newNumberOrBed;

                $logActivity = new ActivityLogController();

                $logActivity->insertActivity($bookingId,1,$action);
             
                DB::commit();
              
              return response()->json($this->setRpta('ok','updated room or bed booking success',[]),200);


        } catch (\Exception $e) {
            
            DB::rollBack();

            return response()->json($this->setRpta('error','transact :'.$e->getMessage(),[]), 400);

        }


        


    }

    protected function getDetailsReservation(Request $request){


         $data = $request->only('reservationId');

            $validator = Validator::make($data, [
              
                'reservationId' =>  'required|integer'
              
                                
            ]);

            if ($validator->fails()) {

                return response()->json($this->setRpta('warning','validator fails',$validator->messages()), 400);
            }


        $reservationId = $request->reservationId;

         if(!Booking::validateIdReservation($reservationId)){
                
               return response()->json($this->setRpta('error','reservation id not found',[]), 400);
            }

            
        $list = Pms::getDetailsReservation($reservationId);

        $middleRpta = $this->setRpta('ok','success list',$list);

        return response()->json($middleRpta,Response::HTTP_OK);

    }



    protected  function getAllReservation(Request $request){


        //ultimos 7 dias creacion 

        $from = Carbon::now()->format('Y-m-d') ;

        $to = Carbon::now()->addDay(7)->format('Y-m-d');

            
        $list = Pms::getAllReservation($from,$to);

        $dataSet = [];

      
        foreach($list as $values){

            $values = (array) $values;

            $totalsget = Pms::alltotals($values["BookingId"]);

    
            $dataSet[] = array(

                "BookingId"=>$values["BookingId"],
                "Guest"=>$values["Guest"],
                "CheckIn"=>$values["CheckIn"],
                "CheckOut"=>$values["CheckOut"],
                "Room"=>$values["Number"],
                "BookedOn"=>$values["BookedOn"],
                "BookingStatus"=>$values["BookingStatus"],
                "Origen"=>$values["Origen"],
                "BtnActivityLog"=>$values["btnActivityLog"],
                "Total"=>$totalsget[0]->Total,
                "Paid"=>$totalsget[0]->Paid,
                "BalanceDue"=>$totalsget[0]->BalanceDue

            );
        }
        

        $middleRpta = $this->setRpta('ok','success list',$dataSet);

        return response()->json($middleRpta,Response::HTTP_OK);

    }

    
}