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
            <th style="font-weight: bold; background-color: #f3f4f6;">Decision Type</th>
            <th style="font-weight: bold; background-color: #f3f4f6;">Total Orders</th>
            <th style="font-weight: bold; background-color: #f3f4f6;">Complied</th>
            <th style="font-weight: bold; background-color: #f3f4f6;">Pending</th>
        </tr>
    </thead>
    <tbody>
        @foreach(['VC' => 'Vigilance Case (VC)', 'PE' => 'Preliminary Enquiry (PE)', 'VE' => 'Vigilance Enquiry (VE)', 'CV' => 'Confidential Verification (CV)', 'SC' => 'Surprise Check (SC)'] as $code => $label)
            <tr>
                <td>{{ $label }}</td>
                <td>{{ $stats[$code]['Total'] }}</td>
                <td>{{ $stats[$code]['Complied'] }}</td>
                <td>{{ $stats[$code]['Pending'] }}</td>
            </tr>
        @endforeach
        <tr>
            <td style="font-weight: bold;">Grand Total</td>
            <td style="font-weight: bold;">{{ $totals['Total'] }}</td>
            <td style="font-weight: bold;">{{ $totals['Complied'] }}</td>
            <td style="font-weight: bold;">{{ $totals['Pending'] }}</td>
        </tr>
    </tbody>
</table>
