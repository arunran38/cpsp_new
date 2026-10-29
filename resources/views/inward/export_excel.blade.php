<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th,
    td {
        border: 1px solid #000000;
        text-align: center;
        vertical-align: middle;
        padding: 5px;
    }

    th {
        background-color: #e2e8f0;
        font-weight: bold;
        text-decoration: none !important;
    }
</style>
@php
    $isReturnedFilter = ($filter ?? request('filter')) === 'returned';
@endphp
<table>
    <thead>
        <tr>
            <th colspan="{{ $isReturnedFilter ? '9' : '8' }}" style="font-size: 16px; font-weight: bold; text-decoration: none !important;">Inward Petitions Report</th>
        </tr>
        <tr>
            <th>#</th>
            <th>Receipt No</th>
            <th>Date of Receipt</th>
            <th>Complainant Name</th>
            <th>Mode of Receipt</th>
            <th>Concerned Seat</th>
            <th>Transferred On</th>
            @if($isReturnedFilter)
                <th>Reason for Return</th>
            @endif
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transfers as $index => $item)
            @php
                $complainant = $item->displayAddresses()->firstWhere('person_type', 'Complainant');
                $wf = $item->workflow_status;
                $status = $wf['label'];
                if ($wf['label'] === 'Transferred' && $item->seat) {
                    $status = 'Transferred to ' . $item->seat->seat_name;
                } elseif ($wf['label'] === 'Returned') {
                    $status = 'Returned to Inward';
                } elseif ($wf['label'] === 'Processed') {
                    $status = 'Processed by CPSP';
                }
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->receipt_no }}</td>
                <td>{{ \Carbon\Carbon::parse($item->date_of_petition_received)->format('d-m-Y') }}</td>
                <td>{{ $complainant?->person_name ?? 'N/A' }}</td>
                <td>{{ $item->mode_of_petition_received }}{{ strtolower($item->mode_of_petition_received) === 'unit' && $item->unit ? ' - ' . $item->unit->unit_name : '' }}</td>
                <td>{{ $item->seat ? $item->seat->seat_name : 'Not Assigned' }}</td>
                <td>{{ $item->created_at?->format('d-m-Y') }}</td>
                @if($isReturnedFilter)
                    <td>{{ $item->is_returned_to_inward ? $item->return_reason : '—' }}</td>
                @endif
                <td>{{ $status }}</td>
            </tr>
        @endforeach
    </tbody>
</table>