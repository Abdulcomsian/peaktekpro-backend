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
    <p>We are pleased to inform you that your requested material order PDF is now ready for your review.</p>

        <!-- Customer Information table -->
        <table style="width: 100%;">
            <tbody>
                <tr>
                    <th colspan="6">
                        <h2 style="text-align: left">Material Order</h2>
                    </th>
                </tr>

                <tr>
                    <td>
                        <p style="margin: 0;">Name</p>
                        <input
                            style="color:#333; min-height:20px; word-wrap:break-word;" type="text" value="{{$agreement->name ?? ''}}" />
                    </td>
                    <td>
                        <p style="margin: 0;">Email</p>
                        <input
                            style="color:#333; min-height:20px; word-wrap:break-word;"
                            type="text"
                            value="{{$agreement->email ?? ''}}" />
                    </td>
                    <td colspan="2">
                        <p style="margin: 0;">Phone</p>
                        <input
                            style="color:#333; min-height:20px; word-wrap:break-word;"
                            type="text"
                            value="{{$agreement->phone ?? ''}}" />
                    </td>
                </tr>
                <tr>
                    <td style="padding-right: 20px;">
                        <p style="margin: 0;">Street</p>
                        <input style="color:#333; width:100px; margin-right:30px; min-height:20px; word-wrap:break-word;" type="text" value="{{$agrement->aggrement->street ?? ''}}" />
                    </td>
                    <td style="padding-right: 20px;">
                        <p style="margin: 0;">City</p>
                        <input style="color:#333; width:100px; margin-right:30px; min-height:20px; word-wrap:break-word;" type="text" value="{{$agrement->aggrement->city ?? ''}}" />
                    </td>
                    <td style="padding-right: 20px;">
                        <p style="margin: 0;">State</p>
                        <input style="color:#333; width:100px; margin-right:30px; min-height:20px; word-wrap:break-word;" type="text" value="{{$agrement->aggrement->state ?? ''}}" />
                    </td>
                    <td>
                        <p style="margin: 0;">Zip Code</p>
                        <input style="color:#333; width:100px; min-height: 20px; word-wrap: break-word;" type="text" value="{{$agrement->aggrement->zip_code ?? ''}}" />
                    </td>
                </tr>

                <td>
                    <p style="margin: 0;">Insurance Number:</p>
                    <input
                        style="color:#333; min-height:20px; word-wrap:break-word;"
                        type="text"
                        value="{{$agrement->aggrement->insurance ?? ''}}" />
                </td>
                <td>
                    <p style="margin: 0;">Claim Number:</p>
                    <input
                        style="color:#333; min-height:20px; word-wrap:break-word;"
                        type="text"
                        value="{{$agrement->aggrement->claim_number ?? ''}}" />
                </td>
                <td colspan="2">
                    <p style="margin: 0;">Policy Number:</p>
                    <input
                        style="color:#333; min-height:20px; word-wrap:break-word;"
                        type="text"
                        value="{{$agrement->aggrement->policy_number?? ''}}" />
                </td>
                

            </tbody>
        </table>
      

        <table style="width: 100%;">
            <tbody>
                <tr>
                    <th colspan="6">
                        <h2 style="text-align: left">Property Measurements</h2>
                    </th>
                </tr>

                <tr>
                    <td>
                        <p style="margin: 0;">Date Needed</p>
                        <input
                            style="color:#333; min-height:20px; word-wrap:break-word;" type="text" value="{{$material_order->date_needed ?? ''}}" />
                    </td>
                    <td>
                        <p style="margin: 0;">Square Count</p>
                        <input
                            style="color:#333; min-height:20px; word-wrap:break-word;"
                            type="text"
                            value="{{$material_order->square_count ?? ''}}" />
                    </td>
                    <td>
                        <p style="margin: 0;">Total Perimeter</p>
                        <input
                            style="color:#333; min-height:20px; word-wrap:break-word;"
                            type="text"
                            value="{{$material_order->total_perimeter ?? ''}}" />
                    </td>
                    <td >
                        <p style="margin: 0;">Ridge LF</p>
                        <input
                            style="color:#333; min-height:20px; word-wrap:break-word;"
                            type="text"
                            value="{{$material_order->ridge_lf ?? ''}}" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <p style="margin: 0;">Build Date</p>
                        <input
                            style="color:#333; min-height:20px; word-wrap:break-word;" type="text" value="{{$material_order->build_date ?? ''}}" />
                    </td>
                    <td>
                        <p style="margin: 0;">Valley SF</p>
                        <input
                            style="color:#333; min-height:20px; word-wrap:break-word;"
                            type="text"
                            value="{{$material_order->valley_sf ?? ''}}" />
                    </td>
                    <td>
                        <p style="margin: 0;">Hip and Ridge LF</p>
                        <input
                            style="color:#333; min-height:20px; word-wrap:break-word;"
                            type="text"
                            value="{{$material_order->hip_and_ridge_lf ?? ''}}" />
                    </td>
                    <td >
                        <p style="margin: 0;">Drip Edge LF</p>
                        <input
                            style="color:#333; min-height:20px; word-wrap:break-word;"
                            type="text"
                            value="{{$material_order->drip_edge_lf ?? ''}}" />
                    </td>
                </tr>
            </tbody>
        </table>
       
        <!-- Materials Section -->
    <h2 style="text-align: left">Material</h2>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="border: 1px solid black; padding: 5px;">Material</th>
                <th style="border: 1px solid black; padding: 5px;">Quantity</th>
                <th style="border: 1px solid black; padding: 5px;">Color</th>
                <th style="border: 1px solid black; padding: 5px;">Order Key</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($material_order->materials ?? [] as $material)
                <tr>
                    <td style="border: 1px solid black; padding: 5px;">{{ $material->material }}</td>
                    <td style="border: 1px solid black; padding: 5px;">{{ $material->quantity }}</td>
                    <td style="border: 1px solid black; padding: 5px;">{{ $material->color }}</td>
                    <td style="border: 1px solid black; padding: 5px;">{{ $material->order_key }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2 style="text-align:left">Notes</h2>
    <div style="width:100%;">
        <p>{{$ready_to_build->notes ?? ''}}</p>
    </div>

   

    <h2 style="text-align:left">Attachments</h2>
    <div style="width:100%;">
        @foreach ($ready_to_build->documents as $document)
            <p>
                <a href="{{ asset($document->image_url) }}" target="_blank">
                    {{ asset($document->image_url) }}
                    <!-- {{public_path('/storage/', '', $document->image_url)}} -->
                </a>
            </p>
        @endforeach
    </div>

    <p>If you have any questions or require further assistance, please do not hesitate to contact us.<p>
    <p>Thank you for your prompt attention to this matter.</p>

    <p>Order Key Fields</p>

    <p><strong>Best regards</strong>,</p>

    <p>Peaktek</p>


    </main>
</body>

</html>