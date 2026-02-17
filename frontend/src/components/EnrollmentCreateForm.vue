<script setup>
const props = defineProps({
  modelValue: { type: Object, required: true },
  creating: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue", "submit"]);

const setField = (key, value) => {
  emit("update:modelValue", { ...props.modelValue, [key]: value });
};
</script>

<template>
  <section class="card">
    <header class="head">
      <div>
        <h2 class="title">Create Enrollment</h2>
        <p class="sub">
          Insert ke 3 tabel (students, courses, enrollments) dalam 1 transaksi (backend).
        </p>
      </div>
    </header>

    <!-- 2 columns, rapi -->
    <div class="grid">
      <div class="field">
        <label class="label">NIM</label>
        <input
          class="input"
          :value="modelValue.nim"
          @input="setField('nim', $event.target.value)"
          placeholder="8–12 digit"
          autocomplete="off"
        />
      </div>

      <div class="field">
        <label class="label">Student Name</label>
        <input
          class="input"
          :value="modelValue.student_name"
          @input="setField('student_name', $event.target.value)"
          placeholder="3–100 chars"
          autocomplete="off"
        />
      </div>

      <div class="field">
        <label class="label">Email</label>
        <input
          class="input"
          type="email"
          :value="modelValue.email"
          @input="setField('email', $event.target.value)"
          placeholder="name@email.com"
          autocomplete="off"
        />
      </div>

      <div class="field">
        <label class="label">Academic Year</label>
        <input
          class="input"
          :value="modelValue.academic_year"
          @input="setField('academic_year', $event.target.value)"
          placeholder="2025/2026"
          autocomplete="off"
        />
      </div>

      <div class="field">
        <label class="label">Course Code</label>
        <input
          class="input"
          :value="modelValue.course_code"
          @input="setField('course_code', $event.target.value)"
          placeholder="IF101"
          autocomplete="off"
        />
      </div>

      <!-- full width biar enak -->
      <div class="field span-2">
        <label class="label">Course Name</label>
        <input
          class="input"
          :value="modelValue.course_name"
          @input="setField('course_name', $event.target.value)"
          placeholder="e.g. Pemrograman Dasar"
          autocomplete="off"
        />
      </div>

      <div class="field">
        <label class="label">Credits</label>
        <input
          class="input"
          type="number"
          min="1"
          max="6"
          :value="modelValue.credits"
          @input="setField('credits', $event.target.value)"
          placeholder="1–6"
        />
      </div>

      <div class="field">
        <label class="label">Semester</label>
        <select
          class="input"
          :value="modelValue.semester"
          @change="setField('semester', $event.target.value)"
        >
          <option value="">Choose</option>
          <option value="GANJIL">GANJIL</option>
          <option value="GENAP">GENAP</option>
        </select>
      </div>

      <div class="field">
        <label class="label">Status</label>
        <select
          class="input"
          :value="modelValue.status"
          @change="setField('status', $event.target.value)"
        >
          <option value="">Choose</option>
          <option value="DRAFT">DRAFT</option>
          <option value="SUBMITTED">SUBMITTED</option>
          <option value="APPROVED">APPROVED</option>
          <option value="REJECTED">REJECTED</option>
        </select>
      </div>

      <div class="actions span-2">
        <button class="btn btn-primary" @click="emit('submit')" :disabled="creating">
          <span v-if="creating" class="spinner"></span>
          <span>{{ creating ? "Saving..." : "Save Enrollment" }}</span>
        </button>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Card glassy enterprise */
.card{
  background: rgba(255,255,255,.78);
  border: 1px solid rgba(15,23,42,.06);
  backdrop-filter: blur(12px);
  border-radius: 18px;
  padding: 18px;
  box-shadow: 0 16px 40px rgba(15,23,42,.08);
}

.head{ margin-bottom: 14px; }
.title{
  margin: 0;
  font-size: 18px;
  letter-spacing: -0.01em;
  color: #0f172a;
}
.sub{
  margin: 6px 0 0;
  color:#64748b;
  font-size: 12px;
}

/* ✅ 2 kolom rapi */
.grid{
  display:grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px 18px;
  align-items: start;
}
.span-2{ grid-column: 1 / -1; }

@media (max-width: 900px){
  .grid{ grid-template-columns: 1fr; }
  .span-2{ grid-column: auto; }
}

/* ✅ Field */
.field{ min-width: 0; }
.label{
  display:block;
  margin: 0 0 8px;          /* ini penting biar label gak “nabrak” input */
  font-size: 12px;
  font-weight: 800;
  color:#334155;
  line-height: 1.2;
}

.input{
  width:100%;
  box-sizing: border-box;
  padding: 12px 12px;
  border-radius: 12px;
  border: 1px solid rgba(148,163,184,.55);
  background: rgba(255,255,255,.75);
  outline:none;
  line-height: 1.25;
}

.input:focus{
  border-color: rgba(99,102,241,.95);
  box-shadow: 0 0 0 4px rgba(99,102,241,.14);
}

/* Actions */
.actions{
  margin-top: 4px;
  display:flex;
  justify-content:flex-start;
}

.btn{
  padding: 11px 14px;
  border-radius: 12px;
  border: 1px solid rgba(148,163,184,.55);
  background: rgba(255,255,255,.7);
  cursor: pointer;
  font-weight: 900;
  color: #0f172a;
}

.btn-primary{
  border-color: rgba(99,102,241,.55);
  background: linear-gradient(135deg, rgba(99,102,241,.95), rgba(79,70,229,.95));
  color:#fff;
  min-width: 240px;
}
.btn:disabled{ opacity:.55; cursor:not-allowed; }

.spinner{
  width:14px; height:14px; border-radius:999px;
  border:2px solid rgba(255,255,255,.6);
  border-top-color: rgba(255,255,255,1);
  display:inline-block; margin-right:8px;
  animation: spin .9s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
