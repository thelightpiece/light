@php
    /**
     * PDF Invoice Template - Strict Table Layout
     * Menggunakan layout berbasis tabel penuh untuk memastikan presisi tinggi 
     * saat dikonversi menggunakan html2pdf. Mendukung keamanan render gambar (CORS) 
     * dan escaping HTML terstandar.
     */
    $admin_settings = getAdminAllSetting();
    $company_settings = getCompanyAllSetting(creatorId());
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ \App\Models\Utility::getValByName('SITE_RTL') == 'on' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ \App\Models\Invoice::invoiceNumberFormat($invoice->invoice_id) }}</title>
    <style>
        /* CSS Khusus Mesin PDF untuk Standar Rendering Profesional */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            font-size: 14px;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .invoice-container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 30px;
            background: #ffffff;
            box-sizing: border-box;
        }
        .header-table {
            width: 100%;
            margin-bottom: 40px;
            border-collapse: collapse;
            border: none;
        }
        .header-table td {
            vertical-align: top;
            border: none;
        }
        .logo-img {
            max-width: 220px;
            max-height: 90px;
            object-fit: contain;
        }
        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            text-align: right;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 1.5px;
        }
        .invoice-meta {
            text-align: right;
            font-size: 13px;
            color: #555555;
            line-height: 1.7;
        }
        .invoice-meta strong {
            color: #2c3e50;
        }
        .info-table {
            width: 100%;
            margin-bottom: 35px;
            border-collapse: collapse;
        }
        .info-table th {
            text-align: left;
            background-color: #f4f6f9;
            color: #2c3e50;
            padding: 12px;
            font-size: 14px;
            border-bottom: 2px solid #2c3e50;
            text-transform: uppercase;
        }
        .info-table td {
            padding: 12px;
            vertical-align: top;
            font-size: 13px;
            line-height: 1.6;
            width: 50%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #2c3e50;
            color: #ffffff;
            font-weight: 600;
            padding: 14px 10px;
            text-align: left;
            font-size: 13px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e9ecef;
            font-size: 13px;
            color: #495057;
            vertical-align: middle;
        }
        .items-table tr:nth-child(even) td {
            background-color: #fafbfc;
        }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        
        .summary-table {
            width: 45%;
            float: right;
            border-collapse: collapse;
        }
        .summary-table th, .summary-table td {
            padding: 12px 10px;
            font-size: 14px;
            border-bottom: 1px solid #e9ecef;
        }
        .summary-table th {
            text-align: left;
            color: #2c3e50;
            font-weight: 600;
        }
        .summary-table .total-row th, .summary-table .total-row td {
            font-weight: bold;
            font-size: 16px;
            color: #2c3e50;
            border-top: 2px solid #2c3e50;
            border-bottom: none;
            background-color: #f4f6f9;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .signature-section {
            margin-top: 80px;
            width: 100%;
            text-align: right;
        }
        .signature-box {
            display: inline-block;
            width: 250px;
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px solid #000000;
            margin-bottom: 10px;
            height: 80px;
        }
        .badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            color: #ffffff;
            display: inline-block;
        }
        .bg-info { background-color: #17a2b8; }
        .bg-primary { background-color: #007bff; }
        .bg-secondary { background-color: #6c757d; }
        .bg-warning { background-color: #ffc107; color: #212529; }
        .bg-success { background-color: #28a745; }
        .bg-dark { background-color: #343a40; }
        
        .btn-download {
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: background-color 0.3s;
        }
        .btn-download:hover { background-color: #0056b3; }

        @media print {
            .no-print { display: none !important; }
            .invoice-container { padding: 0; box-shadow: none; max-width: 100%; }
            body { background-color: transparent; }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; padding: 25px; background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
    <button id="downloadBtn" class="btn-download" onclick="saveAsPDF()">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: middle; margin-right: 8px;">
            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
        </svg>
        {{ __('Download PDF') }}
    </button>
</div>

<div class="invoice-container" id="printableArea">
    
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                @if(!empty(sidebar_logo()))
                    <img src="{{ get_file(sidebar_logo()) }}" class="logo-img" alt="Company Logo">
                @else
                    <h2 style="color: #2c3e50; margin: 0;">{{ $company_settings['company_name'] ?? config('app.name') }}</h2>
                @endif
                <div style="margin-top: 15px; font-size: 13px; color: #555555; line-height: 1.6;">
                    {{ $company_settings['company_address'] ?? '' }}<br>
                    {{ $company_settings['company_city'] ?? '' }} {{ $company_settings['company_state'] ?? '' }} {{ $company_settings['company_zipcode'] ?? '' }}<br>
                    {{ $company_settings['company_country'] ?? '' }}<br>
                    @if(!empty($company_settings['company_telephone']))
                        <strong>{{ __('Tel') }}:</strong> {{ $company_settings['company_telephone'] }}<br>
                    @endif
                    @if(!empty($company_settings['company_email']))
                        <strong>{{ __('Email') }}:</strong> {{ $company_settings['company_email'] }}
                    @endif
                </div>
            </td>
            <td style="width: 50%;" class="invoice-meta">
                <div class="invoice-title">{{ __('INVOICE') }}</div>
                <div><strong>{{ __('Invoice Number') }}:</strong> {{ \App\Models\Invoice::invoiceNumberFormat($invoice->invoice_id) }}</div>
                <div><strong>{{ __('Issue Date') }}:</strong> {{ company_date_formate($invoice->issue_date) }}</div>
                <div><strong>{{ __('Due Date') }}:</strong> {{ company_date_formate($invoice->due_date) }}</div>
                <div style="margin-top: 8px;">
                    <strong>{{ __('Status') }}:</strong> 
                    @php
                        $statusClass = [
                            0 => 'bg-info', 1 => 'bg-primary', 2 => 'bg-secondary',
                            3 => 'bg-warning', 4 => 'bg-success'
                        ][$invoice->status] ?? 'bg-dark';
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ __(\App\Models\Invoice::$statues[$invoice->status] ?? 'Unknown') }}</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <th>{{ __('Billed To') }}</th>
            <th>{{ __('Shipped To') }}</th>
        </tr>
        <tr>
            <td>
                @if(!empty($customer))
                    <strong style="font-size: 15px;">{{ $customer->billing_name ?? $customer->name ?? '' }}</strong><br>
                    {{ $customer->billing_address ?? '' }}<br>
                    {{ $customer->billing_city ?? '' }} {{ $customer->billing_state ?? '' }}<br>
                    {{ $customer->billing_country ?? '' }} {{ $customer->billing_zip ?? '' }}<br>
                    @if(!empty($customer->billing_phone))
                        <strong>{{ __('Phone') }}:</strong> {{ $customer->billing_phone }}
                    @endif
                @else
                    <span style="color: #999999;">{{ __('No billing information available.') }}</span>
                @endif
            </td>
            <td>
                @if(!empty($customer))
                    <strong style="font-size: 15px;">{{ $customer->shipping_name ?? $customer->name ?? '' }}</strong><br>
                    {{ $customer->shipping_address ?? '' }}<br>
                    {{ $customer->shipping_city ?? '' }} {{ $customer->shipping_state ?? '' }}<br>
                    {{ $customer->shipping_country ?? '' }} {{ $customer->shipping_zip ?? '' }}<br>
                    @if(!empty($customer->shipping_phone))
                        <strong>{{ __('Phone') }}:</strong> {{ $customer->shipping_phone }}
                    @endif
                @else
                    <span style="color: #999999;">{{ __('No shipping information available.') }}</span>
                @endif
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">#</th>
                <th style="width: 35%;">{{ __('Item') }}</th>
                <th class="text-center" style="width: 10%;">{{ __('Qty') }}</th>
                <th class="text-right" style="width: 15%;">{{ __('Rate') }}</th>
                <th class="text-right" style="width: 10%;">{{ __('Discount') }}</th>
                <th class="text-right" style="width: 10%;">{{ __('Tax') }}</th>
                <th class="text-right" style="width: 15%;">{{ __('Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $taxesData = [];
            @endphp
            @if(isset($invoice->items) && count($invoice->items) > 0)
                @foreach ($invoice->items as $key => $item)
                    @php
                        $taxAmount = 0;
                        if (!empty($item->tax)) {
                            $taxes = explode(',', $item->tax);
                            foreach ($taxes as $taxID) {
                                $tax = \App\Models\Tax::find($taxID);
                                if ($tax) {
                                    $currentTax = ($item->price * $item->quantity * $tax->rate) / 100;
                                    $taxAmount += $currentTax;
                                    
                                    if (array_key_exists($tax->name, $taxesData)) {
                                        $taxesData[$tax->name] += $currentTax;
                                    } else {
                                        $taxesData[$tax->name] = $currentTax;
                                    }
                                }
                            }
                        }
                        $itemTotal = ($item->price * $item->quantity) - $item->discount + $taxAmount;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td>
                            <strong style="color: #2c3e50;">{{ !empty($item->product) ? $item->product->name : $item->item }}</strong><br>
                            @if(!empty($item->description))
                                <span style="font-size: 11px; color: #777777;">{{ $item->description }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ currency_format_with_sym($item->price) }}</td>
                        <td class="text-right">{{ currency_format_with_sym($item->discount) }}</td>
                        <td class="text-right">{{ currency_format_with_sym($taxAmount) }}</td>
                        <td class="text-right">{{ currency_format_with_sym($itemTotal) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #999;">{{ __('No items found in this invoice.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="clearfix">
        <table class="summary-table">
            <tr>
                <th>{{ __('Subtotal') }}</th>
                <td class="text-right">{{ currency_format_with_sym($invoice->getSubTotal()) }}</td>
            </tr>
            <tr>
                <th>{{ __('Total Discount') }}</th>
                <td class="text-right">{{ currency_format_with_sym($invoice->getTotalDiscount()) }}</td>
            </tr>
            
            @if(!empty($taxesData))
                @foreach($taxesData as $taxName => $taxPrice)
                <tr>
                    <th>{{ $taxName }}</th>
                    <td class="text-right">{{ currency_format_with_sym($taxPrice) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <th>{{ __('Total Tax') }}</th>
                    <td class="text-right">{{ currency_format_with_sym($invoice->getTotalTax()) }}</td>
                </tr>
            @endif
            
            <tr class="total-row">
                <th>{{ __('Grand Total') }}</th>
                <td class="text-right">{{ currency_format_with_sym($invoice->getTotal()) }}</td>
            </tr>
            <tr>
                <th>{{ __('Paid Amount') }}</th>
                <td class="text-right" style="color: #28a745;">{{ currency_format_with_sym($invoice->getTotal() - $invoice->getDue()) }}</td>
            </tr>
            <tr class="total-row">
                <th style="color: #d9534f;">{{ __('Due Amount') }}</th>
                <td class="text-right" style="color: #d9534f;">{{ currency_format_with_sym($invoice->getDue()) }}</td>
            </tr>
        </table>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <strong style="color: #2c3e50; font-size: 15px;">{{ __('Authorized Signature') }}</strong><br>
            <span style="font-size: 13px; color: #555555;">{{ $company_settings['company_name'] ?? config('app.name') }}</span>
        </div>
    </div>

</div>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script>
    /**
     * Konfigurasi PDF Generation
     * Menggunakan setting resolusi tinggi (scale: 3) dan useCORS aktif untuk memastikan 
     * logo dan elemen eksternal dimuat sempurna saat di-render.
     */
    function saveAsPDF() {
        var element = document.getElementById('printableArea');
        var downloadBtn = document.getElementById('downloadBtn');
        
        // Sembunyikan tombol sementara
        if(downloadBtn) downloadBtn.style.display = 'none';

        var opt = {
            margin:       0.4,
            filename:     '{{ \App\Models\Invoice::invoiceNumberFormat($invoice->invoice_id) }}.pdf',
            image:        { type: 'jpeg', quality: 1 },
            html2canvas:  { 
                scale: 3, 
                dpi: 300, 
                letterRendering: true,
                useCORS: true 
            },
            jsPDF:        { unit: 'in', format: 'A4', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).save().then(function () {
            // Tampilkan kembali setelah selesai
            if(downloadBtn) downloadBtn.style.display = 'inline-block';
        });
    }
</script>

</body>
</html>