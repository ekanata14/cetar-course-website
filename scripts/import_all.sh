#!/usr/bin/env bash
#
# Impor semua JSON soal ke DB + tempel ke roadmap "Pejuang CPNS 2026".
#   Modul 2 = Bank Try Out CPNS (file TIU/CPNS)
#   Modul 4 = Bank Try Out TWK  (file TWK) — modul ini sudah tidak ada lagi
#             di roadmap saat ini; baris TWK di bawah akan otomatis dilewati
#             karena JSON-nya belum dibangun.
#
# Idempoten: kuis yang judulnya sudah ada akan dilewati.
# Jalankan dari root project:  bash scripts/import_all.sh
#
set -euo pipefail
cd "$(dirname "$0")/.."

JSON_DIR=storage/app/question-import

# slug|Judul Kuis|module_id|durasi_menit
ROWS=(
  "cpns-paket-01|CPNS Paket 01|2|100"
  "cpns-paket-02|CPNS Paket 02|2|100"
  "cpns-paket-03|CPNS Paket 03|2|100"
  "cpns-paket-04|CPNS Paket 04|2|100"
  "cpns-paket-05|CPNS Paket 05|2|100"
  "cpns-paket-06|CPNS Paket 06|2|100"
  "cpns-paket-07|CPNS Paket 07|2|100"
  "cpns-paket-08|CPNS Paket 08|2|100"
  "cpns-paket-09|CPNS Paket 09|2|100"
  "cpns-paket-10|CPNS Paket 10|2|100"
  "twk-paket-02|TWK Paket 2|4|30"
  "twk-paket-04|TWK Paket 4|4|30"
  "twk-paket-05|TWK Paket 5|4|30"
  "twk-paket-06|TWK Paket 6|4|30"
  "twk-paket-08|TWK Paket 8|4|30"
  "twk-paket-09|TWK Paket 9|4|30"
  "twk-paket-10|TWK Paket 10|4|30"
)

for row in "${ROWS[@]}"; do
  IFS='|' read -r slug title module duration <<< "$row"
  json="$JSON_DIR/$slug.json"
  if [[ ! -f "$json" ]]; then
    echo "⏭  $slug: JSON belum ada — lewati (jalankan build_questions.py dulu)"
    continue
  fi
  php artisan questions:import "$json" \
    --quiz-title="$title" --slug="$slug" --module="$module" --duration="$duration"
done

echo "Selesai."
