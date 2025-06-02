<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rest Password Email</title>
    <style>
        /* Define your CSS styles here */
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- <h1> Title:- <strong>User Creation</strong>,</h1> -->

         <p>We are excited to welcome you to PeakTek!<p>
        <p>This email is to inform you that your account have been created.</p>
        <p>Please use the below credentials to Login:</p>
        <center>
                <ul>
                    <li><strong>Email:</strong>{{$email}}</li>
                    <li><strong>Password:</strong>{{$password}}</li>
                </ul>
        </center>

        <p>To get started, please log in to your account by visiting <a target="_blank" href="{{ env('FRONTEND_URL') }}">PeakTek</a>.<p>
        
        <p><strong>Best regards</strong>,</p>

        <p>PeakTek</p>
    </div>
</body>
</html>
