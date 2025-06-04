<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No Title</title>
</head>
<body>
<style>
    body {
        height: 100vh;
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }
</style>
    <h1>Dear <strong>{{$user->name}}</strong></h1>

    <p>We are excited to welcome you to PeakTek!<p>
    <p>This email is to inform you that you have been assigned the role of {{$user->role->name}} within our application.</p>
    <p><strong>Login Information:<strong></p>

    <ul>
        <li><strong>Email:</strong>{{$user->email}}</li>
        <li><strong>Password:</strong>{{$password}}</li>
    </ul>

    <p>To get started, please log in to your account by visiting <a target="_blank" href="{{ env('FRONTEND_URL') }}">PeakTek</a>.<p>

    <p><strong>Best regards</strong>,</p>

    <p>Peaktek</p>
    
</body>
</html>