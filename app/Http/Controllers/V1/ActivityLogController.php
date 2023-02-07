<?php
namespace App\Http\Controllers\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\ActivityLog;


class ActivityLogController extends Controller
{
   


    
    public function insertActivity($bookingId,$order,$description){

       
        ActivityLog::insertActivity($bookingId,$description,$order);

        

    }
   
   protected function getActivityLog(Request $request){


   		$data = $request->only('bookingId','dateFrom','dateTo','activeDate');

        $validator = Validator::make($data, [
            
            'bookingId' => 'required|integer',
            'activeDate'=>'required|integer|in:0,1',
            'dateFrom'=>'nullable|date|required_unless:activeDate,0',
            'dateTo'=>'nullable|date|required_unless:activeDate,0',
            
           
            
        ]);
        
        if ($validator->fails()) {

            $middleRpta = $this->setRpta('warning','validator fails',$validator->messages());

            return response()->json($middleRpta, 400);
        }
      
    
        $list = ActivityLog::getActivityLog($request);

        $middleRpta = $this->setRpta('ok','success list',$list);

        return response()->json($middleRpta,Response::HTTP_OK);

   }
    
}