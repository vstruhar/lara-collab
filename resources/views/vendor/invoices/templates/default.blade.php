<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ $invoice->name }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <style type="text/css" media="screen">
        html {
            font-family: sans-serif;
            line-height: 1.15;
            margin: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            text-align: left;
            background-color: #fff;
            font-size: 11px;
            margin: 36pt;
        }

        h4 {
            margin-top: 0;
            margin-bottom: 0.5rem;
        }

        p {
            margin-top: 0;
            margin-bottom: 0.7rem;
        }

        strong {
            font-weight: bolder;
        }

        img {
            vertical-align: middle;
            border-style: none;
        }

        table {
            border-collapse: collapse;
        }

        th {
            text-align: inherit;
        }

        h4,
        .h4 {
            margin-bottom: 0.5rem;
            font-weight: 500;
            line-height: 1.2;
        }

        h4,
        .h4 {
            font-size: 1.5rem;
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: top;
        }

        .table.table-items td {
            border-top: 1px solid #dee2e6;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
        }

        .mt-2 {
            margin-top: 1.5rem !important;
        }

        .pr-0,
        .px-0 {
            padding-right: 0 !important;
        }

        .pl-0,
        .px-0 {
            padding-left: 0 !important;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-uppercase {
            text-transform: uppercase !important;
        }

        * {
            font-family: "DejaVu Sans";
        }

        body,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        table,
        th,
        tr,
        td,
        p,
        div {
            line-height: 1.1;
        }

        .party-header {
            font-size: 1.2rem;
            font-weight: 400;
        }

        .total-amount {
            font-size: 12px;
            font-weight: 700;
        }

        .border-0 {
            border: none !important;
        }

        .border-bottom-4 {
            border-bottom: 4px solid #dee2e6 !important;
        }

        .cool-gray {
            color: #6B7280;
        }

        .company-name {
            font-size: 1.85rem;
        }

        .seller-name,
        .buyer-name {
            font-size: 1.15rem;
        }

        .table-bank-account {
            background-color: #dee2e6;
        }

        .bank-account-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-left: 6px;
        }

        .bank-account-details p {
            margin: 7px 0;
        }

        .invoice-info {
            font-size: 0.74rem;
        }

        .invoice-info .invoice-number {
            font-size: 1.3rem;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    <table class="table mt-2">
        <tbody>
            <tr>
                <td class="border-0 pl-0" width="65%">
                    @if ($invoice->logo)
                        <img src="{{ $invoice->getLogo() }}" alt="logo" height="85">
                    @else
                        <h4 class="company-name">
                            <strong>{{ $invoice->buyer->name }}</strong>
                        </h4>
                    @endif
                </td>
                <td class="border-0 pl-0 invoice-info">
                    <p class="invoice-number">Number: <strong>{{ $invoice->getSerialNumber() }}</strong></p>
                    <p>Invoice date: <strong>{{ $invoice->getDate() }}</strong></p>
                    <p>Due by: <strong>{{ $invoice->getPayUntilDate() }}</strong></p>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Seller - Buyer --}}
    <table class="table">
        <thead>
            <tr class="border-bottom-4">
                <th class="border-0 pl-0 party-header" width="48.5%">
                    Seller
                </th>
                <th class="border-0" width="3%"></th>
                <th class="border-0 pl-0 party-header">
                    Buyer
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="px-0">
                    @if ($invoice->seller->name)
                        <p class="seller-name">
                            <strong>{{ $invoice->seller->name }}</strong>
                        </p>
                    @endif

                    @if ($invoice->seller->address)
                        <p class="seller-address">
                            Address: {{ $invoice->seller->address }}
                        </p>
                    @endif

                    @if ($invoice->seller->phone)
                        <p class="seller-phone">
                            Phone: {{ $invoice->seller->phone }}
                        </p>
                    @endif

                    @foreach ($invoice->seller->custom_fields as $key => $value)
                        @if (!empty($value))
                            <p class="seller-custom-field">
                                {{ str($key)->replace('id', 'ID')->ucfirst() }}: {{ $value }}
                            </p>
                        @endif
                    @endforeach
                </td>
                <td class="border-0"></td>
                <td class="px-0">
                    @if ($invoice->buyer->name)
                        <p class="buyer-name">
                            <strong>{{ $invoice->buyer->name }}</strong>
                        </p>
                    @endif

                    @if ($invoice->buyer->address)
                        <p class="buyer-address">
                            Address: {{ $invoice->buyer->address }}
                        </p>
                    @endif

                    @if ($invoice->buyer->phone)
                        <p class="buyer-phone">
                            Phone: {{ $invoice->buyer->phone }}
                        </p>
                    @endif

                    @foreach ($invoice->buyer->custom_fields as $key => $value)
                        @if (!empty($value))
                            <p class="buyer-custom-field">
                                {{ str($key)->replace('id', 'ID')->ucfirst() }}: {{ $value }}
                            </p>
                        @endif
                    @endforeach
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Bank info --}}
    <table class="table table-bank-account">
        <tr>
            <td width="52%">
                <div class="bank-account-title">Bank account</h2>
            </td>
            <td class="px-0 bank-account-details" width="48%">
                <p>IBAN: {{ $invoice->getCustomData()['iban'] }}</p>
                <p>SWIFT: {{ $invoice->getCustomData()['swift'] }}</p>
            </td>
        </tr>
    </table>

    {{-- Table --}}
    <table class="table table-items">
        <thead>
            <tr>
                <th scope="col" class="border-0 pl-0">Description</th>
                @if ($invoice->hasItemUnits)
                    <th scope="col" class="text-center border-0">Units</th>
                @endif
                <th scope="col" class="text-center border-0">{{ $invoice->isFixedAmount() || $invoice->items->every(fn ($item) => $item->isFixedPrice()) ? '' : 'Quantity' }}</th>
                <th scope="col" class="text-right border-0">{{ $invoice->isFixedAmount() || $invoice->items->every(fn ($item) => $item->isFixedPrice()) ? '' : 'Price' }}</th>
                @if ($invoice->hasItemDiscount)
                    <th scope="col" class="text-right border-0">Discount</th>
                @endif
                @if ($invoice->hasItemTax)
                    <th scope="col" class="text-right border-0">Tax</th>
                @endif
                <th scope="col" class="text-right border-0 pr-0">{{ $invoice->isFixedAmount() ? '' : 'Sub total' }}</th>
            </tr>
        </thead>
        <tbody>
            {{-- Items --}}
            @foreach ($invoice->items as $item)
                @if ($invoice->isFixedAmount())
                <tr>
                    <td class="pl-0">
                        {{ $item->title }}
                    </td>
                    @if ($invoice->hasItemUnits)
                        <td></td>
                    @endif
                    <td></td>
                    <td></td>
                    @if ($invoice->hasItemDiscount)
                        <td></td>
                    @endif
                    @if ($invoice->hasItemTax)
                        <td></td>
                    @endif
                    <td></td>
                </tr>
                @else
                    <tr>
                        <td class="pl-0">
                        {{ $item->title }}

                        @if ($item->description)
                            <p class="cool-gray">{{ $item->description }}</p>
                        @endif
                    </td>
                    @if ($invoice->hasItemUnits)
                        <td class="text-center">{{ $item->isFixedPrice() ? '-' : $item->units }}</td>
                    @endif
                    <td class="text-center">{{ $item->isFixedPrice() ? '-' : $item->quantity }}</td>
                    <td class="text-right">
                        {{ $item->isFixedPrice() ? '-' : $invoice->formatCurrency($item->price_per_unit) }}
                    </td>
                    @if ($invoice->hasItemDiscount)
                        <td class="text-right">
                            {{ $item->isFixedPrice() ? '-' : $invoice->formatCurrency($item->discount) }}
                        </td>
                    @endif
                    @if ($invoice->hasItemTax)
                        <td class="text-right">
                            {{ $item->isFixedPrice() ? '-' : $invoice->formatCurrency($item->tax) }}
                        </td>
                    @endif

                    <td class="text-right pr-0">
                        {{ $invoice->formatCurrency($item->sub_total_price) }}
                    </td>
                </tr>
                @endif
            @endforeach
            {{-- Summary --}}
            @if ($invoice->hasItemOrInvoiceDiscount())
                <tr>
                    <td colspan="{{ $invoice->table_columns - 2 }}" class="border-0"></td>
                    <td class="text-right pl-0">Total discount</td>
                    <td class="text-right pr-0">
                        {{ $invoice->formatCurrency($invoice->total_discount) }}
                    </td>
                </tr>
            @endif
            @if ($invoice->taxable_amount)
                <tr>
                    <td colspan="{{ $invoice->table_columns - 2 }}" class="border-0"></td>
                    <td class="text-right pl-0">Taxable amount</td>
                    <td class="text-right pr-0">
                        {{ $invoice->formatCurrency($invoice->taxable_amount) }}
                    </td>
                </tr>
            @endif
            @if ($invoice->tax_rate)
                <tr>
                    <td colspan="{{ $invoice->table_columns - 2 }}" class="border-0"></td>
                    <td class="text-right pl-0">Tax rate</td>
                    <td class="text-right pr-0">
                        {{ $invoice->tax_rate }}%
                    </td>
                </tr>
            @endif
            @if ($invoice->hasItemOrInvoiceTax())
                <tr>
                    <td colspan="{{ $invoice->table_columns - 2 }}" class="border-0"></td>
                    <td class="text-right pl-0">Total taxes</td>
                    <td class="text-right pr-0">
                        {{ $invoice->formatCurrency($invoice->total_taxes) }}
                    </td>
                </tr>
            @endif
            @if ($invoice->shipping_amount)
                <tr>
                    <td colspan="{{ $invoice->table_columns - 2 }}" class="border-0"></td>
                    <td class="text-right pl-0">Shipping</td>
                    <td class="text-right pr-0">
                        {{ $invoice->formatCurrency($invoice->shipping_amount) }}
                    </td>
                </tr>
            @endif
            <tr>
                <td colspan="{{ $invoice->table_columns - 2 }}" class="border-0"></td>
                <td class="text-right pl-0">Total amount</td>
                <td class="text-right pr-0 total-amount">
                    {{ $invoice->formatCurrency($invoice->total_amount) }}
                </td>
            </tr>
        </tbody>
    </table>

    @if ($invoice->notes)
        <p>
            Notes: {!! $invoice->notes !!}
        </p>
    @endif

    <script type="text/php">
        if (isset($pdf) && $PAGE_COUNT > 1) {
            $text = "Page {PAGE_NUM} / {PAGE_COUNT}";
            $size = 10;
            $font = $fontMetrics->getFont("Verdana");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width);
            $y = $pdf->get_height() - 35;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>

</html>
