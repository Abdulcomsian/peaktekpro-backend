<html lang="en">

<head>
    <style>
        @page {
            margin: 100px 40px;
        }

        body {
            font-family: sans-serif;
            color: #333;
        }

        h2 {
            font-size: 20px;
            text-transform: none;
            margin-bottom: 15px;
            color: #333;
        }

        p {
            color: #333;
        }

        input {
            padding-inline: 10px;
            padding-block: 13px;
            color: #666;
            border: 1px solid lightgray;
            border-radius: 5px;
            background-color: #66666614;
        }

        label {
            font-size: 1rem;
            color: #666;
            margin-bottom: 5px;
            display: inline-block;
        }

        table:not(.header-image-table) {
            border-spacing: 20px;
        }

        table tr td {
            padding-block: 0.2rem;
         
        }
        .list {
            padding-left: 15px;
            display: flex;
          
        }

        .list li {
            line-height: 1.5;
        }

        header {
            position: fixed;
            top: -100px;
            left: 0px;
            right: 0px;
            height: 45px;
            font-size: 20px !important;
            color: white;
            text-align: center;
            line-height: 35px;
        }

        footer {
            position: fixed;
            bottom: -110px;
            left: 0px;
            right: 0px;
            height: 50px;
            font-size: 20px !important;
            color: white;
            text-align: center;
            line-height: 35px;
        }
        .empty-field {
            visibility: hidden; /* Space is preserved */
        }

    </style>
</head>

<body>
    <!-- Define header and footer blocks before your content -->
    <header>
        <img src="{{ public_path('assets/pdf_header.png') }}" width="800" />
    </header>

    <footer>
        <img src="{{ public_path('assets/pdf_footer.png') }}" width="800" />
    </footer>

    <!-- Wrap the content of your PDF inside a main tag -->
    <main>

    <p>I hope this message finds you well.</p>
    <p>We are pleased to inform you that you have added as a crew.</p>  
        <!-- Materials Section -->
   

    <h2 style="text-align:left">Content</h2>
    <div style="width:100%;">
        <p>{{$crewInformation->content ?? ''}}</p>
    </div>


    <p>If you have any questions or require further assistance, please do not hesitate to contact us.<p>
    <p>Thank you for your prompt attention to this matter.</p>

    <p>Order Key Fields</p>

    <p><strong>Best regards</strong>,</p>

    <p>Peaktek</p>


    </main>
</body>

</html>