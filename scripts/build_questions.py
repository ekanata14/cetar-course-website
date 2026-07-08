#!/usr/bin/env python3
"""
Strukturkan teks soal hasil ekstraksi menjadi JSON siap-impor,
sekaligus mengisi kunci jawaban + pembahasan via Claude headless.

Pemakaian:
    python3 scripts/build_questions.py <slug> [slug2 ...]

Input : storage/app/question-import/txt/{slug}.txt
Output: storage/app/question-import/{slug}.json
"""

import json
import re
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
TXT_DIR = ROOT / 'storage/app/question-import/txt'
OUT_DIR = ROOT / 'storage/app/question-import'

PROMPT = """Kamu menerima teks mentah soal latihan CPNS Indonesia hasil ekstraksi dari dokumen Word (di bawah, setelah tanda =====). Strukturkan SEMUA soal menjadi JSON.

Aturan parsing:
- Setiap soal terdiri dari pertanyaan lalu 4-5 opsi jawaban (baris-baris pendek setelah pertanyaan).
- Abaikan baris header seperti "Soal No : X" — itu hanya penomoran.
- Jika ada teks bacaan/cerita panjang yang menjadi dasar soal, masukkan ke "passage"; kalau tidak ada, null.
- Penanda [IMG:namafile] menunjukkan gambar milik soal tersebut: isi field "image" dengan namafile-nya (tanpa [IMG:]), dan hapus penanda dari teks. Kalau tidak ada gambar, null.
- Perbaiki typo spasi ganda dan karakter aneh seperlunya, tapi JANGAN mengubah makna soal/opsi.

Untuk setiap soal, kamu juga harus:
- "section": klasifikasikan "TWK" (wawasan kebangsaan: Pancasila, UUD, sejarah, bahasa Indonesia, nasionalisme, integritas), "TIU" (intelegensia umum: sinonim/antonim/analogi, silogisme, deret angka, aritmetika, logika, gambar/figural), atau "TKP" (karakteristik pribadi: situasi kerja, pilihan sikap).
- "correct_answer": tentukan jawaban yang benar ("A"-"E") berdasarkan pengetahuanmu. Untuk soal TKP pilih opsi yang paling tepat menurut penilaian resmi CAT BKN.
- "explanation": pembahasan singkat 1-3 kalimat dalam bahasa Indonesia yang menjelaskan mengapa jawaban itu benar.

Keluarkan HANYA array JSON valid (tanpa markdown fence, tanpa teks lain), format:
[{"section":"TWK","passage":null,"image":null,"text":"...","option_a":"...","option_b":"...","option_c":"...","option_d":"...","option_e":"..." atau null,"correct_answer":"A","explanation":"..."}]

=====
"""


class SessionLimit(Exception):
    """Kuota Claude CLI habis — hentikan dengan rapi, bisa dilanjut nanti."""


def call_claude(text: str) -> list:
    result = subprocess.run(
        ['claude', '-p', '--output-format', 'text'],
        input=PROMPT + text,
        capture_output=True,
        text=True,
        timeout=1800,
    )
    out = result.stdout.strip()

    if re.search(r'session limit|usage limit|hit your.*limit', out, re.I):
        raise SessionLimit(out.splitlines()[0] if out else 'limit tercapai')

    # Buang pagar markdown bila ada
    out = re.sub(r'^```(?:json)?\s*', '', out)
    out = re.sub(r'\s*```$', '', out)

    start, end = out.find('['), out.rfind(']')
    if start == -1 or end == -1:
        raise ValueError(f'Tidak menemukan JSON array. Output awal: {out[:400]}')

    return json.loads(out[start:end + 1])


def chunks_of(text: str, max_chars: int = 24000) -> list:
    """Pecah teks di batas baris kosong agar soal tidak terpotong."""
    if len(text) <= max_chars:
        return [text]

    blocks = text.split('\n\n')
    chunks, current = [], ''
    for block in blocks:
        if current and len(current) + len(block) + 2 > max_chars:
            chunks.append(current)
            current = block
        else:
            current = current + '\n\n' + block if current else block
    if current:
        chunks.append(current)
    return chunks


def main():
    for slug in sys.argv[1:]:
        txt = (TXT_DIR / f'{slug}.txt').read_text(encoding='utf-8')
        out_path = OUT_DIR / f'{slug}.json'

        if out_path.exists():
            print(f'{slug}: sudah ada, lewati')
            continue

        questions = []
        parts = chunks_of(txt)
        try:
            for i, part in enumerate(parts, 1):
                print(f'{slug}: memproses bagian {i}/{len(parts)} ({len(part)} char)...', flush=True)
                questions.extend(call_claude(part))
        except SessionLimit as e:
            print(f'\n⏸  Kuota Claude tercapai ({e}). Berhenti di "{slug}".', flush=True)
            print('   Jalankan ulang perintah yang sama setelah kuota reset — file yang sudah jadi otomatis dilewati.', flush=True)
            sys.exit(2)

        out_path.write_text(json.dumps(questions, ensure_ascii=False, indent=1), encoding='utf-8')
        print(f'{slug}: {len(questions)} soal → {out_path.name}', flush=True)


if __name__ == '__main__':
    main()
