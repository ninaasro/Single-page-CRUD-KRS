<script setup>
import { reactive, watch, computed } from "vue";

const props = defineProps({
  open: { type: Boolean, default: false },
  initial: { type: Object, default: null },
  saving: { type: Boolean, default: false },
});

const emit = defineEmits(["close", "submit"]);

const form = reactive({
  id: null,
  nim: "",
  student_name: "",
  email: "",
  course_code: "",
  course_name: "",
  credits: "",
  academic_year: "",
  semester: "",
  status: "DRAFT",
});

watch(
  () => [props.open, props.initial],
  ([isOpen, val]) => {
    if (!isOpen) return;
    if (!val) return;

    form.id = val.id ?? null;
    form.nim = val.nim ?? "";
    form.student_name = val.student_name ?? "";
    form.email = val.email ?? "";
    form.course_code = val.course_code ?? "";
    form.course_name = val.course_name ?? "";
    form.credits = val.credits ?? "";
    form.academic_year = val.academic_year ?? "";
    form.semester = val.semester ?? "";
    form.status = (val.status ?? "DRAFT").toUpperCase();
  },
  { immediate: true }
);

const canSubmit = computed(() => !!form.id);

const onSubmit = () => {
  if (!form.id) {
    alert("ID kosong. Pastikan API mengirim field id.");
    return;
  }
  emit("submit", {
    id: form.id,
    nim: form.nim,
    student_name: form.student_name,
    email: form.email,
    course_code: form.course_code,
    course_name: form.course_name,
    credits: form.credits === "" ? "" : Number(form.credits),
    academic_year: form.academic_year,
    semester: form.semester,
    status: form.status,
  });
};
</script>

<template>
  <div v-if="open" class="overlay" @click.self="emit('close')">
    <div class="modal">
      <div class="modal-head">
        <div>
          <div class="title">Edit Enrollment</div>
          <div class="sub">Kamu bisa update enrollment + student + course sekaligus.</div>
        </div>
        <button type="button" class="x" @click="emit('close')">✕</button>
      </div>

      <div class="grid">
        <div class="section">
          <div class="section-title">Student</div>

          <label class="label">NIM</label>
          <input v-model="form.nim" class="input" placeholder="contoh: 20230001" />

          <label class="label">Name</label>
          <input v-model="form.student_name" class="input" placeholder="Nama mahasiswa" />

          <label class="label">Email</label>
          <input v-model="form.email" class="input" placeholder="email@domain.com" />
        </div>

        <div class="section">
          <div class="section-title">Course</div>

          <label class="label">Course Code</label>
          <input v-model="form.course_code" class="input" placeholder="contoh: IF101" />

          <label class="label">Course Name</label>
          <input v-model="form.course_name" class="input" placeholder="Nama mata kuliah" />

          <label class="label">Credits</label>
          <input v-model="form.credits" class="input" type="number" min="1" max="6" placeholder="1-6" />
        </div>

        <div class="section">
          <div class="section-title">Enrollment</div>

          <label class="label">Academic Year</label>
          <input v-model="form.academic_year" class="input" placeholder="2025/2026" />

          <label class="label">Semester</label>
          <select v-model="form.semester" class="input">
            <option value="">-- pilih --</option>
            <option value="GANJIL">GANJIL</option>
            <option value="GENAP">GENAP</option>
          </select>

          <label class="label">Status</label>
          <select v-model="form.status" class="input">
            <option value="DRAFT">DRAFT</option>
            <option value="SUBMITTED">SUBMITTED</option>
            <option value="APPROVED">APPROVED</option>
            <option value="REJECTED">REJECTED</option>
          </select>
        </div>
      </div>

      <div class="footer">
        <button type="button" class="btn btn-ghost" @click="emit('close')">Cancel</button>
        <button type="button" class="btn btn-primary" :disabled="saving || !canSubmit" @click="onSubmit">
          <span v-if="!saving">Save Changes</span>
          <span v-else>Saving...</span>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* style kamu tetap */
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.35);
  backdrop-filter: blur(6px);
  display: grid;
  place-items: center;
  z-index: 50;
  padding: 16px;
}
.modal {
  width: min(980px, 100%);
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.92);
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 30px 80px rgba(15, 23, 42, 0.25);
  overflow: hidden;
}
.modal-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  padding: 16px 16px 10px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.25);
}
.title { font-weight: 900; font-size: 18px; letter-spacing: -0.01em; }
.sub { color: #64748b; font-size: 12px; margin-top: 2px; }
.x {
  border: 1px solid rgba(148, 163, 184, 0.5);
  background: rgba(255, 255, 255, 0.7);
  border-radius: 12px;
  padding: 8px 10px;
  cursor: pointer;
  font-weight: 900;
}
.grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 14px;
  padding: 14px 16px;
}
@media (max-width: 920px) { .grid { grid-template-columns: 1fr; } }
.section {
  border: 1px solid rgba(148, 163, 184, 0.22);
  border-radius: 14px;
  padding: 12px;
  background: rgba(248, 250, 252, 0.7);
}
.section-title { font-weight: 900; margin-bottom: 10px; }
.label { display: block; font-size: 12px; color: #64748b; margin: 10px 0 6px; }
.input {
  width: 100%;
  padding: 11px 12px;
  border-radius: 12px;
  border: 1px solid rgba(148, 163, 184, 0.55);
  background: rgba(255, 255, 255, 0.75);
  outline: none;
}
.footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 12px 16px 16px;
  border-top: 1px solid rgba(148, 163, 184, 0.25);
}
.btn {
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid rgba(148, 163, 184, 0.55);
  background: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  font-weight: 900;
}
.btn:disabled { opacity: 0.55; cursor: not-allowed; }
.btn-primary {
  border-color: rgba(99, 102, 241, 0.55);
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.95), rgba(79, 70, 229, 0.95));
  color: #fff;
}
.btn-ghost { background: transparent; border-color: transparent; color: #475569; }
</style>
