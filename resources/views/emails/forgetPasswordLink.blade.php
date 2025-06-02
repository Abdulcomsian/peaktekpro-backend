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
        <h1>Dear <strong>{{ $user->name }}</strong>,</h1>

        <p>Please click on the Link to update Your Password is:</p>
        <center>
        <a href="{{ 'https://peaktekcrm.com/reset-password?token=' . $token . '&email=' . $user->email }}" target="_blank" class="btn">
            Change Password
        </a>

         <!-- <a href="{{ url('reset-password/'.$token.'?email='.$user->email) }}" target="_blank" class="btn">
            Change Password
        </a> -->
        </center>

        <p>Please use this Link to complete your action.</p>
        
        <p>If you have any questions or require further assistance, please do not hesitate to contact us.</p>

        <p>Thank you for your prompt attention to this matter.</p>

        <p><strong>Best regards</strong>,</p>

        <p>PeakTek</p>
    </div>
</body>
</html>
