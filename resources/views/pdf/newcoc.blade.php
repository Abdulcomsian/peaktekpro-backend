<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>coc pdf</title>
    <style>
  * {
    padding: 0;
    margin: 0;
    box-sizing: border-box;
  }
  body {
    font-family: sans-serif;
    color: black;
    font-size: 12px;
  }
  h2 {
    font-size: 16px;
    margin: 10px 0;
    text-transform: uppercase;
  }
  p {
    color: #333;
    line-height: 1.5;
  }
  input, .label, .value {
    font-size: 12px;
  }
  .label {
    font-weight: bold;
    margin-right: 5px;
  }
  .value {
    font-weight: normal;
  }
  table {
    width: 100%;
    max-width: 700px;
    margin: 0 auto 15px;
    border-collapse: collapse;
  }
  td {
    padding: 5px;
    vertical-align: top;
  }
  .page-break {
    page-break-before: always;
  }
  .list li {
    margin-bottom: 10px;
  }
</style>

  </head>
  <body>
    <table class="header-image-table" style="margin-bottom: 60px">
      <tbody>
        <tr>
          <td>
          <img src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('assets/pdf_header.png'))) }}" style="width: 100%; max-width: 700px;" />
          </td>
        </tr>
      </tbody>
    </table>
    <!-- section 1 -->
    <table style="width: 100%; max-width: 700px; margin: 0 auto;">
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
    <table style="max-width:700px; margin: auto">
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
    <table style="max-width: 700px; margin: auto">
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
    <table style="max-width: 700px; margin: auto">
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
            <br/><br>
            <div style="display: flex; justify-content: left; max-width: 1200px;">
              <h2 style="text-align: left">Notes</h2>
                <p>
                {!! $coc->notes ?? '' !!}
                </p>
            </div>

            <br><br>
            <div>
            <table >
  <tbody>
    <!-- Row for Company Representative Signature, Printed Name, and Date Signed -->
    <tr>
    <h2 style="text-align: left">Digital Signatures</h2>

      <!-- Company Representative Signature -->
      <td style="vertical-align: top; width: 40%; padding-right: 20px; margin-right:auto; ">
        <div style="margin-bottom: 10px;">
          <strong>Company Representative Signature:</strong>
        </div>
        <img
          src="{{ public_path($coc->company_representative_signature) }}"
          style="width: 200px; height: auto; border: 1px solid #ccc; padding: 5px;"
          alt="Company Representative Signature"
        />
      </td>

      <!-- Printed Name -->
      <td style="vertical-align: top; width: 30%; padding-left: 20px;">
        <div style="margin-bottom: 10px;">
          <strong>Printed Name:</strong>
        </div>
        <div
          style="
            border-bottom: 1px dashed lightslategray;
            padding-bottom: 5px;
            font-size: 14px;
          "
        >
          {{$coc->company_printed_name ?? ''}}
        </div>
      </td>

      <!-- Date Signed -->
      <td style="vertical-align: top; width: 30%; padding-left: 20px;">
        <div style="margin-bottom: 10px;">
          <strong>Date Signed:</strong>
        </div>
        <div
          style="
            border-bottom: 1px dashed lightslategray;
            padding-bottom: 5px;
            font-size: 14px;
          "
        >
          {{$coc->company_signed_date ?? ''}}
        </div>
      </td>
    </tr>

    <!-- Row for Customer Signature -->
    <tr>
      <td style="vertical-align: top; width: 40%; padding-right: 20px; padding-top: 30px;">
        <div style="margin-bottom: 10px;">
          <strong>Customer Signature:</strong>
        </div>
        <img
          src="{{ public_path($coc->customer_signature) }}"
          style="width: 200px; height: auto; border: 1px solid #ccc; padding: 5px;"
          alt="Customer Signature"
        />
      </td>
      <td colspan="2" style="vertical-align: top; padding-left: 20px; padding-top: 30px;"></td>
    </tr>
  </tbody>
</table>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

  </body>
</html>
