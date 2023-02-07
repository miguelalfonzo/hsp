<?php
namespace App\Http\Controllers\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Maintenance;
use App\Models\Pms;
use App\Http\Controllers\V1\CorreoController;
use DB;

class MaintenanceController extends Controller
{
   
 protected $userGlobal ;


  public function __construct(Request $request)
    {
        $token = $request->header('Authorization');

        if($token != '')
            
           

            $this->userGlobal = JWTAuth::parseToken()->authenticate();

           
    }

  


    protected function options(Request $request)
    {
        


        $data = $request->only('type');

        $validator = Validator::make($data, [
            'type' => 'required|integer',
           
            
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }
      
    
        $list = Maintenance::options($request);

        $middleRpta = $this->setRpta('ok','success list',$list);

        return response()->json($middleRpta,Response::HTTP_OK);

    }
    


    

    

    protected function getPermissions(Request $request){

        $data = $request->only('user');

        $validator = Validator::make($data, [
            
          
            'user'=>'required|integer'
           
            
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }
      
        
        $user = $request->user;

        $hotel = JWTAuth::parseToken()->authenticate()->HotelId;

        $list = Maintenance::getPermissions($user,$hotel);

        $middleRpta = $this->setRpta('ok','success list',$list);

        return response()->json($middleRpta,Response::HTTP_OK);

    }
   

    protected function seekerGuest(Request $request){

        $data = $request->only('term');

        $validator = Validator::make($data, [
            
          
            'term'=>'nullable|max:250'
           
            
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }
      
        
       
        $list = Maintenance::seekerGuest($request);

        $middleRpta = $this->setRpta('ok','success list',$list);

        return response()->json($middleRpta,Response::HTTP_OK);

    }
   


    protected function seekerProducts(Request $request){

        $data = $request->only('category','term');

        $validator = Validator::make($data, [
            
            'category' => 'required|integer',
            'term'=>'nullable|max:250'
           
            
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }
      
        
       
        $list = Maintenance::seekerProducts($request);

        $middleRpta = $this->setRpta('ok','success list',$list);

        return response()->json($middleRpta,Response::HTTP_OK);

    }
   



    //RTYPES - MAINTENANCE

     protected function getRoomTypes(Request $request)
    {
        

        $data = $request->only('id');

        $validator = Validator::make($data, [

            'id' => 'required|integer'    
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }

        
        $list = Maintenance::getRoomTypes($request);

        $middleRpta = $this->setRpta('ok','success list',$list);

        return response()->json($middleRpta,Response::HTTP_OK);

    }



    protected function saveRoomType(Request $request)
    {
        

        $data = $request->only('id','name','nameEs','description','descriptionEs','descriptionBeds','maxPerson','area','private','flagExternalBath','flagSharedBath','flagBreakfast','flagTv','flagWifi','flagFullDay','flagFridge','flagBalcony','flagHotTub','flagRoomService');

        $validator = Validator::make($data, [

            'id' => 'required|integer',
            'name' => 'required|string|max:250',
            'nameEs' => 'required|string|max:250',
            'description' => 'required|string|max:250',    
            'descriptionEs' => 'required|string|max:250',
            'descriptionBeds' => 'required|string|max:250',
            'maxPerson' => 'required|integer',
          
            'area' => 'required|string|max:10',
            'private' => 'required|integer|in:0,1',
            'flagExternalBath' => 'required|integer|in:0,1',
            'flagSharedBath' => 'required|integer|in:0,1',
            'flagBreakfast' => 'required|integer|in:0,1',
            'flagTv' => 'required|integer|in:0,1',
            'flagWifi' => 'required|integer|in:0,1',
            'flagFullDay' => 'required|integer|in:0,1',
            'flagFridge' => 'required|integer|in:0,1',
            'flagBalcony' => 'required|integer|in:0,1',
            'flagHotTub' => 'required|integer|in:0,1',
            'flagRoomService' => 'required|integer|in:0,1',
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }

        
        $resultId = Maintenance::saveRoomType($request);


         if(isset($resultId[0]->ID)){

            return response()->json($this->setRpta('ok','room type save successfully ',$resultId[0]->ID),201);

        }

            
        return response()->json($this->setRpta('error','could not save room type ',[]),400);

    }
    

    //maintenance rates


    
   

   protected function getRates(Request $request)
    {
        

        $data = $request->only('id');

        $validator = Validator::make($data, [

            'id' => 'required|integer'    
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }

        
        $list = Maintenance::getRates($request);

        $middleRpta = $this->setRpta('ok','success list',$list);

        return response()->json($middleRpta,Response::HTTP_OK);

    }



    

     protected function saveRates(Request $request)
    {
        

        $data = $request->only('id','description','hierarchy','objectTypes','rate','rateBooking','dateFrom','dateTo');

        $validator = Validator::make($data, [

            'id' => 'required|integer',
            'description' => 'required|string|max:250',
            'hierarchy' => 'required|integer',    
            'objectTypes' => 'nullable|required_unless:id,0',
            'rate' => 'required|numeric',
            'rateBooking' => 'required|numeric',
            'dateFrom' => 'nullable|date|required_unless:hierarchy,0',
            'dateTo' => 'nullable|date|required_unless:hierarchy,0|after:dateFrom',
           
           
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }

        
        $resultId = Maintenance::saveRates($request);


         if(isset($resultId[0]->ID)){

            return response()->json($this->setRpta('ok','rate save successfully ',$resultId[0]->ID),201);

        }

            
        return response()->json($this->setRpta('error','could not save rate ',[]),400);

    }
    

    //maintenance room

    


   protected function getRooms(Request $request)
    {
        

        $data = $request->only('id');

        $validator = Validator::make($data, [

            'id' => 'required|integer'    
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }

        
        $list = Maintenance::getRooms($request);

        $middleRpta = $this->setRpta('ok','success list',$list);

        return response()->json($middleRpta,Response::HTTP_OK);

    }




    protected function saveRooms(Request $request)
    {
        

        $data = $request->only('id','number','description','category','roomType');

        $validator = Validator::make($data, [

            'id' => 'required|integer',
            'number' => 'required|string|max:10',
            'description' => 'nullable|string|max:250',
            'category' => 'required|string|max:10', 
            'roomType' => 'required|integer',  
           
           
           
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }

        
        $resultId = Maintenance::saveRooms($request);


         if(isset($resultId[0]->ID)){

            return response()->json($this->setRpta('ok','room / bed save successfully ',$resultId[0]->ID),201);

        }

            
        return response()->json($this->setRpta('error','could not save room / bed ',[]),400);

    }

    //coupon


    
    
     protected function getCoupon(Request $request)
    {
        

        $data = $request->only('id');

        $validator = Validator::make($data, [

             'id' => 'required|integer'    
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }

        
        $list = Maintenance::getCoupon($request);

        $middleRpta = $this->setRpta('ok','success list',$list);

        return response()->json($middleRpta,Response::HTTP_OK);

    }


    protected function validateCodeCoupon($code)
    {

        $hotel = $this->userGlobal->HotelId;

        $query = DB::select("SELECT count(*) AS total FROM tblcoupon WHERE HotelId=? AND Code=?",array($hotel,trim($code)));


        
        if(intval($query[0]->total)>0){

            return $this->setRpta('error','the coupon '.$code.' is already registered',[]);

        }

          return $this->setRpta('ok','validate code coupon success',[]);

    }



     protected function saveCoupon(Request $request)
    {
        

        $data = $request->only('id','code','value','count','active','expirationDate');

        $validator = Validator::make($data, [

            'id' => 'required|integer',
            'code' => 'required|string|max:50',
            'value' => 'required|numeric',
            'count' => 'required|integer', 
            'active' => 'required|integer|in:0,1',  
            'expirationDate' => 'required|date|after_or_equal:today',  
           
           
           
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }

        $middleRpta =  $this->validateCodeCoupon($request->code);

        if($middleRpta["status"] == "error"){


             return response()->json($middleRpta,400);
        }

        $resultId = Maintenance::saveCoupon($request);


         if(isset($resultId[0]->ID)){

            return response()->json($this->setRpta('ok','coupon save successfully ',$resultId[0]->ID),201);

        }

            
        return response()->json($this->setRpta('error','could not save coupon ',[]),400);

    }



    

    protected function getUser(Request $request)
    {
        

        $data = $request->only('id');

        $validator = Validator::make($data, [

             'id' => 'required|integer'    
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }

        
        $list = Maintenance::getUser($request);

        $middleRpta = $this->setRpta('ok','success list',$list);

        return response()->json($middleRpta,Response::HTTP_OK);

    }


    


    protected function saveUser(Request $request)
    {
        
        try {
            
            DB::beginTransaction();

            $data = $request->only('id','firstName','lastName','email','phone','phone2','active','address','dni','userType');

            $validator = Validator::make($data, [

                'id' => 'required|integer',
                'firstName' => 'required|string|max:250',
                'lastName' => 'required|string|max:250',
                'email' => 'required|email', 
                'phone' => 'required|string|max:12',  
                'phone2' => 'nullable|string|max:12',
                'active' => 'required|integer|in:0,1', 
                'address' => 'required|string|max:250', 
                'dni' => 'required|string|max:8', 
                'userType' => 'required|integer', 

               
               
               
            ]);
            
            if ($validator->fails()) {

                $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

                return response()->json($middleRpta, 400);
            }

           

            $resultUser = Maintenance::saveUser($request);

            $idGenerate = $resultUser[0];

            $passwordGenerate = $resultUser[1];


            $sendCredentials = ($request->id == 0)?true:false;


            $hotel = $this->userGlobal->HotelId;

            $userLog = $this->userGlobal->id;
            

            if(isset($idGenerate[0]->ID)){

                if($sendCredentials){

                     $correo = new CorreoController();

                     $correo->sendEmailUserCredentials($request,$passwordGenerate,$hotel,$userLog);

                }

                DB::commit();

                return response()->json($this->setRpta('ok','user save successfully ',$idGenerate[0]->ID),201);

            }

                
            return response()->json($this->setRpta('error','could not save user ',[]),400);

        } catch (\Exception $e) {

             DB::rollBack();

             return response()->json($this->setRpta('error','transact : '.$e->getMessage(),[]), 400);
            
        }



       

    }
}