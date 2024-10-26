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
      .title {
        display: block;
        font-size: 1rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
      }
      .desc {
        line-height: 1.3;
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
    <table style="width: 1200px; margin: auto">
      <thead>
        <tr>
          <th>
            <h2 style="text-align: left">Title</h2>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <span class="label">First Name:</span>
            <span class="value">{{ $job?->title?->first_name ?? '' }}</span>
          </td>
          <td>
            <span class="label">Last Name:</span>
            <span class="value">{{ $job?->title?->last_name ?? '' }}</span>
          </td>
          <td>
            <span class="label">Company Name:</span>
            <span class="value">{{ $job?->title?->company_name ?? '' }}</span>
          </td>
          <td>
            <span class="label">Address:</span>
            <span class="value">{{ $job?->title?->address ?? '' }}</span>
          </td>
          <td>
            <span class="label">City:</span>
            <span class="value">{{ $job?->title?->city ?? '' }}</span>
          </td>
        </tr>
        <tr>
          <td>
            <span class="label">State/Province:</span>
            <span class="value">{{ $job?->title?->state ?? '' }}</span>
          </td>
          <td>
            <span class="label">Zip Code/Postal Code:</span>
            <span class="value">{{ $job?->title?->zip ?? '' }}</span>
          </td>
          <td>
            <span class="label">Report Type:</span>
            <span class="value">{{ $job?->title?->report_type ?? '' }}</span>
          </td>
          <td>
            <span class="label">Date:</span>
            <span class="value">{{ $job?->title?->date ?? '' }}</span>
          </td>
        </tr>
      </tbody>
    </table>
    <table style="width: 1200px; margin: auto">
      <thead>
        <tr>
          <th>
            <h2 style="text-align: left">Introduction</h2>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <p>{!! $job?->introduction?->introduction ?? '' !!}</p>
          </td>
        </tr>
      </tbody>
    </table>
    <table style="width: 1200px; margin: auto">
      <thead>
        <tr>
          <th>
            <h2 style="text-align: left">Inspection</h2>
          </th>
        </tr>
      </thead>
      <tbody>
        @if(is_countable($job?->inspections) && count($job?->inspections) > 0)
        @foreach($job?->inspections as $inspection)
        <tr>
          <td>
            <p>{!! $inspection?->inspection ?? '' !!}</p>
          </td>
          @if(is_countable($inspection?->attachment) && count($inspection?->attachment) > 0)
          @foreach($inspection?->attachment as $attachment)
          <td>
              <a href="{{ url('') . $attachment->url }}" target="_blank">@if(!is_null($attachment->file_name))  {{$attachment->file_name}} @else View @endif</a><br>
          </td>
          @endforeach
          @endif
        </tr>
        @endforeach
        @endif
      </tbody>
    </table>
    <table style="width: 1200px; margin: auto">
      <thead>
        <tr>
          <th>
            <h2 style="text-align: left">Quote Detail</h2>
          </th>
        </tr>
      </thead>
      <tbody>
        @if(is_countable($job?->quote?->sections) && count($job?->quote?->sections) > 0)
        @foreach($job?->quote?->sections  as $section)
        <tr>
          <td colspan="4">
            <h4>{{ $section->title ?? '' }}</h4>
          </td>
        </tr>
        @if(is_countable($section->items) && count($section->items) > 0)
        @foreach($section->items as $item)
        <tr>
          <td>
            <span class="label">Item</span>
            <span class="value">{{ $item->item ?? '' }}</span>
          </td>
          <td>
            <span class="label">Quantity</span>
            <span class="value">{{ $item->quantity ?? '' }}</span>
          </td>
          <td>
            <span class="label">Price</span>
            <span class="value">{{ $item->price ?? '' }}</span>
          </td>
          <td>
            <span class="label">Line Total</span>
            <span class="value">{{ $item->line_total ?? '' }}</span>
          </td>
        </tr>
        @endforeach
        @endif
        @endforeach
        @endif
        <tr>
          <td colspan="4">
            <span class="label">Section total:</span>
            <span class="value">{{ $section->section_total ?? '' }}</span>
          </td>
        </tr>
        <tr>
          <td>
            <span class="label">Profit Margin:</span>
            <span class="value">{{ $job?->quote?->profit_margin ?? '' }}</span>
          </td>
          <td>
            <span class="label">Quote subtotal:</span>
            <span class="value">{{ $job?->quote?->quote_sub_total ?? '' }}</span>
          </td>
          <td>
            <span class="label">Total</span>
            <span class="value">{{ $job?->quote?->quote_total ?? '' }}</span>
          </td>
          <td>
            <span class="label">Notes</span>
            <span class="value">{{ $job?->quote?->notes ?? '' }}</span>
          </td>
        </tr>
      </tbody>
    </table>
    <table style="width: 1200px; margin: auto">
      <thead>
        <tr>
          <th>
            <h2 style="text-align: left">Authorization</h2>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan="4">
            <span style="line-height: 1.6">
              Disclaimer <br />
              <span style="color: #666">
                For example, the terms of an estimate, or a direction to the
                insurer. </span
              ><br />
              I hereby irrevocably direct my Insurer to include the name
              <span style="border-bottom: 1px dashed lightslategray"
                ><strong>{{ $job?->authorization?->disclaimer ?? '' }}</strong></span
              >
              (Roofing Co.) as the payee on any check or draft issued in payment
              of said insurance claim with regard to the building or contents
              repair and to send that check directly to the contractor. I am
              responsible for payment of the deductible in the amount of $<span
                style="border-bottom: 1px dashed lightslategray"
                >XYZ Amount.....</span
              >, and any depreciation (if applicable).
            </span>
          </td>
        </tr>
        <tr>
        @php
        $section_total = 0;
        @endphp
        @if(is_countable($job?->authorization?->sections) && count($job?->authorization?->sections) > 0)
        @foreach($job?->authorization?->sections  as $section)
          <td colspan="4">
            <h4>{{ $section?->title ?? '' }}</h4>
          </td>
        </tr>
        @if(is_countable($section?->items) && count($section?->items) > 0)
        @foreach($section?->items as $item)
        @php
        $section_total = $section_total + $item->line_total;
        @endphp
        <tr>
          <td>
            <span class="label">Item</span>
            <span class="value">{{ $item->item ?? '' }}</span>
          </td>
          <td>
            <span class="label">Quantity</span>
            <span class="value">{{ $item->quantity ?? '' }}</span>
          </td>
          <td>
            <span class="label">Price</span>
            <span class="value">{{ $item->price ?? '' }}</span>
          </td>
          <td>
            <span class="label">Line Total</span>
            <span class="value">{{ $item->line_total ?? '' }}</span>
          </td>
        </tr>
        @endforeach
        @endif
        @endforeach
        @endif
        <tr>
          <td colspan="4">
            <span class="label">Section total:</span>
            <span class="value">{{ $section_total }}</span>
          </td>
        </tr>
      </tbody>
    </table>
    <table style="width: 1200px; margin: auto">
      <thead>
        <tr>
          <th>
            <h4 style="text-align: left">Insurance Details</h4>
          </th>
        </tr>
        <tr>
          <th>
            <h4 style="text-align: left">Item</h4>
          </th>
          <th>
            <h4 style="text-align: left">Selection</h4>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <!--<span class="label">Item</span>-->
            <span class="value">{{ $job?->authorization?->item1 ?? '' }}</span><br>
            <span class="value">{{ $job?->authorization?->item2 ?? '' }}</span><br>
            <span class="value">{{ $job?->authorization?->item3 ?? '' }}</span><br>
          </td>
          <td>
            <!--<span class="label">Selection</span>-->
            <span class="value">{{ $job?->authorization?->section1 ?? '' }}</span><br>
            <span class="value">{{ $job?->authorization?->section2 ?? '' }}</span><br>
            <span class="value">{{ $job?->authorization?->section3 ?? '' }}</span><br>
          </td>
        </tr>
      </tbody>
    </table>
    <table style="width: 1200px; margin: auto">
      <thead>
        <tr>
          <th>
            <h4 style="text-align: left">Primary Signer</h4>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <span class="label">Fist Name:</span>
            <span class="value">{{ $job?->authorization?->signer_first_name ?? '' }}</span>
          </td>
          <td>
            <span class="label">Last Name:</span>
            <span class="value">{{ $job?->authorization?->signer_last_name ?? '' }}</span>
          </td>
          <td>
            <span class="label">Email:</span>
            <span class="value">{{ $job?->authorization?->signer_email ?? '' }}</span>
          </td>
        </tr>
      </tbody>
    </table>
    <table style="width: 1200px; margin: auto">
      <tbody>
        <tr>
          <td>
            <span class="label">Footer Notes:</span>
            <span class="value">{{ $job?->authorization?->footer_notes ?? '' }}</span>
          </td>
        </tr>
      </tbody>
    </table>
    @if(!is_null($job?->paymentSchedule) && $job?->paymentSchedule?->acknowledge == 1)
    <table style="width: 1200px; margin: auto">
      <thead>
        <tr>
          <th>
            <h2 style="text-align: left">Proof of Payment</h2>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <h4>PDF files</h4>
          </td>
        </tr>
        <tr>
          <td>
              @if(is_countable($job?->paymentSchedule?->pdfs) && count($job?->paymentSchedule?->pdfs) > 0)
              @foreach($job?->paymentSchedule?->pdfs as $pdf)
              <a href="{{ url('') . $pdf->pdf_url }}" target="_blank">@if(!is_null($pdf->file_name))  {{$pdf->file_name}} @else View @endif</a><br>
              @endforeach
              @endif
          </td>
        </tr>
        <tr>
          <td>
            <h4>Text</h4>
          </td>
        </tr>
        <tr>
          <td>{!! $job?->paymentSchedule?->content ?? '' !!}</td>
        </tr>
      </tbody>
    </table>
    @endif
    @if(!is_null($job?->roofComponent) && $job?->roofComponent?->acknowledge == 1)
    <table style="width: 1200px; margin: auto">
      <thead>
        <tr>
          <th>
            <h2 style="text-align: left">Roof Component</h2>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <h4>PDF files</h4>
          </td>
        </tr>
        <tr>
          <td>
              @if(is_countable($job?->roofComponent?->pdfs) && count($job?->roofComponent?->pdfs) > 0)
              @foreach($job?->roofComponent?->pdfs as $pdf)
              <a href="{{ url('') . $pdf->pdf_url }}" target="_blank">@if(!is_null($pdf->file_name))  {{$pdf->file_name}} @else View @endif</a><br>
              @endforeach
              @endif
          </td>
        </tr>
        <tr>
          <td>
            <h4>Text</h4>
          </td>
        </tr>
        <tr>
          <td>{!! $job?->roofComponent?->content ?? '' !!}</td>
        </tr>
      </tbody>
    </table>
    @endif
    @if(!is_null($job?->xactimateReport) && $job?->xactimateReport?->acknowledge == 1)
    <table style="width: 1200px; margin: auto">
      <thead>
        <tr>
          <th>
            <h2 style="text-align: left">Measurement Report</h2>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <h4>PDF files</h4>
          </td>
        </tr>
        <tr>
          <td>
              @if(is_countable($job?->xactimateReport?->pdfs) && count($job?->xactimateReport?->pdfs) > 0)
              @foreach($job?->xactimateReport?->pdfs as $pdf)
              <a href="{{ url('') . $pdf->pdf_url }}" target="_blank">@if(!is_null($pdf->file_name))  {{$pdf->file_name}} @else View @endif</a><br>
              @endforeach
              @endif
          </td>
        </tr>
        <tr>
          <td>
            <h4>Text</h4>
          </td>
        </tr>
        <tr>
          <td>{!! $job?->xactimateReport?->content ?? '' !!}</td>
        </tr>
      </tbody>
    </table>
    @endif
    <table style="width: 1200px; margin: auto">
      <thead>
        <tr>
          <th>
            <h2 style="text-align: left">Terms and Conditions</h2>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <p>
              By signing this document I/we hereby authorize(“Company”) to enter
              my property with the address listed on the Authorization page
              (“Property”) and perform the roofing work and other services
              (“Services”) at as set forth in this Contract & the Scope for
              Roofing Services (“Contract”).
            </p>
          </td>
        </tr>
        <tr>
          <td>
            <p>
              <span class="title">1. Scope of Services:</span>Company shall
              provide the services and materials specified in the Scope or a
              portion thereof as identified in the Scope, Description of
              Services, and any other services or materials identified below
              which may be necessary to repair the damage to my property arising
              out of or discovered as a result of the recent insurable incident
              (“Claim”).
            </p>
          </td>
        </tr>
        <tr>
          <td>
            <p>
              <span class="title">2. Conditions of Insurance:</span> In no event
              is Company required to commence the Services until the Insurance
              Company has approved payment of Insurance Proceeds for the Claim
              in an amount that is not less than the amount set forth on the
              Insurance Estimate.
            </p>
          </td>
        </tr>
        <tr>
          <td>
            <p>
              <span class="title">3. Payment for Services:</span> The
              approximate cost of the Services are set forth in the Insurance
              Estimate. Company shall perform the Services in exchange for any
              applicable Insurance Proceeds, plus any deductible amount which
              must be paid, in full, by Owner. Company shall not take any action
              which may be construed as a waiver, rebate, or other form of
              payment to Owner as compensation for all or part of Owner’s
              insurance deductible. Notwithstanding any other provision of this
              Contract, Owner shall pay to Company the cost of any services
              which are not covered by the Insurance Proceeds and are indicated
              as such on the Scope.
            </p>
          </td>
        </tr>
        <tr>
          <td>
            <p>
              <span class="title">4. Insurance Proceeds:</span> “Insurance
              Proceeds” means any and all benefits, reimbursements, or other
              payments which are payable by the Insurance Company to Owner for
              the Services Rendered by Company pursuant to the Claim or any
              Supplement, as defined below. Company shall also be entitled to
              payment of any Insurance Proceeds, to the extent such amounts are
              covered by Owner’s insurance policy, which are payable by
              Insurance Company for any expenses incurred to repair unforeseen
              damage/loss discovered while performing the Services
              (“Supplements”).
            </p>
          </td>
        </tr>
        <tr>
          <td>
            <p>
              <span class="title"
                >5. Assignment of Right to Collect Insurance Proceeds:
              </span>
              a. Assignment. Owner hereby assigns any and all Insurance
              Proceeds, including the right to pursue collection of such
              Insurance Proceeds from the Insurance Company, to Company as part
              of the consideration for the Services. Owner hereby acknowledges
              and agrees that the foregoing assignment includes the right for
              Company to submit invoices, make demands for payment, pursue legal
              actions, and otherwise pursue claims or take action for the
              collection of the Insurance Proceeds directly to or against the
              Insurance Company. The parties acknowledge that this assignment is
              limited to the Insurance Proceeds, and the rights to pursue any
              actions or claims to collect the Insurance Proceeds, and does not
              assign, transfer, or otherwise grant Company any rights to pursue
              or negotiate the Claim or the insurance coverage related thereto.
              b. Direct Payment Authorization. Owner makes this assignment as
              part of the consideration for Company’s agreement to perform the
              Services and hereby agrees to: (i) direct the Insurance Company to
              release any and all estimates, costs, or other payment information
              related to the Claim, Insurance Proceeds, and any Supplement, to
              Company, but only to the extent such information pertains to the
              Services and/or Company’s operations; (ii) permit the Company to
              discuss the scope of Services and the Insurance Proceeds directly
              with the Insurance Company, to the extent Company is permitted to
              do so by applicable law; and (iii) waive any privacy rights
              related to the foregoing. c. Right to Pursue Legal Action Against
              Insurance Company. Company shall, in its sole discretion and
              expense, be entitled to take any reasonable actions necessary,
              including, but not limited to, making demands for payment,
              pursuing legal actions in court, and otherwise pursuing claims and
              taking action against the Insurance Company to collect the
              Insurance Proceeds owed to Company pursuant to this Contract. Upon
              Company’s request, Owner agrees to provide any relevant
              information and, to the extent reasonably necessary, assist
              Company in pursuing a claim or other action against the Insurance
              Company in the event the Insurance Company fails to pay the total
              amount of Insurance Proceeds owed to Company pursuant to the terms
              of this Contract.
            </p>
          </td>
        </tr>
        <tr>
          <td>
            <span class="title">6. Term & Termination:</span>
            Either party may terminate this Contract in the event the other
            party materially breaches the terms set forth herein. Owner may
            terminate this Contract in the event the Insurance Company denies
            payment for the Claim; provided Owner gives Company written notice
            of termination within three (3) days of the Owner learning of such
            denial. However, Owner shall not be entitled to the foregoing
            termination right in the event the denial pertains only to a
            Supplement and not the entire Claim and Owner shall be entitled
            retain any amounts received as payment for Services rendered.
            Further, Owner has the right to terminate this Contract, for any
            reason, within three (3) days of signing this Contract, and Company
            shall make a full refund of any deposits paid by Owner upon
            receiving notice thereof.
          </td>
        </tr>
        <tr>
          <td>
            <span class="title">7. Liability:</span>
            Owner hereby agrees to release, indemnify, defend, and hold Company
            harmless from any claims, damages, lawsuits (including attorney’s
            fees) or other expenses related to Owner’s negligence or Owner’s
            breach of this Contract. IN NO EVENT SHALL COMPANY’S AGGREGATE
            LIABILITY ARISING OUT OF OR RELATED TO THIS CONTRACT, WHETHER
            ARISING OUT OF OR RELATED TO BREACH OF CONTRACT, TORT (INCLUDING
            NEGLIGENCE), OR OTHERWISE, EXCEED THE AGGREGATE AMOUNTS PAID TO
            COMPANY BY OWNER HEREUNDER.
          </td>
        </tr>
        <tr>
          <td>
            <span class="title"> 8. Miscellaneous: </span>

            a. Notices. All notices sent by one party to the other party in
            connection with this Contract must be in writing. b. Severability.
            If any term or provision of this Contract is found by a court of
            competent jurisdiction to be invalid, illegal, or unenforceable in
            any jurisdiction, such provision shall be excluded from this
            Contract and the other terms shall remain in full force and effect.
            c. Entire Agreement. This Contract and any Exhibits attached hereto
            constitute the entire agreement between the parties pertaining to
            the Services and supersedes all prior and contemporaneous
            agreements, proposals, quotes, representations, or other
            understanding of the parties. No supplement, modification, or
            amendment of this Contract shall be binding unless executed in
            writing by duly authorized representatives of both parties. d. Fees
            & Interest. In the case that proceeds owed are not paid and Owner is
            sent to a Collections, Company reserves the right to add the fees
            and interest ensued through the collections process to the remainder
            of the portion due. e. Dispute Debt. Owner has 15-30 days from final
            Invoice to dispute debt in writing and sent to Company. f. In the
            event your account is assigned to a collection agency, you agree to
            pay a collection fee in the amount equal to 30% of the balance due
            assigned to the collection agency.
          </td>
        </tr>
        <tr>
          <td>Placeholder for sign</td>
          <td>
              <span
                style="
                  width: 250px;
                  display: inline-block;
                "
                ><img src="{{ public_path($job?->terms?->sign_image) }}"
              /></span>
          </td>
        </tr>
      </tbody>
    </table>
  </body>
</html>
