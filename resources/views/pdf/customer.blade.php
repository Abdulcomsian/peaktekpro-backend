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
                            style="color:#333" type="text" value="{{$data->job->name}}" />
                    </td>
                    <td>
                        <p>Email</p>
                        <input
                            style="color:#333"
                            type="text"
                            value="{{$data->job->email}}" />
                    </td>
                    <td colspan="2">
                        <p>Phone</p>
                        <input
                            style="color:#333"
                            type="text"
                            value="{{$data->job->phone}}" />
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
                        value="{{$data->insurance}}" />
                </td>
                <td>
                    <p>Claim Number:</p>
                    <input
                        style="color:#333;"
                        type="text"
                        value="{{$data->claim_number}}" />
                </td>
                <td colspan="2">
                    <p>Policy Number:</p>
                    <input
                        style="color:#333;"
                        type="text"
                        value="{{$data->policy_number}}" />
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


        <!-- <div style="display: flex; padding: 10px; align-items: center; color:black;">
            
                <b>This Contract and any agreements entered into between PeakTek Roofing & Restoration <br>
                     (hereinafter referred to as the “Company” or “PeakTek”) and the customer(s) identified herein <br>
                      on the Agreement’s page 1 shall adhere to all applicable copyright laws, regulations, and <br>
                      ordinances in the state of record.</b>
                <p>
              &bull; All contracts are subject to the approval of our credit department and office without exception. <br>
                The person executing this contract must obtain the consent of the officer of the Company for this contract to be effective under any conditions in the state of record.
            </p>
                <p> SHOULD DEFAULT BE MADE IN PAYMENT OF THIS CONTRACT, CHARGES SHALL BE ADDED FROM <br>
                     THE DATE THEREOF AT A RATE EQUAL TO THE GREATER OF ONE- AND ONE-HALF PERCENT (1.5%) PER <br> 
                      MONTH (18% PER ANNUM) WITH A MINIMUM CHARGE OF $20.00 PER MONTH OR THE MAXIMUM <br>
                       AMOUNT ALLOWED BY LAW. IF PLACED IN THE HANDS OF AN ATTORNEY FOR COLLECTION, <br>
                        CUSTOMER SHALL BE RESPONSIBLE FOR ALL ATTORNEY’S FEES AND COSTS. </p>
                <p>
              &bull; The Company shall have no responsibility for damages from rain, fire, tornado, windstorm, or other <br>
                perils, including as is normally contemplated to be covered by HOMEOWNER’S INSURANCE or BUSINESS <br>
                 RISK INSURANCE, or unless specified in writing, made therefor before the commencement of work.
                 </p>
                 <p>
              &bull; The quotation on the face hereof does not include expenses or charges for bond insurance premiums <br>
                 or costs beyond standard insurance coverage, and any such additional expenses, premiums, or costs shall <br>
                  be added to the amount of the contract. (For example, Performance Bonds or Maintenance Bonds.) </p>
                  <p>
              &bull; Replacement of deteriorated decking, fascia boards, roof jacks, ventilators, flashing, or other materials, <br>
                 unless otherwise STATED IN THE CONTRACT, are NOT INCLUDED and will be charged as an extra on a  <br>
                 time and material basis.
                 </p>
                 <p>
              &bull; After 90 days, Company reserves the right to revise the price in accordance with costs in effect at the <br>
                 time. (For example, increases in material cost).</p>
                 <p>
              &bull; The Company shall not be liable for the failure of performance due to labor controversies, strikes, <br>
                 fires, pandemics, wars, riots, protests, supply shortages, labor shortages, weather, inability to obtain <br>
                  materials from usual sources, or any other circumstances beyond the control of the Company, whether of a <br>
                  similar or dissimilar nature.</p>
                  <p>
              &bull; The Company is not responsible for any damage on or below the roof due to leaks, excessive wind- <br>
                driven rain, ice, or hail during the period of warranty. EXCESSIVE WIND is 50 M.P.H. or faster. <br>
              &bull; If material must be reordered or restocked because of cancellation by the Customer, there will be a <br>
                 RESTOCKING FEE equal to fifteen percent (15%) of the contract price. </p>
                 <p>
              &bull; This contract or warranty shall not be assigned except by or with the written permission of the Company.</p>
               <p> &bull; The Company is not responsible for any mold or interior damage resulting from mildew.</p>
               <p> &bull; IF THIS CONTRACT IS CANCELLED BY THE CUSTOMER LATER THAN 5 DAYS from the execution, <br>
                 Customer shall pay the Company a fee for the inspection and construction consulting services provided by <br>
                  PeakTek Roofing & Restoration LLC. By signing this contract, Customer agrees that the appropriate damages <br>
                  for cancellation shall be $200.00 (two hundred dollars) per person per hour expended in evaluating the <br>
                   property or $2,000.00 whichever is greater, as liquidated damages, not as a penalty. <br>
                
                The Company agrees to accept such a reasonable and just compensation for cancellation. <br>
                 For the cancellation to be effective, notice must be sent via certified mail to PeakTek Roofig & Restoration LLC.</p>
                <p>&bull; THIS CONTRACT CANNOT BE CANCELLED ONCE WORK IS COMMENCED EXCEPT BY MUTUAL WRITTEN <br>
                     AGREEMENT OF THE PARTIES.</p>
                     <p>
              &bull; If any provision of this contract should be held to be invalid or unenforceable, the validity and <br>
                 enforceability of the remaining provisions of this contract shall not be affected thereby.</p>
                 <p>
              &bull; ANY REPRESENTATIONS, STATEMENTS, OR OTHER COMMUNICATIONS NOT WRITTEN ON THIS <br>
                 CONTRACT ARE AGREED TO BE <br>
                IMMATERIAL, and not relied on by either party and do not survive the execution of this contract.</p>
                <p>
              &bull; The maximum liability for the Company shall be the original cost of labor and materials for the repair, <br>
                 which Customer agrees shall be a liquidated sum, under any event of default by Company herein.</p>
                 <p>
              &bull; During the duration of the work, the Customer’s homeowner’s insurance will be responsible for any <br>
                 interior damage as long as the Company has taken appropriate action to protect the roof during the repair <br> 
                  of the roof.</p>
                  <p>
              &bull; If there are any solar panels on the roof, the Company will not be responsible for any damage during <br>
                 the repair, so the homeowner agrees to have a solar panel company take the appropriate action to protect it <br>
                  if necessary.</p>
                  <p>
              &bull; The Company is not responsible for the construction problems of your home. If pointed out and notified <br>
                 to our Company, we will try and assist you in correcting them on a timely and material basis.</p>
                 <p>
              &bull; The Company is not responsible for any damage on or below the roof due to leaks from skylights <br>
                unless the Company completes the skylight replacement.</p>
                <p>
              &bull; Warranty is for two (2) years on roof replacement, one (1) year on siding replacement, and one (1) <br>
                 year on gutter repairs. There is no warranty on roof repairs. Extended service warranties are available for  <br>
                 an additional charge. THE WARRANTY IS NON-TRANSFERABLE.</p>
                 <p>
              &bull; Payments are to be made Half-down payment or first insurance check, whichever is greater. The <br>
                 Company reserves the right to bill proportionately based on the percentage of work completed. Further, the <br> 
                 Customer agrees to endorse and turn over to the Company any check received from an insurance company <br>
                 or the third party within seven (7) days of receipt thereof will be considered default. Customer’s violation of <br>
                  this provision shall be considered conversion and entitle the Company to the greater of punitive damages or <br>
                  treble damages.
                  </p>
                  <p>
              &bull; Any hidden conditions or building code-related issues which result in additional labor and/or material <br>
                 costs will require a signed change order to proceed. The Customer understands the Company may issue a <br>
                  stop-work order if the change order is not accepted. (For example, rotten decking, fascia, gas vents, HVAC <br>
                   lines or coils, etc.) The Company is not responsible for damages.</p>
                <p>
              &bull; Customer understands that existing framing issues such as uneven rafter and bowed sheeting are not <br>
                 the responsibility of the Company to fix and will only be repaired if needed and on a time and material <br>
                  basis.</p>
                  <p>
              &bull; Customer understands all insurance proceeds are to be paid to the Company for insurance-approved <br>
                repairs unless noted in writing on the customer agreement or a change order contract.</p>
                <p>
              &bull; ADDITIONALLY, THE COMPANY MAY ENFORCE ITS RIGHT TO PAYMENT BY OTHER MEANS, <br>
                INCLUDING, BUT NOT LIMITED TO, FILING OF A LIEN AGAINST THE PROPERTY OF THE <br>
                 CUSTOMER INVOLVED IN THIS CONTRACT, REPORTING TO APPROPRIATE CREDIT REPORTING <br>
                  AGENCIES, AND ANY OTHER LEGAL REMEDIES AVAILABLE AT LAW.
                  </p>
                  <p>
              &bull; Customer Understands that the Company, subcontracts all dumpster work. Any flat tires due to <br>
                nails left under the dumpster, driveway, or garage damage are the responsibility of the contracted <br>
                 dumpster company.</p>
                 <p>
              &bull; CUSTOMER AGREES TO PAY IN FULL AT THE TIME OF COMPLETION OF EACH CONTRACT <br>

                If you are notified by your insurer that all or part of the claim or contract is not a covered loss under your <br>
                 insurance policy, you may cancel the contract by mailing or delivering a signed and dated copy of this <br>
                 cancellation notice or any other signed and dated written notice to the address listed below, <br>
                  ADMIN@PEAKTEKPRO.COM at any time prior to midnight on the third business day after you have received <br>
                   such notice from your insurer. If you cancel, any payments made by you under the contract, except those for <br>
                    emergency repairs already performed by the Company, will be returned to you within 10 business days of <br>
                    receipt of your cancellation notice.
                </p>
                <p>
              &bull; Customer is responsible for advising the Company of any covenants, conditions, or restrictions for the <br>
                 property. The Company is not responsible for the violation of any covenants, conditions, or restrictions <br>
                  unless Customer notifies the Company in writing and reference is made in the specific terms of this  <br>
                  Agreement.</p>
        </div> -->


        <div style="display: flex; padding: 10px; align-items: center;">
            <div>
                I <span style="border: .1rem solid gray;border-top: none;border-left:none;border-right:none;color:transparent">......................................</span>,the undersigned,hereby cancel this transaction as of <span style="border: .1rem solid gray;border-top: none;border-left:none;border-right:none;color:transparent">.......</span>/<span style="border: .1rem solid gray;border-top: none;border-left:none;border-right:none;color:transparent">.......</span>/<span style="border: .1rem solid gray;border-top: none;border-left:none;border-right:none;color:transparent">............</span>
            </div>
            <div style="margin-top : 1rem;">
                Customer Signature:<span style="border: .1rem solid gray;border-top: none;border-left:none;border-right:none;color:transparent">......................................................................</span>
            </div>
            <!-- Name Field -->



    </main>
</body>

</html>