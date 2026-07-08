#!/usr/bin/env bash
#
# Lanjutkan impor soal setelah kuota Claude reset.
#   1. Bangun JSON (kunci jawaban + pembahasan via Claude) untuk file yang belum jadi.
#   2. Impor semua JSON ke DB + tempel ke roadmap.
#
# Aman diulang: file JSON yang sudah ada & kuis yang sudah diimpor dilewati.
# Jalankan dari root project:  bash scripts/resume.sh
#
set -euo pipefail
cd "$(dirname "$0")/.."

SLUGS=(
  cpns-paket-01 cpns-paket-02 cpns-paket-03 cpns-paket-04 cpns-paket-05
  cpns-paket-06 cpns-paket-07 cpns-paket-08 cpns-paket-09 cpns-paket-10
  twk-paket-02 twk-paket-04 twk-paket-05 twk-paket-06 twk-paket-08
  twk-paket-09 twk-paket-10
)

echo "== Tahap 1: bangun JSON (kunci + pembahasan) =="
python3 scripts/build_questions.py "${SLUGS[@]}"

echo
echo "== Tahap 2: impor ke database =="
bash scripts/import_all.sh
