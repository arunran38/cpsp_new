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
                @elseif(in_array($column, ['VR Remarks', 'Decision', 'Final Recommendation', 'Recommendation Type', 'Directorate Order No'])) <col style="width: 150pt;">
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
                    $displayAddresses = $petition->displayAddresses();
                    $complainants = $displayAddresses->where('person_type', 'Complainant');
                    $accused = $displayAddresses->where('person_type', 'Accused');
                    
                    $isPlaceholderText = function (?string $val): bool {
                        if ($val === null) {
                            return true;
                        }
                        $trimmed = trim(strtolower($val), " \t\n\r\0\x0B.,-");
                        return in_array($trimmed, ['', 'not provided', 'not provided.', 'not available', 'not available.', 'n/a', 'na', 'none', 'nil', 'null'], true);
                    };

                    // Complainant helpers
                    $getComplainantNames = fn($list) => implode('<br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">', $list->pluck('person_name')->filter()->unique()->toArray());
                    $getComplainantPhones = fn($list) => implode('<br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">', $list->pluck('phone')->filter()->unique()->toArray());
                    $getComplainantEmails = fn($list) => implode('<br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">', $list->pluck('email')->filter()->unique()->toArray());
                    
                    $getComplainantAddresses = function($list) use ($isPlaceholderText) {
                        $details = [];
                        foreach ($list as $addr) {
                            $cleanPart = fn($val) => !empty(trim($val ?? '', ", \t\n\r")) ? e(trim($val, ", \t\n\r")) : null;
                            $validFull = $isPlaceholderText($addr->full_address) ? null : $cleanPart($addr->full_address);
                            $addrParts = array_filter([
                                $validFull,
                                $cleanPart($addr->district?->district_name),
                                $cleanPart($addr->pincode)
                            ]);
                            if (!empty($addrParts)) {
                                $details[] = implode(', ', $addrParts);
                            }
                        }
                        return implode('<br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">', array_unique($details));
                    };

                    $getComplainantNameAndAddress = function($list) use ($isPlaceholderText) {
                        $details = [];
                        foreach ($list as $addr) {
                            $detail = "<strong>" . e($addr->person_name) . "</strong>";
                            if ($addr->entity_type === 'Firm' && !empty($addr->contact_person)) {
                                $detail .= " (Contact: " . e($addr->contact_person) . ")";
                            }
                            
                            $cleanPart = fn($val) => !empty(trim($val ?? '', ", \t\n\r")) ? e(trim($val, ", \t\n\r")) : null;
                            $validFull = $isPlaceholderText($addr->full_address) ? null : $cleanPart($addr->full_address);
                            $addrParts = array_filter([
                                $validFull,
                                $cleanPart($addr->district?->district_name),
                                $cleanPart($addr->pincode),
                                !empty($addr->phone) ? "Ph: " . $cleanPart($addr->phone) : null,
                                !empty($addr->email) ? "Email: " . $cleanPart($addr->email) : null
                            ]);
                            
                            if (count($addrParts) > 0) {
                                $detail .= "<br style=\"mso-data-placement:same-cell;\">" . implode(', ', $addrParts);
                            }
                            $details[] = $detail;
                        }
                        return implode('<br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">', $details);
                    };

                    // Suspect (Accused) structured processing
                    $accusedGroups = $accused->groupBy(function ($addr) {
                        if (in_array($addr->person_name, ['Unknown', 'Unknown Suspect', 'Anonymous / Unknown'])) {
                            return $addr->person_name . '_' . ($addr->department_id ?? '') . '_' . ($addr->designation_id ?? '') . '_' . ($addr->phone ?? '') . '_' . ($addr->pen_number ?? '') . '_' . ($addr->entity_type ?? '');
                        }
                        return $addr->person_name . '_' . ($addr->department_id ?? '') . '_' . ($addr->entity_type ?? '');
                    });

                    $suspectEntries = [];
                    foreach ($accusedGroups as $group) {
                        $first = $group->first();
                        $name = $first->person_name;
                        $entityType = $first->entity_type;
                        $contactPerson = $first->contact_person;
                        $phone = $group->pluck('phone')->filter()->first();
                        $email = $group->pluck('email')->filter()->first();
                        
                        // Department details
                        $deptName = null;
                        if ($first->department && !empty(trim($first->department->department_name ?? ''))) {
                            $deptName = trim($first->department->department_name);
                        }

                        // Collect valid addresses for this suspect (ignoring default/placeholder text)
                        $validAddresses = [];
                        foreach ($group as $addr) {
                            $cleanPart = fn($val) => !empty(trim($val ?? '', ", \t\n\r")) ? e(trim($val, ", \t\n\r")) : null;
                            $validFull = $isPlaceholderText($addr->full_address) ? null : $cleanPart($addr->full_address);
                            
                            $parts = array_filter([
                                $validFull,
                                $cleanPart($addr->district?->district_name),
                                $cleanPart($addr->pincode)
                            ]);
                            if (!empty($parts)) {
                                $validAddresses[] = implode(', ', $parts);
                            }
                        }
                        $validAddresses = array_values(array_unique($validAddresses));

                        // Combined address and department with clear separation
                        $combinedAddressDept = [];
                        if (!empty($validAddresses)) {
                            $combinedAddressDept[] = implode('<br style="mso-data-placement:same-cell;">', $validAddresses);
                        }
                        if (!empty($deptName)) {
                            $combinedAddressDept[] = "Department: " . e($deptName);
                        }

                        // Full block for 'Suspect Name & Address'
                        $nameAndAddressBlock = "<strong>" . e($name) . "</strong>";
                        if ($entityType === 'Firm' && !empty($contactPerson)) {
                            $nameAndAddressBlock .= " (Contact: " . e($contactPerson) . ")";
                        }
                        if (!empty($combinedAddressDept)) {
                            $nameAndAddressBlock .= "<br style=\"mso-data-placement:same-cell;\">" . implode('<br style="mso-data-placement:same-cell;">', $combinedAddressDept);
                        }
                        if (!empty($phone)) {
                            $nameAndAddressBlock .= "<br style=\"mso-data-placement:same-cell;\">Ph: " . e($phone);
                        }
                        if (!empty($email)) {
                            $nameAndAddressBlock .= "<br style=\"mso-data-placement:same-cell;\">Email: " . e($email);
                        }

                        $suspectEntries[] = [
                            'name' => e($name) . ($entityType === 'Firm' && !empty($contactPerson) ? " (Contact: " . e($contactPerson) . ")" : ""),
                            'name_and_address' => $nameAndAddressBlock,
                            'address_and_dept' => !empty($combinedAddressDept) ? implode('<br style="mso-data-placement:same-cell;">', $combinedAddressDept) : null,
                            'phone' => !empty($phone) ? e($phone) : null,
                            'email' => !empty($email) ? e($email) : null,
                        ];
                    }

                    $suspectNameAndAddressCell = !empty($suspectEntries)
                        ? implode('<br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">', array_column($suspectEntries, 'name_and_address'))
                        : '';

                    $suspectAddressesOnly = array_filter(array_column($suspectEntries, 'address_and_dept'));
                    $suspectAddressCell = !empty($suspectAddressesOnly)
                        ? implode('<br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">', $suspectAddressesOnly)
                        : '';

                    $suspectNamesOnly = array_filter(array_column($suspectEntries, 'name'));
                    $suspectNameCell = !empty($suspectNamesOnly)
                        ? implode('<br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">', $suspectNamesOnly)
                        : '';

                    $suspectPhonesOnly = array_filter(array_column($suspectEntries, 'phone'));
                    $suspectPhoneCell = !empty($suspectPhonesOnly)
                        ? implode('<br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">', $suspectPhonesOnly)
                        : '';

                    $suspectEmailsOnly = array_filter(array_column($suspectEntries, 'email'));
                    $suspectEmailCell = !empty($suspectEmailsOnly)
                        ? implode('<br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">', $suspectEmailsOnly)
                        : '';

                    $rowData = [
                        '#' => '<td class="text-center">' . ($index + 1) . '</td>',
                        'Receipt No' => '<td style="mso-number-format:\'\@\';" class="bold">' . $petition->receipt_no . '</td>',
                        'Received Date' => '<td class="text-center">' . date('d-m-Y', strtotime($petition->date_of_petition_received)) . '</td>',
                        'File No' => '<td class="text-center">' . ($petition->file_no ?? 'N/A') . '</td>',
                        'File Date' => '<td class="text-center">' . ($petition->file_created_date ? date('d-m-Y', strtotime($petition->file_created_date)) : 'N/A') . '</td>',
                        'Complainant Name & Address' => '<td>' . $getComplainantNameAndAddress($complainants) . '</td>',
                        'Suspect Name & Address' => '<td>' . $suspectNameAndAddressCell . '</td>',
                        'Complainant Name' => '<td>' . $getComplainantNames($complainants) . '</td>',
                        'Complainant Phone' => '<td>' . $getComplainantPhones($complainants) . '</td>',
                        'Complainant Email' => '<td>' . $getComplainantEmails($complainants) . '</td>',
                        'Complainant Address' => '<td>' . $getComplainantAddresses($complainants) . '</td>',
                        'Suspect Name' => '<td>' . $suspectNameCell . '</td>',
                        'Suspect Phone' => '<td>' . $suspectPhoneCell . '</td>',
                        'Suspect Email' => '<td>' . $suspectEmailCell . '</td>',
                        'Suspect Address' => '<td>' . $suspectAddressCell . '</td>',
                        'Nature' => '<td>' . $petition->nature_of_petition . '</td>',
                        'Description' => '<td>' . $petition->description . '</td>',
                        'Mode' => '<td class="text-center">' . $petition->mode_of_petition_received . (strtolower($petition->mode_of_petition_received) === 'unit' && $petition->unit ? ' (' . $petition->unit->unit_name . ')' : '') . '</td>',
                        'Proposed Action' => '<td>' . $petition->proposed_action . '</td>',
                        'Present Status' => '<td class="text-center"><span class="bold">' . $petition->status . '</span></td>',
                        'Seat' => '<td>' . ($petition->seat->seat_name ?? 'N/A') . '</td>',
                        'Unit' => '<td>' . ($petition->latestForwarding->toUnit->unit_name ?? 'N/A') . '</td>',
                        'Forwarded Date' => '<td class="text-center">' . ($petition->latestForwarding?->forwarded_date ? date('d-m-Y', strtotime($petition->latestForwarding->forwarded_date)) : 'N/A') . '</td>',
                        'VR Ref No' => '<td>' . ($petition->latestForwarding->vr_ref_no ?? 'N/A') . '</td>',
                        'VR Date' => '<td class="text-center">' . ($petition->latestForwarding?->vr_date ? date('d-m-Y', strtotime($petition->latestForwarding->vr_date)) : 'N/A') . '</td>',
                        'VR Received At CPSP' => '<td class="text-center">' . ($petition->latestForwarding?->vr_received_at_cpsp_date ? date('d-m-Y', strtotime($petition->latestForwarding->vr_received_at_cpsp_date)) : 'N/A') . '</td>',
                        'VR Remarks' => '<td>' . ($petition->latestForwarding->vr_remarks ?? 'N/A') . '</td>',
                        'Decision' => '<td>' . ($petition->decision ? \App\Models\Decision::getDecisionLabel($petition->decision->final_decision) : 'N/A') . '</td>',
                        'Final Decision Date' => '<td class="text-center">' . ($petition->decision?->decision_date ? date('d-m-Y', strtotime($petition->decision->decision_date)) : 'N/A') . '</td>',
                        'Final Recommendation' => '<td>' . ($petition->decision ? $petition->decision->final_decision : 'N/A') . '</td>',
                        'Recommendation Type' => '<td class="text-center">' . (($petition->decision && $petition->decision->govt_recommendation_type) ? $petition->decision->govt_recommendation_type : 'N/A') . '</td>',
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
