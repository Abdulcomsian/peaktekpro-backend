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
                /* text-transform: uppercase; */
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
                /* page-break-inside: avoid; */

                /* border: 5px solid #000; */
             } 
             /* tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            td {
                page-break-inside: avoid;
            } */
            /* .page-break {
                page-break-before: always;
            } */
            .list {
                padding-left: 15px;
                display: flex;
                /* flex-direction: column;
                gap: 15px; */
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
                /* background-color: #000; */
                color: white;
                text-align: center;
                line-height: 35px;
                /* border: 2px solid green; */
            }

            footer {
                position: fixed; 
                bottom: -110px; 
                left: 0px; 
                right: 0px;
                height: 50px; 
                font-size: 20px !important;
                /* background-color: #000; */
                color: white;
                text-align: center;
                line-height: 35px;
                /* border: 2px solid green; */
            }
        </style>
    </head>
    <body>
        <!-- Define header and footer blocks before your content -->
        <header>
                <img src="{{(public_path('assets/pdf_header.PNG'))}}" width="800"/>
        </header>

        <footer>
                <img src="{{(public_path('assets/pdf_footer.PNG'))}}" width="800"/>
        </footer>

        <!-- Wrap the content of your PDF inside a main tag -->
        <main>
            <!-- Customer Information table -->
            <table style="width: 100%;">
                <tbody>
                    <tr>
                    <th colspan="6">
                        <h2 style="text-align: left">CUSTOMER INFORMATION</h2>
                    </th>
                    </tr>

                    <!-- <tr style="text-align: left;">
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                    </tr>
                    <tr style="text-align: left;">
                        <td>my name</td>
                        <td>email@example.com</td>
                        <td>+9229292929292</td>
                    </tr> -->
            
                    <tr>
                    <td>
                        <p>Name</p>
                        <input 
                        style="color:#333" type="text" value="{{$data->job->name}}"/>
                    </td>
                    <td>
                        <p>Email</p>
                        <input
                        style="color:#333"
                        type="text"
                        value="{{$data->job->email}}"
                        />
                    </td>
                    <td colspan="2">
                        <p>Phone</p>
                        <input
                        style="color:#333"
                        type="text"
                        value="{{$data->job->phone}}"
                        />
                    </td>
                    </tr>
                    <tr>
                        <td style="padding-right: 20px;">
                            <p>Street</p>
                            <input style="color:#333; width:100%; margin-right:30px;" type="text" value="{{$data->street}}" />
                        </td>
                        <td style="padding-right: 20px;">
                            <p>City</p>
                            <input style="color:#333; width:100%; margin-right:30px;" type="text" value="{{$data->city}}" />
                        </td>
                        <td style="padding-right: 20px;">
                            <p>State</p>
                            <input style="color:#333; width:100%; margin-right:30px;" type="text" value="{{$data->state}}" />
                        </td>
                        <td>
                            <p>Zip Code</p>
                            <input style="color:#333; width:100%;" type="text" value="{{$data->zip_code}}" />
                        </td>
                    </tr>

                    <td>
                        <p>Insurance Number:</p>
                        <input
                        style="color:#333;"
                        type="text"
                        value="{{$data->insurance}}"
                        />
                    </td>
                    <td>
                        <p>Claim Number:</p>
                        <input
                        style="color:#333;"
                        type="text"
                        value="{{$data->claim_number}}"
                        />
                    </td>
                    <td colspan="2">
                        <p>Policy Number:</p>
                        <input
                        style="color:#333;"
                        type="text"
                        value="{{$data->policy_number}}"
                        />
                    </td>
                    </tr>
                    
                </tbody>
            </table>

            <section class="content">
            {!! $content->content !!}
            </section>

            <!-- <table style="width: 100%;">
                <tbody>
                    <tr>
                        <td>
                            
                        </td>
                    </tr>
                </tbody>
            </table> -->

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
                "
            >
                <img
                src="{{ public_path($data->customer_signature) }}"
                style="width: 100%; max-width: 230px; height: auto;"
                alt="Customer Signature"
                />
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
                value="{{$data->customer_printed_name}}"
            />
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
                value="{{$data->customer_date}}"
            />
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
                "
            >
                <img
                src="{{ public_path($data->company_signature) }}"
                style="width: 100%; max-width: 230px; height: auto;"
                alt="Company Signature"
                />
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
                value="{{$data->company_printed_name}}"
            />
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
                value="{{$data->company_date}}"
            />
            </td>
        </tr>
        </tbody>
    </table>

    <div style="display: flex; padding: 10px; align-items: center;">
        <div>
            I <span style="border: .1rem solid gray;border-top: none;border-left:none;border-right:none;color:transparent">......................................</span>,the undersigned,hereby cancel this transaction as of <span style="border: .1rem solid gray;border-top: none;border-left:none;border-right:none;color:transparent">.......</span>/<span style="border: .1rem solid gray;border-top: none;border-left:none;border-right:none;color:transparent">.......</span>/<span style="border: .1rem solid gray;border-top: none;border-left:none;border-right:none;color:transparent">............</span>
        </div>
        <div style="margin-top : 1rem;">
        Customer Signature:<span style="border: .1rem solid gray;border-top: none;border-left:none;border-right:none;color:transparent">......................................................................</span>
        </div>
    <!-- Name Field -->
       
    <!-- <table style="width: 100%; max-width: 1200px; margin: auto;">
      <tbody>
        <tr>
          <td>
            <div style="display: flex; padding: 10px; align-items: center;">
              <label for="" style="color: #333;">I</label>
              <input
                style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; color: #333; margin-left: 5px;"
                type="text"
                value="{{$data->customer_name}}"
              />
              <label for="" style="color: #333; margin-left: 30px;">the undersigned, hereby cancel this transaction as of <strong>Date</strong>:</label>
              <input
                style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; color: #333; margin-left: 30px;"
                type="text"
                value="{{$data->agreement_date}}"
              />
              <label for="" style="color: #333; margin-left: 30px;">Customer Signature</label>
              <input
                style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; color: #333; margin-left: 30px;"
                type="text"
                value=""
              />

            </div>
          </td>
        </tr>
     
      </tbody>
    </table> -->

        </main>
    </body>
</html>