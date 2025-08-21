# Laravel CLI Assessment Reporter

This program reads the provided JSON files and generates **Diagnostic**, **Progress**, and **Feedback** reports from the terminal.

---

## Prerequisites

Make sure you have the following installed:
- PHP >= 8.1
- Composer
- Git

### Project setup

```bash
# 1) Clone the app
git clone <repository_url>

# 2) Install the dependencies
composer install
```

### Run the CLI app:

```bash
php artisan report:generate
# Or with flags
php artisan report:generate --student=student1 --type=diagnostic
php artisan report:generate --student=student1 --type=progress
php artisan report:generate --student=student1 --type=feedback
```

### Run the tests:

```bash
php artisan test
```

> **Assumptions**
>
> * Input dates in `student-responses.json` use the format `d/m/Y H:i:s` (e.g., `16/12/2021 10:46:00`).
> * We treat an assessment as **complete** iff the `completed` field is present.
> * We resolve questions from `questions.json` by ID when tallying correctness and strands; we rely on the union of question ids in the responses and `questions.json`.
