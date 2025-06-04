<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature Email</title>
</head>
<body>
<style>
    body {
        height: 100vh;
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }

    .btn {
        display: inline-block;
        padding: 15px 30px;
        font-size: 16px;
        color: #fff;
        background-color: #007bff;
        text-decoration: none;
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }

    .btn:hover {
        background-color: #0056b3;
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.1);
    }

    .btn:active {
        background-color: #004080;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
</style>
    <h1>Dear <strong>{{$companyjob->name}}</strong></h1>

    <p>I hope this email finds you well.<p>
    <p>We are reaching out to inform you that we require your signature on the document of Customer Agreement. Please review and sign the document at your earliest convenience by clicking on the link below:</p>

    <center><a href="{{\Illuminate\Support\Facades\Crypt::decryptString($encrypted_url)}}" target="_blank" class="btn">Visit Link</a></center>

    <p>If you have any questions or require further assistance, please do not hesitate to contact us.<p>
    <p>Thank you for your prompt attention to this matter.</p>

    <p><strong>Best regards</strong>,</p>

    <p>Peaktek</p>
    
</body>
</html>