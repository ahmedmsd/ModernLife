@php
    $isResidential = $quotation->zoho_module === 'Residential_Quotations';
    $isContract = ($type ?? 'quotation') === 'contract';
    $title = $isContract ? 'عقد تصميم' : ($isResidential ? 'عرض سعر سكني' : 'عرض سعر تجاري');
    $rawData = $quotation->raw_data ?? [];
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} #{{ $quotation->quote_number }}</title>
    <style>
        @font-face {
            font-family: 'Inter';
            src: url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap');
        }
        body {
            font-family: 'Inter', sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 40px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .quotation-info {
            text-align: left;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        .section-title {
            font-size: 14px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background-color: #f9fafb;
            text-align: right;
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .totals {
            margin-right: auto;
            width: 300px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        .total-row.grand-total {
            font-weight: bold;
            font-size: 18px;
            color: #2563eb;
            border-top: 2px solid #eee;
            margin-top: 10px;
            padding-top: 10px;
        }
        .terms {
            margin-top: 50px;
            font-size: 12px;
            color: #666;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
        .contract-text {
            white-space: pre-wrap;
            font-size: 14px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">MODERN LIFE</div>
        <div class="quotation-info">
            <h1 style="margin: 0;">{{ $title }}</h1>
            <div>رقم المشروع: {{ $quotation->quote_number }}</div>
            <div>التاريخ: {{ now()->format('Y-m-d') }}</div>
        </div>
    </div>

    <div class="details-grid">
        <div>
            <div class="section-title">مرسل إلى:</div>
            <strong>{{ $client->client_name ?? $rawData['Client_Name'] ?? $rawData['Contact_Person']['name'] ?? 'العميل' }}</strong><br>
            @if($client && $client->phone)
                الهاتف: {{ $client->phone }}<br>
            @endif
            @if($client && $client->email)
                البريد: {{ $client->email }}<br>
            @endif
        </div>
        <div>
            <div class="section-title">معلومات المشروع:</div>
            <strong>الموضوع:</strong> {{ $quotation->subject ?? $rawData['Quotation_Name'] ?? '-' }}<br>
            <strong>النوع:</strong> {{ $isResidential ? 'سكني' : 'تجاري' }}<br>
            <strong>صالح حتى:</strong> {{ $quotation->valid_till ? $quotation->valid_till->format('Y-m-d') : ($rawData['Quotation_Valid_Until'] ?? '-') }}
        </div>
    </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصنف / المنتج</th>
                    <th>الكمية</th>
                    <th>سعر الوحدة</th>
                    <th>الخصم</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $item->product_name }}
                        @if($item->description)
                            <br><small style="color: #666;">{{ $item->description }}</small>
                        @endif
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }} ر.س</td>
                    <td>{{ number_format($item->discount, 2) }} ر.س</td>
                    <td>{{ number_format($item->total, 2) }} ر.س</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    <div class="totals">
        <div class="total-row">
            <span>المجموع الفرعي:</span>
            <span>{{ number_format($quotation->sub_total ?: ($rawData['Total'] ?? 0), 2) }} ر.س</span>
        </div>
        <div class="total-row">
            <span>الضريبة ({{ $rawData['VAT1'] ?? '15' }}%):</span>
            <span>{{ number_format($quotation->tax ?: ($rawData['VAT_Amount'] ?? 0), 2) }} ر.س</span>
        </div>
        @if(($quotation->discount ?: ($rawData['Total_Discount'] ?? 0)) != 0)
        <div class="total-row">
            <span>إجمالي الخصم:</span>
            <span>{{ number_format($quotation->discount ?: ($rawData['Total_Discount'] ?? 0), 2) }} ر.س</span>
        </div>
        @endif
        <div class="total-row grand-total">
            <span>المبلغ الصافي:</span>
            <span>{{ number_format($quotation->total_amount ?: ($rawData['Net_Amount'] ?? 0), 2) }} ر.س</span>
        </div>
    </div>

    @if($isContract)
        <div class="terms" style="page-break-before: always;">
            <div class="section-title">بنود العقد:</div>
            <div class="contract-text">
                {{ $rawData['Contract_Clauses'] ?? 'سيتم إدراج بنود العقد هنا بناءً على نوع التصميم المختار.' }}
            </div>
        </div>
    @else
        <div class="terms">
            <div class="section-title">الشروط والأحكام:</div>
            {!! nl2br(e($rawData['Terms_and_Conditions'] ?? $rawData['Quotation_Notes'] ?? 'تطبق الشروط والأحكام العامة للمؤسسة.')) !!}
        </div>
    @endif

    <div class="no-print" style="margin-top: 50px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer;">طباعة</button>
    </div>
</body>
</html>
