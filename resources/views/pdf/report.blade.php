<html lang="en">

<head>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: sans-serif;
            color: #333;
            margin: 0; /* Ensure no default body margin */
            padding: 0; /* Ensure no default body padding */
        }

        header {
            position: fixed;
            top: 0px;
            left: 0;
            right: 0;
            height: 450px;
            font-size: 18px !important;
            color: white;
            text-align: center;
            line-height: 30px;
            display: flex; /* Ensure flex layout is applied */
            align-items: center; /* Vertically center content */
            justify-content: space-between; /* Space out the boxes */
            background-color: transparent; /* Ensure no background color interferes */
        }

        .header-box {
            flex: 1;
            height: 100%; /* Ensure full height */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-box.blue {
            background-color:rgb(55, 179, 184);
            color: white; /* Ensure text is visible */
        }

        .header-box.white {
            background-color: black;
        }

        .header-box.white img {
            max-height: 40px; /* Adjust logo height as needed */
            width: auto; /* Maintain aspect ratio */
        }

        .company-info {
            text-align: left;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .primary-image {
            width: 100%;
            max-width: 1500px; /* Adjust the image size */
            display: block;
            margin: 0 auto;
        }

        .primary-image img {
            width: 100%; /* Ensure the image takes up the full width */
            height: auto; /* Maintain aspect ratio */
            /* border: 2px solid #ccc;
            border-radius: 10px;  */
        }

        footer {
            position: fixed;
            bottom: -110px;
            left: 0;
            right: 0;
            height: 70px;
            font-size: 14px !important;
            background-color:rgba(19, 16, 16, 0.08);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            margin: 0; /* Remove any extra space */
        }

        .footer-left {
            text-align: left;
            margin: 0;
        }

        .footer-divider {
            width: 1px;
            background-color: white;
            height: 100%;
            margin: 0 10px;
        }

        .footer-right {
            text-align: right;
            margin: 0;
        }

        .primary-image {
            margin-bottom: 20px; /* Add spacing below the image */
            text-align: center; /* Center the image and text */
        }

        .primary-image h3 {
            font-size: 24px; /* Larger font size for the heading */
            color: #333; /* Dark gray color for the text */
            margin-bottom: 10px; /* Add spacing below the heading */
        }

        /* second section css */
        .repairability-assessment-images {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .image {
            flex: 1;
            /* margin: 5px; */
            min-width: 200px; /* Optional: You can change the min-width depending on your design */
        }

        .roof-repair-limitations {
            margin-top: 70px;
            margin-bottom: 10px;
        }

        img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        /* .section {
            page-break-after: always; 
            break-after: page; 
            page-break-inside: avoid; 
        } */

        /* .section h2 {
            color: rgb(55, 179, 184);
            margin-left: 20px; 
            margin-right: 20px; 
        } */

        /* .section p, .section .content,h4,a,h3 {
            margin-left: 20px; 
            margin-right: 20px; 
        } */

        /* .section img {
            width: 100%; 
            margin-left: 0; 
            margin-right: 0; 
        } */
        /* / */
        /* .section, .section-items, .item {
            page-break-inside: avoid; 
            page-break-before: auto;  
            page-break-after: auto;
        } */

        .grand-total {
            page-break-inside: avoid; /* Keep grand total on the same page */
            font-weight: bold;
            margin-top: 10px;
        }

        .section {
            page-break-after: always;
            page-break-inside: avoid;

        }

        .section-items {
            display: inline-block;
            width: 100%;
        }

        h2,h4,p,a {
            margin: 20px;
        }
        .product-compatibility-text{
            margin-left: 10px;
            font-size: 12px;
            color: black

        }

        .pdf-placeholder {
            color: transparent;
            font-size: 0;
        }

    </style>

<!-- <style>
        @page {
            margin: 0;
        }

        body {
            font-family: sans-serif;
            color: #333;
            margin: 0;
            padding: 450px 0 70px 0; /* Reserve space for header and footer */
        }

        header {
            height: 450px;
            margin-top: -450px; /* Pull header into the top padding */
        }

        .header-content {
            width: 100%;
            background-color: rgb(55, 179, 184);
            color: white;
            padding: 20px;
        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 70px;
            background-color: rgb(121, 128, 128);
            color: white;
        }

        /* Keep existing styles for other elements */
        .section {
            page-break-after: always;
        }

        /* Adjust main content to account for header/footer */
        main {
            margin: 20px;
        }
</style> -->
</head>

<body>
@foreach ($report->reportPages as $page)
    <div class="section">
        @if (isset($page->pageData->json_data))
        @php
        $jsonData = $page->pageData->json_data;
        @endphp
        @switch($page->slug)
        @case('introduction')

        <!-- Define header and footer blocks before your content -->
        <header>
            <table width="100%" style="border-collapse: collapse;">
                <tr>
                    <!-- Left section -->
                    <td style="width: 50%; background-color:rgb(55, 179, 184); color: white; text-align: left; padding: 5px;">
                        <!-- <h2>{{ is_string($report->title) ? $report->title : 'Untitled Report' }}</h2> -->
                        <h2 style="color:white;">{{ $jsonData['report_title'] ?? 'No title available.' }}</h2>
                        <p>{{ $jsonData['report_date'] ?? 'No date available.' }}</p>
                    </td>
                    <!-- Right section -->
                    <td style="width: 50%; background-color: white; text-align: right; padding: 10px; vertical-align:middle;">
                        <img src="{{ public_path('assets/logo/logoTest.PNG') }}" style="width:420px; height:auto;" alt="Logo">
                    </td>
                </tr>
            </table>
        </header>

        <footer style="position: fixed; bottom: 0; width: 100%; background-color:rgb(121, 128, 128);">
            <table width="100%" style="color: white; padding: 10px; font-size: 12px; border-collapse: collapse;">
                <tr>
                    <!-- Left Content -->
                    <td style="width: 28%; text-align: center; font-weight: bold;">
                        Thank you for choosing<br>
                        PeakTek Roofing & Restoration
                    </td>

                    <!-- Vertical Divider -->
                    <td style="width: 4%; border-left: 2px solid white;"></td>

                    <!-- Right Content -->
                    <td style="width: 68%; text-align: center;">
                        <strong>admin@peaktekpro.com</strong><br>
                        (629) 333-6170
                    </td>
                </tr>
            </table>
        </footer>

        <!-- Wrap the content of your PDF inside a main tag -->
    <main>
        
        <div class="image">
            @if (isset($jsonData['primary_image']))
            <div class="primary-image">
                <!-- <img src="{{ public_path('storage/template-files/introduction/1738234447_sample_1920×1280.jpeg') }}" alt="Primary Image" style="width: 100%; max-width: 800px; display: block; margin: 0 auto;" /> -->
                <img src="{{ public_path('storage/' . $jsonData['primary_image']['path']) }}" alt="Primary Image" style="width: 100%; max-width: 1800px; height:800px; display: block;" />

            </div>
            @endif
        </div>


        <!-- //this is table  -->
        <table style="width: 100%; border: none; padding: 10px;">
            <tr>
                <td style="width: 50%; padding-right: 10px; vertical-align: top; font-size:20px;">
                    <!-- Text Column -->
                    <h3 style="margin-left:15px;">{{ $jsonData['company_name'] ?? 'No Name of Company available.' }}</h3>
                    <p style="margin-bottom: 2px; line-height: 2px;">{{ $email }}</p>
                    <p style="margin-bottom: 40px; line-height: 2px;">{{ $phone }}</p> <!-- Add space after phone -->
                    
                    <p style="margin-bottom: 2px; line-height: 2px;">{{ $jsonData['company_address'] ?? '' }}</p>
                    <p style="margin-bottom: 2px; line-height: 2px;">{{ $jsonData['company_province'] ?? '' }}</p>
                    <p style="line-height: 2px;">{{ $jsonData['company_postal_code'] ?? '' }}</p>

                </td>
                <td style="width: 50%; vertical-align: middle; text-align: center;">
                    <!-- Image Column -->
                    <img src="{{ public_path('assets/logo/secondaryImage.PNG') }}" 
                        alt="Secondary Image" 
                        style="width: 50%; height: auto; object-fit: cover; margin-bottom: 2px; display: block; margin: 0 auto;" />
                </td>

            </tr>
        </table>

        <!-- first section -->
        @case('introduction')
           <div style="  height: 70px; width: 100%; background-color: rgb(33, 166, 228);   position: relative;">
           <h2 style=" padding-left: 10px; 
            color: white; 
              margin: 0;
  position: absolute;
  top: 50%;
  -ms-transform: translateY(-50%);
  transform: translateY(-50%);
            ">
                {{ is_string($page->name) ? $page->name : 'Unnamed Page' }}
            </h2>
           </div>
            <!-- <h2 style="margin-bottom: 5px; background-color:rgb(33, 166, 228); color:white;width:100%; height:70px;padding-top:30px;padding-left:5px;">{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2> -->
            <div class="roof-repair-limitations" style="font-size:12px; font-family:sans-serif; margin-top:10px; padding-left:20px">
                {!! $jsonData['intro_text'] ?? 'No introduction text available.' !!}
            </div>
        @break

        <!-- second section -->

        @case('repairability-assessment')
           <div style=" margin-top:-20px; height: 70px; width: 100%; background-color: rgb(33, 166, 228);">
           <h2 style=" padding-left: 10px; 
            color: white;  padding-top: 25px; ">
                {{ is_string($page->name) ? $page->name : 'Unnamed Page' }}
            </h2>
           </div>
            <!-- <h2 style="margin-bottom: 5px; background-color:rgb(33, 166, 228); color:white;width:100%; height:70px;padding-top:30px;padding-left:5px;">{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2> -->
            <div class="roof-repair-limitations" style="font-size:12px; font-family:sans-serif; margin-top:10px; padding-left:20px; padding-right:20px">
                {!! $jsonData['roof_repair_limitations_text'] ?? 'No repair limitations text available.' !!}
            </div>

            <div class="repairability-assessment-images">
                

                @if (isset($jsonData['repariability_assessment_images']))
                <div class="image">
                    <!-- <img src="{{ public_path('storage/template-files/introduction/1738234447_sample_1920×1280.jpeg') }}" alt="Primary Image" style="width: 100%; max-width: 800px; display: block; margin: 0 auto;" /> -->
                    <img src="{{ public_path('storage/' . $jsonData['repariability_assessment_images']['path']) }}" alt="Primary Image" style="width: 100%; max-width: 1800px; height:800px; display: block;" />
                </div>
                @endif
            </div>
        @break

        <!-- third Section -->

        @case('repairability-or-compatibility-photos')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="comparison-sections" style="font-size:9.6px; font-family:sans-serif;">
            <h4>Title</h4>
            <p>{{ $jsonData['comparision_sections'][0]['title'] ?? 'No title available.' }}</p>
            @foreach ($jsonData['comparision_sections'][0]['items'] ?? [] as $item)
            <div class="comparison-item">
                <h4>Item {{ $loop->iteration }}</h4>

                <div class="content">
                    {!! $item['content'] ?? 'No content available.' !!}
                </div>

                <div class="image">
                    <img src="{{ public_path('storage/' . str_replace('http://127.0.0.1:8000/storage/', '', $item['image']['path'] ?? '')) }}" alt="repairability-or-compatibility-photos" />
                </div>

            </div>
            @endforeach
        </div>
        @break
       
        <!-- <div style="page-break-after: always; break-after: page;"></div> -->

        <!-- <div class="section"> -->

        @case('product-compatibility')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="product-compatibility-section">
            <div class="product-compatibility-text">
                {!! $jsonData['product_compatibility_text'] ?? 'No compatibility text available.' !!}
            </div>

            <!-- Placeholder for product compatibility PDF -->
            <div class="pdf-placeholder product-compatibility-placeholder" data-section="product-compatibility">
                [product-compatibility-placeholder]
            </div>
        </div>
        @break
        <!-- </div> -->

        <!-- <div style="page-break-after: always; break-after: page;"></div> -->

        @case('unfair-claims-practices')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="unfair-claims-section">
            <div class="pdf-placeholder" data-section="unfair-claims-practices">
                [unfair-claims-practices-placeholder]
            </div>
        </div>
        @break
        <!-- 4th section -->
        <!-- @case('product-compatibility')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="product-compatibility-section" style="font-size:9.6px; font-family:sans-serif;">
            <div class="product-compatibility-text">
                {!! $jsonData['product_compatibility_text'] ?? 'No compatibility text available.' !!}
            </div>
        
            @foreach ($jsonData['product_compatibility_files'] ?? [] as $file)
            <div class="file-item">
                <a href="{{ asset('storage/' . $file['path']) }}" download>
                    Download {{ $file['file_name'] ?? 'No file available.' }}
                </a>
            </div>
            @endforeach
        </div>
        @break
    -->

       <!-- 5th Section  -->
        <!-- @case('unfair-claims-practices')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="unfair-claims-section" style="font-size:9.6px; font-family:sans-serif;">
            <div class="file-item">
                <a href="{{ asset('storage/' . $jsonData['unfair_claim_file']['path'] ?? '#') }}" download>
                    Download {{ $jsonData['unfair_claim_file']['file_name'] ?? 'No file available.' }}
                </a>
            </div>
        </div>
        @break -->


        <!-- 6th Section -->

        @case('applicable-codes-guidelines')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="applicable-codes-guidelines-section" style="font-size:9.6px; font-family:sans-serif;">
            <div class="text-content">
                {!! $jsonData['applicable_code_guidelines_text'] ?? 'No guidelines text available.' !!}
            </div>
        </div>
        @break

        <!-- 7th Section -->

        @case('quote-details')
    <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
    <div class="quote-details-section" style="font-size:9.6px; font-family:sans-serif; margin: 0 20px;">

        @foreach($jsonData['sections'] ?? [] as $section)
            <h4>Section Name: {{ $section['title'] ?? 'No title available.' }}</h4>
            <p>Status: {{ $section['isActive'] == 'true' ? 'Active' : 'Inactive' }}</p>
            <p>Section Total: ${{ number_format($section['sectionTotal'], 2) ?? '0.00' }}</p>

            <table border="1" cellspacing="0" cellpadding="5" width="100%" style="border-collapse: collapse; margin-bottom: 15px;">
                <thead style="background-color: #f2f2f2;">
                    <tr>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Price ($)</th>
                        <th>Line Total ($)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($section['sectionItems'] ?? [] as $item)
                        <tr>
                            <td>{{ $item['description'] ?? 'No description available.' }}</td>
                            <td style="text-align: center;">{{ $item['qty'] ?? '0' }}</td>
                            <td style="text-align: right;">{{ number_format($item['price'] ?? 0, 2) }}</td>
                            <td style="text-align: right;">{{ number_format($item['lineTotal'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

        <div class="grand-total" style="text-align: right; font-weight: bold; margin-top: 10px;">
            <p>Grand Total: ${{ number_format($jsonData['grand_total'] ?? 0, 2) }}</p>
        </div>
    </div>
@break

        

        <!-- 8th Section -->
        @case('authorization-page')
        <h2 style="margin-left: 20px; margin-right: 20px;">{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>

        <div class="authorization-page-section" style="font-size:9.6px; font-family:sans-serif; margin: 0 20px;"> <!-- Added margin here -->

            @foreach($jsonData['sections'] ?? [] as $section)
                <h4 style="margin-top: 10px;">{{ $section['title'] ?? 'No Title Available' }}</h4>
                <p>Total for Section: ${{ number_format($section['sectionTotal'], 2) ?? '0.00' }}</p>

                <table border="1" cellspacing="0" cellpadding="5" width="100%" style="border-collapse: collapse; margin-bottom: 15px;">
                    <thead style="background-color: #f2f2f2;">
                        <tr>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Price ($)</th>
                            <th>Line Total ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section['sectionItems'] ?? [] as $item)
                            <tr>
                                <td>{{ $item['description'] ?? 'No description available.' }}</td>
                                <td style="text-align: center;">{{ $item['qty'] ?? '0' }}</td>
                                <td style="text-align: right;">{{ number_format($item['price'] ?? 0, 2) }}</td>
                                <td style="text-align: right;">{{ number_format($item['lineTotal'] ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach

            <div class="authorization-disclaimer" style="margin-top: 15px; font-style: italic;">
                <p>{{ $jsonData['authorization_disclaimer'] ?? 'No disclaimer available.' }}</p>
            </div>

            <div class="grand-total" style="text-align: right; font-weight: bold; margin-top: 10px;">
                <p>Grand Total: ${{ number_format($jsonData['authorization_sections_grand_total'] ?? 0, 2) }}</p>
            </div>
        </div>
         @break


        <!-- 9th Section -->
        @case('terms-and-conditions')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="terms-and-conditions" style="font-size:9.6px; font-family:sans-serif;">
            {!! $jsonData['terms_and_conditions_text'] !!}
        </div>
        @break

        <!-- 10th Section -->
        @case('warranty')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="terms-and-conditions" style="font-size:9.6px; font-family:sans-serif;">
            {!! $jsonData['warranty_text'] !!}
        </div>
        @break

        @case('')
    <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
    <div class="custom-page-section" style="font-size:9.6px; font-family:sans-serif;">
        @if(isset($jsonData['custom_page_text']))
            <div class="custom-page-text">
                {!! $jsonData['custom_page_text'] !!}
            </div>
        @endif

        <!-- Custom Page PDF Placeholder -->
        @if(isset($jsonData['custom_page_file']))
            <div class="pdf-placeholder" data-section="custom-page-{{ $page->order_no }}">
                [custom-page-{{ $page->order_no }}-placeholder]
            </div>
        @endif
    </div>
    @break


        <!-- @case('')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="custom-page-section" style="font-size:9.6px; font-family:sans-serif;">
            @if(isset($jsonData['custom_page_text']))
            <div class="custom-page-text">
                {!! $jsonData['custom_page_text'] !!}
            </div>
            @endif

            @if(isset($jsonData['custom_page_file']))
                <div class="pdf-placeholder" data-section="custom-page-{{ $page->order_no }}">
                    [custom-page-{{ $page->order_no }}-placeholder]
                </div>
            @endif
            @if(isset($jsonData['custom_page_file']))
            <div class="custom-page-file">
                <a href="{{ asset('storage/' . $jsonData['custom_page_file']['path']) }}" download class="btn btn-primary">
                    Download {{ $jsonData['custom_page_file']['file_name'] }}
                </a>
            </div>
            @endif
        </div>
        @break -->
        @endswitch
        @endif
    </div>
@endforeach

    </main>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.16.0/pdf-lib.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const pdfPlaceholders = document.querySelectorAll('.pdf-placeholder');
        pdfPlaceholders.forEach(async (placeholder) => {
            const pdfPath = placeholder.getAttribute('data-pdf-path');
            const pdfBytes = await fetch(pdfPath).then(res => res.arrayBuffer());
            const pdfDoc = await PDFLib.PDFDocument.load(pdfBytes);
            const pages = pdfDoc.getPages();
            pages.forEach(page => {
                const { width, height } = page.getSize();
                const iframe = document.createElement('iframe');
                iframe.src = URL.createObjectURL(new Blob([pdfBytes], { type: 'application/pdf' }));
                iframe.style.width = `${width}px`;
                iframe.style.height = `${height}px`;
                placeholder.appendChild(iframe);
            });
        });
    });
</script>

</html>