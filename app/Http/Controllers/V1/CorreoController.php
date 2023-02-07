<?php
namespace App\Http\Controllers\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Maintenance;
use Swift_Mailer;
use Swift_SmtpTransport;
use Swift_Message;

use App\Models\ActivityLog;
use App\Models\Pms;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use DB;

class CorreoController extends Controller
{
   

   public function mailOhotels($hotel){

    ;

       $query = DB::select("SELECT EmailParams FROM tblparameters WHERE HotelId=?",array($hotel));

       $email = $query[0]->EmailParams;

       $data = json_decode($email,true);


       
       return array("HOST"     =>$data["host"],
                     "PUERTO"   => $data["port"],
                     "CORREO"   => $data["email"],
                     "PASSWORD"  => $data["password"],
                     "ENCRIPTACION"=> $data["encryption"]);
       


      
    }

    public function getLogo($hotel){

         $data = $this->getDataOhotel($hotel);
          
          $logo = file_get_contents($data->Logo);
          $logo = base64_encode($logo);
          $logo = "data:image/png;base64,".$logo;


        return $logo;

    }


    public function getDataOhotel($hotel){

      $query = DB::select("SELECT * FROM tblhotels WHERE Id=?",array($hotel));

      return $query[0];
    }

public function sendEmailBookingChangeDates($bookingId,$hotel,$user){



  $lang = Pms::getLangEsGuest($bookingId);

    $logo = $this->getLogo($hotel);

    $mail = $this->mailOhotels($hotel);

    
        $host         = $mail['HOST'];
        $port         = $mail['PUERTO'];
        $encriptacion = $mail['ENCRIPTACION'];
        $from         = $mail['CORREO'];
        $password     = $mail['PASSWORD'];


        $header = Pms::getInfoDetail($bookingId);

        $totals =  Pms::alltotals($bookingId);


        $recipients = Pms::getEmailGuest($bookingId);

        $bccEmails = Maintenance::getMailUserLogin($user);

        $transport = (new Swift_SmtpTransport($host, $port, $encriptacion))
              ->setUsername($from)
              ->setPassword($password);

        $mailer = new Swift_Mailer($transport);



        $ohotelEmail = $this->getDataOhotel($hotel)->Email;

        $ohotelPhone = $this->getDataOhotel($hotel)->Phone;
        
        $ohotelsCity = $this->getDataOhotel($hotel)->City;

        $contact = array($ohotelEmail,$ohotelPhone,$ohotelsCity);


        
         $contents=array( 

            'header' => $header ,
            'totals'=>$totals,
            'contact'=>$contact,
            'logo'=> $logo,
            'sign' =>''

          );

          $body = array('information'=> $contents);


        if( !config("global.send_email_production") ){

             $recipients = config("global.support_1");

        }

       
          $subject = ($lang == 'es')?'Modificación de Reserva':'Changes in Booking' ;

          $template = ($lang == 'es')?'emails.booking.ChangeDatesBookingEs':'emails.booking.ChangeDatesBookingEn' ;


          $empresa  = config('app.name') ;

          $message   = (new Swift_Message($subject))
              ->setFrom($from,$empresa)
              ->setTo($recipients)
              ->addBcc($bccEmails)
              ->setBody(view($template, $body)->render(),'text/html');
             
        

        if($mailer->send($message)>0){

             
           ActivityLog::insertLogEmail(1,$hotel,$subject,$contents,$mail,$user,$recipients,$bccEmails);

           return $this->setRpta("ok","it was sent satisfactorily",[]);

        }else{

            ActivityLog::insertLogEmail(0,$hotel,$subject,$contents,$mail,$user,$recipients,$bccEmails);

          
          return $this->setRpta("error","could not send mail",[]);

        }



}



public function sendEmailBookingCancel($bookingId,$hotel,$user){
   

    $lang = Pms::getLangEsGuest($bookingId);

    $logo = $this->getLogo($hotel);

    $mail = $this->mailOhotels($hotel);


        $host         = $mail['HOST'];
        $port         = $mail['PUERTO'];
        $encriptacion = $mail['ENCRIPTACION'];
        $from         = $mail['CORREO'];
        $password     = $mail['PASSWORD'];


        $header = Pms::getInfoDetail($bookingId);

        $totals =  Pms::alltotals($bookingId);


        $recipients = Pms::getEmailGuest($bookingId);

        $bccEmails = Maintenance::getMailUserLogin($user);

        $transport = (new Swift_SmtpTransport($host, $port, $encriptacion))
              ->setUsername($from)
              ->setPassword($password);

        $mailer = new Swift_Mailer($transport);



        $ohotelEmail = $this->getDataOhotel($hotel)->Email;

        $ohotelPhone = $this->getDataOhotel($hotel)->Phone;
        
        $ohotelsCity = $this->getDataOhotel($hotel)->City;

        $contact = array($ohotelEmail,$ohotelPhone,$ohotelsCity);


        
         $contents=array( 

            'header' => $header ,
            'totals'=>$totals,
            'contact'=>$contact,
            'logo'=> $logo,
            'sign' =>''

          );

          $body = array('information'=> $contents);


        if( !config("global.send_email_production") ){

             $recipients = config("global.support_1");

        }

       
          $subject = ($lang == 'es')?'Cancelación de Reserva':'Booking Cancellation' ;

          $template = ($lang == 'es')?'emails.booking.CancelationBookingEs':'emails.booking.CancelationBookingEn' ;


          $empresa  = config('app.name') ;

          $message   = (new Swift_Message($subject))
              ->setFrom($from,$empresa)
              ->setTo($recipients)
              ->addBcc($bccEmails)
              ->setBody(view($template, $body)->render(),'text/html');
             
        

        if($mailer->send($message)>0){

             
           ActivityLog::insertLogEmail(1,$hotel,$subject,$contents,$mail,$user,$recipients,$bccEmails);

           return $this->setRpta("ok","it was sent satisfactorily",[]);

        }else{

            ActivityLog::insertLogEmail(0,$hotel,$subject,$contents,$mail,$user,$recipients,$bccEmails);

          
          return $this->setRpta("error","could not send mail",[]);

        }





}
    
 public function sendEmailUserCredentials($request,$passwordGenerate,$hotel,$user){


  
   
    
        $logo = $this->getLogo($hotel);

        $mail = $this->mailOhotels($hotel);


        $empresa  = config('app.name') ;

        $host         = $mail['HOST'];
        $port         = $mail['PUERTO'];
        $encriptacion = $mail['ENCRIPTACION'];
        $from         = $mail['CORREO'];
        $password     = $mail['PASSWORD'];

        $recipients = $request->email;
        
        $bccEmails = Maintenance::getMailUserLogin($user);

        $transport = (new Swift_SmtpTransport($host, $port, $encriptacion))
              ->setUsername($from)
              ->setPassword($password);

        $mailer = new Swift_Mailer($transport);


        

        $header = 'Estimado(a) colaborador : '.ucfirst($request->firstName).' '.ucfirst($request->lastName) .' el presente correo es para brindarle sus nuevas credenciales para nuestro sistema de reservas .';


        $body = array($recipients,$passwordGenerate);

        $ohotelEmail = $this->getDataOhotel($hotel)->Email;

        $ohotelPhone = $this->getDataOhotel($hotel)->Phone;
        

        $contact = array($ohotelEmail,$ohotelPhone);

        $contents=array( 

            'header' => $header ,
            'body' => $body ,
            'logo'=> $logo,
            'contact' => $contact,
            'sign' =>'',
            

          );

          $body = array('information'=> $contents);


         if( !config("global.send_email_production") ){

             $recipients = config("global.support_1");

        }

       
          $subject = 'Envio de credenciales PMS' ;

          $template = 'emails.user.SendCredentials';


          

          $message   = (new Swift_Message($subject))
              ->setFrom($from,$empresa)
              ->setTo($recipients)
              ->addBcc($bccEmails)
              ->setBody(view($template, $body)->render(),'text/html');
             
        

        if($mailer->send($message)>0){

             
           ActivityLog::insertLogEmail(1,$hotel,$subject,$contents,$mail,$user,$recipients,$bccEmails);

           return $this->setRpta("ok","it was sent satisfactorily",[]);

        }else{

            ActivityLog::insertLogEmail(0,$hotel,$subject,$contents,$mail,$user,$recipients,$bccEmails);

          
          return $this->setRpta("error","could not send mail",[]);

        }



 }


  public function sendEmailBookingSuccess($reservationId,$langEmail,$hotel,$user){
        


    $header = Pms::getDetailsReservation($reservationId);

    $reservation = Pms::getDetailsReservationGroupByRoomsType($reservationId,$langEmail);

    $totalReservation = array_sum(array_column((array)$reservation, 'SumTotal'));

   
    


        $logo = $this->getLogo($hotel);

        $mail = $this->mailOhotels($hotel);
        
        
      


        $host         = $mail['HOST'];
        $port         = $mail['PUERTO'];
        $encriptacion = $mail['ENCRIPTACION'];
        $from         = $mail['CORREO'];
        $password     = $mail['PASSWORD'];

        $recipients = Pms::getEmailHolderReservation($reservationId);
        

          
        $bccEmails = Maintenance::getMailUserLogin($user);

        $transport = (new Swift_SmtpTransport($host, $port, $encriptacion))
              ->setUsername($from)
              ->setPassword($password);

        $mailer = new Swift_Mailer($transport);



        $ohotelEmail = $this->getDataOhotel($hotel)->Email;

        $ohotelPhone = $this->getDataOhotel($hotel)->Phone;
        
        $ohotelsCity = $this->getDataOhotel($hotel)->City;

        $contact = array($ohotelEmail,$ohotelPhone,$ohotelsCity);



         $contents=array( 

            'header' => $header ,
            'reservationId' => $reservationId,
            'totalReservation' => $totalReservation,
            'contact'=>$contact,
            'logo'=> $logo,
            'sign' =>'',
            'reservation' => $reservation

          );

          $body = array('information'=> $contents);


        if( !config("global.send_email_production") ){

             $recipients = config("global.support_1");

        }

       
          $subject = ($langEmail == 'es')?'Reserva Generada Exitosamente':'Booking Generated Successfully' ;

          $template = ($langEmail == 'es')?'emails.booking.SuccessBookingEs':'emails.booking.SuccessBookingEn' ;


          $empresa  = config('app.name') ;

          $message   = (new Swift_Message($subject))
              ->setFrom($from,$empresa)
              ->setTo($recipients)
              ->addBcc($bccEmails)
              ->setBody(view($template, $body)->render(),'text/html');
             
        

        if($mailer->send($message)>0){

             
           ActivityLog::insertLogEmail(1,$hotel,$subject,$contents,$mail,$user,$recipients,$bccEmails);

           return $this->setRpta("ok","it was sent satisfactorily",[]);

        }else{

            ActivityLog::insertLogEmail(0,$hotel,$subject,$contents,$mail,$user,$recipients,$bccEmails);

          
          return $this->setRpta("error","could not send mail",[]);

        }



   }



   
   
    
}