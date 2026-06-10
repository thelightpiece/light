@php
    /**
     * Template Invoice PDF - Dynamic Portrait Layout
     * Mengatur tampilan cetak invoice dengan orientasi Portrait (A4).
     * Semua komponen data ditarik secara dinamis dari pengaturan perusahaan 
     * dan data invoice tanpa menggunakan hardcoded text.
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
        /* Desain Tipografi & Layout Mikro Berstandar Internasional */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2b2b2b;
            font-size: 11px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .invoice-container {
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
            padding: 20px;
            background: #ffffff;
            box-sizing: border-box;
        }
        
        /* Tata Letak Struktur Header */
        .header-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
            border-bottom: 2px solid #e9ecef;
        }
        .header-table td {
            vertical-align: top;
            padding-bottom: 15px;
            border: none;
        }
        .logo-container {
            margin-bottom: 8px;
        }
        .logo-img {
            max-width: 130px;
            max-height: 55px;
            object-fit: contain;
        }
        .company-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e293b;
            line-height: 1.3;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }
        .company-details {
            font-size: 10px;
            color: #64748b;
            line-height: 1.4;
        }
        
        /* Meta Informasi Invoice (Kanan Atas) */
        .meta-container {
            text-align: right;
        }
        .invoice-label {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 2px 0;
            font-size: 10px;
            color: #475569;
        }
        .meta-table td.label-cell {
            text-align: right;
            padding-right: 10px;
            font-weight: 600;
            color: #1e293b;
        }
        .meta-table td.value-cell {
            text-align: right;
            width: 140px;
        }

        /* Informasi Pihak Ketiga (Bill / Ship To) */
        .info-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .info-table th {
            text-align: left;
            background-color: #f8fafc;
            color: #1e293b;
            padding: 8px 10px;
            font-size: 11px;
            font-weight: 700;
            border-bottom: 1px solid #cbd5e1;
            text-transform: uppercase;
        }
        .info-table td {
            padding: 10px;
            vertical-align: top;
            font-size: 11px;
            line-height: 1.5;
            width: 50%;
            border: 1px solid #f1f5f9;
        }

        /* Tabel Rincian Item Barang */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .items-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: 600;
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            border: none;
        }
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
            color: #334155;
            vertical-align: middle;
        }
        .items-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        
        /* Alinyemen Data */
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        
        /* Ringkasan Perhitungan Finansial */
        .summary-wrapper {
            width: 100%;
            margin-bottom: 30px;
        }
        .summary-table {
            width: 45%;
            float: right;
            border-collapse: collapse;
        }
        .summary-table th, .summary-table td {
            padding: 8px 6px;
            font-size: 11px;
            border-bottom: 1px solid #f1f5f9;
        }
        .summary-table th {
            text-align: left;
            color: #475569;
            font-weight: 500;
        }
        .summary-table .total-row th, .summary-table .total-row td {
            font-weight: 700;
            font-size: 12px;
            color: #0f172a;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            background-color: #f8fafc;
        }
        .summary-table .due-row th, .summary-table .due-row td {
            font-weight: bold;
            font-size: 12px;
            color: #b91c1c;
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        
        /* Tanda Tangan */
        .signature-container {
            margin-top: 60px;
            width: 100%;
        }
        .signature-box {
            float: right;
            width: 200px;
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px solid #475569;
            margin-bottom: 6px;
            height: 65px;
        }
        
        /* Komponen Status Badge */
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 700;
            color: #ffffff;
            display: inline-block;
            text-transform: uppercase;
        }
        .bg-info { background-color: #0ea5e9; }
        .bg-primary { background-color: #3b82f6; }
        .bg-secondary { background-color: #64748b; }
        .bg-warning { background-color: #eab308; color: #0f172a; }
        .bg-success { background-color: #22c55e; }
        .bg-dark { background-color: #1e293b; }
        
        /* Tombol download di layar monitor */
        .btn-download {
            background-color: #3b82f6;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 12px;
            display: inline-block;
            margin-bottom: 15px;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .btn-download:hover { background-color: #2563eb; }

        @media print {
            .no-print { display: none !important; }
            .invoice-container { padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; padding: 15px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
    <button id="downloadBtn" class="btn-download" onclick="saveAsPDF()">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: middle; margin-right: 6px;">
            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
        </svg>
        {{ __('Download PDF') }}
    </button>
</div>

<div class="invoice-container" id="printableArea">
    
    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <div class="logo-container">
                    @if(!empty($company_settings['company_logo']))
                        <img src="{{ get_file($company_settings['company_logo']) }}" class="logo-img" alt="Logo">
                    @elseif(!empty(sidebar_logo()))
                        <img src="{{ get_file(sidebar_logo()) }}" class="logo-img" alt="Logo">
                    @else
                        <div class="company-title">{{ $company_settings['company_name'] ?? config('app.name') }}</div>
                    @endif
                </div>
                <div class="company-title">{{ $company_settings['company_name'] ?? config('app.name') }}</div>
                <div class="company-details">
                    {{ $company_settings['company_address'] ?? '' }}<br>
                    @if(!empty($company_settings['company_city'])) {{ $company_settings['company_city'] }}, @endif
                    @if(!empty($company_settings['company_state'])) {{ $company_settings['company_state'] }}, @endif
                    @if(!empty($company_settings['company_zipcode'])) {{ $company_settings['company_zipcode'] }} @endif
                    <br>
                    @if(!empty($company_settings['company_country'])) {{ $company_settings['company_country'] }} <br> @endif
                    
                    @if(!empty($company_settings['company_email']))
                        <strong>{{ __('Email') }}:</strong> {{ $company_settings['company_email'] }} 
                    @endif
                    @if(!empty($company_settings['company_telephone']))
                        | <strong>{{ __('Telp') }}:</strong> {{ $company_settings['company_telephone'] }}
                    @endif
                    <br>
                    @if(!empty($company_settings['registration_number']))
                        <strong>{{ __('Nomor Registrasi') }}:</strong> {{ $company_settings['registration_number'] }}<br>
                    @endif
                    @if(!empty($company_settings['vat_number']))
                        <strong>{{ __('VAT Nomor') }}:</strong> {{ $company_settings['vat_number'] }}
                    @endif
                </div>
            </td>
            <td style="width: 45%;">
                <div class="meta-container">
                    <div class="invoice-label">{{ __('INVOICE') }}</div>
                    <table class="meta-table">
                        <tr>
                            <td class="label-cell">{{ __('Number') }}:</td>
                            <td class="value-cell" style="font-weight: bold; color: #0f172a;">{{ \App\Models\Invoice::invoiceNumberFormat($invoice->invoice_id) }}</td>
                        </tr>
                        <tr>
                            <td class="label-cell">{{ __('Tanggal Terbit') }}:</td>
                            <td class="value-cell">{{ company_date_formate($invoice->issue_date) }}</td>
                        </tr>
                        <tr>
                            <td class="label-cell">{{ __('Tanggal Jatuh Tempo') }}:</td>
                            <td class="value-cell">{{ company_date_formate($invoice->due_date) }}</td>
                        </tr>
                        <tr>
                            <td class="label-cell">{{ __('Status') }}:</td>
                            <td class="value-cell">
                                @php
                                    $statusClass = [
                                        0 => 'bg-info', 1 => 'bg-primary', 2 => 'bg-secondary',
                                        3 => 'bg-warning', 4 => 'bg-success'
                                    ][$invoice->status] ?? 'bg-dark';
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ __(\App\Models\Invoice::$statues[$invoice->status] ?? 'Unknown') }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <thead>
            <tr>
                <th>{{ __('Billed To') }}</th>
                <th>{{ __('Shipped To') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @if(!empty($customer))
                        <strong style="font-size: 12px; color: #0f172a;">{{ $customer->billing_name ?? $customer->name ?? '' }}</strong><br>
                        {{ $customer->billing_address ?? '' }}<br>
                        {{ $customer->billing_city ?? '' }}, {{ $customer->billing_state ?? '' }}<br>
                        {{ $customer->billing_country ?? '' }} {{ $customer->billing_zip ?? '' }}<br>
                        @if(!empty($customer->billing_phone))
                            <strong>{{ __('Phone') }}:</strong> {{ $customer->billing_phone }}
                        @endif
                    @else
                        <span style="color: #94a3b8;">{{ __('No billing information available.') }}</span>
                    @endif
                </td>
                <td>
                    @if(!empty($customer))
                        <strong style="font-size: 12px; color: #0f172a;">{{ $customer->shipping_name ?? $customer->name ?? '' }}</strong><br>
                        {{ $customer->shipping_address ?? '' }}<br>
                        {{ $customer->shipping_city ?? '' }}, {{ $customer->shipping_state ?? '' }}<br>
                        {{ $customer->shipping_country ?? '' }} {{ $customer->shipping_zip ?? '' }}<br>
                        @if(!empty($customer->shipping_phone))
                            <strong>{{ __('Phone') }}:</strong> {{ $customer->shipping_phone }}
                        @endif
                    @else
                        <span style="color: #94a3b8;">{{ __('No shipping information available.') }}</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">#</th>
                <th style="width: 38%;">{{ __('Item') }}</th>
                <th class="text-center" style="width: 8%;">{{ __('Qty') }}</th>
                <th class="text-right" style="width: 14%;">{{ __('Tarif') }}</th>
                <th class="text-right" style="width: 10%;">{{ __('Diskon') }}</th>
                <th class="text-right" style="width: 10%;">{{ __('Pajak') }}</th>
                <th class="text-right" style="width: 15%;">{{ __('Harga') }}</th>
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
                            <strong style="color: #0f172a;">{{ !empty($item->product) ? $item->product->name : $item->item }}</strong><br>
                            @if(!empty($item->description))
                                <span style="font-size: 10px; color: #64748b;">{{ $item->description }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ currency_format_with_sym($item->price) }}</td>
                        <td class="text-right">{{ currency_format_with_sym($item->discount) }}</td>
                        <td class="text-right">{{ currency_format_with_sym($taxAmount) }}</td>
                        <td class="text-right" style="font-weight: 600;">{{ currency_format_with_sym($itemTotal) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #94a3b8;">{{ __('No items found in this invoice.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="summary-wrapper clearfix">
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
                <td class="text-right" style="color: #16a34a;">{{ currency_format_with_sym($invoice->getTotal() - $invoice->getDue()) }}</td>
            </tr>
            <tr class="due-row">
                <th>{{ __('Due Amount') }}</th>
                <td class="text-right">{{ currency_format_with_sym($invoice->getDue()) }}</td>
            </tr>
        </table>
    </div>

    <div class="signature-container clearfix">
        <div class="signature-box">
            <div class="signature-line"></div>
            <strong style="color: #0f172a; font-size: 11px;">{{ __('Authorized Signature') }}</strong><br>
            <span style="font-size: 10px; color: #64748b;">{{ $company_settings['company_name'] ?? config('app.name') }}</span>
        </div>
    </div>

</div>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script>
    /**
     * Memproses Halaman Menjadi File PDF Berorientasi Portrait
     * Menggunakan set kompresi penyesuaian skala agar presisi tinggi di kertas A4.
     */
    function saveAsPDF() {
        var element = document.getElementById('printableArea');
        var downloadBtn = document.getElementById('downloadBtn');
        
        if(downloadBtn) downloadBtn.style.display = 'none';

        var opt = {
            margin:       [0.3, 0.3, 0.3, 0.3],
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
            if(downloadBtn) downloadBtn.style.display = 'inline-block';
        });
    }
</script>

</body>
</html>