<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuestionImportTemplateController extends Controller
{
    /**
     * Unduh template Excel impor soal (header + 2 baris contoh).
     */
    public function __invoke(): BinaryFileResponse
    {
        $path = storage_path('app/template-import-soal.xlsx');

        SimpleExcelWriter::create($path)
            ->addRow([
                'section' => 'TWK',
                'passage' => '',
                'text' => 'Nilai persatuan Indonesia tercermin dalam sila Pancasila ke ....',
                'option_a' => '1',
                'option_b' => '2',
                'option_c' => '3',
                'option_d' => '4',
                'option_e' => '5',
                'correct_answer' => 'C',
                'points' => 5,
                'explanation' => 'Sila ketiga, Persatuan Indonesia, menegaskan nilai persatuan bangsa.',
                'image_url' => '',
            ])
            ->addRow([
                'section' => 'TIU',
                'passage' => '',
                'text' => 'Perhatikan gambar pola berikut. Bangun selanjutnya adalah ....',
                'option_a' => 'Segitiga',
                'option_b' => 'Persegi',
                'option_c' => 'Lingkaran',
                'option_d' => 'Trapesium',
                'option_e' => '',
                'correct_answer' => 'B',
                'points' => 5,
                'explanation' => 'Pola berulang setiap 3 bangun sehingga urutan berikutnya persegi.',
                'image_url' => 'https://drive.google.com/file/d/CONTOH_ID_FILE/view',
            ])
            ->close();

        return response()->download($path, 'template-import-soal.xlsx');
    }
}
