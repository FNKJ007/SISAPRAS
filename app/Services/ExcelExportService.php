<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExportService
{
    /**
     * Inisialisasi Spreadsheet baru lengkap dengan Header Resmi Dinas & BRAMA
     */
    public static function createWithHeader(string $title, ?string $subtitle = null, array $meta = []): array
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setShowGridLines(true);

        // Header Title Blok
        $sheet->setCellValue('A1', 'DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN KABUPATEN BANDUNG');
        $sheet->setCellValue('A2', 'SISTEM INFORMASI BRAMA (BERKALA RAWAT ARMADA, ALAT, DAN SARANA)');
        $sheet->setCellValue('A3', strtoupper($title));

        $sub = $subtitle ?? ('Tanggal Cetak: ' . date('d/m/Y H:i') . ' WIB');
        $sheet->setCellValue('A4', $sub);

        // Styling Kop Dokumen
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('16244F'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(9.5)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4B5563'));
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F172A'));
        $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(8.5)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        $currentRow = 6;

        // Meta info tambahan jika ada
        if (!empty($meta)) {
            foreach ($meta as $label => $val) {
                $sheet->setCellValue('A' . $currentRow, $label . ':');
                $sheet->setCellValue('B' . $currentRow, $val);
                $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(9);
                $sheet->getStyle('B' . $currentRow)->getFont()->setSize(9);
                $currentRow++;
            }
            $currentRow++;
        }

        return [$spreadsheet, $sheet, $currentRow];
    }

    /**
     * Menuliskan header kolom tabel dengan style navy gelap & teks putih tebal
     */
    public static function setTableHeaders(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $row, array $headers): void
    {
        $colIndex = 1;
        foreach ($headers as $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($colLetter . $row, $header);
            $colIndex++;
        }

        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = "A{$row}:{$lastColLetter}{$row}";

        $sheet->getRowDimension($row)->setRowHeight(26);

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 10,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '16244F'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '0F172A'],
                ],
            ],
        ]);
    }

    /**
     * Menerapkan style zebra, border, dan format pada baris data
     */
    public static function styleDataRows(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        int $startRow,
        int $endRow,
        int $totalCols,
        array $currencyCols = [],
        array $centerCols = [],
        array $rightCols = []
    ): void {
        if ($endRow < $startRow) {
            return;
        }

        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

        // Border seluruh area data
        $sheet->getStyle("A{$startRow}:{$lastColLetter}{$endRow}")->applyFromArray([
            'font' => [
                'size' => 9.5,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'D1D5DB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Zebra stripes & row heights
        for ($r = $startRow; $r <= $endRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(20);
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:{$lastColLetter}{$r}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');
            }
        }

        // Center columns
        foreach ($centerCols as $colIndex) {
            $colLetter = is_numeric($colIndex) ? \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) : $colIndex;
            $sheet->getStyle("{$colLetter}{$startRow}:{$colLetter}{$endRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Right-aligned columns
        foreach ($rightCols as $colIndex) {
            $colLetter = is_numeric($colIndex) ? \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) : $colIndex;
            $sheet->getStyle("{$colLetter}{$startRow}:{$colLetter}{$endRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // Currency columns
        foreach ($currencyCols as $colIndex) {
            $colLetter = is_numeric($colIndex) ? \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) : $colIndex;
            $sheet->getStyle("{$colLetter}{$startRow}:{$colLetter}{$endRow}")
                ->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle("{$colLetter}{$startRow}:{$colLetter}{$endRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
    }

    /**
     * Auto fit column widths (kolom A diset fixed kecil & compact khusus untuk kolom NO)
     */
    public static function autoFitColumns(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $totalCols): void
    {
        // Kolom A selalu untuk 'NO', gunakan ukuran kecil dan proporsional (width 6.5)
        $sheet->getColumnDimension('A')->setAutoSize(false);
        $sheet->getColumnDimension('A')->setWidth(6.5);

        for ($i = 2; $i <= $totalCols; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
    }

    /**
     * Stream download response file Excel .xlsx
     */
    public static function streamDownload(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        $sanitizedFilename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
        if (!str_ends_with($sanitizedFilename, '.xlsx')) {
            $sanitizedFilename .= '.xlsx';
        }

        return new StreamedResponse(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $sanitizedFilename . '"',
                'Cache-Control'       => 'max-age=0',
                'Pragma'              => 'public',
            ]
        );
    }
}
