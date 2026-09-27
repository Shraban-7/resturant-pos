<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $sale->order_id }}</title>
    <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Poppins:100,200,300,400,500,600,700,800,900&display=swap' type='text/css'>
    <style>
        * { box-sizing: border-box; }
        html, body { min-height: 100vh; margin: 0; }
        body {
            font-family: 'Poppins', system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            font-size: 14px;
            color: #111827;
            background: #f9fafb;
            line-height: 1.5;
        }
        .invoice-container {
            min-height: 100vh;
            max-width: 880px;
            margin: 0 auto;
            background: #ffffff;
            position: relative;
            padding: 2rem;
        }
        .table-borderless td { padding: 5px !important; }
        footer {
            margin-top: 3rem;
            text-align: center;
        }
        .receipt-note { white-space: pre-line; }
        @media print {
            body { background: #fff; }
            .invoice-container { box-shadow: none; max-width: 100%; }
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-center sm:text-left">
                    @isset($settings->image)
                        <img src="{{ storage_url($settings->image) }}" height="100" alt="img" />
                    @endisset
                </div>
                <div class="text-center sm:text-right">
                    <h4 class="text-3xl font-semibold m-0">Invoice</h4>
                </div>
            </div>
            <hr class="my-4 border-gray-200">
            @if(! empty(trim((string) ($settings->receipt_header ?? ''))))
                <p class="receipt-note text-sm text-center text-gray-600 m-0">{{ $settings->receipt_header }}</p>
                <hr class="my-4 border-gray-200">
            @endif
        </header>

        <main>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div><strong>Date:</strong> {{ $sale->sale_date }}</div>
                <div class="sm:text-right"><strong>Invoice No:</strong> {{ $sale->order_id }}</div>
            </div>
            <hr class="my-4 border-gray-200">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                <div class="order-1 sm:order-2 sm:text-right">
                    <strong>Pay To:</strong>
                    @isset($settings)
                        <address class="not-italic mt-1">
                            @if($settings->name){{ $settings->name }}<br>@endif
                            @if($settings->address){{ $settings->address }}<br>@endif
                            @if($settings->phone){{ $settings->phone }}<br>@endif
                            @if($settings->email){{ $settings->email }}<br>@endif
                        </address>
                    @endisset
                </div>
                <div class="order-2 sm:order-1">
                    <strong>Invoiced To:</strong>
                    <p class="mt-1 mb-0">Walk-in Customer</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border border-gray-200 mb-4 text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <td class="px-3 py-2 font-semibold w-1/2"><strong>Item</strong></td>
                            <td class="px-3 py-2 font-semibold text-center"><strong>Rate</strong></td>
                            <td class="px-3 py-2 font-semibold text-center w-16"><strong>QTY</strong></td>
                            <td class="px-3 py-2 font-semibold text-right w-32"><strong>Amount</strong></td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sale->items as $item)
                            <tr class="border-t border-gray-200">
                                <td class="px-3 py-2">{{ $item->item_name }}</td>
                                <td class="px-3 py-2 text-center">{{ $item->unit_price }}</td>
                                <td class="px-3 py-2 text-center">{{ $item->quantity }}</td>
                                <td class="px-3 py-2 text-right">{{ $item->total_price }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <tbody>
                        <tr>
                            <td class="text-right py-1"><strong>Sub Total:</strong></td>
                            <td class="w-32 text-right py-1">{{ $sale->subtotal }}</td>
                        </tr>
                        @if($sale->discount > 0)
                            <tr>
                                <td class="text-right py-1"><strong>Discount (-):</strong></td>
                                <td class="w-32 text-right py-1">{{ $sale->discount }}</td>
                            </tr>
                        @endif
                        @if(($sale->vat_amount ?? 0) > 0)
                            <tr>
                                <td class="text-right py-1"><strong>{{ ($sale->vat_mode ?? '') === 'inclusive' ? 'VAT incl. (' . ($sale->vat_rate ?? 0) . '%)' : 'VAT (' . ($sale->vat_rate ?? 0) . '%)' }}:</strong></td>
                                <td class="w-32 text-right py-1">{{ $sale->vat_amount }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-right py-1"><strong>Total:</strong></td>
                            <td class="w-32 text-right py-1 font-bold">{{ $sale->payable }}</td>
                        </tr>
                        @if($sale->due > 0)
                            <tr>
                                <td class="text-right py-1"><strong>Paid:</strong></td>
                                <td class="w-32 text-right py-1">{{ $sale->paid }}</td>
                            </tr>
                            <tr>
                                <td class="text-right py-1 text-red-600"><strong>Due:</strong></td>
                                <td class="w-32 text-right py-1 text-red-600 font-bold">{{ $sale->due }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </main>

        <footer>
            <p class="receipt-note text-sm m-0">{{ $settings->receipt_footer ?: 'THANK YOU!' }}</p>
        </footer>
    </div>
</body>

<script>
    window.print();
    window.onafterprint = function() {
        window.close();
    };
</script>

</html>
