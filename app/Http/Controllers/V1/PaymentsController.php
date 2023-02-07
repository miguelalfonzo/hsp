<?php
namespace App\Http\Controllers\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Maintenance;

use App\Models\Pms;


 
class PaymentsController extends Controller
{
   

    
    
    



     public function paypal()
    {
        


       return view('payments.paypal');

    }
    
    public function culqui()
    {
        

        return view('payments.culqui');
        

    }

    public function culquiPayment($request){

        require 'plugins/requests/library/Requests.php';

        \Requests::register_autoloader();
        
        require 'plugins/culqi/lib/culqi.php';
        
        //obtener monto guardado de la reserva

        

        $amount = Pms::getTotalReservation($request->reservationId);

      
        

        $emailHolder = Pms::getEmailHolderReservation($request->reservationId);     
        

        $SECRET_KEY = "sk_test_S73fcELW95DFXhGi";

        $culqi = new \Culqi\Culqi(array('api_key' => $SECRET_KEY));

        $charge = $culqi->Charges->create(
            array(
                
                "amount" =>number_format($amount,0)*100 ,
                //"amount" => ($amount)*100,
                "capture" => true,
                "currency_code" => "USD",
                "description" => "pago reservación",
                "email" => $emailHolder,
                "source_id" => $request->token
            )
        );

       

        return json_encode($charge);
    }
    
    
   
   
    
}