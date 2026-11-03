<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditLogsExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected Collection $logs;
    protected array $filters;

    public function __construct(Collection $logs, array $filters = [])
    {
        $this->logs = $logs;
        $this->filters = $filters;
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->logs as $index => $log) {
            $rows[] = [
                $index + 1,
                $log->audit_id,
                $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'N/A',
                $log->session_duration ?? 'N/A',
                $log->user_name ?? ($log->user->name ?? 'System/Guest'),
                $log->user_pen ?? ($log->user->pen ?? 'N/A'),
                $log->seat ? $log->seat->seat_name : 'N/A',
                $log->action,
                $log->module,
                $log->description,
                $log->ip_address ?? '127.0.0.1',
                $log->url,
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        $dateInfo = 'Generated on: ' . now()->format('d/m/Y h:i A');
        if (!empty($this->filters['from_date']) && !empty($this->filters['to_date'])) {
            $dateInfo .= ' | Period: ' . $this->filters['from_date'] . ' to ' . $this->filters['to_date'];
        }

        return [
            ['CPSP - Audit Log Records Export'],
            [$dateInfo],
            [],
            [
                '#',
                'Log ID',
                'Date & Time',
                'Session Duration',
                'User Name',
                'PEN',
                'Seat',
                'Action',
                'Module',
                'Description',
                'IP Address',
                'URL',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->logs) + 4;
        $lastCol = 'L';

        // Merge title rows
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->mergeCells("A2:{$lastCol}2");

        // Page setup
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        // Alignment
        $sheet->getStyle("A1:{$lastCol}2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A4:B{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C4:C{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D4:D{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F4:F{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H4:I{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("K4:K{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Borders
        if (count($this->logs) > 0) {
            $sheet->getStyle("A4:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        return [
            1 => ['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '0F172A']]],
            2 => ['font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '475569']]],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B'], // Slate 800
                ],
            ],
        ];
    }
}

