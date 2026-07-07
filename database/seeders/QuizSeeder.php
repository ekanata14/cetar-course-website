<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Seed kuis contoh dengan soal bersection (TWK/TIU/TKP & SNBT),
     * lalu distribusikan ke paket via pivot polimorfik `package_content`.
     */
    public function run(): void
    {
        // ==========================================
        // 1. TRY OUT CPNS (soal contoh nyata + filler dari factory)
        // ==========================================
        $cpnsQuiz = Quiz::create([
            'title' => 'Try Out Akbar SKD CPNS #1',
            'description' => 'Simulasi SKD CPNS sesuai kisi-kisi resmi: 30 TWK, 35 TIU, 45 TKP (versi ringkas untuk demo).',
            'duration_minutes' => 100,
        ]);

        $cpnsQuiz->questions()->createMany($this->cpnsSampleQuestions());

        // Filler tambahan per section agar grid ujian terasa nyata
        foreach (['TWK' => 5, 'TIU' => 5, 'TKP' => 5] as $section => $count) {
            Question::factory()->count($count)->create([
                'quiz_id' => $cpnsQuiz->id,
                'section' => $section,
            ]);
        }

        // ==========================================
        // 2. TRY OUT SNBT
        // ==========================================
        $snbtQuiz = Quiz::create([
            'title' => 'Try Out UTBK-SNBT #1',
            'description' => 'Simulasi UTBK-SNBT: Penalaran Umum dan Pengetahuan Kuantitatif (versi ringkas untuk demo).',
            'duration_minutes' => 90,
        ]);

        foreach (['Penalaran Umum' => 6, 'Pengetahuan Kuantitatif' => 6] as $section => $count) {
            Question::factory()->count($count)->create([
                'quiz_id' => $snbtQuiz->id,
                'section' => $section,
            ]);
        }

        // ==========================================
        // 3. Distribusi konten ke paket (morph pivot package_content)
        // ==========================================
        $cpnsPackage = Package::where('slug', 'pejuang-cpns-2026')->first();
        $snbtPackage = Package::where('slug', 'juara-snbt-2026')->first();

        $cpnsPackage?->quizzes()->attach($cpnsQuiz);
        $snbtPackage?->quizzes()->attach($snbtQuiz);
    }

    /**
     * Soal contoh bergaya SKD asli (1 per section) supaya demo terlihat kredibel.
     *
     * @return array<int, array<string, mixed>>
     */
    private function cpnsSampleQuestions(): array
    {
        return [
            [
                'section' => 'TWK',
                'passage' => null,
                'text' => 'Nilai-nilai Pancasila yang menjadi dasar dalam kehidupan berbangsa dan bernegara bersumber pada sila. Sikap rela berkorban demi kepentingan bangsa dan negara merupakan pengamalan sila ke...',
                'option_a' => 'Satu',
                'option_b' => 'Dua',
                'option_c' => 'Tiga',
                'option_d' => 'Empat',
                'option_e' => 'Lima',
                'correct_answer' => 'C',
                'points' => 5,
                'explanation' => 'Rela berkorban demi bangsa dan negara adalah wujud Persatuan Indonesia (sila ke-3).',
            ],
            [
                'section' => 'TIU',
                'passage' => null,
                'text' => 'Jika 3x + 5 = 20, maka nilai dari 6x - 4 adalah...',
                'option_a' => '22',
                'option_b' => '24',
                'option_c' => '26',
                'option_d' => '28',
                'option_e' => '30',
                'correct_answer' => 'C',
                'points' => 5,
                'explanation' => '3x = 15 sehingga x = 5. Maka 6(5) - 4 = 26.',
            ],
            [
                'section' => 'TKP',
                'passage' => null,
                'text' => 'Rekan kerja Anda sering datang terlambat dan membebankan tugasnya kepada Anda. Sikap Anda adalah...',
                'option_a' => 'Melaporkannya langsung kepada atasan',
                'option_b' => 'Menegurnya secara pribadi dan mengingatkan tanggung jawabnya',
                'option_c' => 'Membiarkannya karena bukan urusan saya',
                'option_d' => 'Mengerjakan tugasnya agar pekerjaan selesai',
                'option_e' => 'Menceritakan perilakunya kepada rekan lain',
                'correct_answer' => 'B',
                'points' => 5,
                'explanation' => 'Jawaban paling profesional: komunikasi asertif secara pribadi sebelum eskalasi.',
            ],
        ];
    }
}
