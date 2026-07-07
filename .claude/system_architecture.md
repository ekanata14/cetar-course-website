# System Architecture: Cetar (Cepat Pintar) v2.1

## 1. High-Level Overview
Cetar is a premium Computer Based Test (CBT) and e-learning SaaS platform tailored for CPNS and SNBT preparation. The system is built to handle high concurrency during mass Try Out events while maintaining a modular subscription and affiliate ecosystem.

## 2. Technology Stack
- **Backend:** Laravel 12 (PHP 8.3+)
- **Frontend/Reactivity:** Livewire 3 & Alpine.js
- **Styling:** Tailwind CSS (Mobile-First approach)
- **Database:** PostgreSQL 16 / MySQL 8
- **Cache & Queue:** Redis
- **Infrastructure:** Docker & Nginx (Prepared for horizontal scaling)

## 3. Core Architectural Pattern: Action-Oriented
We strictly avoid "Fat Controllers". The architecture follows the **Action-Oriented** pattern.

### Request Lifecycle
`Route` -> `Thin Controller / Livewire Component` -> `Action Class` -> `Model/Database`

- **Controllers/Livewire Components:** Only handle HTTP request validation (`#[Validate]`), authorization (`Gate/Policy`), and passing DTOs/Arrays to Actions.
- **Action Classes (`app/Actions`):** Contain pure business logic. They are highly testable, single-responsibility classes (e.g., `ProcessPaymentWebhook`, `DistributeAffiliateCommission`, `SubmitQuizAttempt`).
- **Models:** Only contain relationships, casts, and scopes. No complex business logic.

## 4. Domain & Database Architecture

### A. Authentication & Access Control
- `users`: Standard auth. Extensions include `role` (super_admin, user), `wallet_balance`, and `referral_code`.
- **Authorization:** Handled via Laravel Policies. Super Admins bypass all restrictions.

### B. Modular Subscription Engine
- `packages`: The main product wrapper (e.g., "CPNS", "SNBT").
- `package_plans`: Pricing tiers linked to durations (1 Month, 12 Months).
- `user_subscriptions`: Pivot tracking active entitlements. 
  - *Logic:* Access to a `quiz` or `content` is resolved by checking if the user holds an active `user_subscription` for the parent `package_id`.

### C. Affiliate & Financial Engine
- `payments`: Tracks Webhook statuses from Midtrans/Xendit.
- `affiliate_commissions`: Ledger for incoming referral money.
- `withdrawals`: Ledger for outgoing money (payouts to users).
  - *Event-Driven:* When a `payment` status hits `settled`, an Event triggers the `DistributeAffiliateCommission` action, which calculates the cut, inserts a record in `affiliate_commissions`, and increments the referrer's `wallet_balance`.

### D. Core CBT Engine
- `quizzes` & `questions`: The exam structure. Questions include passages, 5 options, and point weights.
- `package_content`: Polymorphic/Pivot table linking quizzes to multiple packages.
- `user_quiz_attempts`: Tracks start time, end time, and final score.
- `user_quiz_answers`: Auto-saved answers per question.

## 5. Frontend & Reactivity Boundaries

### Livewire 3 (Server-Side)
- Used for heavy data lifting, pagination, form submissions, and secure database interactions.
- **CBT Implementation:** Handles navigating between questions, fetching the current question payload, and saving the answer to the database (`$wire.saveAnswer()`).

### Alpine.js (Client-Side)
- Used strictly to reduce server round-trips for UI states.
- **CBT Implementation:** The exam countdown timer is written in pure Alpine.js. It reads the absolute `started_at` timestamp injected by Laravel, calculates the remaining time locally, and triggers `$wire.submitQuiz()` when time expires. Modals, dropdowns, and mobile sidebars are exclusively Alpine.js.

## 6. Directory Structure Blueprint
```text
app/
├── Actions/
│   ├── Affiliate/
│   │   ├── DistributeCommission.php
│   │   └── ProcessWithdrawal.php
│   ├── Quiz/
│   │   ├── CalculateFinalScore.php
│   │   └── SaveUserAnswer.php
│   └── Subscription/
│       ├── ProvisionPackageAccess.php
│       └── HandlePaymentWebhook.php
├── Livewire/
│   ├── Admin/
│   ├── Exam/
│   │   └── QuizEngine.php
│   └── User/
├── Models/
└── Policies/