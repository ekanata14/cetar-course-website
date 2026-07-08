<?php

use App\Actions\Quiz\ImportQuestions;
use App\Livewire\Admin\Quiz\Questions;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Livewire\Livewire;
use Spatie\SimpleExcel\SimpleExcelWriter;

/** Helper: tulis file xlsx sementara berisi baris soal */
function importXlsx(array $rows): string
{
    $path = sys_get_temp_dir().'/import-test-'.uniqid().'.xlsx';

    $writer = SimpleExcelWriter::create($path);
    foreach ($rows as $row) {
        $writer->addRow($row);
    }
    $writer->close();

    return $path;
}

function validRow(array $overrides = []): array
{
    return array_merge([
        'section' => 'TWK',
        'passage' => '',
        'text' => 'Ibukota Indonesia adalah ....',
        'option_a' => 'Jakarta',
        'option_b' => 'Bandung',
        'option_c' => 'Surabaya',
        'option_d' => 'Medan',
        'option_e' => 'Nusantara',
        'correct_answer' => 'A',
        'points' => 5,
        'explanation' => 'Jakarta adalah ibukota Indonesia.',
        'image_url' => '',
    ], $overrides);
}

// ==========================================
// TEMPLATE
// ==========================================

test('admin can download the import template', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);

    $this->actingAs($admin)
        ->get(route('admin.quizzes.import-template'))
        ->assertOk()
        ->assertDownload('template-import-soal.xlsx');
});

test('regular users cannot download the import template', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.quizzes.import-template'))
        ->assertRedirect();
});

// ==========================================
// AKSI IMPOR
// ==========================================

test('a valid file imports all rows into the quiz', function () {
    $quiz = Quiz::factory()->create();
    $path = importXlsx([
        validRow(),
        validRow(['text' => 'Lambang negara Indonesia adalah ....', 'correct_answer' => 'B', 'section' => 'tiu']),
    ]);

    $result = app(ImportQuestions::class)->execute($quiz, $path);

    expect($result->failed())->toBeFalse()
        ->and($result->imported)->toBe(2)
        ->and($quiz->questions()->count())->toBe(2);

    $second = $quiz->questions()->latest('id')->first();
    expect($second->correct_answer)->toBe('B')
        ->and($second->section)->toBe('TIU'); // dinormalisasi ke huruf besar
});

test('an invalid row aborts the whole import with a row-numbered error', function () {
    $quiz = Quiz::factory()->create();
    $path = importXlsx([
        validRow(),
        validRow(['option_a' => '']), // baris 3 di Excel (setelah header)
    ]);

    $result = app(ImportQuestions::class)->execute($quiz, $path);

    expect($result->failed())->toBeTrue()
        ->and($result->errors[0])->toContain('Baris 3')
        ->and($quiz->questions()->count())->toBe(0); // all-or-nothing
});

test('correct answer E is rejected when option E is empty', function () {
    $quiz = Quiz::factory()->create();
    $path = importXlsx([
        validRow(['option_e' => '', 'correct_answer' => 'E']),
    ]);

    $result = app(ImportQuestions::class)->execute($quiz, $path);

    expect($result->failed())->toBeTrue()
        ->and($quiz->questions()->count())->toBe(0);
});

test('empty points defaults to five', function () {
    $quiz = Quiz::factory()->create();
    $path = importXlsx([validRow(['points' => ''])]);

    app(ImportQuestions::class)->execute($quiz, $path);

    expect($quiz->questions()->sole()->points)->toBe(5);
});

// ==========================================
// GAMBAR SOAL
// ==========================================

test('google drive share links are normalized for display', function () {
    $question = new Question(['image_url' => 'https://drive.google.com/file/d/1AbC_dEf-123/view?usp=sharing']);

    expect($question->imageDisplayUrl())->toBe('https://lh3.googleusercontent.com/d/1AbC_dEf-123');

    $question->image_url = 'https://drive.google.com/open?id=XyZ-987';
    expect($question->imageDisplayUrl())->toBe('https://lh3.googleusercontent.com/d/XyZ-987');

    $question->image_url = 'https://example.com/img/soal.png';
    expect($question->imageDisplayUrl())->toBe('https://example.com/img/soal.png');

    $question->image_url = null;
    expect($question->imageDisplayUrl())->toBeNull();
});

test('image url survives the import into the question row', function () {
    $quiz = Quiz::factory()->create();
    $path = importXlsx([
        validRow(['image_url' => 'https://drive.google.com/file/d/FILE123/view']),
    ]);

    app(ImportQuestions::class)->execute($quiz, $path);

    expect($quiz->questions()->sole()->image_url)->toBe('https://drive.google.com/file/d/FILE123/view');
});

// ==========================================
// UI LIVEWIRE (modal impor)
// ==========================================

test('admin can import questions through the kelola soal page', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $quiz = Quiz::factory()->create();

    $path = importXlsx([validRow()]);
    $upload = new \Illuminate\Http\Testing\File('soal.xlsx', fopen($path, 'r'));

    Livewire::actingAs($admin)
        ->test(Questions::class, ['quiz' => $quiz])
        ->call('openImport')
        ->set('importFile', $upload)
        ->call('import')
        ->assertHasNoErrors()
        ->assertSet('showImport', false);

    expect($quiz->questions()->count())->toBe(1);
});

test('the import modal shows row errors and keeps nothing on failure', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $quiz = Quiz::factory()->create();

    $path = importXlsx([validRow(['text' => ''])]);
    $upload = new \Illuminate\Http\Testing\File('soal.xlsx', fopen($path, 'r'));

    $component = Livewire::actingAs($admin)
        ->test(Questions::class, ['quiz' => $quiz])
        ->call('openImport')
        ->set('importFile', $upload)
        ->call('import')
        ->assertSet('showImport', true);

    expect($component->get('importErrors'))->not->toBeEmpty()
        ->and($quiz->questions()->count())->toBe(0);
});
