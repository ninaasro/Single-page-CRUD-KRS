<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use App\Models\Student;
use App\Models\Course;
use App\Models\Enrollment;

class EnrollmentController extends Controller
{
    /**
     * =========================================================
     * FILTER SPEC (field whitelist + operator whitelist)
     * =========================================================
     */
    private function filterSpec(): array
    {
        return [
            // enrollments
            'id'            => ['col' => 'enrollments.id',            'ops' => ['equals', 'between', 'in']],
            'academic_year' => ['col' => 'enrollments.academic_year', 'ops' => ['equals', 'between', 'in']],
            'semester'      => ['col' => 'enrollments.semester',      'ops' => ['equals', 'in']],
            'status'        => ['col' => 'enrollments.status',        'ops' => ['equals', 'in']],
            'created_at'    => ['col' => 'enrollments.created_at',    'ops' => ['equals', 'between']],
            'updated_at'    => ['col' => 'enrollments.updated_at',    'ops' => ['equals', 'between']],

            // students
            'student_nim'   => ['col' => 'students.nim',              'ops' => ['equals', 'contains', 'startswith', 'in']],
            'student_name'  => ['col' => 'students.name',             'ops' => ['equals', 'contains', 'startswith', 'in']],
            'student_email' => ['col' => 'students.email',            'ops' => ['equals', 'contains', 'startswith', 'in']],

            // courses
            'course_code'    => ['col' => 'courses.code',             'ops' => ['equals', 'contains', 'startswith', 'in']],
            'course_name'    => ['col' => 'courses.name',             'ops' => ['equals', 'contains', 'startswith', 'in']],
            'course_credits' => ['col' => 'courses.credits',          'ops' => ['equals', 'between', 'in']],
        ];
    }

    /**
     * =========================================================
     * SORT SPEC (field whitelist)
     * =========================================================
     */
    private function sortSpec(): array
    {
        return [
            'id'             => 'enrollments.id',
            'academic_year'  => 'enrollments.academic_year',
            'semester'       => 'enrollments.semester',
            'status'         => 'enrollments.status',
            'created_at'     => 'enrollments.created_at',
            'updated_at'     => 'enrollments.updated_at',

            'student_nim'    => 'students.nim',
            'student_name'   => 'students.name',
            'student_email'  => 'students.email',

            'course_code'    => 'courses.code',
            'course_name'    => 'courses.name',
            'course_credits' => 'courses.credits',
        ];
    }

    /**
     * =========================================================
     * Normalize filters input (string JSON -> array)
     * =========================================================
     */
    private function normalizeFilters($filters): array
    {
        if (is_string($filters)) {
            $decoded = json_decode($filters, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                abort(response()->json([
                    'message' => 'Invalid filters JSON',
                    'errors'  => ['filters' => ['filters must be valid JSON array']],
                ], 422));
            }
            return $decoded;
        }

        return is_array($filters) ? $filters : [];
    }

    /**
     * =========================================================
     * Normalize sorts input (string JSON -> array)
     * supports:
     * - sorts=[{"field":"academic_year","dir":"desc"}, ...]
     * - (fallback) sort_by + sort_dir
     * =========================================================
     */
    private function normalizeSorts(Request $request): array
    {
        $sorts = $request->input('sorts');

        if (is_string($sorts)) {
            $decoded = json_decode($sorts, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                abort(response()->json([
                    'message' => 'Invalid sorts JSON',
                    'errors'  => ['sorts' => ['sorts must be valid JSON array']],
                ], 422));
            }
            $sorts = $decoded;
        }

        if (is_array($sorts) && count($sorts) > 0) {
            return $sorts;
        }

        // fallback legacy single sort
        $sortBy  = (string) $request->get('sort_by', 'id');
        $sortDir = strtolower((string) $request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        return [
            ['field' => $sortBy, 'dir' => $sortDir]
        ];
    }

    /**
     * =========================================================
     * Apply sorts (multi-column)
     * - whitelist field via sortSpec
     * - dir only asc/desc
     * - add stable tiebreaker by enrollments.id (desc)
     * =========================================================
     */
    private function applySorts($query, Request $request): void
    {
        $sortMap = $this->sortSpec();
        $sorts   = $this->normalizeSorts($request);

        $applied = 0;

        foreach ($sorts as $s) {
            $field = $s['field'] ?? null;
            $dir   = strtolower((string)($s['dir'] ?? 'asc'));
            $dir   = $dir === 'desc' ? 'desc' : 'asc';

            if (!$field || !isset($sortMap[$field])) continue;

            $query->orderBy($sortMap[$field], $dir);
            $applied++;
        }

        if ($applied === 0) {
            $query->orderBy('enrollments.id', 'desc');
            return;
        }

        // tiebreaker biar hasil konsisten
        $query->orderBy('enrollments.id', 'desc');
    }

    /**
     * =========================================================
     * QUERY BUILDER (index & export)
     * =========================================================
     */
    private function baseQuery(Request $request)
    {
        $query = DB::table('enrollments')
            ->join('students', 'students.id', '=', 'enrollments.student_id')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->select([
                'enrollments.id as id',
                'enrollments.academic_year',
                'enrollments.semester',
                'enrollments.status',
                'enrollments.created_at',
                'enrollments.updated_at',

                'students.nim as student_nim',
                'students.name as student_name',
                'students.email as student_email',

                'courses.code as course_code',
                'courses.name as course_name',
                'courses.credits as course_credits',
            ]);

        // ADVANCED FILTER
        $spec    = $this->filterSpec();
        $filters = $this->normalizeFilters($request->input('filters'));

        $logic = strtoupper((string) $request->input('logic', 'AND'));
        $logic = in_array($logic, ['AND', 'OR'], true) ? $logic : 'AND';

        if (count($filters) > 0) {
            $query->where(function ($q) use ($filters, $spec, $logic) {
                foreach ($filters as $filter) {
                    $field = $filter['field'] ?? null;
                    $op    = strtolower((string)($filter['operator'] ?? 'equals'));
                    $value = $filter['value'] ?? null;

                    if (!$field || $value === null) continue;
                    if (!isset($spec[$field])) continue;

                    $allowedOps = $spec[$field]['ops'];
                    if (!in_array($op, $allowedOps, true)) continue;

                    $column = $spec[$field]['col'];
                    $this->applyOperator($q, $column, $op, $value, $logic);
                }
            });
        }

        // SEARCH
        if ($request->filled('search')) {
            $search = (string) $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('students.nim', 'ilike', "%{$search}%")
                    ->orWhere('students.name', 'ilike', "%{$search}%")
                    ->orWhere('courses.code', 'ilike', "%{$search}%");
            });
        }

        // QUICK FILTER
        if ($request->filled('status')) {
            $query->where('enrollments.status', $request->status);
        }
        if ($request->filled('semester')) {
            $query->where('enrollments.semester', $request->semester);
        }

        return $query;
    }

    /**
     * =========================================================
     * APPLY OPERATOR
     * =========================================================
     */
    private function applyOperator($query, $column, $operator, $value, $logic = 'AND')
    {
        $operator = strtolower((string) $operator);
        $isOr = ($logic === 'OR');

        $whereLike = function ($col, $op, $val) use ($query, $isOr) {
            return $isOr ? $query->orWhere($col, $op, $val) : $query->where($col, $op, $val);
        };

        switch ($operator) {
            case 'contains':
                $whereLike($column, 'ilike', "%{$value}%");
                break;

            case 'startswith':
                $whereLike($column, 'ilike', "{$value}%");
                break;

            case 'equals':
                $isOr ? $query->orWhere($column, '=', $value) : $query->where($column, '=', $value);
                break;

            case 'in':
                if (is_string($value)) {
                    $value = array_values(array_filter(array_map('trim', explode(',', $value))));
                }
                if (!is_array($value) || count($value) === 0) break;

                $isOr ? $query->orWhereIn($column, $value) : $query->whereIn($column, $value);
                break;

            case 'between':
                if (is_string($value)) {
                    $parts = array_values(array_filter(array_map('trim', explode(',', $value))));
                    if (count($parts) === 2) $value = $parts;
                }
                if (!is_array($value) || count($value) !== 2) break;

                $isOr
                    ? $query->orWhereBetween($column, [$value[0], $value[1]])
                    : $query->whereBetween($column, [$value[0], $value[1]]);
                break;

            default:
                $isOr ? $query->orWhere($column, '=', $value) : $query->where($column, '=', $value);
                break;
        }
    }

    /**
     * =========================================================
     * LIST (pagination + advanced order)
     * =========================================================
     */
    public function index(Request $request)
    {
        $query = $this->baseQuery($request);

        // ✅ Advanced Order
        $this->applySorts($query, $request);

        $pageSize = $request->integer('page_size', 10);
        return $query->paginate($pageSize);
    }

    /**
     * =========================================================
     * EXPORT CSV (streaming)
     * ✅ mengikuti Advanced Order dengan chunk() (bukan chunkById)
     * =========================================================
     */
    public function export(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        $fileName = 'enrollments_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($request) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'nim',
                'student_name',
                'student_email',
                'course_code',
                'course_name',
                'course_credits',
                'semester',
                'academic_year',
                'status',
            ]);

            $query = $this->baseQuery($request);

            // ✅ apply multi-sort untuk export
            $this->applySorts($query, $request);

            // ✅ streaming chunk (ORDER tetap respected)
            $query->chunk(5000, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->student_nim,
                        $r->student_name,
                        $r->student_email,
                        $r->course_code,
                        $r->course_name,
                        $r->course_credits,
                        $r->semester,
                        $r->academic_year,
                        $r->status,
                    ]);
                }
                fflush($out);
            });

            fclose($out);
        }, $fileName, [
            'Content-Type'  => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    // =========================
    // STORE / UPDATE / DESTROY
    // (bagian bawah kamu bisa keep seperti punya kamu)
    // =========================

    public function store(Request $request)
    {
        $request->validate([
            'nim'           => ['required', 'regex:/^\d{8,12}$/'],
            'student_name'  => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255'],
            'course_code'   => ['required', 'regex:/^[A-Z]{2,4}[0-9]{3}$/'],
            'course_name'   => ['required', 'string', 'max:255'],
            'credits'       => ['required', 'integer', 'min:1', 'max:6'],
            'academic_year' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'      => ['required', Rule::in(['GANJIL', 'GENAP'])],
            'status'        => ['required', Rule::in(['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED'])],
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $student = Student::firstOrCreate(
                    ['nim' => $request->nim],
                    ['name' => $request->student_name, 'email' => $request->email]
                );

                $student->fill([
                    'name'  => $request->student_name,
                    'email' => $request->email,
                ])->save();

                $course = Course::firstOrCreate(
                    ['code' => $request->course_code],
                    ['name' => $request->course_name, 'credits' => $request->credits]
                );

                $course->fill([
                    'name'    => $request->course_name,
                    'credits' => $request->credits,
                ])->save();

                $enrollment = Enrollment::create([
                    'student_id'    => $student->id,
                    'course_id'     => $course->id,
                    'academic_year' => $request->academic_year,
                    'semester'      => $request->semester,
                    'status'        => $request->status,
                ]);

                return response()->json([
                    'message' => 'Enrollment created',
                    'data'    => $enrollment->load(['student', 'course']),
                ], 201);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Create failed',
                'errors'  => [
                    'enrollment' => ['Duplicate enrollment for the same student/course/year/semester.']
                ],
            ], 409);
        }
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'academic_year' => ['sometimes', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'      => ['sometimes', Rule::in(['GANJIL', 'GENAP'])],
            'status'        => ['sometimes', Rule::in(['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED'])],

            'nim'          => ['sometimes', 'regex:/^\d{8,12}$/'],
            'student_name' => ['sometimes', 'string', 'max:255'],
            'email'        => ['sometimes', 'email', 'max:255'],

            'course_code' => ['sometimes', 'regex:/^[A-Z]{2,4}[0-9]{3}$/'],
            'course_name' => ['sometimes', 'string', 'max:255'],
            'credits'     => ['sometimes', 'integer', 'min:1', 'max:6'],
        ]);

        try {
            $result = DB::transaction(function () use ($validated, $enrollment) {
                $student = $enrollment->student;

                if (array_key_exists('nim', $validated)) {
                    $student = Student::firstOrCreate(
                        ['nim' => $validated['nim']],
                        [
                            'name'  => $validated['student_name'] ?? 'Unknown',
                            'email' => $validated['email'] ?? ('student' . $validated['nim'] . '@example.com'),
                        ]
                    );
                }

                if ($student && (array_key_exists('student_name', $validated) || array_key_exists('email', $validated))) {
                    $student->fill([
                        'name'  => $validated['student_name'] ?? $student->name,
                        'email' => $validated['email'] ?? $student->email,
                    ])->save();
                }

                $course = $enrollment->course;

                if (array_key_exists('course_code', $validated)) {
                    $course = Course::firstOrCreate(
                        ['code' => $validated['course_code']],
                        [
                            'name'    => $validated['course_name'] ?? 'Unknown',
                            'credits' => $validated['credits'] ?? 1,
                        ]
                    );
                }

                if ($course && (array_key_exists('course_name', $validated) || array_key_exists('credits', $validated))) {
                    $course->fill([
                        'name'    => $validated['course_name'] ?? $course->name,
                        'credits' => $validated['credits'] ?? $course->credits,
                    ])->save();
                }

                $enrollment->fill([
                    'student_id'    => $student?->id ?? $enrollment->student_id,
                    'course_id'     => $course?->id ?? $enrollment->course_id,
                    'academic_year' => $validated['academic_year'] ?? $enrollment->academic_year,
                    'semester'      => $validated['semester'] ?? $enrollment->semester,
                    'status'        => $validated['status'] ?? $enrollment->status,
                ]);

                $enrollment->save();

                return $enrollment->load(['student', 'course']);
            });

            return response()->json([
                'message' => 'Enrollment updated',
                'data'    => $result,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Update failed',
                'errors'  => [
                    'enrollment' => ['Duplicate enrollment for the same student/course/year/semester.']
                ],
            ], 409);
        }
    }

    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();

        return response()->json([
            'message' => 'Enrollment deleted',
        ]);
    }
}
