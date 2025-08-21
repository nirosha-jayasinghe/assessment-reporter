# Laravel CLI Assessment Reporter

This program reads the provided JSON files and generates **Diagnostic**, **Progress**, and **Feedback** reports from the terminal.

---
> > **Assumptions**
>
> * Input dates in `student-responses.json` use the format `d/m/Y H:i:s` (e.g., `16/12/2021 10:46:00`).
> * We treat an assessment as **complete** iff the `completed` field is present.
> * We resolve questions from `questions.json` by ID when tallying correctness and strands; we rely on the union of question ids in the responses and `questions.json`.


## How To Use

```text
$ php artisan report:generate
Student ID: student1
Report to generate (1 for Diagnostic, 2 for Progress, 3 for Feedback): 1

Tony Stark recently completed Numeracy assessment on 16th December 2021 10:46 AM
He got 15 questions right out of 16. Details by strand given below:

Number and Algebra: 5 out of 5 correct
Measurement and Geometry: 7 out of 7 correct
Statistics and Probability: 3 out of 4 correct

```

### Run tests:

```bash
php artisan test
