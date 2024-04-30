<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title style="font-weight: bold; font-size: 22px; color: #ff7900;  margin-left: 600px;"> Louer meublée </title>

        <style>
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background: rgb(221, 221, 228);
                margin: 5px;
            }
            hr {
                margin-top: -10px;
                z-index: 5;
            }
            .before {
                font-size: 18px;
                justify-content: center;
                align-content: center;
            }
            .body {
                width: 190px;
                height: 80px;
                position: relative;
                font-weight: bold;
                font-size: 32px;
                color:  #ff7900;
            }
            .span {
                margin-top: 26px;
                margin-left: 67px;
                position: absolute;
            }
            

        </style>
    </head>
    <body>

        <div style="width: 650px; height: 450px; margin-left: 100px; background: #fff">
                <div> 
                    <center> <img style="width: 150px; height: 150px; " src="{{ $message->embed(public_path('image/logo/logo.jpg')) }}"> </img> </center> 
                    <hr style="margin-top: -15px;">
                </div>
                <div style="margin-top: -20px; color: #1e272e; font-size: 24px;">  <center> <p> {{ $mail ['title'] }} </p>  </center></div> 
              <center>  <div class="body"> <div class="span"> {{$mail ['body'] }} </div> </div> </center> 
               <center> <p class="margin: 3px" style="font-size: 20px; color: #1e272e;"> Soyez prudent <span style="color: red"> NE PARTAGEZ PAS CE CODE </span>. </center>  
                </p>
                <p style="font-weight: bold; font-size: 22px; color: #ff7900;  margin-left: 490px;"> Louer meublée </p>
        </div> 

    </body>
</html>