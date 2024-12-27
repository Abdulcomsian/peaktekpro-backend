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

        .image img {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="title">{{ is_string($report->title) ? $report->title : 'Untitled Report' }}</div>

    @foreach ($report->reportPages as $page)
        <div class="section">
            <h2>{{ is_string($page->name) ? $page->name : 'Unnamed Page' }}</h2>

            @if (isset($page->pageData->json_data))
                @foreach ($page->pageData->json_data as $key => $value)
                    <p><strong>{{ ucfirst($key) }}:</strong></p>
                    @if (is_array($value))
                        <ul>
                            @foreach ($value as $item)
                                <li>{{ is_string($item) ? $item : json_encode($item) }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div>{!! $value !!}</div>
                    @endif
                @endforeach
            @endif
        </div>
    @endforeach

</body>

</html>