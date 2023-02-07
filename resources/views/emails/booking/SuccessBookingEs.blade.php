<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>{{ config('app.name') }} - Reserva </title>
  <style type="text/css">
  body {margin: 0; padding: 0; min-width: 100%!important;}
  img {height: auto;}
  .content {width: 100%; max-width: 600px;}
  .header {padding: 40px 30px 20px 30px;}
  .innerpadding {padding: 30px 30px 30px 30px;}
  .borderbottom {border-bottom: 1px solid #f2eeed;}
  .subhead {font-size: 15px; color: #ffffff; font-family: sans-serif; letter-spacing: 10px;}
  .h1, .h2, .bodycopy {color: #153643; font-family: sans-serif;}
  .h1 {font-size: 33px; line-height: 38px; font-weight: bold;}
  .h2 {padding: 0 0 15px 0; font-size: 24px; line-height: 28px; font-weight: bold;}
  .bodycopy {font-size: 12px; line-height: 22px;}
  .button {text-align: center; font-size: 18px; font-family: sans-serif; font-weight: bold; padding: 0 30px 0 30px;}
  .button a {color: #ffffff; text-decoration: none;}
  .footer {padding: 20px 30px 15px 30px;}
  .footercopy {font-family: sans-serif; font-size: 14px; color: #ffffff;}
  .footercopy a {color: #ffffff; text-decoration: underline;}
.headerfamily {font-family: Arial, sans-serif; }
.font-12{font-size: 12px}
.font-13{font-size: :13px}
.font-10{font-size: :10px}

.Narrow{ font-family: 'Arial narrow', sans-serif;}
.spacing{letter-spacing: 0.1em;}

.logo-padding{padding: 0 20px 20px 0;}
.w-100{width: 100%;}
.p-10{padding: 10px;}
.p-7{padding: 7px;}
.p-5{padding: 5px;}
.booking-details{width: 100%; border-collapse: collapse;border-color: #DDDDDD}
.color-h5{color:#00662E};
.booking-data{width: 100%; border-collapse: collapse;border:1px solid #A5A4A4}
.d-none{display: none}

  @media only screen and (max-width: 550px), screen and (max-device-width: 550px) {
  body[yahoo] .hide {display: none!important;}
  body[yahoo] .buttonwrapper {background-color: transparent!important;}
  body[yahoo] .button {padding: 0px!important;}
  body[yahoo] .button a {background-color: #e05443; padding: 15px 15px 13px!important;}
  body[yahoo] .unsubscribe {display: block; margin-top: 20px; padding: 10px 50px; background: #2f3942; border-radius: 5px; text-decoration: none!important; font-weight: bold;}
  }

  /*@media only screen and (min-device-width: 601px) {
    .content {width: 600px !important;}
    .col425 {width: 425px!important;}
    .col380 {width: 380px!important;}
    }*/

  </style>
</head>

<body yahoo bgcolor="#f6f8f1">
<table width="100%" bgcolor="#f6f8f1" border="0" cellpadding="0" cellspacing="0">
<tr>
  <td>
    <!--[if (gte mso 9)|(IE)]>
      <table width="600" align="center" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td>
    <![endif]-->     
    <table bgcolor="#ffffff" class="content" align="center" cellpadding="0" cellspacing="0" border="0">
      <tr>
        <td bgcolor="#ffffff" class="header">
          <table  class="w-100" align="left" border="0" cellpadding="0" cellspacing="0" >  
            <tr>
              <td class="logo-padding" height="70">

                 <img src="{{$information['logo']}}" alt="" class="">

                
              </td>
              <td class="headerfamily logo-padding" height="70">

                <h5 align="right">RESERVA - #{{$information['reservationId']}}</h5>

                
              </td>
            </tr>

           
          </table>
          <table  class="w-100" align="left" border="0" cellpadding="0" cellspacing="0" >  
            <tr>
              
              <td class="Narrow logo-padding" height="30" >

              <p class="font-14">Hay una nueva reserva generada , consulte a continuación la información de la misma</p>

              <h5 class="spacing color-h5" >Datos de la Reserva</h5>
                
                <table class="font-12 Narrow booking-data">
                  <tr class="font-10">
                    <td class="p-10">INGRESO</td>
                    <td>SALIDA</td>
                    <td>NOCHES</td>
                    <td>HABITACIONES</td>
                    <td>TOTAL</td>
                  </tr>
                  <tr class="">
                    <td class="p-10">{{$information['header'][0]->CheckIn}}</td>
                    <td>{{$information['header'][0]->CheckOut}}</td>
                    <td>{{$information['header'][0]->LengthDays}}</td>
                    <td>{{count($information['header'])}}</td>
                    <td><strong>$ {{number_format($information['totalReservation'],2)}}</strong></td>
                  </tr>
                </table>


                 <h5 class="spacing color-h5">Titular de la Reserva</h5>
                
                <table class="font-12 Narrow booking-data" >
                  <tr class="font-10">
                    <td class="p-10">NOMBRE</td>
                    <td>PAIS</td>
                    <td>EMAIL</td>
                    <td>CELULAR</td>
                    
                  </tr>
                  <tr class="">
                    <td class="p-10">{{ucfirst($information['header'][0]->Guest)}}</td>
                    <td>{{$information['header'][0]->Country}}</td>
                    <td>{{$information['header'][0]->Email}}</td>
                    <td>{{$information['header'][0]->Phone}}</td>
                    
                  </tr>
                </table>

                <br>
                

                <table class="font-12 Narrow booking-details" border=1 >
                  <tr class="font-10">
                    <th class="p-10" align="left">Descripción</th>
                    <th class="p-10" align="left">Cantidad</th>
                    <th class="p-10" align="left">Precio Unitario</th>
                    <th class="p-10" align="left">Descuento</th>
                    

                    <th class="p-10" align="left">SubTotal</th>


                    <th class="p-10 d-none" align="left">Igv</th>
                    <th class="p-10 d-none" align="left">Total</th>
                    
                  </tr>

                  <?php

                    $subtotal = 0;
                    $igv = 0;
                    $total = 0;

                  ?>

                    @foreach($information['reservation'] as $list)


                    <tr class="">
                    <td class="p-5">Fecha de CheckIn : {{$information['header'][0]->CheckIn}}<br>Fecha de Checkout : {{$information['header'][0]->CheckOut}} <br>Adultos : {{$information['header'][0]->Adults}} <br>Niños : {{$information['header'][0]->Kids}} <br>  {{$list->RoomType}} <br>{{$list->Description}}</td>
                    <td class="p-5">{{$list->CountType}}</td>
                    <td class="p-5">$ {{number_format($list->PriceUnitary,2)}}</td>
                    <td class="p-5">$ {{number_format($list->SumDiscount,2)}}</td>
                    <td class="p-5">$ {{number_format($list->SumSubTotal,2)}}</td>
                    
                    <td class="p-5 d-none">$ {{$list->SumIgvVal}}</td>
                    <td class="p-5 d-none">$ {{$list->SumTotal}}</td>


                  </tr>

                  <?php 

                    $subtotal += $list->SumSubTotal;
                    $igv += $list->SumIgvVal;
                    $total += $list->SumTotal;

                   ?>
                    
                @endforeach


                  

                  <tr class="">
                    
                    <td class="p-5"  colspan="5" align="right"><br>SubTotal  &nbsp;&nbsp; $ &nbsp;{{number_format($subtotal,2)}}<br>Impuestos ({{intval($information['header'][0]->ValueIgv)}} %) &nbsp;&nbsp; $ &nbsp; {{number_format($igv,2)}}<br>Total  &nbsp;&nbsp; $ &nbsp; {{number_format($total,2)}}</td>
                   
                    
                  </tr>
                </table>

                <p>Si necesitas hacer cambios o requieres asistencia por favor llama {{$information['contact'][1]}} o mándanos un correo a {{$information['contact'][0]}}</p>

                <p>¡Tenemos muchas ganas de recibirte en {{$information['contact'][2]}} pronto!</p>
              </td>
            </tr>

           
          </table>
         
        </td>
      </tr>
     

      
     
      

       <tr>
        <td class="innerpadding bodycopy">

          <img src="{{$information['sign']}}" alt="" class=""><br>
          <strong>Atentamente</strong><br>
          <strong>Equipo {{ config('app.name') }}</strong>
          
        </td>
      </tr>

      <tr>
        <td class="footer" bgcolor="#38664C">
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td align="center" class="footercopy">
                &reg; Todos los derechos reservados.<br/>
                <a href="#" class="unsubscribe">
              </td>
            </tr>
           
          </table>
        </td>
      </tr>
    </table>
    <!--[if (gte mso 9)|(IE)]>
          </td>
        </tr>
    </table>
    <![endif]-->
    </td>
  </tr>
</table>
</body>
</html>