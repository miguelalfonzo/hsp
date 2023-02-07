<?php

namespace App\Http\Controllers\V1;
use App\Http\Controllers\Controller;
use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Maintenance;

class AuthController extends Controller
{
    
     
    public function register(Request $request)
    {
       
        $data = $request->only('email', 'password', 'name');
        
        $validator = Validator::make($data, [
            'name'=>'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50',
        ]);
        

        if ($validator->fails()) {
            

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }
       
        $user = User::create([
            'name'=>$request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)

        ]);
        

        $credentials = $request->only('email', 'password');
        
        $data = array(

          
            'token' => JWTAuth::attempt($credentials),
            'user' => $user
        );



         $middleRpta = $this->setRpta('ok','user created',$data);

        return response()->json($middleRpta, Response::HTTP_OK);

    }
    
    public function authenticate(Request $request)
    {
        
        $credentials = $request->only('email', 'password');
        
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);
        
        if ($validator->fails()) {

             $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }
      
        try {


            $user = User::where('email', $credentials['email'])->first();

            if(!(!empty($user) && $user->Active == 1 )) {
                
                return response()->json($this->setRpta('error','unauthorized user, contact the administrator',[]), 401);
            }


            if (!$token = JWTAuth::attempt($credentials)) {
                
                return response()->json($this->setRpta('error','login failed',[]), 401);
            }

        } catch (JWTException $e) {
            
            return response()->json($this->setRpta('error',$e->getMessage(),[]), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
       
         $permissions = Maintenance::getPermissions(Auth::user()->id,Auth::user()->HotelId);
         
         $data = array('token' => $token,'user' => Auth::user() ,'permissions'=>$permissions );

         return response()->json($this->setRpta('ok','data success',$data), 200);

        
    }

    
    public function logout(Request $request)
    {
        
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);

            
        }
        try {
            
            JWTAuth::invalidate($request->token);
            


            $middleRpta = $this->setRpta('ok','user disconnected',[]);

            return response()->json($middleRpta, 200);

        } catch (JWTException $e) {
            
          

            $middleRpta = $this->setRpta('error',$e->getMessage(),[]);

            return response()->json($middleRpta, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

    public function getUser(Request $request)
    {
        
        $this->validate($request, [
            'token' => 'required'
        ]);
        
        $user = JWTAuth::authenticate($request->token);
        
        
        if(!$user){

             return response()->json($this->setRpta('error','invalid token / token expired',[]), 401);

            
        }
        
        $permissions = Maintenance::getPermissions(Auth::user()->id,Auth::user()->HotelId);

        $data= array(

            'user' => $user ,
            'token'=>$request->token,
            'permissions'=>$permissions
        );
        return response()->json($this->setRpta('ok','success ',$data), 200);



        
    }
}
