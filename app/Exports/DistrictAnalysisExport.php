<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DistrictAnalysisExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected array $districtStats;
    protected int $grandTotal;
    protected ?string $dateFrom;
    protected ?string $dateTo;
    protected ?string $status;
    protected ?string $nature;
    protected ?string $mode;

    public function __construct(
        array $districtStats,
        int $grandTotal,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $status = null,
        ?string $nature = null,
        ?string $mode = null
    ) {
        $this->districtStats = $districtStats;
        $this->grandTotal = $grandTotal;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->status = $status;
        $this->nature = $nature;
        $this->mode = $mode;
    }

    public function array(): array
    {
        $rows = [];

        // Data Rows
        if (empty($this->districtStats)) {
            $rows[] = ['-', 'No district data available', 0];
        } else {
            foreach ($this->districtStats as $index => $stat) {
                $label = $stat['district_name'];
                if (!empty($stat['district_code'])) {
                    $label .= ' (' . $stat['district_code'] . ')';
                }

                $rows[] = [
                    $index + 1,
                    $label,
                    (int) $stat['petition_count'],
                ];
            }
        }

        // Empty separator row
        $rows[] = ['', '', ''];

        // Grand Total row
        $rows[] = [
            'Total',
            'Grand Total',
            $this->grandTotal,
        ];

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

        $filterParts = [$period];
        if (!empty($this->status)) {
            $statusLabel = in_array($this->status, ['Received', 'Processed']) ? 'Processed' : $this->status;
            $filterParts[] = 'Status: ' . $statusLabel;
        }
        if (!empty($this->nature)) {
            $filterParts[] = 'Nature: ' . $this->nature;
        }
        if (!empty($this->mode)) {
            $filterParts[] = 'Mode: ' . $this->mode;
        }

        return [
            ['District-wise Statistical Analysis Report'],
            [implode(' | ', $filterParts)],
            [],
            [
                '#',
                'District Name',
                'Petition Count',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $dataCount = count($this->districtStats);
        $lastRow = $dataCount + 6; // 4 heading rows + data + 1 blank + 1 total

        // Merge title rows across columns A to C
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A2:C2');

        // Page setup
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // Alignment
        $sheet->getStyle('A1:C2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A4:A{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C4:C{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Borders
        $tableDataRange = "A4:C" . ($lastRow - 2);
        $sheet->getStyle($tableDataRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['italic' => true, 'color' => ['rgb' => '555555']]],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0F766E'], // Teal 700
                ],
            ],
            $lastRow => ['font' => ['bold' => true]],
        ];
    }
}

