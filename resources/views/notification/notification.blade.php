<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Louer Meublée</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: rgb(221, 221, 228);
            margin: 0;
        }

        .container {
            width: 90%; /* Adaptable width */
            max-width: 650px; /* Limits maximum width */
            height: auto; /* Adjusts based on content */
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .logo {
            width: 150px;
            height: 150px;
            margin-bottom: 10px;
        }

        .separator {
            border-top: 1px solid #ff7900;
            margin: 10px 0;
        }

        .email-title {
            color: #1e272e;
            font-size: 1.5em; /* Relative font size */
            margin-bottom: 10px;
        }

        .email-body {
            font-size: 2em;
            font-weight: bold;
            color: #ff7900;
            margin: 20px 0;
            position: relative;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .email-body span {
            position: absolute;
            top: 26px;
            left: 67px;
        }

        .footer {
            color: #ff7900;
            font-weight: bold;
            font-size: 1.5em;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <img class="logo" src="{{ asset('image/logo/logo.png') }}" alt="Logo Louer Meublée">
        <hr class="separator">
        <div class="email-title">{{ $mail['title'] }}</div>
        <div class="email-body"><span>{{ $mail['body'] }}</span></div>
        <div class="footer"></div>
    </div>
</body>
</html>
