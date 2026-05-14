<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->order_id }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-weight: normal;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            width: 80mm;
            margin: 0 auto;
            padding: 2mm;
            line-height: 1.2;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color: #000;
            background: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 3mm;
        }

        .restaurant-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .restaurant-info {
            font-size: 12px;
            margin-bottom: 1mm;
        }

        .invoice-title {
            font-weight: bold;
            text-align: center;
            margin: 2mm 0;
            font-size: 16px;
            text-decoration: underline;
        }

        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
            font-size: 12px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2mm 0;
        }

        .items-table th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 1mm 0;
            font-size: 13px;
        }

        .items-table td {
            padding: 0.5mm 0;
            vertical-align: top;
        }

        .items-table .item-name { width: 60%; }
        .items-table .item-qty  { width: 10%; text-align: center; }
        .items-table .item-price{ width: 30%; text-align: right; }

        .total-section {
            margin-top: 3mm;
            border-top: 1px solid #000;
            padding-top: 2mm;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 1mm 0;
        }

        .total-label { font-weight: bold; }

        .dashed-line {
            border-top: 1px dashed #000;
            margin: 2mm 0;
        }

        .thank-you {
            margin: 3mm 0;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="restaurant-name">{{ $settings->name ?? 'Restaurant Name' }}</div>
        <div class="restaurant-info">{{ $settings->address ?? '' }}</div>
        <div class="restaurant-info">Tel: {{ $settings->phone ?? '' }}</div>
    </div>

    <div class="dashed-line"></div>

    <div class="invoice-title">SALES INVOICE</div>

    <div class="invoice-info">
        <div>#{{ $sale->order_id }}</div>
        <div>{{ $sale->created_at->format('Y-m-d H:i') }}</div>
    </div>

    <div class="invoice-info">
        <div>Staff: {{ $sale->waiter ? $sale->waiter->name : 'Counter Order' }}</div>
        <div>Table: {{ $sale->dining_table_id ?? 'N/A' }}</div>
    </div>

    <div class="dashed-line"></div>

    <table class="items-table">
        <thead>
            <tr>
                <th class="item-name">ITEM</th>
                <th class="item-qty">QTY</th>
                <th class="item-price">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td class="item-qty">{{ $item->quantity }}</td>
                    <td class="item-price">{{ money($item->total_price) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="dashed-line"></div>

    <div class="total-section">
        <div class="total-row">
            <div>Subtotal:</div>
            <div>{{ money($sale->subtotal) }}</div>
        </div>
        <div class="total-row">
            <div class="total-label">TOTAL:</div>
            <div class="total-label">{{ money($sale->payable) }}</div>
        </div>
    </div>

    <div class="thank-you">THANK YOU!</div>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.print();
        window.onafterprint = function() {
            window.close();
        };
    });
</script>

</html>
