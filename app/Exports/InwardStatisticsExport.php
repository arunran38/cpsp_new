<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InwardStatisticsExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $seatStats;
    protected $summaryTotals;
    protected $dateFrom;
    protected $dateTo;
    protected $canViewProcessed;

    public function __construct(array $seatStats, array $summaryTotals, $dateFrom, $dateTo, bool $canViewProcessed)
    {
        $this->seatStats = $seatStats;
        $this->summaryTotals = $summaryTotals;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->canViewProcessed = $canViewProcessed;
    }

    public function array(): array
    {
        $rows = [];
        
        // Data Rows
        foreach ($this->seatStats as $index => $stat) {
            $row = [
                $index + 1,
                $stat['seat_name'],
                $stat['occupant'],
                $stat['total_sent'],
            ];

            if ($this->canViewProcessed) {
                $row[] = $stat['processed'];
                $row[] = $stat['pending'];
            }

            $rows[] = $row;
        }
        
        // Empty row before summary
        $emptyRow = array_fill(0, $this->canViewProcessed ? 6 : 4, '');
        $rows[] = $emptyRow;

        // Summary Row
        $summaryRow = [
            'Total Summary',
            '',
            '',
            $this->summaryTotals['totalSent'],
        ];

        if ($this->canViewProcessed) {
            $summaryRow[] = $this->summaryTotals['processed'];
            $summaryRow[] = $this->summaryTotals['pending'];
        }

        $rows[] = $summaryRow;

        return $rows;
    }

    public function headings(): array
    {
        $period = 'Report Period: ';
        if ($this->dateFrom && $this->dateTo) {
            $period .= date('d/m/Y', strtotime($this->dateFrom)) . ' to ' . date('d/m/Y', strtotime($this->dateTo));
        } elseif ($this->dateFrom) {
            $period .= date('d/m/Y', strtotime($this->dateFrom)) . ' to Today';
        } elseif ($this->dateTo) {
            $period .= 'Beginning to ' . date('d/m/Y', strtotime($this->dateTo));
        } else {
            $period .= 'All Time';
        }

        $headings = [
            ['Inward Seat-Wise Petition Statistics Data Sheet'],
            [$period],
            [],
            [
                '#',
                'Concerned Seat',
                'Seat Occupant (Officer)',
                'Total Inward'
            ]
        ];

        if ($this->canViewProcessed) {
            $headings[3][] = 'Processed';
            $headings[3][] = 'Pending';
        }

        return $headings;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->seatStats) + 6; // 4 heading rows + data + empty row + summary
        $lastCol = $this->canViewProcessed ? 'F' : 'D';
        
        // Merge title rows so it doesn't break AutoSize on Column A
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->mergeCells("A2:{$lastCol}2");
        
        // Page setup for printing (A4, Portrait/Landscape, Fit to 1 page wide)
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        
        // Alignments
        $sheet->getStyle("A1:{$lastCol}2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A4:A{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D4:{$lastCol}{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Borders for table data (Row 4 to LastRow - 2)
        $tableDataRange = "A4:{$lastCol}" . ($lastRow - 2);
        $sheet->getStyle($tableDataRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Borders for Summary row
        $sheet->getStyle("A{$lastRow}:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        $styles = [
            // Title formatting
            1    => ['font' => ['bold' => true, 'size' => 14]],
            2    => ['font' => ['italic' => true, 'color' => ['rgb' => '555555']]],
            // Table Headers formatting
            4    => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0F766E'], // Teal 700
                ]
            ],
            // Summary row formatting
            $lastRow => ['font' => ['bold' => true]],
        ];
        
        return $styles;
    }
}
