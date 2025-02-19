<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
    <style>
      * {
        padding: 0;
        margin: 0;
        box-sizing: border-box;
      }
      body {
        font-family: sans-serif;
        color: black;
      }
      h2 {
        font-size: 20px;
        /* text-transform: uppercase; */
        text-transform: none;
        margin-bottom: 15px;
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
        /* border: 5px solid #000; */
      }
      .page-break {
        page-break-before: always;
      }
      .list {
        padding-left: 15px;
        display: flex;
        /* flex-direction: column;
        gap: 15px; */
      }
      .list li {
        line-height: 1.5;
      }

      footer {
      width: 100%;
      background-color: #f1f1f1;
      text-align: center;
      padding: 20px 0;
    }
    </style>
  </head>
  <body>
    <table class="header-image-table" style="margin-bottom: 60px">
      <tbody>
        <tr>
          <td>
            <img src="{{'data:image/png;base64,'.base64_encode(file_get_contents(public_path('assets/pdf_header.png')))}}" width="1500"/>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- section4 -->
    <table style="width: 100%; max-width: 1200px; margin: auto;">
      <tbody>
        <tr>
          <th colspan="6">
            <h2 style="text-align: left">CUSTOMER INFORMATION</h2>
          </th>
        </tr>
   
        <tr>
          <td>
            <p>Name</p>
            <input
              style="width: 100%;color:#333"
              type="text"
              value="{{$data->job->name}}"
            />
          </td>
          <td>
            <p>Email</p>
            <input
              style="width: 100%;color:#333"
              type="text"
              value="{{$data->job->email}}"
            />
          </td>
          <td colspan="2">
            <p>Phone</p>
            <input
              style="width: 100%;color:#333"
              type="text"
              value="{{$data->job->phone}}"
            />
          </td>
        </tr>
        <tr>
          <td>
            <p>Street</p>
            <input style="color:#333" type="text" value="{{$data->street}}" />
          </td>
          <td>
            <p>City</p>
            <input style="color:#333" type="text" value="{{$data->city}}" />
          </td>
          <td>
            <p>State</p>
            <input style="color:#333" type="text" value="{{$data->state}}" />
          </td>
          <td>
            <p>Zip Code</p>
            <input
              style="color:#333"
              type="text"
              value="{{$data->zip_code}}"
            />
          </td>
        </tr>
        <tr>
          <td>
            <p>Insurance Number:</p>
            <input
              style="width: 100%; color:#333;"
              type="text"
              value="{{$data->insurance}}"
            />
          </td>
          <td>
            <p>Claim Number:</p>
            <input
              style="width: 100%; color:#333;"
              type="text"
              value="{{$data->claim_number}}"
            />
          </td>
          <td colspan="2">
            <p>Policy Number:</p>
            <input
              style="width: 100%; color:#333;"
              type="text"
              value="{{$data->policy_number}}"
            />
          </td>
        </tr>
      </tbody>
    </table>
    <!-- <pre>{{ print_r($content, true) }}</pre> -->

    <table style="max-width: 1200px; margin: auto;">
    <tr>
        <td>
            @foreach($content as $item)
                @if($item['type'] === 'heading')
                    <h{{ $item['level'] }}>{{ $item['content'] }}</h{{ $item['level'] }}>
                @elseif($item['type'] === 'paragraph')
                    <p>{{ $item['content'] }}</p>
                @elseif($item['type'] === 'orderedList')
                    <ol>
                        @foreach($item['items'] as $listItem)
                            <li>
                                {{ $listItem['content'] }}
                                @if(!empty($listItem['subList']))
                                    <ul>
                                        @foreach($listItem['subList'] as $subItem)
                                            <li>{{ $subItem['content'] }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @elseif($item['type'] === 'unorderedList')
                    <ul>
                        @foreach($item['items'] as $listItem)
                            <li>
                                {{ $listItem['content'] }}
                                @if(!empty($listItem['subList']))
                                    <ul>
                                        @foreach($listItem['subList'] as $subItem)
                                            <li>{{ $subItem['content'] }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endforeach
        </td>
    </tr>
</table>





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
              border: 1px solid #ccc;
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
              border: 1px solid #ccc;
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

    <table style="width: 100%; max-width: 1200px; margin: auto;">
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
            </div>
          </td>
        </tr>
     
      </tbody>
    </table>

    <!-- add footer of page -->
    <table class="footer-image-table" style="margin-top: 250px">
      <tbody>
        <tr>
          <td>
            <img src="{{'data:image/png;base64,'.base64_encode(file_get_contents(public_path('assets/pdf_footer.PNG')))}}" width="1500"/>
          </td>
        </tr>
      </tbody>
    </table>
    
    <!-- Page Break -->
    <!-- <div class="page-break"></div> -->
    <!-- End -->
     <!-- add header for second page -->
    <!-- <table class="header-image-table" style="margin-bottom: 60px">
      <tbody>
        <tr>
          <td>
            <img src="{{'data:image/png;base64,'.base64_encode(file_get_contents(public_path('assets/pdf_header.png')))}}" width="1500"/>
          </td>
        </tr>
      </tbody>
    </table> -->

 <!-- add footer of page -->
    <!-- <table class="footer-image-table" style="margin-top: 750px">
      <tbody>
        <tr>
          <td>
            <img src="{{'data:image/png;base64,'.base64_encode(file_get_contents(public_path('assets/pdf_footer.PNG')))}}" width="1500"/>
          </td>
        </tr>
      </tbody>
    </table> -->

  </body>
</html>