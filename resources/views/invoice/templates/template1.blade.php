@php
    /**
     * Template Invoice PDF - Full Width Fluid Layout
     * Mengatur tampilan cetak invoice dengan lebar 100% dinamis.
     * Mencegah distorsi scaling (efek kaca pembesar) pada html2pdf dengan
     * menghapus max-width dan menggunakan tipografi berbasis Point (pt).
     */
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ isset($settings['site_rtl']) && $settings['site_rtl'] == 'on' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        {{ \App\Models\Invoice::invoiceNumberFormat($invoice->invoice_id, $invoice->created_by, $invoice->workspace) }} | 
        {{ !empty(company_setting('title_text', $invoice->created_by, $invoice->workspace)) ? company_setting('title_text', $invoice->created_by, $invoice->workspace) : (!empty(admin_setting('title_text')) ? admin_setting('title_text') : 'WorkDo') }}
    </title>
    
    <style type="text/css">
        :root {
            --theme-color: {{ $color ?? '#3b82f6' }};
            --white: #ffffff;
            --black: #000000;
            --text-dark: #1e293b;
            --text-gray: #475569;
            --border-color: #cbd5e1;
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: var(--text-dark);
            font-size: 10pt; /* Menggunakan pt agar stabil saat diprint */
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: var(--white);
            -webkit-font-smoothing: antialiased;
        }

        /* * Container dibuat 100% tanpa max-width agar menyesuaikan lebar 
         * kertas (Landscape/Portrait) bawaan dari script jsPDF.
         */
        .invoice-container {
            width: 100%;
            max-width: 100%; 
            margin: 0;
            padding: 0;
            background: var(--white);
            box-sizing: border-box;
        }
        
        /* HEADER SECTION */
        .header-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
            border-bottom: 2px solid var(--border-color);
        }
        .header-table td {
            vertical-align: top;
            padding-bottom: 15px;
            border: none;
        }
        .logo-container {
            margin-bottom: 10px;
        }
        .logo-img {
            max-width: 150px;
            max-height: 70px;
            object-fit: contain;
        }
        .company-title {
            font-size: 13pt;
            font-weight: bold;
            color: var(--text-dark);
            line-height: 1.3;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .company-details {
            font-size: 9pt;
            color: var(--text-gray);
            line-height: 1.6;
        }

        /* INVOICE META (Kanan Atas) */
        .meta-container {
            text-align: right;
        }
        .invoice-label {
            font-size: 22pt;
            font-weight: bold;
            color: var(--theme-color);
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 4px 0;
            font-size: 9pt;
            color: var(--text-gray);
        }
        .meta-table td.label-cell {
            text-align: right;
            padding-right: 15px;
            font-weight: bold;
            color: var(--text-dark);
            width: 60%;
        }
        .meta-table td.value-cell {
            text-align: right;
            color: var(--text-dark);
            width: 40%;
        }

        /* CUSTOMER INFO */
        .info-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .info-table th {
            text-align: left;
            background-color: var(--bg-light);
            color: var(--text-dark);
            padding: 10px 12px;
            font-size: 10pt;
            font-weight: bold;
            border-bottom: 2px solid var(--theme-color);
            text-transform: uppercase;
        }
        .info-table td {
            padding: 12px;
            vertical-align: top;
            font-size: 9pt;
            line-height: 1.6;
            width: 50%;
            border: 1px solid var(--border-color);
        }

        /* ITEMS TABLE */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .items-table th {
            background-color: var(--theme-color);
            color: var(--white);
            font-weight: bold;
            padding: 12px 10px;
            text-align: left;
            font-size: 9pt;
            text-transform: uppercase;
            border: none;
        }
        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid var(--border-color);
            font-size: 9.5pt;
            color: var(--text-dark);
            vertical-align: middle;
        }
        .items-table tr:nth-child(even) td {
            background-color: var(--bg-light);
        }

        /* TEXT ALIGNMENT */
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }

        /* SUMMARY TABLE */
        .summary-wrapper {
            width: 100%;
            margin-bottom: 30px;
        }
        .summary-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
        }
        .summary-table th, .summary-table td {
            padding: 10px;
            font-size: 10pt;
            border-bottom: 1px solid var(--border-color);
        }
        .summary-table th {
            text-align: left;
            color: var(--text-gray);
            font-weight: bold;
        }
        .summary-table .total-row th, .summary-table .total-row td {
            font-weight: bold;
            font-size: 12pt;
            color: var(--theme-color);
            border-top: 2px solid var(--theme-color);
            border-bottom: 2px solid var(--theme-color);
            background-color: var(--bg-light);
        }

        /* BANK DETAILS */
        .bank-details {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            font-size: 9pt;
        }
        .bank-details th {
            background-color: var(--bg-light);
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid var(--border-color);
        }
        .bank-details td {
            padding: 10px;
            border: 1px solid var(--border-color);
        }

        /* FOOTER */
        .invoice-footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            font-size: 9pt;
            color: var(--text-gray);
        }
        
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>

<body>
    <div class="invoice-container" id="boxes">
        
        <table class="header-table">
            <tr>
                <td style="width: 60%;">
                    <div class="logo-container">
                        <img src="{{ $img }}" class="logo-img" alt="Logo">
                    </div>
                    <div class="company-title">{{ $settings['company_name'] ?? '' }}</div>
                    <div class="company-details">
                        {{ $settings['company_address'] ?? '' }}<br>
                        @if(isset($settings['company_city']) && !empty($settings['company_city'])) {{ $settings['company_city'] }}, @endif
                        @if(isset($settings['company_state']) && !empty($settings['company_state'])) {{ $settings['company_state'] }}, @endif
                        @if(isset($settings['company_zipcode']) && !empty($settings['company_zipcode'])) {{ $settings['company_zipcode'] }} @endif
                        <br>
                        @if(isset($settings['company_country']) && !empty($settings['company_country'])) {{ $settings['company_country'] }} <br> @endif
                        
                        @if(isset($settings['company_email']) && !empty($settings['company_email']))
                            <strong>{{ __('Email') }}:</strong> {{ $settings['company_email'] }} 
                        @endif
                        @if(isset($settings['company_telephone']) && !empty($settings['company_telephone']))
                            | <strong>{{ __('Telp') }}:</strong> {{ $settings['company_telephone'] }}
                        @endif
                        <br>
                        @if(isset($settings['registration_number']) && !empty($settings['registration_number']))
                            <strong>{{ __('Nomor Registrasi') }}:</strong> {{ $settings['registration_number'] }}<br>
                        @endif
                        @if(isset($settings['vat_number']) && !empty($settings['vat_number']))
                            <strong>{{ __('VAT Nomor') }}:</strong> {{ $settings['vat_number'] }}
                        @endif
                    </div>
                </td>
                <td style="width: 40%;">
                    <div class="meta-container">
                        <div class="invoice-label">{{ __('INVOICE') }}</div>
                        <table class="meta-table">
                            <tr>
                                <td class="label-cell">{{ __('Number') }}:</td>
                                <td class="value-cell" style="font-weight: bold;">{{ \App\Models\Invoice::invoiceNumberFormat($invoice->invoice_id, $invoice->created_by, $invoice->workspace) }}</td>
                            </tr>
                            <tr>
                                <td class="label-cell">{{ __('Tanggal Terbit') }}:</td>
                                <td class="value-cell">{{ company_date_formate($invoice->issue_date, $invoice->created_by, $invoice->workspace) }}</td>
                            </tr>
                            <tr>
                                <td class="label-cell">{{ __('Tanggal Jatuh Tempo') }}:</td>
                                <td class="value-cell">{{ company_date_formate($invoice->due_date, $invoice->created_by, $invoice->workspace) }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <table class="info-table">
            <thead>
                <tr>
                    <th>{{ __('Bill To') }}</th>
                    <th>{{ __('Ship To') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        @if(!empty($customer))
                            <strong style="font-size: 11pt; color: var(--text-dark);">{{ !empty($customer->billing_name) ? $customer->billing_name : (!empty($customer->name) ? $customer->name : '') }}</strong><br>
                            {{ !empty($customer->billing_address) ? $customer->billing_address : '' }}<br>
                            {{ !empty($customer->billing_city) ? $customer->billing_city . ' ,' : '' }}
                            {{ !empty($customer->billing_state) ? $customer->billing_state . ' ,' : '' }}
                            {{ !empty($customer->billing_zip) ? $customer->billing_zip : '' }}<br>
                            {{ !empty($customer->billing_country) ? $customer->billing_country : '' }}<br>
                            @if(!empty($customer->billing_phone))
                                <strong>{{ __('Phone') }}:</strong> {{ $customer->billing_phone }}
                            @endif
                            <br>
                            @if(!empty($customer->email))
                                <strong>{{ __('Email') }}:</strong> {{ $customer->email }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if(!empty($customer))
                            <strong style="font-size: 11pt; color: var(--text-dark);">{{ !empty($customer->shipping_name) ? $customer->shipping_name : (!empty($customer->name) ? $customer->name : '') }}</strong><br>
                            {{ !empty($customer->shipping_address) ? $customer->shipping_address : '' }}<br>
                            {{ !empty($customer->shipping_city) ? $customer->shipping_city . ' ,' : '' }}
                            {{ !empty($customer->shipping_state) ? $customer->shipping_state . ' ,' : '' }}
                            {{ !empty($customer->shipping_zip) ? $customer->shipping_zip : '' }}<br>
                            {{ !empty($customer->shipping_country) ? $customer->shipping_country : '' }}<br>
                            @if(!empty($customer->shipping_phone))
                                <strong>{{ __('Phone') }}:</strong> {{ $customer->shipping_phone }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">#</th>
                    <th style="width: 35%;">{{ __('Item') }}</th>
                    <th class="text-center" style="width: 10%;">{{ __('Kuantitas') }}</th>
                    <th class="text-right" style="width: 15%;">{{ __('Tarif') }}</th>
                    <th class="text-right" style="width: 10%;">{{ __('Diskon') }}</th>
                    <th class="text-right" style="width: 10%;">{{ __('Pajak') }}</th>
                    <th class="text-right" style="width: 15%;">{{ __('Harga') }}</th>
                </tr>
            </thead>
            <tbody>
                @if (isset($invoice->items) && count($invoice->items) > 0)
                    @foreach ($invoice->items as $key => $item)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td>
                                <strong>
                                    {{ !empty($item->product()) ? $item->product()->name : '' }}
                                </strong>
                                <!-- @if(!empty($item->description))
                                    <br><span style="font-size: 8.5pt; color: var(--text-gray);">{{ $item->description }}</span>
                                @endif -->
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">{{ currency_format_with_sym($item->price, $invoice->created_by, $invoice->workspace) }}</td>
                            <td class="text-right">{{ currency_format_with_sym($item->discount, $invoice->created_by, $invoice->workspace) }}</td>
                            <td class="text-right">
                                @if (!empty($item->itemTax))
                                    @foreach ($item->itemTax as $taxes)
                                        <span>{{ $taxes['name'] }} ({{ $taxes['rate'] }})</span><br>
                                        <span>{{ currency_format_with_sym($taxes['tax_price'], $invoice->created_by, $invoice->workspace) }}</span><br>
                                    @endforeach
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right" style="font-weight: bold;">
                                {{ currency_format_with_sym($item->price * $item->quantity - $item->discount + (isset($item->tax_price) ? $item->tax_price : 0), $invoice->created_by, $invoice->workspace) }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 15px;">{{ __('No items found') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="summary-wrapper clearfix">
            <table class="summary-table">
                <tr>
                    <th>{{ __('Subtotal') }}</th>
                    <td class="text-right">{{ currency_format_with_sym($invoice->getSubTotal(), $invoice->created_by, $invoice->workspace) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Discount') }}</th>
                    <td class="text-right">{{ currency_format_with_sym($invoice->getTotalDiscount(), $invoice->created_by, $invoice->workspace) }}</td>
                </tr>
                @if (!empty($invoice->taxesData))
                    @foreach ($invoice->taxesData as $taxName => $taxPrice)
                        <tr>
                            <th>{{ $taxName }}</th>
                            <td class="text-right">{{ currency_format_with_sym($taxPrice, $invoice->created_by, $invoice->workspace) }}</td>
                        </tr>
                    @endforeach
                @endif
                <tr class="total-row">
                    <th>{{ __('Total') }}</th>
                    <td class="text-right">{{ currency_format_with_sym($invoice->getTotal(), $invoice->created_by, $invoice->workspace) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Paid Amount') }}</th>
                    <td class="text-right" style="color: #16a34a; font-weight: bold;">
                        {{ currency_format_with_sym($invoice->getTotal() - $invoice->getDue(), $invoice->created_by, $invoice->workspace) }}
                    </td>
                </tr>
                <tr>
                    <th style="color: #b91c1c;">{{ __('Due Amount') }}</th>
                    <td class="text-right" style="color: #b91c1c; font-weight: bold;">
                        {{ currency_format_with_sym($invoice->getDue(), $invoice->created_by, $invoice->workspace) }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- <div class="clearfix" style="margin-top: 40px;">
            <strong style="font-size: 11pt;">{{ __('Bank Details') }}:</strong>
            <table class="bank-details">
                <thead>
                    <tr>
                        <th>{{ __('Bank Name') }}</th>
                        <th>{{ __('Account Name') }}</th>
                        <th>{{ __('Account Number') }}</th>
                        <th>{{ __('Contact Number') }}</th>
                        <th>{{ __('Bank Address') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($bank_details_list) && count($bank_details_list) > 0)
                        @foreach ($bank_details_list as $key => $bank)
                            <tr>
                                <td>{{ $bank->bank_name }}</td>
                                <td>{{ $bank->holder_name }}</td>
                                <td>{{ $bank->account_number }}</td>
                                <td>{{ $bank->contact_number }}</td>
                                <td>{{ $bank->bank_address }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center">-</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div> -->

        <div class="invoice-footer">
            <p> 
                <strong>{{ isset($settings['footer_title']) ? $settings['footer_title'] : '' }}</strong><br>
                {{ isset($settings['footer_notes']) ? $settings['footer_notes'] : '' }} 
            </p>
        </div>

    </div>

    @if (!isset($preview))
        @include('invoice.script')
    @endif
</body>

</html>