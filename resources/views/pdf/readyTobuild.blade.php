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
                            style="color:#333; min-height:20px; word-wrap:break-word;" type="text" value="{{$data->name}}" />
                    </td>
                    <td>
                        <p style="margin: 0;">Email</p>
                        <input
                            style="color:#333; min-height:20px; word-wrap:break-word;"
                            type="text"
                            value="{{$data->email}}" />
                    </td>
                    <td colspan="2">
                        <p style="margin: 0;">Phone</p>
                        <input
                            style="color:#333; min-height:20px; word-wrap:break-word;"
                            type="text"
                            value="{{$data->phone}}" />
                    </td>
                </tr>
                <tr>
                    <td style="padding-right: 20px;">
                        <p style="margin: 0;">Street</p>
                        <input style="color:#333; width:100px; margin-right:30px; min-height:20px; word-wrap:break-word;" type="text" value="{{$data->aggrement->street ?? ''}}" />
                    </td>
                    <td style="padding-right: 20px;">
                        <p style="margin: 0;">City</p>
                        <input style="color:#333; width:100px; margin-right:30px; min-height:20px; word-wrap:break-word;" type="text" value="{{$data->aggrement->city ?? ''}}" />
                    </td>
                    <td style="padding-right: 20px;">
                        <p style="margin: 0;">State</p>
                        <input style="color:#333; width:100px; margin-right:30px; min-height:20px; word-wrap:break-word;" type="text" value="{{$data->aggrement->state ?? ''}}" />
                    </td>
                    <td>
                        <p style="margin: 0;">Zip Code</p>
                        <input style="color:#333; width:100px; min-height: 20px; word-wrap: break-word;" type="text" value="{{$data->aggrement->zip_code ?? ''}}" />
                    </td>
                </tr>

                <td>
                    <p style="margin: 0;">Insurance Number:</p>
                    <input
                        style="color:#333; min-height:20px; word-wrap:break-word;"
                        type="text"
                        value="{{$data->aggrement->insurance ?? ''}}" />
                </td>
                <td>
                    <p style="margin: 0;">Claim Number:</p>
                    <input
                        style="color:#333; min-height:20px; word-wrap:break-word;"
                        type="text"
                        value="{{$data->aggrement->claim_number}}" />
                </td>
                <td colspan="2">
                    <p style="margin: 0;">Policy Number:</p>
                    <input
                        style="color:#333; min-height:20px; word-wrap:break-word;"
                        type="text"
                        value="{{$data->aggrement->policy_number}}" />
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
        <p>{{$readybuild->notes}}</p>
    </div>

   

    <h2 style="text-align:left">Attachments</h2>
<div style="width:100%;">
    @foreach ($readybuild->documents as $document)
        <p>
            <a href="{{ asset($document->image_url) }}" target="_blank">
                {{ asset($document->image_url) }}
                <!-- {{public_path('/storage/', '', $document->image_url)}} -->
            </a>
        </p>
    @endforeach
</div>







      


        <!-- <div style="margin-bottom: 100px;">
            <table style="width: 100%; max-width: 1200px; margin: auto;">
                <tbody>
                    <tr>
                        <td>
                            <h2>SIGNATURES</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding: 15px; text-align: left; width: 40%;">
                            <p>Customer Signature:</p>
                            <div
                                style="
                    width: 250px;
                    border: 1px solid black;
                    padding: 10px;
                    text-align: center;
                    margin-top: 5px;
                    color:#333;
                    ">
                                <img
                                    src="{{ public_path($data->customer_signature) }}"
                                    style="width: 100%; max-width: 230px; height: auto;"
                                    alt="Customer Signature" />
                            </div>
                        </td>
                        <td style="padding: 15px; text-align: left; width: 30%;">
                            <p>Printed Name:</p>
                            <input
                                style="
                    width: 100%;
                    padding: 5px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                    margin-top: 5px;
                    color:#333;
                    "
                                type="text"
                                value="{{$data->customer_printed_name}}" />
                        </td>
                        <td style="padding: 15px; text-align: left; width: 30%;">
                            <p>Date Signed:</p>
                            <input
                                style="
                    width: 100%;
                    padding: 5px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                    margin-top: 5px;
                    color:#333;
                    "
                                type="text"
                                value="{{$data->customer_date}}" />
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding: 15px; text-align: left; width: 40%;">
                            <p>Company Representative Signature:</p>
                            <div
                                style="
                                        width: 250px;
                                        border: 1px solid black;
                                        padding: 10px;
                                        text-align: center;
                                        margin-top: 5px;
                                        ">
                                <img
                                    src="{{ public_path($data->company_signature) }}"
                                    style="width: 100%; max-width: 230px; height: auto;"
                                    alt="Company Signature" />
                            </div>
                        </td>
                        <td style="padding: 15px; text-align: left; width: 30%;">
                            <p>Printed Name:</p>
                            <input
                                style="
                    width: 100%;
                    padding: 5px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                    margin-top: 5px;
                    color:#333
                    "
                                type="text"
                                value="{{$data->company_printed_name}}" />
                        </td>
                        <td style="padding: 15px; text-align: left; width: 30%;">
                            <p>Date Signed:</p>
                            <input
                                style="
                    width: 100%;
                    padding: 5px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                    margin-top: 5px;
                    color:#333;
                    "
                                type="text"
                                value="{{$data->company_date}}" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div> -->
       
        

<!-- show the cancellation section at the end of page -->
       

    <!-- Replace the existing cancellation section with this -->
    <!-- <div style="position: fixed; bottom: 0; left: 0; right: 0; padding: 10px;">
        <div>
            I 
            <span style="border: .1rem solid gray; border-top: none; border-left: none; border-right: none; color: {{ !empty($data->customer_name) ? 'black' : 'transparent' }}">
                {{ !empty($data->customer_name) ? $data->customer_name : '......................................'}}
            </span>, the undersigned, hereby cancel this transaction as of 
            <span style="border: .1rem solid gray; border-top: none; border-left: none; border-right: none; display: inline-block; color: {{ !empty($data->agreement_date) ? 'black' : 'transparent' }}">
                {{ !empty($data->agreement_date) 
                    ? explode('/', $data->agreement_date)[0] . '/' . explode('/', $data->agreement_date)[1] . '/' . explode('/', $data->agreement_date)[2] 
                    : '...... / ...... / ............' }}
            </span>
        </div>

        <div style="margin-top : 1rem;">
            Customer Signature:
            <span style="border: .1rem solid gray;border-top: none;border-left:none;border-right:none; color:transparent">
                ............................................................
            </span>
        </div>
    </div> -->


    </main>
</body>

</html>