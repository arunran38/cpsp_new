<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-type" content="text/html;charset=utf-8" />
    <style>
        table { border-collapse: collapse; width: 100%; border: 1pt solid #000; }
        th { background-color: #cbd5e1; font-weight: bold; border: 1pt solid #000; padding: 10px 5px; font-size: 11pt; font-family: Arial, sans-serif; text-align: center; vertical-align: middle; }
        td { border: 1pt solid #cbd5e1; padding: 8px 5px; vertical-align: middle; font-size: 10pt; font-family: Arial, sans-serif; text-align: center; }
        .header-title { font-size: 16pt; font-weight: bold; text-align: center; font-family: Arial, sans-serif; }
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
            @foreach ($columns as $column)
                @if($column === '#') <col style="width: 30pt;">
                @elseif($column === 'Receipt No') <col style="width: 80pt;">
                @elseif($column === 'Description' || str_contains($column, 'Address')) <col style="width: 220pt;">
                @elseif(in_array($column, ['VR Remarks', 'Decision', 'Final Recommendation'])) <col style="width: 150pt;">
                @else <col style="width: 100pt;">
                @endif
            @endforeach
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
                    $complainants = $petition->addresses->where('person_type', 'Complainant');
                    $accused = $petition->addresses->where('person_type', 'Accused');
                    
                    // Helpers to extract specific fields
                    $getNames = fn($list) => implode('<br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">', $list->pluck('person_name')->toArray());
                    $getPhones = fn($list) => implode('<br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">', $list->pluck('phone')->filter()->toArray());
                    $getEmails = fn($list) => implode('<br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">', $list->pluck('email')->filter()->toArray());
                    
                    $getAddresses = function($list) {
                        $details = [];
                        foreach ($list as $addr) {
                            $cleanPart = fn($val) => !empty(trim($val ?? '', ", \t\n\r")) ? e(trim($val, ", \t\n\r")) : null;
                            $addrParts = array_filter([
                                $cleanPart($addr->full_address),
                                $cleanPart($addr->district?->district_name),
                                $cleanPart($addr->pincode)
                            ]);
                            if (!empty($addrParts)) {
                                $details[] = implode(', ', $addrParts);
                            }
                        }
                        return implode('<br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">', $details);
                    };

                    $getNameAndAddress = function($list) {
                        $details = [];
                        foreach ($list as $addr) {
                            $detail = "<strong>" . e($addr->person_name) . "</strong>";
                            if ($addr->entity_type === 'Firm' && !empty($addr->contact_person)) {
                                $detail .= " (Contact: " . e($addr->contact_person) . ")";
                            }
                            
                            $cleanPart = fn($val) => !empty(trim($val ?? '', ", \t\n\r")) ? e(trim($val, ", \t\n\r")) : null;
                            $addrParts = array_filter([
                                $cleanPart($addr->full_address),
                                $cleanPart($addr->district?->district_name),
                                $cleanPart($addr->pincode),
                                !empty($addr->phone) ? "Ph: " . $cleanPart($addr->phone) : null,
                                !empty($addr->email) ? "Email: " . $cleanPart($addr->email) : null
                            ]);
                            
                            if (count($addrParts) > 0) $detail .= "<br style=\"mso-data-placement:same-cell;\">" . implode(', ', $addrParts);
                            $details[] = $detail;
                        }
                        return implode('<br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">', $details);
                    };

                    $rowData = [
                        '#' => '<td class="text-center">' . ($index + 1) . '</td>',
                        'Receipt No' => '<td style="mso-number-format:\'\@\';" class="bold">' . $petition->receipt_no . '</td>',
                        'Received Date' => '<td class="text-center">' . date('d-m-Y', strtotime($petition->date_of_petition_received)) . '</td>',
                        'File No' => '<td class="text-center">' . ($petition->file_no ?? 'N/A') . '</td>',
                        'File Date' => '<td class="text-center">' . ($petition->file_created_date ? date('d-m-Y', strtotime($petition->file_created_date)) : 'N/A') . '</td>',
                        'Complainant Name & Address' => '<td>' . $getNameAndAddress($complainants) . '</td>',
                        'Suspect Name & Address' => '<td>' . $getNameAndAddress($accused) . '</td>',
                        'Complainant Name' => '<td>' . $getNames($complainants) . '</td>',
                        'Complainant Phone' => '<td>' . $getPhones($complainants) . '</td>',
                        'Complainant Email' => '<td>' . $getEmails($complainants) . '</td>',
                        'Complainant Address' => '<td>' . $getAddresses($complainants) . '</td>',
                        'Suspect Name' => '<td>' . $getNames($accused) . '</td>',
                        'Suspect Phone' => '<td>' . $getPhones($accused) . '</td>',
                        'Suspect Email' => '<td>' . $getEmails($accused) . '</td>',
                        'Suspect Address' => '<td>' . $getAddresses($accused) . '</td>',
                        'Nature' => '<td>' . $petition->nature_of_petition . '</td>',
                        'Description' => '<td>' . $petition->description . '</td>',
                        'Mode' => '<td class="text-center">' . $petition->mode_of_petition_received . '</td>',
                        'Proposed Action' => '<td>' . $petition->proposed_action . '</td>',
                        'Present Status' => '<td class="text-center"><span class="bold">' . $petition->status . '</span></td>',
                        'Seat' => '<td>' . ($petition->seat->seat_name ?? 'N/A') . '</td>',
                        'Unit' => '<td>' . ($petition->latestForwarding->toUnit->unit_name ?? 'N/A') . '</td>',
                        'Forwarded Date' => '<td class="text-center">' . ($petition->latestForwarding?->forwarded_date ? date('d-m-Y', strtotime($petition->latestForwarding->forwarded_date)) : 'N/A') . '</td>',
                        'VR Ref No' => '<td>' . ($petition->latestForwarding->vr_ref_no ?? 'N/A') . '</td>',
                        'VR Date' => '<td class="text-center">' . ($petition->latestForwarding?->vr_date ? date('d-m-Y', strtotime($petition->latestForwarding->vr_date)) : 'N/A') . '</td>',
                        'VR Received At CPSP' => '<td class="text-center">' . ($petition->latestForwarding?->vr_received_at_cpsp_date ? date('d-m-Y', strtotime($petition->latestForwarding->vr_received_at_cpsp_date)) : 'N/A') . '</td>',
                        'VR Remarks' => '<td>' . ($petition->latestForwarding->vr_remarks ?? 'N/A') . '</td>',
                        'Decision' => '<td>' . ($petition->decision->final_decision ?? 'N/A') . '</td>',
                        'Final Decision Date' => '<td class="text-center">' . ($petition->decision?->decision_date ? date('d-m-Y', strtotime($petition->decision->decision_date)) : 'N/A') . '</td>',
                        'Final Recommendation' => '<td>' . ($petition->decision->final_decision ?? 'N/A') . '</td>',
                        'Directorate Order No' => '<td>' . ($petition->decision->directorate_order_number ?? 'N/A') . '</td>',
                    ];
                @endphp
                <tr>
                    @foreach ($columns as $column)
                        {!! $rowData[$column] ?? '<td>-</td>' !!}
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top: 15px; font-size: 8pt; text-align: right; font-family: Arial, sans-serif;">Generated by CPSP System</div>
</body>
</html>

