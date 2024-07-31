<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Order</title>
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
    <h1>Dear <strong>{{$user->name}}</strong></h1>

    <p>I hope this message finds you well.</p>
    <p>We are pleased to inform you that your requested material order PDF is now ready for your review.</p>

    <h2>Material Order Details:</h2>
    <ul>
        <li><strong>City: </strong>{{$material_order->city}}</li>
        <li><strong>State: </strong>{{$material_order->state}}</li>
        <li><strong>Build Date: </strong>{{$material_order->build_date}}</li>
        <li><strong>Policy Number: </strong>{{$material_order->policy_number}}</li>
        <li><strong>Claim Number: </strong>{{$material_order->claim_number}}</li>
    </ul>

    <p>Please click the link below to view the PDF for furthur details:</p>

    <center><a href="{{ url('') . $material_order->sign_pdf_url }}" target="_blank" class="btn">View Material Order PDF</a></center>
    <br>

    <p>If you have any questions or require further assistance, please do not hesitate to contact us.<p>
    <p>Thank you for your prompt attention to this matter.</p>

    <p><strong>Best regards</strong>,</p>

    <p>DBQP</p>
    
</body>
</html>