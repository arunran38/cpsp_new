<table>
    <thead>
        <tr>
            <th colspan="4" style="font-size: 16px; font-weight: bold; text-align: center;">
                COMPLIANCE STATISTICS REPORT
            </th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: center;">
                @if($dateFrom && $dateTo)
                    (Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }})
                @elseif($dateFrom)
                    (From {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }})
                @elseif($dateTo)
                    (Up to {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }})
                @endif
            </th>
        </tr>
        <tr>
            <th colspan="4"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #f3f4f6; text-align: center; vertical-align: middle;">Decision Type</th>
            <th style="font-weight: bold; background-color: #f3f4f6; text-align: center; vertical-align: middle;">Total Orders</th>
            <th style="font-weight: bold; background-color: #f3f4f6; text-align: center; vertical-align: middle;">Complied</th>
            <th style="font-weight: bold; background-color: #f3f4f6; text-align: center; vertical-align: middle;">Pending</th>
        </tr>
    </thead>
    <tbody>
        @foreach(['VC' => 'Vigilance Case (VC)', 'PE' => 'Preliminary Enquiry (PE)', 'VE' => 'Vigilance Enquiry (VE)', 'CV' => 'Confidential Verification (CV)', 'IV' => 'Internal Vigilance (IV)',
                            'IV' => 'Internal Vigilance (IV)', 'SC' => 'Surprise Check (SC)'] as $code => $label)
            <tr>
                <td style="text-align: center; vertical-align: middle;">{{ $label }}</td>
                <td style="text-align: center; vertical-align: middle;">{{ $stats[$code]['Total'] }}</td>
                <td style="text-align: center; vertical-align: middle;">{{ $stats[$code]['Complied'] }}</td>
                <td style="text-align: center; vertical-align: middle;">{{ $stats[$code]['Pending'] }}</td>
            </tr>
        @endforeach
        <tr>
            <td style="font-weight: bold; text-align: center; vertical-align: middle;">Grand Total</td>
            <td style="font-weight: bold; text-align: center; vertical-align: middle;">{{ $totals['Total'] }}</td>
            <td style="font-weight: bold; text-align: center; vertical-align: middle;">{{ $totals['Complied'] }}</td>
            <td style="font-weight: bold; text-align: center; vertical-align: middle;">{{ $totals['Pending'] }}</td>
        </tr>
    </tbody>
</table>
