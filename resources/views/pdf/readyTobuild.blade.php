<!DOCTYPE html>
<html lang="en">

<head>
    <style>
        @page {
            margin: 80px 50px;
        }

        body {
            font-family: Arial, sans-serif;
            color: #333;
            font-size: 14px;
            line-height: 1.6;
        }

        h2 {
            font-size: 18px;
            text-transform: uppercase;
            margin-bottom: 10px;
            padding-bottom: 5px;
            display: inline-block;
            position: relative;
            border-bottom: none;
            /* Remove the black line */
        }

        h2::after {
            content: "";
            display: block;
            width: 80px;
            /* Adjust length to fit around 4-5 words */
            height: 2px;
            background-color: rgb(83, 165, 231);
            /* Blue color */
            margin-top: 4px;
        }


        p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            font-size: 14px;
        }

        .section {
            margin-bottom: 30px;
        }

        header,
        footer {
            position: fixed;
            left: 0px;
            right: 0px;
            text-align: center;
            height: 50px;
        }

        header {
            top: -70px;
        }

        footer {
            bottom: -50px;
        }
    </style>
</head>

<body>
    <header>
        <img src="{{ public_path('assets/material-order.png') }}" width="100%" />
    </header>

    <footer>
        <img src="{{ public_path('assets/pdf_footer.png') }}" width="100%" />
    </footer>

    <main>
        <br>
        <div class="section">
            <h2>Customer Information</h2>
            <table>
                <tr>
                    <td><strong>Name:</strong> {{$data->name}}</td>
                    <td><strong>Email:</strong> {{$data->email}}</td>
                    <td><strong>Phone:</strong> {{$data->phone}}</td>
                </tr>
                <tr>
                    <td><strong>Street:</strong> {{$data->aggrement->street ?? ''}}</td>
                    <td><strong>City:</strong> {{$data->aggrement->city ?? ''}}</td>
                    <td><strong>State:</strong> {{$data->aggrement->state ?? ''}}</td>
                    <td><strong>Zip Code:</strong> {{$data->aggrement->zip_code ?? ''}}</td>
                </tr>
            </table>
        </div>

        <div class="section">
    <h2>Property Measurements</h2>
    <table>
        <tr>
            <td><strong>Date Needed:</strong></td>
            <td><strong>Square Count:</strong></td>
            <td><strong>Total Perimeter:</strong></td>
            <td><strong>Ridge LF:</strong></td>
        </tr>
        <tr>
            <td>{{$material_order->date_needed ?? ''}}</td>
            <td>{{$material_order->square_count ?? ''}}</td>
            <td>{{$material_order->total_perimeter ?? ''}}</td>
            <td>{{$material_order->ridge_lf ?? ''}}</td>
        </tr>
        <tr>
            <td><strong>Build Date:</strong></td>
            <td><strong>Valley SF:</strong></td>
            <td><strong>Hip and Ridge LF:</strong></td>
            <td><strong>Drip Edge LF:</strong></td>
        </tr>
        <tr>
            <td>{{$material_order->build_date ?? ''}}</td>
            <td>{{$material_order->valley_sf ?? ''}}</td>
            <td>{{$material_order->hip_and_ridge_lf ?? ''}}</td>
            <td>{{$material_order->drip_edge_lf ?? ''}}</td>
        </tr>
    </table>
</div>


        <div class="section">
            <h2>Materials</h2>
            <table>
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Quantity</th>
                        <th>Color</th>
                        <th>Order Key</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($material_order->materials ?? [] as $material)
                    <tr>
                        <td>{{ $material->material }}</td>
                        <td>{{ $material->quantity }}</td>
                        <td>{{ $material->color }}</td>
                        <td>{{ $material->order_key }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Notes</h2>
            <p>{{ html_entity_decode(strip_tags($readybuild->notes)) }}</p>

        </div>
    </main>
</body>

</html>