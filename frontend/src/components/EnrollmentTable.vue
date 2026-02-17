<script setup>
const props = defineProps({
  rows: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  sortBy: { type: String, default: "id" },
  sortDir: { type: String, default: "desc" },
  currentPage: { type: Number, default: 1 },
  totalPages: { type: Number, default: 1 },
  canPrev: Boolean,
  canNext: Boolean,
});

const emit = defineEmits(["toggleSort", "prev", "next", "delete", "edit"]);

const sortIcon = (key) => {
  if (props.sortBy !== key) return "⇅";
  return props.sortDir === "asc" ? "↑" : "↓";
};

const badgeClass = (status) => {
  const s = String(status || "").toUpperCase();
  if (s === "APPROVED") return "badge badge-green";
  if (s === "SUBMITTED") return "badge badge-amber";
  if (s === "REJECTED") return "badge badge-red";
  return "badge badge-gray";
};

const statusLabel = (status) => String(status || "").toUpperCase() || "DRAFT";

const rowNim = (r) => r?.student_nim ?? r?.student?.nim ?? "";
const rowName = (r) => r?.student_name ?? r?.student?.name ?? "";
const rowEmail = (r) => r?.student_email ?? r?.student?.email ?? "";
const rowCourseCode = (r) => r?.course_code ?? r?.course?.code ?? "";
const rowCourseName = (r) => r?.course_name ?? r?.course?.name ?? "";
const rowCredits = (r) => r?.course_credits ?? r?.course?.credits ?? "";

const normalizeRow = (row) => ({
  id: row?.id,
  nim: rowNim(row),
  student_name: rowName(row),
  email: rowEmail(row),
  course_code: rowCourseCode(row),
  course_name: rowCourseName(row),
  credits: rowCredits(row),
  academic_year: row?.academic_year ?? "",
  semester: row?.semester ?? "",
  status: statusLabel(row?.status),
});

const requestDelete = (row) => {
  if (!row?.id) return alert("Delete gagal: row.id undefined. Pastikan API mengirim field id.");
  emit("delete", row);
};

const requestEdit = (row) => {
  if (!row?.id) return alert("Edit gagal: row.id undefined. Pastikan API mengirim field id.");
  emit("edit", normalizeRow(row));
};
</script>

<template>
  <div class="card">
    <div class="head between">
      <h2>Enrollments</h2>
      <div class="muted tiny">Klik header untuk sort. Edit dilakukan via modal.</div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th class="sticky">Actions</th>
            <th class="sticky clickable" @click="emit('toggleSort', 'academic_year')">
              Academic Year <span class="sort">{{ sortIcon("academic_year") }}</span>
            </th>
            <th class="sticky clickable" @click="emit('toggleSort', 'student_nim')">
              NIM <span class="sort">{{ sortIcon("student_nim") }}</span>
            </th>
            <th class="sticky clickable" @click="emit('toggleSort', 'student_name')">
              Name <span class="sort">{{ sortIcon("student_name") }}</span>
            </th>
            <th class="sticky clickable" @click="emit('toggleSort', 'course_code')">
              Course Code <span class="sort">{{ sortIcon("course_code") }}</span>
            </th>
            <th class="sticky clickable" @click="emit('toggleSort', 'course_name')">
              Course Name <span class="sort">{{ sortIcon("course_name") }}</span>
            </th>
            <th class="sticky clickable" @click="emit('toggleSort', 'semester')">
              Semester <span class="sort">{{ sortIcon("semester") }}</span>
            </th>
            <th class="sticky clickable" @click="emit('toggleSort', 'status')">
              Status <span class="sort">{{ sortIcon("status") }}</span>
            </th>
          </tr>
        </thead>

        <tbody>
          <template v-if="loading">
            <tr v-for="i in 6" :key="`sk-${i}`">
              <td><div class="sk sk-btn"></div></td>
              <td v-for="j in 7" :key="`sk-${i}-${j}`"><div class="sk"></div></td>
            </tr>
          </template>

          <tr v-else-if="rows.length === 0">
            <td class="empty" colspan="8">
              <div class="empty-box">
                <div class="empty-icon">📦</div>
                <div>
                  <div class="empty-title">No data</div>
                  <div class="empty-sub">Coba ubah filter atau buat enrollment baru.</div>
                </div>
              </div>
            </td>
          </tr>

          <template v-else>
            <tr v-for="row in rows" :key="row.id" class="row">
              <td class="actions-col">
                <div class="actions">
                  <button type="button" class="btn btn-primary btn-sm" @click="requestEdit(row)">
                    Edit
                  </button>
                  <button type="button" class="btn btn-danger btn-sm" @click="requestDelete(row)">
                    Delete
                  </button>
                </div>
              </td>

              <td>{{ row?.academic_year }}</td>
              <td>{{ rowNim(row) }}</td>
              <td>{{ rowName(row) }}</td>
              <td>{{ rowCourseCode(row) }}</td>
              <td>{{ rowCourseName(row) }}</td>
              <td>{{ row?.semester }}</td>
              <td><span :class="badgeClass(row?.status)">{{ statusLabel(row?.status) }}</span></td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div class="pager">
      <button type="button" class="btn" @click="emit('prev')" :disabled="!canPrev">Prev</button>
      <div class="pager-mid">
        <div class="muted tiny">Page</div>
        <div class="pager-num">{{ currentPage }} / {{ totalPages }}</div>
      </div>
      <button type="button" class="btn" @click="emit('next')" :disabled="!canNext">Next</button>
    </div>
  </div>
</template>

<style scoped>
.card {
  background: rgba(255, 255, 255, 0.78);
  border: 1px solid rgba(15, 23, 42, 0.06);
  backdrop-filter: blur(12px);
  border-radius: 18px;
  padding: 16px;
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
}
.head { display: flex; align-items: baseline; gap: 12px; margin-bottom: 12px; }
.between { justify-content: space-between; }
h2 { margin: 0; font-size: 18px; letter-spacing: -0.01em; }
.muted { color: #64748b; }
.tiny { font-size: 12px; }

.table-wrap {
  overflow: auto;
  border-radius: 14px;
  border: 1px solid rgba(148, 163, 184, 0.28);
  background: rgba(255, 255, 255, 0.65);
}
table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 980px; }
thead th {
  position: sticky;
  top: 0;
  background: rgba(248, 250, 252, 0.95);
  backdrop-filter: blur(8px);
  z-index: 1;
  font-size: 13px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.28);
}
th, td { padding: 12px; text-align: left; white-space: nowrap; vertical-align: top; }
.clickable { cursor: pointer; user-select: none; }
.sort { margin-left: 6px; color: #64748b; font-weight: 800; }
tbody td { border-bottom: 1px solid rgba(148, 163, 184, 0.18); }
.row:hover td { background: rgba(99, 102, 241, 0.06); }

.actions-col { width: 200px; }
.actions { display: flex; gap: 8px; align-items: center; } /* ✅ flex taruh di div, bukan td */

.btn {
  padding: 9px 12px;
  border-radius: 12px;
  border: 1px solid rgba(148, 163, 184, 0.55);
  background: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  font-weight: 800;
}
.btn:disabled { opacity: 0.55; cursor: not-allowed; }
.btn-primary {
  border-color: rgba(99, 102, 241, 0.55);
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.95), rgba(79, 70, 229, 0.95));
  color: #fff;
}
.btn-danger {
  border-color: rgba(239, 68, 68, 0.4);
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.95), rgba(220, 38, 38, 0.95));
  color: #fff;
}
.btn-sm { padding: 8px 10px; border-radius: 10px; }

.badge {
  display: inline-block;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.02em;
}
.badge-gray { background: rgba(100, 116, 139, 0.16); color: #334155; }
.badge-amber { background: rgba(245, 158, 11, 0.18); color: #92400e; }
.badge-green { background: rgba(16, 185, 129, 0.18); color: #065f46; }
.badge-red { background: rgba(239, 68, 68, 0.18); color: #991b1b; }

.sk {
  height: 14px;
  border-radius: 10px;
  background: linear-gradient(90deg, rgba(148,163,184,.22), rgba(148,163,184,.1), rgba(148,163,184,.22));
  background-size: 200% 100%;
  animation: shimmer 1.2s ease-in-out infinite;
}
.sk-btn { height: 30px; width: 120px; border-radius: 12px; }
@keyframes shimmer { 0% { background-position: 0% 0; } 100% { background-position: 200% 0; } }

.empty { padding: 30px; }
.empty-box { display: flex; gap: 12px; align-items: center; justify-content: center; }
.empty-icon { font-size: 22px; }
.empty-title { font-weight: 800; }
.empty-sub { color: #64748b; font-size: 12px; margin-top: 2px; }

.pager { margin-top: 12px; display: flex; align-items: center; justify-content: center; gap: 14px; }
.pager-mid { text-align: center; }
.pager-num { font-weight: 900; letter-spacing: -0.01em; }
</style>
