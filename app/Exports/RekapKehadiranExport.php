<?php

namespace App\Exports;

use App\Models\Rapat;
use App\Models\Undangan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapKehadiranExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected string $rapatId;

    protected ?Rapat $rapat = null;

    private int $nomorUrut = 0;

    public function __construct(string $rapatId)
    {
        $this->rapatId = $rapatId;
        $this->rapat = Rapat::find($rapatId);
    }

    /**
     * Mengamankan teks sel dari formula injection (CSV/Excel injection)
     * dan membersihkan karakter tak lazim.
     */
    public function amankanSel(?string $teks): string
    {
        if ($teks === null) {
            return '-';
        }

        $bersih = preg_replace('/[*~`]+/', '', $teks);
        $bersih = preg_replace('/\s+/', ' ', (string) $bersih);
        $bersih = trim($bersih);

        if ($bersih === '' || $bersih === '-') {
            return '-';
        }

        // Formula injection mitigation:
        // Jika nilai diawali dengan karakter formula =, +, -, @, \t, atau \r,
        // tambahkan tanda petik tunggal ' agar dieksekusi sebagai teks literal di Excel
        if (preg_match('/^[=+\-@\t\r]/', $bersih)) {
            return "'".$bersih;
        }

        return $bersih;
    }

    /**
     * Ambil data koleksi undangan beserta kehadiran
     */
    public function collection(): Collection
    {
        return Undangan::with(['kehadiran' => function ($query) {
            $query->where('dibatalkan', false);
        }])
            ->where('rapat_id', $this->rapatId)
            ->orderBy('urutan', 'asc')
            ->orderBy('dibuat_pada', 'asc')
            ->get();
    }

    /**
     * Header Kolom Excel
     */
    public function headings(): array
    {
        return [
            'No.',
            'Nama Peserta',
            'Jabatan',
            'Instansi / Banjar',
            'No. HP',
            'Status Kehadiran',
            'Waktu Hadir (WITA)',
            'Jalur Check-in',
            'Keterangan / Perwakilan',
            'Sumber Undangan',
        ];
    }

    /**
     * Mapping data per baris
     *
     * @param  Undangan  $row
     */
    public function map($row): array
    {
        $this->nomorUrut++;
        $kehadiran = $row->kehadiran->first();
        $hadir = ($kehadiran !== null && ! $kehadiran->dibatalkan);

        $statusKehadiran = $hadir ? 'HADIR' : 'BELUM HADIR';

        $waktuHadir = '-';
        $jalurCheckin = '-';
        $keterangan = '-';

        if ($hadir) {
            $waktu = $kehadiran->waktu_perangkat ?? $kehadiran->dibuat_pada;
            $waktuHadir = $waktu ? $waktu->timezone('Asia/Makassar')->format('d/m/Y H:i:s').' WITA' : '-';
            $jalurCheckin = strtoupper($kehadiran->jalur->value ?? '-');

            if (! empty($kehadiran->diwakili_oleh)) {
                $keterangan = 'Diwakili oleh: '.$this->amankanSel($kehadiran->diwakili_oleh);
            }
        }

        $sumber = $row->sumber?->value === 'tambahan' ? 'Tambahan di Tempat' : 'Daftar Undangan Resmi';

        return [
            $this->nomorUrut,
            $this->amankanSel($row->nama),
            $this->amankanSel($row->jabatan),
            $this->amankanSel($row->instansi),
            $this->amankanSel($row->hp),
            $statusKehadiran,
            $waktuHadir,
            $jalurCheckin,
            $keterangan,
            $sumber,
        ];
    }

    /**
     * Styling Header & Kolom
     */
    public function styles(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Style header
        $sheet->getStyle('A1:'.$highestColumn.'1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2B6446'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);

        // Border untuk seluruh tabel
        if ($highestRow > 1) {
            $sheet->getStyle('A1:'.$highestColumn.$highestRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D1D5DB'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Alignment center untuk kolom No, Status, Waktu, Jalur, Sumber
            $sheet->getStyle('A2:A'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E2:H'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J2:J'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }

    /**
     * Judul Sheet
     */
    public function title(): string
    {
        $kode = $this->rapat?->kode ?? 'RAPAT';

        return 'Kehadiran '.$kode;
    }
}
