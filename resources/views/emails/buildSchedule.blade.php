<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>OTP Email</title>
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
        <strong>{{ $build_detail->subject }}</strong>

        <h2 style="text-align: center; font-size: 2em;"></h2>
        <p>Supplier Name: {{$supplier->name}}</p>
        <p> {{$build_detail->content}}</p>
        <p>If you have any questions or require further assistance, please do not hesitate to contact us.</p>

        <p>Thank you for your attention to this matter.</p>

        <p><strong>Best regards</strong>,</p>

        <p>PeakTek</p>
    </div>
</body>
</html>
