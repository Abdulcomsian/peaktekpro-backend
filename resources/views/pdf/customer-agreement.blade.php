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
    <table style="width: 1200px; margin: auto">
      <thead>
        <tr>
          <th colspan="6">
            <h2 style="text-align: left">Customer Information</h2>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <label for="">name:</label>
            <input
              style="width: 100%;"
              type="text"
              value="{{$data->job->name}}"
            />
          </td>
          <td>
            <label for="">email:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->job->email}}"
            />
          </td>
          <td colspan="2">
            <label for="">phone:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->job->phone}}"
            />
          </td>
        </tr>
        <tr>
          <td>
            <label for="">Street:</label>
            <input style="width: 100%" type="text" value="{{$data->street}}" />
          </td>
          <td>
            <label for="">City:</label>
            <input style="width: 100%" type="text" value="{{$data->city}}" />
          </td>
          <td>
            <label for="">State:</label>
            <input style="width: 100%" type="text" value="{{$data->state}}" />
          </td>
          <td>
            <label for="">Zip:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->zip_code}}"
            />
          </td>
        </tr>
        <tr>
          <td>
            <label for="">Insurance:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->insurance}}"
            />
          </td>
          <td>
            <label for="">Claim Number:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->claim_number}}"
            />
          </td>
          <td colspan="2">
            <label for="">Policy Number:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->policy_number}}"
            />
          </td>
        </tr>
      </tbody>
    </table>
    <!-- section4 -->
    <table style="max-width: 1200px; margin: auto">
      <tbody>
        <tr>
          <td>
            <h2 style="text-align: left">ACKNOWLEDGEMENTS</h2>
            <ul class="list">
              <li>
                <p>
                  Customer affirms and acknowledges ownership of the property
                  situated at the address provided above and asserts
                  authorization and competency to engage in this Agreement.
                </p>
              </li>
              <li>
                <p>
                  PeakTek Roofing & Restoration ("PeakTek") will supply
                  materials, equipment, and labor, either directly or through
                  independent contractors, as outlined herein (the "Work").
                </p>
              </li>
              <li>
                <p>
                  Customer has enlisted PeakTek as the chosen contractor due to
                  PeakTek's expertise and its licensing, bonding, and insurance.
                  Customer comprehends that PeakTek will proceed with the Work
                  and associated tasks related to the insurance claim included
                  within this agreement, relying on this agreement as a
                  foundation.
                </p>
              </li>
            </ul>
          </td>
        </tr>
      </tbody>
    </table>
    <table style="max-width: 1200px; margin: auto">
      <tbody>
        <tr>
          <td>
            <h2 style="text-align: left">INSURANCE</h2>
            <ul class="list">
              <li>
                <p>
                  Both parties acknowledge that, except for deductible and
                  upgrade costs, payment for the Work may be facilitated by the
                  Customer's insurer. However, the Customer acknowledges the
                  necessity to assess any property damage separately. The
                  Customer grants authorization for PeakTek Roofing &
                  Restoration (hereafter referred to as "PeakTek") to allocate
                  its time and expertise to aid in evaluating damages and
                  providing repair or replacement recommendations, potentially
                  covered under an insurance claim, subject to the approval of
                  the Customer's insurer.
                </p>
              </li>
              <li>
                <p>
                  The Customer designates PeakTek as the sole contractor
                  responsible for completing the work, ensuring compliance with
                  all local, state, federal, code, and safety regulations.
                  Additionally, the Customer accepts responsibility for any
                  expenses not covered by insurance, including but not limited
                  to work portions, deductibles, enhancements, depreciation, or
                  additional work requested by the Customer. Such payments must
                  be settled within thirty (30) days of written notification
                  from PeakTek.
                </p>
              </li>
            </ul>
          </td>
        </tr>
      </tbody>
    </table>
    <table style="max-width: 1200px; margin: auto">
      <tbody>
        <tr>
          <td>
            <h2 style="text-align: left">PRICING</h2>
            <ul class="list">
              <li>
                <p>
                  "Price Agreeable" encompasses all funds paid or agreed upon as
                  outlined in the Claim, including but not limited to the
                  Insurance Deductible, Actual Cash Value, Replacement Cost
                  Value, Recoverable Depreciation, Supplements, change orders,
                  profit, overhead, markups, and/or margin
                </p>
              </li>
              <li>
                <p>
                  The undersigned parties hereby consent to the terms stipulated
                  in the aforementioned Agreement and any supplementary terms
                  and conditions detailed on the reverse side herein
                </p>
              </li>
              <li>
                <p>
                  IN WITNESS WHEREOF, the undersigned parties have willingly and
                  voluntarily caused the execution of this Agreement, either
                  individually or by their duly authorized representative, on
                  the effective date of acceptance indicated below.
                </p>
              </li>
              <li>
                <p>
                  Customer has enlisted PeakTek as the chosen contractor due to
                  Terms: By signing the Agreement, the homeowner authorizes
                  PeakTek Roofing & Restoration to pursue the property owner’s
                  best interest for project replacement or repair at a "price
                  agreeable" to both the insurance company and PeakTek, without
                  additional costs to the property owner beyond the deductible
                  and any upgrades. Once the "price agreeable" is determined, it
                  shall constitute the final contract price, and the property
                  owner authorizes PeakTek to procure labor and materials in
                  accordance with the agreed-upon price and specifications
                  outlined herein and on the reverse side of this document to
                  carry out the replacement or repair. Any funds received from
                  the insurance company for contractor overhead, profit, and/or
                  cost increase supplements will be remitted to PeakTek Roofing
                  & Restoration.
                </p>
              </li>
            </ul>
          </td>
        </tr>
      </tbody>
    </table>
    <table style="width: 1200px; margin: auto">
      <thead>
        <tr>
          <th colspan="6">
            <h2 style="text-align: left">Customer Information</h2>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <label for="">Customer Signature:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->customer_signature}}"
            />
          </td>
          <td>
            <label for="">Printed Name:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->customer_printed_name}}"
            />
          </td>
          <td>
            <label for="">Date Signed:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->customer_date}}"
            />
          </td>
        </tr>
        <tr>
          <td>
            <label for="">Company Representative Signature:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->company_signature}}"
            />
          </td>
          <td>
            <label for="">Printed Name:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->company_printed_name}}"
            />
          </td>
          <td>
            <label for="">Date Signed:</label>
            <input
              style="width: 100%"
              type="text"
              value="{{$data->company_date}}"
            />
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Page Break -->
    <div class="page-break"></div>
    <!-- End -->

    <table style="max-width: 1200px; margin: auto">
      <tr>
        <td>
          <ul class="list">
            <li>
              <p>
                This Contract and any agreements entered into between PeakTek
                Roofing & Restoration (hereinafter referred to as the “Company”
                or “PeakTek”) and the customer(s) identified herein on the
                Agreement’s page 1 shall adhere to all applicable copyright
                laws, regulations, and ordinances in the state of record.
              </p>
            </li>
            <li>
              <p>Indemnity Statement</p>
            </li>
            <li>
              <p>
                The homeowner shall not be held liable for any injuries,
                accidents, or damages that occur on the property during the
                re-roof project. PeakTek Roofing & Restoration assumes full
                responsibility for all job site accidents and incidents,
                ensuring coverage through our workers' compensation insurance
                and any other applicable insurance policies.
              </p>
            </li>
            <li>
              <p>
                This Contract and any agreements made pursuant thereto between
                PeakTek Roofing & Restoration (referred to as the “Co.” or
                “Company” or “PeakTek”) and the customer(s) named herein on the
                Agreement’s page 1 will be subject to all appropriate laws,
                regulations, and ordinances in the state of record.
              </p>
            </li>
            <li>
              <p>
                In the event of default in payment under this contract, charges
                shall be applied from the date thereof at a rate equivalent to
                the greater of one and one-half percent (1.5%) per month (18%
                per annum), with a minimum charge of $20.00 per month or the
                maximum amount permitted by law. If legal action is required for
                collection, the Customer shall bear all attorney's fees and
                associated costs.
              </p>
            </li>
            <li>
              <p>
                PeakTek Roofing & Restoration ("the Company") disclaims
                responsibility for damages arising from rain, fire, tornadoes,
                windstorms, or other perils unless specifically agreed upon in
                writing before commencing work or covered by homeowner's
                insurance or business risk insurance.
              </p>
            </li>
            <li>
              <p>
                Unless stated otherwise in the contract, replacement of
                deteriorated decking, fascia boards, roof jacks, ventilators,
                flashing, or other materials is not included and will be billed
                separately on a time and material basis
              </p>
            </li>
            <li>
              <p>
                After 90 days, the Company reserves the right to adjust the
                price based on current costs, such as material expenses
              </p>
            </li>
            <li>
              <p>
                The Company shall not be liable for performance failures due to
                uncontrollable circumstances such as labor disputes, strikes,
                fires, pandemics, wars, riots, protests, supply shortages,
                weather, or other events beyond its control.
              </p>
            </li>
            <li>
              <p>
                The Company is not responsible for any damage on or below the
                roof due to leaks, excessive wind-driven rain, ice, or hail
                during the period of warranty. EXCESSIVE WIND is 50 M.P.H. or
                faster.
              </p>
            </li>
            <li>
              <p>
                In case of material reorder or restocking due to customer
                cancellation, a restocking fee equal to fifteen percent (15%) of
                the contract price will be applied
              </p>
            </li>
            <li>
              <p>
                The Company disclaims liability for any mold, mildew or interior
                damage resulting from prior leaks.
              </p>
            </li>
            <li>
              <p>
                Cancellation of this contract later than 5 days from execution
                incurs a fee of $200.00 per person per hour expended in property
                evaluation or $2,000.00, whichever is greater, as liquidated
                damages.
              </p>
            </li>
            <li>
              <p>
                Once work has commenced, this contract cannot be canceled except
                by mutual written agreement.
              </p>
            </li>
            <li>
              <p>
                If any provision of this contract is deemed invalid or
                unenforceable, the remaining provisions shall remain unaffected
              </p>
            </li>
            <li>
              <p>
                Any verbal communications outside this contract are deemed
                immaterial and not relied upon by either party.
              </p>
            </li>
            <li>
              <p>
                During work, the customer's homeowner's insurance is responsible
                for interior damage if the Company has protected the roof
                adequately
              </p>
            </li>
            <li>
              <p>
                The Company disclaims responsibility for any damage to solar
                panels during repairs
              </p>
            </li>
            <li>
              <p>
                The Company is not liable for construction issues of the
                customer's home unless notified and specified in writing.
              </p>
            </li>
            <li>
              <p>
                The Company disclaims responsibility for damage from leaks from
                skylights unless it completes the skylight replacement.
              </p>
            </li>
            <li>
              <p>
                Warranty periods are specified for different types of work, and
                extended service warranties are available for additional
                charges.
              </p>
            </li>
            <li>
              <p>
                Payments are to be made in accordance with the agreed terms,
                with insurance checks to be endorsed to the Company promptly.
              </p>
            </li>
            <li>
              <p>
                Additional labor or material costs due to hidden conditions or
                building code issues require a signed change order.
              </p>
            </li>
            <li>
              <p>
                The Company is not responsible for fixing existing framing
                issues unless necessary, on a time and material basis
              </p>
            </li>
            <li>
              <p>
                All insurance proceeds for approved repairs are to be paid to
                the Company unless stated otherwise in writing.
              </p>
            </li>
            <li>
              <p>
                Customers are required to pay in full upon completion of the
                project.
              </p>
            </li>
            <li>
              <p>
                Customers have the right to cancel the contract within three
                business days of being notified by their insurer that the claim
                or contract is not covered, except for emergency repairs already
                completed.
              </p>
            </li>
            <li>
              <p>
                Customers must inform the Company of any property covenants,
                conditions, or restrictions, as the Company is not liable unless
                notified in writing and reference is made in the contract terms.
              </p>
            </li>
          </ul>
        </td>
      </tr>
    </table>
    <table style="width: 1200px; margin: auto">
      <tr>
      <td>
  <span
    style="
      text-decoration: underline;
      margin-top: 50px;
    "
  >{{ $data->customer_name }}</span>
  <span> the undersigned, hereby cancel this transaction as of </span>
  <span
    style="
      text-decoration: underline;
    "
  >{{ $data->agreement_date }}</span>
</td>

      </tr>
      <tr>
        <td>
          <span style="margin-top: 20px; display: inline-block"
            >Customer Signature:</span
          >
          <span
            style="
              width: 250px;
              display: inline-block;
            "
            ><img src="{{ public_path($data->sign_image_url) }}"
          /></span>
        </td>
      </tr>
    </table>
  </body>
</html>