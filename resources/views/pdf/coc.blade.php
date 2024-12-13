<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
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
        text-transform: uppercase;
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
      .label {
        font-size: 1rem;
        font-weight: 600;
        color: #666;
        display: inline-block;
      }
      .value {
        font-size: 0.8rem;
        font-weight: 400;
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
        flex-direction: column;
        gap: 15px;
      }
      .list li {
        line-height: 1.5;
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
    <!-- section 1 -->
    <table style="width: 1200px; margin: auto">
      <tbody>
        <tr>
        <h2 style="text-align: left">Customer Information</h2>
          <td>
            <span class="label">Name:</span>
            <span class="value">{{$coc->name ?? ''}}</span>
          </td>
          <td>
            <span class="label">Email:</span>
            <span class="value">{{$coc->email ?? ''}}</span>
          </td>
          <td>
            <span class="label">Phone:</span>
            <span class="value">{{$coc->phone ?? ''}}</span>
          </td>
          <!-- <td>
            <span class="label">Supplier:</span>
            <span class="value">XYZ</span>
          </td>
          <td>
            <span class="label">Supplier Id:</span>
            <span class="value">XYZ</span>
          </td> -->
        </tr>
        <tr>
          <td>
            <span class="label">Street:</span>
            <span class="value">{{$coc->street ?? ''}}</span>
          </td>
          <td>
            <span class="label">City:</span>
            <span class="value">{{$coc->city ?? ''}}</span>
          </td>
          <td>
            <span class="label">State:</span>
            <span class="value">{{$coc->state ?? ''}}</span>
          </td>
          <td>
            <span class="label">Zip:</span>
            <span class="value">{{$coc->zip_code ?? ''}}</span>
          </td>
          <!-- <td>
            <span class="label">Insurance:</span>
            <span class="value">{{$coc->insurance ?? ''}}</span>
          </td>
        </tr>
        <tr>
          <td>
            <span class="label">Insurance Email:</span>
            <span class="value">{{ $job?->summary?->insurance_representative ?? '' }}</span>
          </td>
          <td>
            <span class="label">Claim Number:</span>
            <span class="value">{{$coc->claim_number ?? ''}}</span>
          </td>
          <td>
            <span class="label">Policy Number:</span>
            <span class="value">{{$coc->policy_number ?? ''}}</span>
          </td>
          <td>
            <span class="label">Supplier ID:</span>
            <span class="value">XYZ</span>
          </td>
          <td>
            <span class="label">Material:</span>
            <span class="value">XYZ</span>
          </td> -->
        </tr>
      </tbody>
    </table>


    <!-- section4 -->
    <table style="max-width: 1200px; margin: auto">
      <tbody>
        <tr>
          <td>
            <h2 style="text-align: left">Certificate of Completion</h2>
            <p style="line-height: 1.6">
              This certificate of completion is hereby awarded to
              <span style="border-bottom: 1px dashed lightslategray"
                ><strong>{{$coc->awarded_to ?? ''}}</strong></span
              >
              Enter award to value for the successful completion of the loss
              stated above. This project was completed by SOUTHERN ROOFING AND
              RENOVATIONS, LICENSE # 73775, a licensed general contractor in the
              state of Tennessee, in accordance with all relevant laws and
              regulations. We certify that all work on this project was
              completed in compliance with Tennessee law, which requires a
              licensed general contractor to supervise and manage the project.
              Our team of qualified professionals ensured that all work was done
              to the highest standards and met all relevant codes and
              regulations.
            </p>
          </td>
        </tr>
        <tr>
          <td>
            <h2 style="text-align: left">Depreciation</h2>
            <p style="line-height: 1.6">
              We request that the depreciation on this loss claim be released to
              <span style="border-bottom: 1px dashed lightslategray"
                ><strong>{{$coc->released_to ?? ''}}</strong></span
              >
              Enter released to value as the work has been completed and meets
              all requirements set forth by the insurance policy. We have
              thoroughly inspected the completed work and ensured that it meets
              all of the standards set forth by the insurance policy.
            </p>
          </td>
        </tr>
        <tr>
          <td>
            <h2 style="text-align: left">Overhead and Profit:</h2>
            <p style="line-height: 1.6">
              We also request that overhead and profit be included in the final
              claim settlement. This is in compliance with Tennessee law, which
              requires that overhead and profit be included in the final project
              cost. Our team of experts worked to ensure that all aspects of the
              project were completed to the highest standards, and we believe
              that our work deserves to be compensated fairly.
            </p>
          </td>
        </tr>
      </tbody>
    </table>
    <table style="width: 1200px; margin: auto">
      <tbody>
        <tr>
          <td>
            <span class="label">Job Total:</span>
            <span class="value">{{$coc->job_total ?? ''}}</span>
          </td>
          <td>
            <span class="label">Customer Paid Upgrades:</span>
            <span class="value">{{$coc->customer_paid_upgrades ?? ''}}</span>
          </td>
          <td>
            <span class="label">Deductible:</span>
            <span class="value">{{$coc->deductible ?? ''}}</span>
          </td>
        </tr>
        <tr>
          <td>
            <span class="label">ACV Check:</span>
            <span class="value">{{$coc->acv_check ?? ''}}</span>
          </td>
          <td>
            <span class="label">RCV Check:</span>
            <span class="value">{{$coc->rcv_check ?? ''}}</span>
          </td>
          <td>
            <span class="label">Supplemental Items:</span>
            <span class="value">{{$coc->supplemental_items ?? ''}}</span>
          </td>
        </tr>
      </tbody>
    </table>
    <table style="max-width: 1200px; margin: auto">
      <tbody>
        <tr>
          <td>
            <h2 style="text-align: left">Conclusion</h2>
            <p style="line-height: 1.6">
              We would like to thank
              <span style="border-bottom: 1px dashed lightslategray"
                ><strong>{{$coc->conclusion ?? ''}}</strong></span
              >
              for the opportunity to work on this project. We take great pride
              in our work and are pleased to have been able to provide quality
              construction services. We believe that the completed work meets
              all requirements set forth by the insurance policy and Tennessee
              law, and we request that the depreciation be released and overhead
              and profit be included in the final claim settlement.
            </p>
            <span style="border-bottom: 1px dashed lightslategray"
              >Sincerely <strong>{{$coc->sincerely ?? ''}}</strong></span
            >
          </td>
        </tr>
      </tbody>
    </table>

    <table style="max-width: 1200px; margin: auto">
      <tbody>
        <tr>
          <td>
            <h2 style="text-align: left">Notes</h2>
            <p style="line-height: 1.6">    
              {{$coc->notes}}
              
            </p>
          </td>
        </tr>
      </tbody>
    </table>


    <table style="width: 1200px; border-collapse: collapse;">
  <tbody>
    <tr>
      <td style="vertical-align: top; padding-right: 20px;">
        <h2 style="text-align: left; margin: 0 0 15px;">Digital Signatures</h2>
        <span style="display: inline-block; vertical-align: top;">Company Representative Signature:</span>
        <span
          style="
            width: 250px;
            display: inline-block;
            vertical-align: top;
            margin-left: 10px;
          "
        >
          <img
            src="{{ public_path($coc->company_representative_signature) }}"
            style="max-width: 100%; height: auto;"
            alt="Company Representative Signature"
          />
        </span>
      </td>
      <td style="vertical-align: top; padding-left: 20px;">
        <span class="label">
          <h3 style="margin: 0;"><strong>Printed Name:</strong></h3>
        </span>
        <span
          style="
            display: inline-block;
            border-bottom: 1px dashed lightslategray;
            padding-bottom: 5px;
            margin-top: 5px;
          "
        >
          <p style="margin: 0;">{{$coc->company_printed_name ?? ''}}</p>
        </span>
      </td>
      <td style="vertical-align: top; padding-left: 20px;">
        <span class="label">
          <h3 style="margin: 0;"><strong>Date Signed:</strong></h3>
        </span>
        <span
          style="
            display: inline-block;
            border-bottom: 1px dashed lightslategray;
            padding-bottom: 5px;
            margin-top: 5px;
          "
        >
          <p style="margin: 0;">{{$coc->company_signed_date ?? ''}}</p>
        </span>
      </td>
    </tr>
    <tr>
      <td style="vertical-align: top; padding-top: 20px;">
        <span style="display: inline-block; vertical-align: top;">Customer Signature:</span>
        <span
          style="
            width: 250px;
            display: inline-block;
            vertical-align: top;
            margin-left: 10px;
          "
        >
          <img
            src="{{ public_path($coc->customer_signature) }}"
            style="max-width: 100%; height: auto;"
            alt="Customer Signature"
          />
        </span>
      </td>
    </tr>
  </tbody>
</table>

  </body>
</html>
