<script setup>
import { computed } from "vue";

const props = defineProps({
  filters: { type: Array, default: () => [] },
  logic: { type: String, default: "AND" },
});

const emit = defineEmits(["update:filters", "update:logic"]);

const localFilters = computed({
  get: () => props.filters,
  set: (v) => emit("update:filters", v),
});

const localLogic = computed({
  get: () => props.logic,
  set: (v) => emit("update:logic", v),
});

const fieldOptions = [
  { value: "student_nim", label: "Student NIM" },
  { value: "student_name", label: "Student Name" },
  { value: "student_email", label: "Student Email" },
  { value: "course_code", label: "Course Code" },
  { value: "course_name", label: "Course Name" },
  { value: "course_credits", label: "Course Credits" },
  { value: "academic_year", label: "Academic Year" },
  { value: "semester", label: "Semester" },
  { value: "status", label: "Status" },
  { value: "created_at", label: "Created At" },
];

const operatorOptions = [
  { value: "equals", label: "Equals" },
  { value: "contains", label: "Contains" },
  { value: "startswith", label: "Starts With" },
  { value: "in", label: "In (A,B,C)" },
  { value: "between", label: "Between (min,max)" },
];

const addFilter = () => {
  localFilters.value = [
    ...(localFilters.value || []),
    { field: "student_nim", operator: "contains", value: "" },
  ];
};

const removeFilter = (idx) => {
  const next = [...(localFilters.value || [])];
  next.splice(idx, 1);
  localFilters.value = next;
};

const clearAll = () => {
  localFilters.value = [];
};
</script>

<template>
  <section class="af-card">
    <header class="af-head">
      <div class="af-title-wrap">
        <div class="af-title">Advanced Filter</div>
        <div class="af-sub">Multi-filter + AND/OR</div>
      </div>

      <div class="af-actions">
        <div class="af-pill">
          <span class="af-pill-label">Logic</span>
          <select v-model="localLogic" class="af-select" aria-label="Logic">
            <option value="AND">AND</option>
            <option value="OR">OR</option>
          </select>
        </div>

        <button type="button" class="af-btn af-btn-primary" @click="addFilter">+ Add Filter</button>

        <button
          type="button"
          class="af-btn af-btn-ghost"
          :disabled="!localFilters || localFilters.length === 0"
          @click="clearAll"
        >
          Clear
        </button>
      </div>
    </header>

    <div class="af-body">
      <div v-if="!localFilters || localFilters.length === 0" class="af-empty">
        Belum ada filter. Klik <b>Add Filter</b> untuk mulai.
      </div>

      <div v-else class="af-list">
        <div v-for="(f, idx) in localFilters" :key="idx" class="af-row">
          <div class="af-row-grid">
            <div class="af-field">
              <label class="af-label">Field</label>
              <select v-model="f.field" class="af-input">
                <option v-for="opt in fieldOptions" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>

            <div class="af-field">
              <label class="af-label">Operator</label>
              <select v-model="f.operator" class="af-input">
                <option v-for="op in operatorOptions" :key="op.value" :value="op.value">
                  {{ op.label }}
                </option>
              </select>
            </div>

            <div class="af-field">
              <label class="af-label">Value</label>
              <input
                v-model="f.value"
                class="af-input"
                placeholder="contoh: IF001 | 1,6 | 2025/2026"
              />
            </div>

            <div class="af-field af-field-del">
              <label class="af-label">&nbsp;</label>
              <button type="button" class="af-del" @click="removeFilter(idx)" aria-label="Remove">
                ✕
              </button>
            </div>
          </div>

          <div class="af-hint">Tips: <b>in</b> pakai "A,B,C" • <b>between</b> pakai "min,max"</div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.af-card {
  margin-top: 14px;
  width: 100%;          /* ✅ full lebar container */
  max-width: 100%;      /* ✅ jangan dibatasi */
  background: rgba(255, 255, 255, 0.78);
  border: 1px solid rgba(15, 23, 42, 0.06);
  backdrop-filter: blur(12px);
  border-radius: 18px;
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
  overflow: hidden;
}

.af-head {
  display: grid;
  grid-template-columns: 1fr auto;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.18);
  background: rgba(248, 250, 252, 0.65);
}

.af-title {
  font-weight: 900;
  font-size: 16px;
  letter-spacing: -0.01em;
}
.af-sub {
  color: #64748b;
  font-size: 12px;
  margin-top: 2px;
}

.af-actions {
  display: flex;
  gap: 10px;
  align-items: center;
  justify-content: flex-end;
  flex-wrap: wrap;
}

.af-pill {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border-radius: 14px;
  border: 1px solid rgba(148, 163, 184, 0.35);
  background: rgba(255, 255, 255, 0.75);
}
.af-pill-label {
  font-size: 12px;
  color: #64748b;
  font-weight: 800;
}

.af-select {
  padding: 8px 10px;
  border-radius: 12px;
  border: 1px solid rgba(148, 163, 184, 0.45);
  background: rgba(255, 255, 255, 0.85);
  font-weight: 900;
  min-width: 92px;
  outline: none;
}

.af-btn {
  border-radius: 12px;
  padding: 10px 12px;
  font-weight: 900;
  cursor: pointer;
  border: 1px solid transparent;
  transition:
    transform 0.12s ease,
    opacity 0.2s ease;
}
.af-btn:active {
  transform: translateY(1px);
}
.af-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.af-btn-primary {
  color: #fff;
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.95), rgba(5, 150, 105, 0.95));
  box-shadow: 0 10px 20px rgba(16, 185, 129, 0.18);
}
.af-btn-ghost {
  background: rgba(255, 255, 255, 0.7);
  border-color: rgba(148, 163, 184, 0.45);
  color: #0f172a;
}

.af-body {
  padding: 14px 16px 16px;
}

.af-empty {
  color: #64748b;
  font-size: 13px;
  padding: 10px 0;
}

.af-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.af-row {
  border: 1px solid rgba(148, 163, 184, 0.18);
  background: rgba(248, 250, 252, 0.65);
  border-radius: 16px;
  padding: 12px;
}

.af-row-grid {
  display: grid;
  grid-template-columns: 1.2fr 1fr 2fr 44px;
  gap: 10px;
  align-items: end;
}

.af-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.af-label {
  font-size: 12px;
  color: #64748b;
  font-weight: 800;
}

.af-input {
  width: 100%;
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid rgba(148, 163, 184, 0.55);
  background: rgba(255, 255, 255, 0.82);
  outline: none;
}

.af-field-del {
  align-items: stretch;
}

.af-del {
  width: 44px;
  height: 40px;
  border-radius: 12px;
  border: 1px solid rgba(239, 68, 68, 0.35);
  background: rgba(239, 68, 68, 0.1);
  color: #991b1b;
  cursor: pointer;
  font-weight: 900;
}

.af-hint {
  margin-top: 10px;
  font-size: 12px;
  color: #64748b;
}

@media (max-width: 920px) {
  .af-head {
    grid-template-columns: 1fr;
    align-items: stretch;
  }
  .af-actions {
    justify-content: flex-start;
  }
  .af-row-grid {
    grid-template-columns: 1fr;
  }
  .af-del {
    width: 100%;
  }
}
</style>
