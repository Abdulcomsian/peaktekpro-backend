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
      .page-break {
            page-break-before: always;
        }
    </style>
  </head>
  <body>
        <table style="max-width: 1200px; margin: auto;">
            <thead>
                <tr>
                    <th colspan="6">
                      <h1 style="text-align: left; margin-bottom: 20px;">Customer Information</h1>
                    </th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <label for="">name:</label> 
                        <input
                          style="
                            width: 100%;
                            height: 30px;
                            border-radius: 5px;
                            border: 2px solid rgba(0, 0, 0, 0.115);
                            background-color: rgba(1, 52, 89, 0.077);
                          "
                          type="text"
                          value="{{$data->job->name}}"
                        />
                    </td>
                    <td >
                        <label for="">email:</label>
                        <input
                            style="
                            width: 100%;
                            height: 30px;
                            border-radius: 5px;
                            border: 2px solid rgba(0, 0, 0, 0.115);
                            background-color: rgba(1, 52, 89, 0.077);
                            "
                            type="text"
                            value="{{$data->job->email}}"
                            />
                    </td>
                    <td colspan="2">
                        <label for="">phone:</label> 
                        <input
                        style="
                        width: 100%;
                        height: 30px;
                        border-radius: 5px;
                        border: 2px solid rgba(0, 0, 0, 0.115);
                        background-color: rgba(1, 52, 89, 0.077);
                        "
                        type="text"
                        value="{{$data->job->phone}}"
                        />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="">Street:</label> 
                        <input
                          style="
                            width: 100%;
                            height: 30px;
                            border-radius: 5px;
                            border: 2px solid rgba(0, 0, 0, 0.115);
                            background-color: rgba(1, 52, 89, 0.077);
                          "
                          type="text"
                          value="{{$data->street}}"
                        />
                    </td>
                    <td>
                        <label for="">City:</label> 
                        <input
                          style="
                            width: 100%;
                            height: 30px;
                            border-radius: 5px;
                            border: 2px solid rgba(0, 0, 0, 0.115);
                            background-color: rgba(1, 52, 89, 0.077);
                          "
                          type="text"
                          value="{{$data->city}}"
                        />
                    </td>
                    <td>
                        <label for="">State:</label> 
                        <input
                          style="
                            width: 100%;
                            height: 30px;
                            border-radius: 5px;
                            border: 2px solid rgba(0, 0, 0, 0.115);
                            background-color: rgba(1, 52, 89, 0.077);
                          "
                          type="text"
                          value="{{$data->state}}"
                        />
                    </td>
                    <td>
                        <label for="">Zip:</label> 
                        <input
                          style="
                            width: 100%;
                            height: 30px;
                            border-radius: 5px;
                            border: 2px solid rgba(0, 0, 0, 0.115);
                            background-color: rgba(1, 52, 89, 0.077);
                          "
                          type="text"
                          value="{{$data->zip_code}}"
                        />
                    </td>
                </tr>
                <tr>
                  <td>
                      <label for="">Insurance:</label> 
                      <input
                        style="
                          width: 100%;
                          height: 30px;
                          border-radius: 5px;
                          border: 2px solid rgba(0, 0, 0, 0.115);
                          background-color: rgba(1, 52, 89, 0.077);
                        "
                        type="text"
                        value="{{$data->insurance}}"
                      />
                  </td>
                  <td>
                      <label for="">Claim Number:</label>
                      <input
                          style="
                          width: 100%;
                          height: 30px;
                          border-radius: 5px;
                          border: 2px solid rgba(0, 0, 0, 0.115);
                          background-color: rgba(1, 52, 89, 0.077);
                          "
                          type="text"
                          value="{{$data->claim_number}}"
                          />
                  </td>
                  <td colspan="2">
                      <label for="">Policy Number:</label> 
                      <input
                      style="
                      width: 100%;
                      height: 30px;
                      border-radius: 5px;
                      border: 2px solid rgba(0, 0, 0, 0.115);
                      background-color: rgba(1, 52, 89, 0.077);
                      "
                      type="text"
                      value="{{$data->policy_number}}"
                      />
                  </td>
              </tr>
            </tbody>
        </table>
    <!-- section4 -->
    <table style="max-width: 1200px; margin: auto;">
      <thead>
        <tr>
            <th>
              <h1 style="text-align: left; font-family: Arial; margin-top: 20px; padding-bottom: 10px;">ACKNOWLEDGEMENTS
              </h1>
            </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <ul style="list-style-type: disc; padding-left: 20px;">
                <li style="font-family: Arial; padding-bottom: 10px;">
                    <p style="font-family: Arial; padding-block: 10px;">
                        Customer affirms and acknowledges ownership of the property situated
                        at the address provided above and asserts authorization and competency
                        to engage in this Agreement.
                    </p>
                </li>
                <li style="font-family: Arial; padding-bottom: 10px;">
                    <p  style="font-family: Arial; padding-block: 10px;">
                        PeakTek Roofing & Restoration ("PeakTek") will supply materials,
                        equipment, and labor, either directly or through independent
                        contractors, as outlined herein (the "Work").
                    </p>
                </li>
                <li style="font-family: Arial; padding-bottom: 10px;">
                    <p  style="font-family: Arial;padding-block: 10px;">
                        Customer has enlisted PeakTek as the chosen contractor due to
                        PeakTek's expertise and its licensing, bonding, and insurance.
                        Customer comprehends that PeakTek will proceed with the Work and
                        associated tasks related to the insurance claim included within this
                        agreement, relying on this agreement as a foundation.
                    </p>
                </li>
            </ul>
          </td>
        </tr>
      </tbody>
    </table>
    <table style="max-width: 1200px; margin: auto;">
      <thead>
        <tr>
            <th>
              <h1 style="text-align: left; font-family: Arial; margin-top: 20px; padding-bottom: 10px;">INSURANCE
              </h1>
            </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <ul style="list-style-type: disc; padding-left: 20px;">
                <li style="font-family: Arial; padding-bottom: 10px;">
                    <p style="font-family: Arial; padding-block: 10px;">
                    Both parties acknowledge that, except for deductible and upgrade costs, payment for the Work may be facilitated by the Customer's insurer.
                    However, the Customer acknowledges the necessity to assess any property damage separately. The Customer grants authorization for PeakTek
                    Roofing & Restoration (hereafter referred to as "PeakTek") to allocate its time and expertise to aid in evaluating damages and providing repair or
                    replacement recommendations, potentially covered under an insurance claim, subject to the approval of the Customer's insurer.

                    </p>
                </li>
                <li style="font-family: Arial; padding-bottom: 10px;">
                    <p  style="font-family: Arial; padding-block: 10px;">
                        The Customer designates PeakTek as the sole contractor responsible for completing the work, ensuring compliance with all local, state, federal,
                        code, and safety regulations. Additionally, the Customer accepts responsibility for any expenses not covered by insurance, including but not limited
                        to work portions, deductibles, enhancements, depreciation, or additional work requested by the Customer. Such payments must be settled within
                        thirty (30) days of written notification from PeakTek.
                    </p>
                </li>
            </ul>
          </td>
        </tr>
      </tbody>
    </table>
    <table style="max-width: 1200px; margin: auto;">
      <thead>
        <tr>
            <th>
              <h1 style="text-align: left; font-family: Arial; margin-top: 20px; padding-bottom: 10px;">PRICING
              </h1>
            </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <ul style="list-style-type: disc; padding-left: 20px;">
                <li style="font-family: Arial; padding-bottom: 10px;">
                    <p style="font-family: Arial; padding-block: 10px;">
                        "Price Agreeable" encompasses all funds paid or agreed upon as outlined in the Claim, including but not limited to the Insurance Deductible, Actual
                        Cash Value, Replacement Cost Value, Recoverable Depreciation, Supplements, change orders, profit, overhead, markups, and/or margin
                    </p>
                </li>
                <li style="font-family: Arial; padding-bottom: 10px;">
                    <p  style="font-family: Arial; padding-block: 10px;">
                        The undersigned parties hereby consent to the terms stipulated in the aforementioned Agreement and any supplementary terms and conditions
                        detailed on the reverse side herein
                    </p>
                </li>
                <li style="font-family: Arial; padding-bottom: 10px;">
                    <p  style="font-family: Arial;padding-block: 10px;">
                        IN WITNESS WHEREOF, the undersigned parties have willingly and voluntarily caused the execution of this Agreement, either individually or by
                        their duly authorized representative, on the effective date of acceptance indicated below.
                    </p>
                </li>
                <li style="font-family: Arial; padding-bottom: 10px;">
                    <p  style="font-family: Arial;padding-block: 10px;">
                        Customer has enlisted PeakTek as the chosen contractor due to
                        Terms: By signing the Agreement, the homeowner authorizes PeakTek Roofing & Restoration to pursue the property owner’s best interest for
                        project replacement or repair at a "price agreeable" to both the insurance company and PeakTek, without additional costs to the property owner
                        beyond the deductible and any upgrades. Once the "price agreeable" is determined, it shall constitute the final contract price, and the property
                        owner authorizes PeakTek to procure labor and materials in accordance with the agreed-upon price and specifications outlined herein and on the
                        reverse side of this document to carry out the replacement or repair. Any funds received from the insurance company for contractor overhead,
                        profit, and/or cost increase supplements will be remitted to PeakTek Roofing & Restoration.
                    </p>
                </li>
            </ul>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Page Break -->
    <div class="page-break"></div>
    <!-- End -->

    <table style="max-width: 1200px; margin: auto; margin-top: 50px;">
        <thead>
            <tr>
                <th colspan="6">
                    <h1 style="text-align: left; margin-bottom: 20px;">Customer Information</h1>
                </th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <label for="">Customer Signature:</label> 
                    <input
                    style="
                        width: 100%;
                        height: 30px;
                        border-radius: 5px;
                        border: 2px solid rgba(0, 0, 0, 0.115);
                        background-color: rgba(1, 52, 89, 0.077);
                    "
                    type="text"
                    value="{{$data->customer_signature}}"
                    />
                </td>
                <td>
                    <label for="">Printed Name:</label>
                    <input
                        style="
                        width: 100%;
                        height: 30px;
                        border-radius: 5px;
                        border: 2px solid rgba(0, 0, 0, 0.115);
                        background-color: rgba(1, 52, 89, 0.077);
                        "
                        type="text"
                        value="{{$data->customer_printed_name}}"
                        />
                </td>
                <td>
                    <label for="">Date Signed:</label> 
                    <input
                    style="
                    width: 100%;
                    height: 30px;
                    border-radius: 5px;
                    border: 2px solid rgba(0, 0, 0, 0.115);
                    background-color: rgba(1, 52, 89, 0.077);
                    "
                    type="text"
                    value="{{$data->customer_date}}"
                    />
                </td>
                <td></td>
                <td></td>
            </tr>
            <tr>
            <td>
                <label for="">Company Representative Signature:</label> 
                <input
                    style="
                    width: 100%;
                    height: 30px;
                    border-radius: 5px;
                    border: 2px solid rgba(0, 0, 0, 0.115);
                    background-color: rgba(1, 52, 89, 0.077);
                    "
                    type="text"
                    value="{{$data->company_signature}}"
                />
            </td>
            <td>
                <label for="">Printed Name:</label>
                <input
                    style="
                    width: 100%;
                    height: 30px;
                    border-radius: 5px;
                    border: 2px solid rgba(0, 0, 0, 0.115);
                    background-color: rgba(1, 52, 89, 0.077);
                    "
                    type="text"
                    value="{{$data->company_printed_name}}"
                    />
            </td>
            <td>
                <label for="">Date Signed:</label> 
                <input
                style="
                width: 100%;
                height: 30px;
                border-radius: 5px;
                border: 2px solid rgba(0, 0, 0, 0.115);
                background-color: rgba(1, 52, 89, 0.077);
                "
                type="text"
                value="{{$data->company_date}}"
                />
            </td>
            <td></td>
            <td></td>
        </tr>
        </tbody>
    </table>
  <table style="max-width: 1200px; margin: auto; margin-top: 50px;">
    <tr>
      <td>
        <ul style="list-style-type: disc; padding-left: 20px;">
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 10px; font-weight: bold;">
                    This Contract and any agreements entered into between PeakTek Roofing & Restoration (hereinafter referred to as the “Company” or
                    “PeakTek”) and the customer(s) identified herein on the Agreement’s page 1 shall adhere to all applicable copyright laws, regulations, and
                    ordinances in the state of record.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 10px; font-weight: bold;">
                    Indemnity Statement
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 10px; font-weight: bold;">
                    The homeowner shall not be held liable for any injuries, accidents, or damages that occur on the property during the re-roof project. PeakTek
                    Roofing & Restoration assumes full responsibility for all job site accidents and incidents, ensuring coverage through our workers'
                    compensation insurance and any other applicable insurance policies.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    This Contract and any agreements made pursuant thereto between PeakTek Roofing & Restoration (referred to as the “Co.” or “Company” or “PeakTek”)
                    and the customer(s) named herein on the Agreement’s page 1 will be subject to all appropriate laws, regulations, and ordinances in the state of record.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    In the event of default in payment under this contract, charges shall be applied from the date thereof at a rate equivalent to the greater of one and one-half
                    percent (1.5%) per month (18% per annum), with a minimum charge of $20.00 per month or the maximum amount permitted by law. If legal action is
                    required for collection, the Customer shall bear all attorney's fees and associated costs.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    PeakTek Roofing & Restoration ("the Company") disclaims responsibility for damages arising from rain, fire, tornadoes, windstorms, or other perils unless
                    specifically agreed upon in writing before commencing work or covered by homeowner's insurance or business risk insurance.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    Unless stated otherwise in the contract, replacement of deteriorated decking, fascia boards, roof jacks, ventilators, flashing, or other materials is not
                    included and will be billed separately on a time and material basis
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    After 90 days, the Company reserves the right to adjust the price based on current costs, such as material expenses
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    The Company shall not be liable for performance failures due to uncontrollable circumstances such as labor disputes, strikes, fires, pandemics, wars,
                    riots, protests, supply shortages, weather, or other events beyond its control.  
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    The Company is not responsible for any damage on or below the roof due to leaks, excessive wind-driven rain, ice, or hail during the period of warranty.
                    EXCESSIVE WIND is 50 M.P.H. or faster.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    In case of material reorder or restocking due to customer cancellation, a restocking fee equal to fifteen percent (15%) of the contract price will be applied
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    The Company disclaims liability for any mold, mildew or interior damage resulting from prior leaks.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    Cancellation of this contract later than 5 days from execution incurs a fee of $200.00 per person per hour expended in property evaluation or $2,000.00,
                    whichever is greater, as liquidated damages.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    Once work has commenced, this contract cannot be canceled except by mutual written agreement.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    If any provision of this contract is deemed invalid or unenforceable, the remaining provisions shall remain unaffected
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    Any verbal communications outside this contract are deemed immaterial and not relied upon by either party.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    During work, the customer's homeowner's insurance is responsible for interior damage if the Company has protected the roof adequately
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    The Company disclaims responsibility for any damage to solar panels during repairs
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    The Company is not liable for construction issues of the customer's home unless notified and specified in writing.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    The Company disclaims responsibility for damage from leaks from skylights unless it completes the skylight replacement.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    Warranty periods are specified for different types of work, and extended service warranties are available for additional charges.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    Payments are to be made in accordance with the agreed terms, with insurance checks to be endorsed to the Company promptly.
                </p>
            </li>
        </ul>
      </td>
    </tr>
  </table>
  <table  style="max-width: 1200px; margin: auto; margin-top: 10px;">
    <tr>
      <td>
        <ul style="list-style-type: disc; padding-left: 20px;">
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    Additional labor or material costs due to hidden conditions or building code issues require a signed change order.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    The Company is not responsible for fixing existing framing issues unless necessary, on a time and material basis
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    All insurance proceeds for approved repairs are to be paid to the Company unless stated otherwise in writing.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    Customers are required to pay in full upon completion of the project.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    Customers have the right to cancel the contract within three business days of being notified by their insurer that the claim or contract is not covered,
                    except for emergency repairs already completed.
                </p>
            </li>
            <li style="font-family: Arial; padding-bottom: 10px;">
                <p  style="font-family: Arial;padding-block: 20px;">
                    Customers must inform the Company of any property covenants, conditions, or restrictions, as the Company is not liable unless notified in writing and
                    reference is made in the contract terms.
                </p>
            </li>
        </ul>
      </td>
    </tr>
    <tr>
                <td>
                    <span style="border: 1px solid rgb(0, 0, 0); width: 200px; display: inline-block; margin-top: 50px;"></span><span>the undersigned, hereby cancel this transaction as of </span>  <span style="border: 1px solid rgb(0, 0, 0); width: 50px; display: inline-block;"></span><span>/</span>
                    <span style="border: 1px solid rgb(0, 0, 0); width: 50px; display: inline-block;"></span><span>/</span> <span style="border: 1px solid rgb(0, 0, 0); width: 80px; display: inline-block;"></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span style="margin-top: 20px;display: inline-block;">Customer Signature:</span> <span style="border: 1px solid rgb(0, 0, 0); width: 200px; display: inline-block;"><img src="{{ public_path($data->sign_image_url) }}"></span>
                </td>
            </tr>
  </table>
  </section>

  </body>
</html>