<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-type" content="text/html;charset=utf-8" />
    <style>
        table { border-collapse: collapse; width: 100%; border: 1pt solid #000; }
        th { background-color: #cbd5e1; font-weight: bold; border: 1pt solid #000; padding: 10px 5px; font-size: 11pt; font-family: Arial, sans-serif; text-align: center; vertical-align: middle; }
        td { border: 1pt solid #cbd5e1; padding: 8px 5px; vertical-align: middle; font-size: 10pt; font-family: Arial, sans-serif; text-align: center; }
        td.text-left { text-align: center; }
        .header-title { font-size: 16pt; font-weight: bold; text-align: center; font-family: Arial, sans-serif; }
        .bold { font-weight: bold; }
        @page {
            margin: 0.5in;
            mso-header-margin: 0.5in;
            mso-footer-margin: 0.5in;
            mso-page-orientation: landscape;
        }
    </style>
</head>
<body>
    <table>
        <tr><td colspan="3" class="header-title" style="border:none;">{{ $mainHeading }}</td></tr>
        <tr><td colspan="3" style="border:none; height:10px;"></td></tr>
        
        <colgroup>
            <col style="width: 50pt;"> <!-- Sl No -->
            <col style="width: 250pt;"> <!-- Department Name -->
            <col style="width: 100pt;"> <!-- Total Petitions -->
        </colgroup>
        <thead>
            <tr>
                <th>Sl No</th>
                <th>Department Name</th>
                <th>Total Petitions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left bold">{{ $row->department_name }}</td>
                    <td class="bold">{{ $row->total_petitions }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No departmental data available</td>
                </tr>
            @endforelse
            
            @if($data->count() > 0)
                <tr>
                    <td colspan="2" class="bold" style="text-align: right; background-color: #f1f5f9;">Total Petitions:</td>
                    <td class="bold" style="background-color: #f1f5f9; color: #000; font-size: 12pt;">
                        {{ $data->sum('total_petitions') }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
