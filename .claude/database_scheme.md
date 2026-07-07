# Database Schema: Cetar (Cepat Pintar) v2.1

## 1. Domain: Auth & Financials

### `users`
| Column | Type | Constraints/Notes |
| :--- | :--- | :--- |
| `id` | bigint (PK) | Auto-increment |
| `name` | varchar | |
| `email` | varchar | Unique |
| `password` | varchar | |
| `role` | enum | ['super_admin', 'user'], default: 'user' |
| `referral_code` | varchar | Unique, Indexed (untuk pencarian afiliasi) |
| `referred_by` | bigint (FK) | references `users.id`, Nullable (Siapa yang mengundang, dari ?ref= saat registrasi) |
| `wallet_balance` | decimal | Default: 0.00 (Saldo komisi afiliasi) |
| `timestamps` | timestamp | created_at, updated_at |

### `affiliate_commissions`
| Column | Type | Constraints/Notes |
| :--- | :--- | :--- |
| `id` | bigint (PK) | Auto-increment |
| `referrer_id` | bigint (FK) | references `users.id` (Yang mengundang) |
| `referred_id` | bigint (FK) | references `users.id` (Yang mendaftar) |
| `payment_id` | bigint (FK) | references `payments.id` |
| `amount` | decimal | Nominal komisi yang didapat |
| `status` | enum | ['pending', 'success', 'cancelled'] |
| `timestamps` | timestamp | |

### `withdrawals`
| Column | Type | Constraints/Notes |
| :--- | :--- | :--- |
| `id` | bigint (PK) | Auto-increment |
| `user_id` | bigint (FK) | references `users.id` |
| `amount` | decimal | Nominal penarikan tunai |
| `bank_details` | text | JSON (Nama bank, nomor rekening, nama pemilik) |
| `status` | enum | ['pending', 'success', 'rejected'] |
| `processed_by` | bigint (FK) | references `users.id` (Admin yang menyetujui), Nullable |
| `timestamps` | timestamp | |

## 2. Domain: Modular Subscriptions

### `packages`
| Column | Type | Constraints/Notes |
| :--- | :--- | :--- |
| `id` | bigint (PK) | Auto-increment |
| `name` | varchar | Contoh: "Pejuang CPNS 2026" |
| `slug` | varchar | Unique |
| `description` | text | Nullable |
| `is_active` | boolean | Default: true |
| `timestamps` | timestamp | |

### `package_plans`
| Column | Type | Constraints/Notes |
| :--- | :--- | :--- |
| `id` | bigint (PK) | Auto-increment |
| `package_id` | bigint (FK) | references `packages.id` ON DELETE CASCADE |
| `name` | varchar | Contoh: "1 Bulan", "1 Tahun" |
| `duration_days` | integer | Contoh: 30, 365 |
| `price` | decimal | Harga paket |
| `timestamps` | timestamp | |

### `user_subscriptions`
| Column | Type | Constraints/Notes |
| :--- | :--- | :--- |
| `id` | bigint (PK) | Auto-increment |
| `user_id` | bigint (FK) | references `users.id` ON DELETE CASCADE |
| `package_id` | bigint (FK) | references `packages.id` ON DELETE CASCADE |
| `status` | enum | ['active', 'expired', 'suspended'] |
| `expires_at` | timestamp | Batas waktu akses paket |
| `timestamps` | timestamp | |

### `payments`
| Column | Type | Constraints/Notes |
| :--- | :--- | :--- |
| `id` | bigint (PK) | Auto-increment |
| `user_id` | bigint (FK) | references `users.id` |
| `package_plan_id`| bigint (FK) | references `package_plans.id` |
| `amount` | decimal | Total tagihan |
| `status` | enum | ['pending', 'settled', 'failed', 'expired'] |
| `external_id` | varchar | Unique, Nullable (Invoice/order reference untuk matching webhook DOKU) |
| `payment_url` | varchar | URL gateway (DOKU), Nullable |
| `timestamps` | timestamp | |

## 3. Domain: Core CBT & Content Distribution

### `quizzes`
| Column | Type | Constraints/Notes |
| :--- | :--- | :--- |
| `id` | bigint (PK) | Auto-increment |
| `title` | varchar | Contoh: "Try Out Akbar CPNS 1" |
| `description` | text | Nullable |
| `duration_minutes`| integer | Durasi ujian dalam menit (contoh: 100) |
| `timestamps` | timestamp | |

### `package_content` (Polymorphic Pivot)
| Column | Type | Constraints/Notes |
| :--- | :--- | :--- |
| `package_id` | bigint (FK) | references `packages.id` ON DELETE CASCADE |
| `contentable_type`| varchar | Model tujuan (contoh: `App\Models\Quiz`) |
| `contentable_id` | bigint | ID dari model tujuan |
| `Index` | index | Composite index (`package_id`, `contentable_type`, `contentable_id`) |

### `questions`
| Column | Type | Constraints/Notes |
| :--- | :--- | :--- |
| `id` | bigint (PK) | Auto-increment |
| `quiz_id` | bigint (FK) | references `quizzes.id` ON DELETE CASCADE |
| `section` | varchar | Contoh: 'TWK', 'TIU', 'TKP' (Untuk grouping/tabs) |
| `passage` | text | Nullable (Teks bacaan panjang) |
| `text` | text | Pertanyaan utama |
| `option_a` | text | |
| `option_b` | text | |
| `option_c` | text | |
| `option_d` | text | |
| `option_e` | text | Nullable |
| `correct_answer` | char(1) | Contoh: 'A', 'B', 'C', 'D', 'E' |
| `points` | integer | Default: 5 (Untuk TKP bisa di-handle khusus) |
| `explanation` | text | Nullable (Pembahasan jawaban) |
| `timestamps` | timestamp | |

### `user_quiz_attempts`
| Column | Type | Constraints/Notes |
| :--- | :--- | :--- |
| `id` | bigint (PK) | Auto-increment |
| `user_id` | bigint (FK) | references `users.id` ON DELETE CASCADE |
| `quiz_id` | bigint (FK) | references `quizzes.id` ON DELETE CASCADE |
| `started_at` | timestamp | Patokan waktu Alpine.js |
| `completed_at` | timestamp | Nullable (Diisi saat submit/waktu habis) |
| `score` | integer | Default: 0 (Kalkulasi akhir) |
| `status` | enum | ['in_progress', 'completed'] |
| `timestamps` | timestamp | |

### `user_quiz_answers`
| Column | Type | Constraints/Notes |
| :--- | :--- | :--- |
| `id` | bigint (PK) | Auto-increment |
| `attempt_id` | bigint (FK) | references `user_quiz_attempts.id` ON DELETE CASCADE |
| `question_id` | bigint (FK) | references `questions.id` ON DELETE CASCADE |
| `selected_option`| char(1) | Nullable |
| `is_doubtful` | boolean | Default: false (Fitur "Ragu-ragu") |
| `is_correct` | boolean | Nullable (Disimpan saat kalkulasi akhir untuk optimalisasi read) |
| `unique` | unique | Composite unique (`attempt_id`, `question_id`) untuk upsert auto-save |
| `timestamps` | timestamp | |
