<script setup>
import { ref, computed, onMounted, watch } from "vue";
import {
  fetchEnrollments,
  createEnrollment,
  updateEnrollmentStatus,
  updateEnrollment,
  deleteEnrollment,
} from "../api/enrollmentApi";

import EnrollmentToolbar from "../components/EnrollmentToolbar.vue";
import EnrollmentCreateForm from "../components/EnrollmentCreateForm.vue";
import EnrollmentTable from "../components/EnrollmentTable.vue";
import EnrollmentEditModal from "../components/EnrollmentEditModal.vue";
import ConfirmModal from "../components/ConfirmModal.vue";
import Toast from "../components/Toast.vue";
import AdvancedFilterPanel from "../components/AdvancedFilterPanel.vue";
import AdvancedOrderPanel from "../components/AdvancedOrderPanel.vue";

/**
 * State
 */
const enrollments = ref([]);
const currentPage = ref(1);
const pageSize = ref(10);
const total = ref(0);
const loading = ref(false);
const creating = ref(false);
const exporting = ref(false);

const search = ref("");
const debouncedSearch = ref("");

const statusFilter = ref("");
const semesterFilter = ref("");

// legacy single sort (UI header table)
const sortBy = ref("id");
const sortDir = ref("desc");

/**
 * Advanced filter state
 */
const filters = ref([]);
const logic = ref("AND");

/**
 * ✅ Advanced Order state (multi sort)
 * Format: [{ field:"academic_year", dir:"desc" }, ...]
 */
const sorts = ref([{ field: sortBy.value, dir: sortDir.value }]);

/**
 * Toast
 */
const toast = ref({ show: false, type: "success", title: "", message: "" });
const showToast = (type, title, message) => {
  toast.value = { show: true, type, title, message };
  setTimeout(() => (toast.value.show = false), 2600);
};

/**
 * Delete modal
 */
const modalOpen = ref(false);
const modalRow = ref(null);

const openDelete = (row) => {
  modalRow.value = row;
  modalOpen.value = true;
};
const closeDelete = () => {
  modalOpen.value = false;
  modalRow.value = null;
};

/**
 * Edit modal
 */
const editOpen = ref(false);
const editSaving = ref(false);
const editInitial = ref(null);

const openEdit = (payload) => {
  editInitial.value = { ...payload };
  editOpen.value = true;
};
const closeEdit = () => {
  editOpen.value = false;
  editInitial.value = null;
};

/**
 * Form create
 */
const form = ref({
  nim: "",
  student_name: "",
  email: "",
  course_code: "",
  course_name: "",
  credits: "",
  academic_year: "2025/2026",
  semester: "",
  status: "",
});

/**
 * Debounce search
 */
let debounceTimer = null;
watch(search, (val) => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    debouncedSearch.value = val;
  }, 400);
});

/**
 * Computed
 */
const totalPages = computed(() => Math.max(1, Math.ceil(total.value / pageSize.value)));
const canPrev = computed(() => currentPage.value > 1);
const canNext = computed(() => currentPage.value < totalPages.value);

/**
 * Pager handlers
 */
const goPrev = () => {
  if (currentPage.value > 1) currentPage.value -= 1;
};
const goNext = () => {
  if (currentPage.value < totalPages.value) currentPage.value += 1;
};

/**
 * ✅ Multi-sort helpers
 */
const normalizeDir = (dir) => (String(dir).toLowerCase() === "asc" ? "asc" : "desc");

/**
 * Set primary sort (dipakai saat klik header tabel)
 */
const setPrimarySort = (field, dir) => {
  const next = [];
  next.push({ field, dir: normalizeDir(dir) });

  // keep secondary sorts (exclude same field)
  for (const s of sorts.value || []) {
    if (!s?.field) continue;
    if (s.field === field) continue;
    next.push({ field: s.field, dir: normalizeDir(s.dir) });
  }

  sorts.value = next;
};

/**
 * Sort handler (table header)
 */
const toggleSort = (key) => {
  if (sortBy.value === key) {
    sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  } else {
    sortBy.value = key;
    sortDir.value = "asc";
  }

  // sync ke multi sort (primary)
  setPrimarySort(sortBy.value, sortDir.value);
};

/**
 * Build params (list & export)
 */
const buildQueryParams = () => ({
  page: currentPage.value,
  page_size: pageSize.value,
  search: debouncedSearch.value || undefined,
  status: statusFilter.value || undefined,
  semester: semesterFilter.value || undefined,

  logic: logic.value || "AND",

  filters: filters.value?.length ? JSON.stringify(filters.value) : undefined,

  sorts: sorts.value?.length ? JSON.stringify(sorts.value) : undefined,
});

/**
 * API actions
 */
const load = async () => {
  loading.value = true;
  try {
    const data = await fetchEnrollments(buildQueryParams());
    enrollments.value = data.data ?? [];
    total.value = data.total ?? 0;

    const tp = Math.max(1, Math.ceil((data.total ?? 0) / pageSize.value));
    if (currentPage.value > tp) currentPage.value = tp;
  } catch (e) {
    showToast("error", "Fetch failed", JSON.stringify(e?.response?.data ?? e.message, null, 2));
  } finally {
    loading.value = false;
  }
};

const onCreate = async () => {
  creating.value = true;
  try {
    await createEnrollment({
      ...form.value,
      credits: form.value.credits === "" ? "" : Number(form.value.credits),
    });

    showToast("success", "Created", "Enrollment berhasil dibuat.");
    form.value = {
      nim: "",
      student_name: "",
      email: "",
      course_code: "",
      course_name: "",
      credits: "",
      academic_year: "2025/2026",
      semester: "",
      status: "",
    };

    currentPage.value = 1;
    await load();
  } catch (e) {
    const payload = e?.response?.data ?? { message: e.message };
    showToast("error", "Create failed", JSON.stringify(payload, null, 2));
  } finally {
    creating.value = false;
  }
};

const onSaveStatus = async ({ id, status }) => {
  try {
    await updateEnrollmentStatus(id, status);
    showToast("success", "Updated", "Status berhasil diubah.");
    await load();
  } catch (e) {
    const payload = e?.response?.data ?? { message: e.message };
    showToast("error", "Update failed", JSON.stringify(payload, null, 2));
  }
};

const onConfirmDelete = async () => {
  const row = modalRow.value;
  if (!row) return;

  try {
    await deleteEnrollment(row.id);
    showToast("success", "Deleted", "Enrollment berhasil dihapus.");
    closeDelete();

    if (enrollments.value.length === 1 && currentPage.value > 1) {
      currentPage.value -= 1;
    }
    await load();
  } catch (e) {
    const payload = e?.response?.data ?? { message: e.message };
    showToast("error", "Delete failed", JSON.stringify(payload, null, 2));
  }
};

const onSubmitEdit = async (payload) => {
  if (!payload?.id) {
    showToast("error", "Edit failed", "ID kosong. Pastikan row punya field id.");
    return;
  }

  editSaving.value = true;
  try {
    await updateEnrollment(payload.id, {
      nim: payload.nim,
      student_name: payload.student_name,
      email: payload.email,
      course_code: payload.course_code,
      course_name: payload.course_name,
      credits: payload.credits === "" ? "" : Number(payload.credits),
      academic_year: payload.academic_year,
      semester: payload.semester,
      status: payload.status,
    });

    showToast("success", "Updated", "Enrollment berhasil diubah.");
    closeEdit();
    await load();
  } catch (e) {
    const payloadErr = e?.response?.data ?? { message: e.message };
    showToast("error", "Edit failed", JSON.stringify(payloadErr, null, 2));
  } finally {
    editSaving.value = false;
  }
};

/**
 * EXPORT CSV
 */
const exportCsv = async () => {
  exporting.value = true;
  try {
    const base = import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000";
    const params = new URLSearchParams();

    params.set("page_size", String(pageSize.value));

    if (debouncedSearch.value) params.set("search", debouncedSearch.value);
    if (statusFilter.value) params.set("status", statusFilter.value);
    if (semesterFilter.value) params.set("semester", semesterFilter.value);

    if (logic.value) params.set("logic", logic.value);
    if (filters.value?.length) params.set("filters", JSON.stringify(filters.value));
    if (sorts.value?.length) params.set("sorts", JSON.stringify(sorts.value));

    const url = `${base}/api/enrollments/export?${params.toString()}`;
    const res = await fetch(url, { method: "GET", headers: { Accept: "text/csv" } });

    if (!res.ok) {
      const text = await res.text();
      throw new Error(text || `Export failed: ${res.status}`);
    }

    const blob = await res.blob();
    const cd = res.headers.get("content-disposition") || "";
    const match = cd.match(/filename\*=UTF-8''([^;]+)|filename="?([^"]+)"?/i);
    const filename = decodeURIComponent(match?.[1] || match?.[2] || "enrollments.csv");

    const a = document.createElement("a");
    const objectUrl = window.URL.createObjectURL(blob);
    a.href = objectUrl;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    window.URL.revokeObjectURL(objectUrl);

    showToast("success", "Export success", `File: ${filename}`);
  } catch (e) {
    showToast("error", "Export failed", e?.message || String(e));
  } finally {
    exporting.value = false;
  }
};

/**
 * ✅ Watchers
 * - query berubah → reset page 1 → load
 * - page berubah → load
 *
 * Penting: jangan panggil load dobel di onMounted.
 */
watch(
  [pageSize, debouncedSearch, statusFilter, semesterFilter, filters, logic, sorts],
  async () => {
    currentPage.value = 1;
    await load();
  },
  { deep: true },
);

watch(currentPage, load);

/**
 * ✅ Sinkronkan sortBy/sortDir dari sorts[0] (buat UI header)
 * Ini bikin header table tetap konsisten meski multi sort berubah dari panel.
 */
watch(
  sorts,
  (v) => {
    const p = v?.[0];
    if (!p?.field) return;
    sortBy.value = p.field;
    sortDir.value = normalizeDir(p.dir);
  },
  { deep: true },
);

onMounted(async () => {
  // jangan setPrimarySort di sini (itu akan mengubah sorts dan memicu watcher)
  // cukup load sekali
  await load();
});

const modalLabel = computed(() => {
  const r = modalRow.value;
  if (!r) return "";
  const nim = r.student_nim ?? r.student?.nim ?? "-";
  const code = r.course_code ?? r.course?.code ?? "-";
  return `${nim} • ${code} • ${r.academic_year ?? "-"}`;
});
</script>

<template>
  <div class="page">
    <Toast v-bind="toast" />

    <div class="container">
      <div class="hero">
        <div class="hero-left">
          <div class="logo">📘</div>
          <div>
            <h1>Enrollment Management</h1>
            <p class="muted">
              Single-page CRUD — server-side pagination, sorting, filtering, searching, inline
              update, delete.
            </p>
          </div>
        </div>

        <div class="hero-right">
          <div class="chips">
            <div class="chip">
              Total: <b>{{ total }}</b>
            </div>
            <div class="chip">
              Page: <b>{{ currentPage }}</b> / {{ totalPages }}
            </div>
          </div>

          <button class="btn-export" :disabled="exporting" @click="exportCsv">
            <span v-if="!exporting" class="btn-content">
              <span class="icon">⬇</span>
              Export CSV
            </span>
            <span v-else class="btn-content">
              <span class="spinner"></span>
              Exporting...
            </span>
          </button>
        </div>
      </div>

      <div class="stack">
        <EnrollmentToolbar
          :search="search"
          @update:search="search = $event"
          :status="statusFilter"
          @update:status="statusFilter = $event"
          :semester="semesterFilter"
          @update:semester="semesterFilter = $event"
          :page-size="pageSize"
          @update:pageSize="pageSize = $event"
        />

        <AdvancedFilterPanel
          :filters="filters"
          @update:filters="filters = $event"
          :logic="logic"
          @update:logic="logic = $event"
        />

        <AdvancedOrderPanel :sorts="sorts" @update:sorts="sorts = $event" />

        <EnrollmentCreateForm v-model="form" :creating="creating" @submit="onCreate" />

        <EnrollmentTable
          :rows="enrollments"
          :loading="loading"
          :sortBy="sortBy"
          :sortDir="sortDir"
          :currentPage="currentPage"
          :totalPages="totalPages"
          :canPrev="canPrev"
          :canNext="canNext"
          @toggleSort="toggleSort"
          @prev="goPrev"
          @next="goNext"
          @delete="openDelete"
          @edit="openEdit"
          @saveStatus="onSaveStatus"
        />
      </div>
    </div>

    <EnrollmentEditModal
      :open="editOpen"
      :initial="editInitial"
      :saving="editSaving"
      @close="closeEdit"
      @submit="onSubmitEdit"
    />

    <ConfirmModal
      :open="modalOpen"
      title="Hapus Enrollment?"
      :message="`Kamu akan menghapus: ${modalLabel}`"
      confirmText="Delete"
      cancelText="Cancel"
      @close="closeDelete"
      @confirm="onConfirmDelete"
    />
  </div>
</template>

<style scoped>
/* style kamu tetap */
.page {
  min-height: 100vh;
  padding: 28px 18px 60px;
  background:
    radial-gradient(1200px 500px at 10% 0%, rgba(99, 102, 241, 0.16), transparent 60%),
    radial-gradient(900px 500px at 90% 10%, rgba(16, 185, 129, 0.14), transparent 55%), #f7f8fc;
  color: #0f172a;
  font-family:
    ui-sans-serif,
    system-ui,
    -apple-system,
    Segoe UI,
    Roboto,
    Arial,
    sans-serif;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
}

.stack {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.hero {
  margin: 0 auto 14px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
}

.hero-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.hero-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 10px;
}

.logo {
  width: 44px;
  height: 44px;
  display: grid;
  place-items: center;
  border-radius: 14px;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.22), rgba(16, 185, 129, 0.18));
  border: 1px solid rgba(15, 23, 42, 0.06);
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

h1 {
  margin: 0;
  font-size: 28px;
  letter-spacing: -0.02em;
}

.muted {
  color: #64748b;
}

.chips {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.chip {
  background: rgba(255, 255, 255, 0.7);
  border: 1px solid rgba(15, 23, 42, 0.06);
  backdrop-filter: blur(10px);
  border-radius: 999px;
  padding: 8px 12px;
  font-size: 13px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
}

.btn-export {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border: none;
  border-radius: 14px;
  padding: 11px 18px;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.3px;
  cursor: pointer;
  color: #fff;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.95), rgba(79, 70, 229, 0.95));
  box-shadow:
    0 10px 25px rgba(79, 70, 229, 0.25),
    0 4px 10px rgba(79, 70, 229, 0.15);
  transition:
    transform 0.15s ease,
    box-shadow 0.2s ease,
    opacity 0.2s ease;
}
.btn-export:hover {
  transform: translateY(-2px);
  box-shadow:
    0 14px 32px rgba(79, 70, 229, 0.35),
    0 6px 16px rgba(79, 70, 229, 0.25);
}
.btn-export:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: 0 6px 14px rgba(79, 70, 229, 0.15);
}

.btn-content {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.icon {
  font-size: 14px;
  transform: translateY(-1px);
}
.spinner {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top: 2px solid #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 920px) {
  .hero {
    flex-direction: column;
    align-items: stretch;
  }
  .hero-right {
    align-items: flex-start;
  }
  .chips {
    justify-content: flex-start;
  }
}
</style>
