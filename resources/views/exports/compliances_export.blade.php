<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000000;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .header {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="{{ $tab === 'pending' ? 5 : 6 }}" class="header">
                {{ $mainHeading }}
            </td>
        </tr>
        <tr>
            <th>Sl No</th>
            <th>Petition No</th>
            <th>Order No</th>
            <th>Decision</th>
            @if($tab === 'pending')
                <th>Date</th>
            @else
                <th>Action No & Date</th>
                <th>Unit</th>
            @endif
        </tr>
        @forelse($decisions as $index => $decision)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $decision->petition->receipt_no }}</td>
                <td>{{ $decision->directorate_order_number ?? 'N/A' }}</td>
                <td>{{ \App\Models\Decision::getDecisionLabel($decision->final_decision) }}</td>
                @if($tab === 'pending')
                    <td>{{ \Carbon\Carbon::parse($decision->decision_date)->format('d-M-Y') }}</td>
                @else
                    <td>
                        {{ $decision->petition->compliance->action_number }}<br>
                        {{ \Carbon\Carbon::parse($decision->petition->compliance->action_date)->format('d-M-Y') }}
                    </td>
                    <td>{{ $decision->petition->compliance->unit->unit_name ?? 'N/A' }}</td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ $tab === 'pending' ? 5 : 6 }}" class="text-center">No records found.</td>
            </tr>
        @endforelse
    </table>
</body>
</html>
