<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-type" content="text/html;charset=utf-8" />
    <style>
        table { border-collapse: collapse; width: 100%; border: 1pt solid #000; }
        th { background-color: #cbd5e1; font-weight: bold; border: 1pt solid #000; padding: 10px 5px; font-size: 11pt; font-family: Arial, sans-serif; text-align: center; }
        td { border: 1pt solid #cbd5e1; padding: 8px 5px; vertical-align: top; font-size: 10pt; font-family: Arial, sans-serif; }
        .header-title { font-size: 16pt; font-weight: bold; text-align: center; font-family: Arial, sans-serif; text-decoration: underline; }
        @page {
            margin: 0.5in;
            mso-header-margin: 0.5in;
            mso-footer-margin: 0.5in;
            mso-page-orientation: landscape;
        }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <table>
        <tr><td colspan="{{ count($columns) }}" class="header-title" style="border:none;">{{ $mainHeading }}</td></tr>
        <tr><td colspan="{{ count($columns) }}" style="border:none; height:10px;"></td></tr>
        
        <colgroup>
            <col style="width: 30pt;"> <!-- # -->
            <col style="width: 80pt;"> <!-- Petition No -->
            <col style="width: 70pt;"> <!-- Received Date -->
            <col style="width: 220pt;"> <!-- Complainant Name & Address -->
            <col style="width: 220pt;"> <!-- Suspect Name & Address -->
            <col style="width: 90pt;"> <!-- Nature -->
            <col style="width: 250pt;"> <!-- Description -->
            <col style="width: 60pt;"> <!-- Mode -->
            <col style="width: 70pt;"> <!-- Status -->
            @if(Auth::user()->role === 'admin' || $tab !== 'all')
                <col style="width: 80pt;">
            @endif
            @if($tab === 'vrs')
                <col style="width: 80pt;">
            @endif
        </colgroup>
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($petitions as $index => $petition)
                @php
                    $complainantsDetails = [];
                    foreach ($petition->addresses->where('person_type', 'Complainant') as $addr) {
                        $detail = "<strong>" . e($addr->person_name) . "</strong>";
                        if ($addr->entity_type === 'Firm' && !empty($addr->contact_person)) {
                            $detail .= " (Contact: " . e($addr->contact_person) . ")";
                        }
                        
                        $addrParts = [];
                        if (!empty($addr->full_address)) {
                            $addrParts[] = e($addr->full_address);
                        }
                        if ($addr->district && !empty($addr->district->district_name)) {
                            $addrParts[] = e($addr->district->district_name);
                        }
                        if (!empty($addr->pincode)) {
                            $addrParts[] = e($addr->pincode);
                        }
                        if (!empty($addr->phone)) {
                            $addrParts[] = "Ph: " . e($addr->phone);
                        }
                        
                        if (count($addrParts) > 0) {
                            $detail .= "<br>" . implode(', ', $addrParts);
                        }
                        
                        $complainantsDetails[] = $detail;
                    }
                    
                    $accusedDetails = [];
                    foreach ($petition->addresses->where('person_type', 'Accused') as $addr) {
                        $detail = "<strong>" . e($addr->person_name) . "</strong>";
                        if ($addr->entity_type === 'Firm' && !empty($addr->contact_person)) {
                            $detail .= " (Contact: " . e($addr->contact_person) . ")";
                        }
                        
                        $addrParts = [];
                        if (!empty($addr->full_address)) {
                            $addrParts[] = e($addr->full_address);
                        }
                        if ($addr->district && !empty($addr->district->district_name)) {
                            $addrParts[] = e($addr->district->district_name);
                        }
                        if (!empty($addr->pincode)) {
                            $addrParts[] = e($addr->pincode);
                        }
                        if (!empty($addr->phone)) {
                            $addrParts[] = "Ph: " . e($addr->phone);
                        }
                        
                        if (count($addrParts) > 0) {
                            $detail .= "<br>" . implode(', ', $addrParts);
                        }
                        
                        $accusedDetails[] = $detail;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="mso-number-format:'\@';" class="bold">{{ $petition->petition_no }}</td>
                    <td class="text-center">{{ date('d-m-Y', strtotime($petition->date_of_petition_received)) }}</td>
                    <td>{!! implode('<br><br>', $complainantsDetails) !!}</td>
                    <td>{!! implode('<br><br>', $accusedDetails) !!}</td>
                    <td>{{ $petition->nature_of_petition }}</td>
                    <td>{{ $petition->description }}</td>
                    <td class="text-center">{{ $petition->mode_of_petition_received }}</td>
                    <td class="text-center"><span class="bold">{{ $petition->status }}</span></td>

                    @if (Auth::user()->role === 'admin')
                        <td>{{ $petition->seat->seat_name ?? 'N/A' }}</td>
                    @endif
                    @if ($tab === 'forwarded')
                        <td>{{ $petition->latestForwarding->toUnit->unit_name ?? 'N/A' }}</td>
                    @endif
                    @if ($tab === 'vrs')
                        <td>{{ $petition->latestForwarding->vr_ref_no ?? 'N/A' }}</td>
                        <td class="text-center">{{ $petition->latestForwarding->vr_date ? date('d-m-Y', strtotime($petition->latestForwarding->vr_date)) : 'N/A' }}</td>
                    @endif
                    @if ($tab === 'decisions')
                        <td>{{ $petition->decision->decision_remarks ?? 'N/A' }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top: 15px; font-size: 8pt; text-align: right; font-family: Arial, sans-serif;">Generated by CPSP System</div>
</body>
</html>
