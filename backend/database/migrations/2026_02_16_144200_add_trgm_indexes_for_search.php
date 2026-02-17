<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // pg_trgm untuk ilike/contains
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        // GIN trigram index untuk pencarian ILIKE %...%
        DB::statement('CREATE INDEX IF NOT EXISTS students_nim_trgm_idx ON students USING GIN (nim gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS students_name_trgm_idx ON students USING GIN (name gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS courses_code_trgm_idx ON courses USING GIN (code gin_trgm_ops)');

        // Index untuk filter/sort cepat di enrollments
        DB::statement('CREATE INDEX IF NOT EXISTS enrollments_status_idx ON enrollments(status)');
        DB::statement('CREATE INDEX IF NOT EXISTS enrollments_semester_idx ON enrollments(semester)');
        DB::statement('CREATE INDEX IF NOT EXISTS enrollments_academic_year_idx ON enrollments(academic_year)');
        DB::statement('CREATE INDEX IF NOT EXISTS enrollments_created_at_idx ON enrollments(created_at)');

        // Kombinasi yang sering kepakai
        DB::statement('CREATE INDEX IF NOT EXISTS enrollments_year_semester_status_idx ON enrollments(academic_year, semester, status)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS students_nim_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS students_name_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS courses_code_trgm_idx');

        DB::statement('DROP INDEX IF EXISTS enrollments_status_idx');
        DB::statement('DROP INDEX IF EXISTS enrollments_semester_idx');
        DB::statement('DROP INDEX IF EXISTS enrollments_academic_year_idx');
        DB::statement('DROP INDEX IF EXISTS enrollments_created_at_idx');
        DB::statement('DROP INDEX IF EXISTS enrollments_year_semester_status_idx');

        // extension tidak wajib di-drop
        // DB::statement('DROP EXTENSION IF EXISTS pg_trgm');
    }
};
