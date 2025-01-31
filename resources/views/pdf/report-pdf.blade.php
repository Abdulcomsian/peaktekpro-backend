<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ is_string($report->title) ? $report->title : 'Untitled Report' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h2 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .section p {
            margin: 5px 0;
        }

        .image {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            /* Ensure wrapping on smaller screens */
        }

        .image .primary-image,
        .image .secondary-image {
            width: 48%;
            /* Set width for each image container */
        }

        .image img {
            width: 100%;
            /* Make sure the image takes the full width of the container */
            height: auto;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="title">{{ is_string($report->title) ? $report->title : 'Untitled Report' }}</div>

    @foreach ($report->reportPages as $page)
    <div class="section">
        @if (isset($page->pageData->json_data))
        @php
        $jsonData = $page->pageData->json_data;
        @endphp
        @switch($page->slug)
        @case('introduction')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="intro-text">
            <h3>Text</h3>
            {!! $jsonData['intro_text'] ?? 'No intro text available.' !!}
        </div>

        <div class="report-title">
            <h3>Report Title</h3>
            <p>{{ $jsonData['report_title'] ?? 'No title available.' }}</p>
        </div>

        <div class="report-date">
            <h3>Report Date</h3>
            <p>{{ $jsonData['report_date'] ?? 'No date available.' }}</p>
        </div>

        <div class="image">
            @if (isset($jsonData['primary_image']))
            <div class="primary-image">
                <h3>Primary Image</h3>
                <img src="{{ asset('storage/' . $jsonData['primary_image']['path']) }}" alt="Primary Image" />
            </div>
            @endif

            @if (isset($jsonData['secondary_image']))
            <div class="secondary-image">
                <h3>Secondary Image</h3>
                <img src="{{ asset('storage/' . $jsonData['secondary_image']['path']) }}" alt="Secondary Image" />
            </div>
            @endif
        </div>

        <div class="company-details">
            <h3>Company Details</h3>
            <p><strong>Address:</strong> {{ $jsonData['company_address'] ?? 'No address available.' }}</p>
            <p><strong>Province:</strong> {{ $jsonData['company_province'] ?? 'No province available.' }}</p>
            <p><strong>Postal Code:</strong> {{ $jsonData['company_postal_code'] ?? 'No postal code available.' }}</p>
        </div>
        @break

        @case('repairability-assessment')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="roof-repair-limitations">
            {!! $jsonData['roof_repair_limitations_text'] ?? 'No repair limitations text available.' !!}
        </div>

        <div class="repairability-assessment-images">
            @foreach ($jsonData['repariability_assessment_images'] ?? [] as $image)
            <div class="image">
                <img src="{{ asset('storage/' . $image['path']) }}" alt="{{ $image['file_name'] }}" />
            </div>
            @endforeach
        </div>
        @break

        @case('repairability-or-compatibility-photos')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="comparison-sections">
            <h4>Title</h4>
            <p>{{ $jsonData['comparision_sections'][0]['title'] ?? 'No title available.' }}</p>
            @foreach ($jsonData['comparision_sections'][0]['items'] ?? [] as $item)
            <div class="comparison-item">
                <h4>Item {{ $loop->iteration }}</h4>

                <div class="content">
                    {!! $item['content'] ?? 'No content available.' !!}
                </div>

                <div class="image">
                    <img src="{{ $item['image']['path'] ?? '#' }}" alt="{{ $item['image']['file_name'] ?? 'No image available.' }}" />
                </div>
            </div>
            @endforeach
        </div>
        @break

        @case('product-compatibility')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="product-compatibility-section">
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

        @case('unfair-claims-practices')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="unfair-claims-section">
            <div class="file-item">
                <a href="{{ asset('storage/' . $jsonData['unfair_claim_file']['path'] ?? '#') }}" download>
                    Download {{ $jsonData['unfair_claim_file']['file_name'] ?? 'No file available.' }}
                </a>
            </div>
        </div>
        @break

        @case('applicable-codes-guidelines')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="applicable-codes-guidelines-section">
            <div class="text-content">
                {!! $jsonData['applicable_code_guidelines_text'] ?? 'No guidelines text available.' !!}
            </div>
        </div>
        @break

        @case('quote-details')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="quote-details-section">
            @foreach($jsonData['sections'] ?? [] as $section)
            <div class="section">
                <h4>{{ $section['title'] ?? 'No title available.' }}</h4>
                <p>Status: {{ $section['isActive'] == 'true' ? 'Active' : 'Inactive' }}</p>
                <p>Total for Section: ${{ number_format($section['sectionTotal'], 2) ?? '0.00' }}</p>

                <div class="section-items">
                    @foreach($section['sectionItems'] ?? [] as $item)
                    <div class="item">
                        <p><strong>Description:</strong> {{ $item['description'] ?? 'No description available.' }}</p>
                        <p><strong>Quantity:</strong> {{ $item['qty'] ?? '0' }}</p>
                        <p><strong>Price:</strong> ${{ number_format($item['price'] ?? 0, 2) }}</p>
                        <p><strong>Line Total:</strong> ${{ number_format($item['lineTotal'] ?? 0, 2) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            <div class="grand-total">
                <p><stron>Grand Total:</strong> ${{ number_format($jsonData['grand_total'] ?? 0, 2) }}</p>
            </div>
        </div>
        @break

        @case('authorization-page')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="authorization-page-section">
            @foreach($jsonData['sections'] as $section)
            <div class="section">
                <h4>{{ $section['title'] }}</h4>
                <p>Total for Section: ${{ number_format($section['sectionTotal'], 2) }}</p>

                <div class="section-items">
                    @foreach($section['sectionItems'] as $item)
                    <div class="item">
                        <p><strong>Description:</strong> {{ $item['description'] }}</p>
                        <p><strong>Quantity:</strong> {{ $item['qty'] }}</p>
                        <p><strong>Price:</strong> ${{ number_format($item['price'], 2) }}</p>
                        <p><strong>Line Total:</strong> ${{ number_format($item['lineTotal'], 2) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            <div class="authorization-disclaimer">
                <p>{{ $jsonData['authorization_disclaimer'] }}</p>
            </div>

            <div class="grand-total">
                <p><stron>Grand Total:</strong> ${{ number_format($jsonData['authorization_sections_grand_total'], 2) }}</p>
            </div>
        </div>
        @break


        @case('terms-and-conditions')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="terms-and-conditions">
            {!! $jsonData['terms_and_conditions_text'] !!}
        </div>
        @break


        @case('warranty')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="terms-and-conditions">
            {!! $jsonData['warranty_text'] !!}
        </div>
        @break

        @case('')
        <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>
        <div class="custom-page-section">
            <!-- Custom Page Text -->
            @if(isset($jsonData['custom_page_text']))
            <div class="custom-page-text">
                {!! $jsonData['custom_page_text'] !!}
            </div>
            @endif

            <!-- Custom Page File -->
            @if(isset($jsonData['custom_page_file']))
            <div class="custom-page-file">
                <a href="{{ asset('storage/' . $jsonData['custom_page_file']['path']) }}" download class="btn btn-primary">
                    Download {{ $jsonData['custom_page_file']['file_name'] }}
                </a>
            </div>
            @endif
        </div>
        @break
        @endswitch
        @endif
    </div>
@endforeach


</body>

</html>
