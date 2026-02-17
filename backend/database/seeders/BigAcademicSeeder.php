<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BigAcademicSeeder extends Seeder
{
    public function run(): void
    {
        // optional kalau environment kamu kuat
        // set_time_limit(0);
        // ini_set('memory_limit', '-1');

        // ==== CONFIG (boleh kamu ubah) ====
        $studentsCount    = (int) env('SEED_STUDENTS', 200000);
        $coursesCount     = (int) env('SEED_COURSES', 200);
        $enrollmentsCount = (int) env('SEED_ENROLLMENTS', 5000000);

        // batch besar = lebih cepat, tapi jangan kebesaran biar tidak jebol memory
        $batchStudents = (int) env('SEED_BATCH_STUDENTS', 10000);
        $batchCourses  = (int) env('SEED_BATCH_COURSES', 2000);
        $batchEnroll   = (int) env('SEED_BATCH_ENROLL', 20000);

        $academicYears = ['2021/2022','2022/2023','2023/2024','2024/2025','2025/2026'];
        $semesters     = ['GANJIL','GENAP'];
        $statuses      = ['DRAFT','SUBMITTED','APPROVED','REJECTED'];

        // penting: jangan simpan query log (bisa makan RAM)
        DB::disableQueryLog();
        DB::connection()->disableQueryLog();

        // ✅ timestamp dibuat sekali, jangan inside loop
        $now = now()->toDateTimeString();

        // kalau pakai PostgreSQL, truncate yang aman + reset identity
        // kalau MySQL, statement ini mungkin beda. (MySQL: TRUNCATE students; TRUNCATE courses; TRUNCATE enrollments;)
        $driver = DB::getDriverName();
        $truncate = function (string $table) use ($driver) {
            if ($driver === 'pgsql') {
                DB::statement("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE");
            } else {
                DB::statement("TRUNCATE TABLE {$table}");
            }
        };

        /**
         * ======================
         * 1) Seed students
         * ======================
         */
        $this->command?->info("Truncating tables...");
        // urutan: enrollments dulu biar FK aman (kalau bukan pgsql-cascade)
        $truncate('enrollments');
        $truncate('students');
        $truncate('courses');

        $this->command?->info("Seeding students: {$studentsCount}");
        $rows = [];

        for ($i = 1; $i <= $studentsCount; $i++) {
            $nim = str_pad((string) $i, 10, '0', STR_PAD_LEFT);

            $rows[] = [
                'nim'        => $nim,
                'name'       => "Student {$i}",
                'email'      => "student{$i}@example.com",
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= $batchStudents) {
                DB::table('students')->insert($rows);
                $rows = [];
            }
        }
        if (!empty($rows)) DB::table('students')->insert($rows);

        /**
         * ======================
         * 2) Seed courses
         * ======================
         */
        $this->command?->info("Seeding courses: {$coursesCount}");
        $rows = [];

        for ($i = 1; $i <= $coursesCount; $i++) {
            $code = 'IF' . str_pad((string) $i, 3, '0', STR_PAD_LEFT); // IF001, IF002...

            $rows[] = [
                'code'       => $code,
                'name'       => "Course {$code}",
                'credits'    => ($i % 6) + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= $batchCourses) {
                DB::table('courses')->insert($rows);
                $rows = [];
            }
        }
        if (!empty($rows)) DB::table('courses')->insert($rows);

        /**
         * ======================
         * 3) Seed enrollments 5.000.000
         * ======================
         */
        $minStudentId = (int) DB::table('students')->min('id');
        $maxStudentId = (int) DB::table('students')->max('id');
        $minCourseId  = (int) DB::table('courses')->min('id');
        $maxCourseId  = (int) DB::table('courses')->max('id');

        $studentSpan = $maxStudentId - $minStudentId + 1;
        $courseSpan  = $maxCourseId - $minCourseId + 1;

        if ($studentSpan <= 0 || $courseSpan <= 0) {
            throw new \RuntimeException("Invalid span. students/courses empty?");
        }

        $this->command?->info("Seeding enrollments: {$enrollmentsCount}");

        $rows = [];
        $yearsCount = count($academicYears);
        $semCount   = count($semesters);
        $statusCount= count($statuses);

        // NOTE:
        // mapping deterministik agar variasi dan minim duplikat.
        // Unique constraint kamu: (student_id, course_id, academic_year, semester)
        // Maka kombinasi harus cukup besar:
        // total unik teoritis = studentSpan * courseSpan * yearsCount * semCount
        // pastikan enrollmentsCount <= itu.
        $maxUnique = $studentSpan * $courseSpan * $yearsCount * $semCount;
        if ($enrollmentsCount > $maxUnique) {
            throw new \RuntimeException("enrollmentsCount={$enrollmentsCount} melebihi kapasitas unik {$maxUnique}. Kurangi SEED_ENROLLMENTS.");
        }

        for ($i = 0; $i < $enrollmentsCount; $i++) {
            $studentOffset = $i % $studentSpan;

            // bikin course berputar dan berubah relatif terhadap student offset
            // supaya tidak monoton (lebih menyebar)
            $courseOffset  = (intdiv($i, $studentSpan)) % $courseSpan;

            $yearIndex = (intdiv($i, ($studentSpan * $courseSpan))) % $yearsCount;
            $semIndex  = (intdiv($i, ($studentSpan * $courseSpan * $yearsCount))) % $semCount;

            $rows[] = [
                'student_id'   => $minStudentId + $studentOffset,
                'course_id'    => $minCourseId + $courseOffset,
                'academic_year'=> $academicYears[$yearIndex],
                'semester'     => $semesters[$semIndex],
                'status'       => $statuses[$i % $statusCount],
                'created_at'   => $now,
                'updated_at'   => $now,
            ];

            if (count($rows) >= $batchEnroll) {
                DB::table('enrollments')->insert($rows);
                $rows = [];
            }

            // progress log tiap 200k biar enak lihat jalan
            if ($i > 0 && $i % 200000 === 0) {
                $this->command?->info("... inserted {$i} / {$enrollmentsCount}");
            }
        }

        if (!empty($rows)) DB::table('enrollments')->insert($rows);

        $this->command?->info("DONE.");
        $this->command?->info("students=" . DB::table('students')->count());
        $this->command?->info("courses=" . DB::table('courses')->count());
        $this->command?->info("enrollments=" . DB::table('enrollments')->count());
    }
}
