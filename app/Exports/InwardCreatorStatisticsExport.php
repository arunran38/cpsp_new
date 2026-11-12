<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InwardCreatorStatisticsExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected array $creatorStats;
    protected array $summaryTotals;
    protected ?string $dateFrom;
    protected ?string $dateTo;
    protected bool $canViewProcessed;
    protected string $section;
    protected string $creatorLabel;
    protected string $origin;
    protected string $originLabel;

    public function __construct(
        array $creatorStats,
        array $summaryTotals,
        ?string $dateFrom,
        ?string $dateTo,
        bool $canViewProcessed,
        string $section = 'all',
        string $creatorLabel = 'All Creators',
        string $origin = 'inward',
        string $originLabel = 'Inward Registered'
    ) {
        $this->creatorStats = $creatorStats;
        $this->summaryTotals = $summaryTotals;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->canViewProcessed = $canViewProcessed;
        $this->section = $section;
        $this->creatorLabel = $creatorLabel;
        $this->origin = $origin;
        $this->originLabel = $originLabel;
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->creatorStats as $index => $stat) {
            $row = [
                $index + 1,
                $stat['name'],
                $stat['pen'] ?? '-',
                $stat['designation'] ?? 'Staff',
                $stat['seat_name'] ?? $stat['section'],
                $stat['total_created'],
            ];

            if ($this->canViewProcessed) {
                $row[] = $stat['processed'];
                $row[] = $stat['pending'];
            }

            $rows[] = $row;
        }

        // Empty row before summary
        $emptyRow = array_fill(0, $this->canViewProcessed ? 8 : 6, '');
        $rows[] = $emptyRow;

        // Summary Row
        $summaryRow = [
            'Total Summary',
            '',
            '',
            '',
            '',
            $this->summaryTotals['totalCreated'],
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

        $title = 'Petition Created By - Staff Statistics Data Sheet';

        $headings = [
            [$title],
            [$period . ' | Petition Creator Scope: ' . $this->creatorLabel . ' | Origin: ' . ($this->originLabel ?? 'Inward Registered')],
            [],
            [
                '#',
                'Creator / Staff Name',
                'PEN',
                'Designation',
                'Assigned Section / Seat',
                'Total Petitions Created',
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
        $lastRow = count($this->creatorStats) + 6; // 4 heading rows + data + empty row + summary
        $lastCol = $this->canViewProcessed ? 'H' : 'F';

        // Merge title rows so it doesn't break AutoSize on Column A
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->mergeCells("A2:{$lastCol}2");

        // Page setup for printing
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // Alignments
        $sheet->getStyle("A1:{$lastCol}2")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A4:A{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C4:C{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F4:{$lastCol}{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Borders for table data
        $tableDataRange = "A4:{$lastCol}" . ($lastRow - 2);
        $sheet->getStyle($tableDataRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Borders for Summary row
        $sheet->getStyle("A{$lastRow}:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $styles = [
            1    => ['font' => ['bold' => true, 'size' => 14]],
            2    => ['font' => ['italic' => true, 'color' => ['rgb' => '555555']]],
            4    => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0F766E'], // Teal 700
                ]
            ],
            $lastRow => ['font' => ['bold' => true]],
        ];

        return $styles;
    }
}

