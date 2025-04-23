
<!DOCTYPE html>
<html>
<head>
    <title>Print Badges</title>
    <style>
        @media print {
            .page-break {
                page-break-after: always;
            }
        }

        body {
            margin: 0;
            padding: 0;
        }

        .a4-page {
            width: 210mm;
            height: 297mm;
            padding: 10mm;
            box-sizing: border-box;
            page-break-after: always;
        }

        .badge-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 10mm;
        }

        .visitor-badge {
            width: calc(50% - 5mm); /* Two per row with 10mm gap */
            height: 90mm; /* Adjust height as needed */
            border: 1px solid #000;
            padding: 10px;
            box-sizing: border-box;
        }

        .badge-header, .badge-body {
            text-align: center;
        }

        .badge-header .logo img {
            height: 40px;
        }

        svg {
            width: 100%;
            height: 40px;
        }

        @media screen {
            .print-btn {
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    @php
    $users = App\Models\User::get();
@endphp
    <div class="print-btn">
        <button onclick="window.print()">Print Badges</button>
    </div>

    @foreach ($users->chunk(6) as $chunk)
        <div class="a4-page">
            <div class="badge-container">
                @foreach ($chunk as $user)
                    <div class="visitor-badge">
                        <div class="badge-header">
                            <div class="logo">
                                <img src="{{ asset('logo.png') }}" alt="Company Logo" onerror="this.style.display='none'">
                            </div>
                        </div>

                        {!! $user->categoryLabel(false) !!}

                        <div class="badge-body">
                            <h3> {{ $user->name }} </h3>
                            <h5>{{ $user->country }}</h5>
                            @if ($user->category == 3 || $user->category == 5 || $user->category == 6)

            
                            @else
                            <div class="badge-barcode">
                                <svg id="barcode-{{ $user->id }}"></svg>
                                <p class="barcode-text">{{ $user->barcode }}</p>
                            </div>
                            @endif
                           
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <script src="https://cdn.jsdelivr.net/jsbarcode/3.11.5/JsBarcode.all.min.js"></script>
    <script>
        @foreach ($users as $user)
            JsBarcode("#barcode-{{ $user->id }}", "{{ $user->barcode }}", {
                format: "CODE128",
                displayValue: false
            });
        @endforeach
    </script>

</body>
</html>
