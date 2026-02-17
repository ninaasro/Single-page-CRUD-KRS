<script setup>
import { computed } from "vue";

const props = defineProps({
  sorts: { type: Array, default: () => [] }, // [{field, dir}]
});

const emit = defineEmits(["update:sorts"]);

const localSorts = computed({
  get: () => props.sorts,
  set: (v) => emit("update:sorts", v),
});

const fieldOptions = [
  { value: "id", label: "ID" },
  { value: "academic_year", label: "Academic Year" },
  { value: "semester", label: "Semester" },
  { value: "status", label: "Status" },
  { value: "student_nim", label: "Student NIM" },
  { value: "student_name", label: "Student Name" },
  { value: "student_email", label: "Student Email" },
  { value: "course_code", label: "Course Code" },
  { value: "course_name", label: "Course Name" },
  { value: "course_credits", label: "Course Credits" },
  { value: "created_at", label: "Created At" },
];

const addSort = () => {
  localSorts.value = [
    ...(localSorts.value || []),
    { field: "academic_year", dir: "desc" },
  ];
};

const removeSort = (idx) => {
  const next = [...(localSorts.value || [])];
  next.splice(idx, 1);
  localSorts.value = next;
};

const moveUp = (idx) => {
  if (idx <= 0) return;
  const next = [...localSorts.value];
  [next[idx - 1], next[idx]] = [next[idx], next[idx - 1]];
  localSorts.value = next;
};

const moveDown = (idx) => {
  if (idx >= localSorts.value.length - 1) return;
  const next = [...localSorts.value];
  [next[idx + 1], next[idx]] = [next[idx], next[idx + 1]];
  localSorts.value = next;
};

const clearAll = () => {
  localSorts.value = [];
};
</script>

<template>
  <div class="ao-card">
    <div class="ao-head">
      <div class="ao-title-wrap">
        <div class="ao-title">Advanced Order</div>
        <div class="ao-sub">Multi-column sort (priority top → bottom)</div>
      </div>

      <div class="ao-actions">
        <button type="button" class="ao-ghost" @click="clearAll" :disabled="!localSorts?.length">
          Clear
        </button>
        <button type="button" class="ao-add" @click="addSort">+ Add Sort</button>
      </div>
    </div>

    <div class="ao-body">
      <div v-if="!localSorts || localSorts.length === 0" class="ao-empty">
        Belum ada sorting. Klik <b>Add Sort</b>. (Jika kosong, sorting pakai klik header tabel seperti biasa.)
      </div>

      <div v-else class="ao-list">
        <div v-for="(s, idx) in localSorts" :key="idx" class="ao-row">
          <div class="ao-index">#{{ idx + 1 }}</div>

          <select v-model="s.field" class="ao-input">
            <option v-for="opt in fieldOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>

          <select v-model="s.dir" class="ao-input ao-dir">
            <option value="asc">ASC</option>
            <option value="desc">DESC</option>
          </select>

          <div class="ao-buttons">
            <button type="button" class="ao-mini" @click="moveUp(idx)" :disabled="idx === 0">↑</button>
            <button
              type="button"
              class="ao-mini"
              @click="moveDown(idx)"
              :disabled="idx === localSorts.length - 1"
            >
              ↓
            </button>
            <button type="button" class="ao-del" @click="removeSort(idx)">✕</button>
          </div>
        </div>

        <div class="ao-hint">
          Contoh: <b>Academic Year DESC</b>, <b>Semester ASC</b>, <b>Student NIM ASC</b>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.ao-card {
  width: 100%;
  background: rgba(255, 255, 255, 0.78);
  border: 1px solid rgba(15, 23, 42, 0.06);
  backdrop-filter: blur(12px);
  border-radius: 18px;
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
  overflow: hidden;
}

.ao-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 14px 16px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.22);
}

.ao-title {
  font-weight: 900;
  font-size: 16px;
  letter-spacing: -0.01em;
}
.ao-sub {
  color: #64748b;
  font-size: 12px;
  margin-top: 2px;
}

.ao-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.ao-add {
  border: none;
  border-radius: 12px;
  padding: 10px 14px;
  font-weight: 900;
  cursor: pointer;
  color: #fff;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.95), rgba(79, 70, 229, 0.95));
  box-shadow: 0 10px 20px rgba(79, 70, 229, 0.18);
}

.ao-ghost {
  border-radius: 12px;
  padding: 10px 14px;
  font-weight: 900;
  cursor: pointer;
  border: 1px solid rgba(148, 163, 184, 0.55);
  background: rgba(255, 255, 255, 0.7);
}
.ao-ghost:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.ao-body {
  padding: 14px 16px 16px;
}

.ao-empty {
  color: #64748b;
  font-size: 13px;
  padding: 8px 0;
}

.ao-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.ao-row {
  display: grid;
  grid-template-columns: auto 1fr 140px auto;
  gap: 10px;
  align-items: center;
}

.ao-index {
  font-weight: 900;
  color: #475569;
  width: 40px;
  text-align: center;
  background: rgba(15, 23, 42, 0.04);
  border: 1px solid rgba(148, 163, 184, 0.35);
  border-radius: 12px;
  padding: 10px 8px;
}

.ao-input {
  width: 100%;
  padding: 11px 12px;
  border-radius: 12px;
  border: 1px solid rgba(148, 163, 184, 0.55);
  background: rgba(255, 255, 255, 0.75);
  outline: none;
}

.ao-dir {
  text-transform: uppercase;
  font-weight: 900;
}

.ao-buttons {
  display: flex;
  gap: 8px;
  align-items: center;
}

.ao-mini {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  border: 1px solid rgba(148, 163, 184, 0.55);
  background: rgba(255, 255, 255, 0.75);
  cursor: pointer;
  font-weight: 900;
}
.ao-mini:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.ao-del {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  border: 1px solid rgba(239, 68, 68, 0.35);
  background: rgba(239, 68, 68, 0.1);
  color: #991b1b;
  cursor: pointer;
  font-weight: 900;
}

.ao-hint {
  margin-top: 4px;
  color: #64748b;
  font-size: 12px;
}

@media (max-width: 920px) {
  .ao-head {
    flex-direction: column;
    align-items: stretch;
  }
  .ao-actions {
    justify-content: flex-end;
  }
  .ao-row {
    grid-template-columns: 1fr;
  }
  .ao-index {
    width: 100%;
  }
  .ao-buttons {
    justify-content: flex-end;
  }
}
</style>
