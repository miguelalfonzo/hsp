<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>{{ config('app.name') }} - PMS </title>
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
.Narrow{ font-family: 'Arial narrow', sans-serif;}
.spacing{letter-spacing: 0.1em;}
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
          <table  align="left" border="0" cellpadding="0" cellspacing="0" style="width: 100%">  
            <tr>
              <td height="70" style="padding: 0 20px 20px 0;">

                 <img src="{{$information['logo']}}" alt="" class="">

                
              </td>
              <td class="headerfamily" height="70" style="padding: 0 20px 20px 0;">

                <h5 align="right">SISTEMA PMS - {{ config('app.name') }}</h5>

                
              </td>
            </tr>

           
          </table>
          <table  align="left" border="0" cellpadding="0" cellspacing="0" style="width: 100%">  
            <tr>
              
              <td class="Narrow " height="30" style="padding: 0 10px 10px 0;">

              <p class="font-14">{{$information['header']}}</p>

    
                
                 <p class="font-14"><strong>Usuario : </strong>{{$information['body'][0]}}</p>
                 <p class="font-14"><strong>Contraseña </strong>: {{$information['body'][1]}}</p>

                  
                  <p class="font-14">Para poder acceder a la plataforma ingresa desde <a href="{{config('global.url_production')}}" target="_blank">aqui</a> , si presentas problemas para poder ingresar comunicarse al <strong>{{$information['contact'][1]}} </strong> o mandanos un mensaje a <strong>{{$information['contact'][0]}}</strong>.</p>

              
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