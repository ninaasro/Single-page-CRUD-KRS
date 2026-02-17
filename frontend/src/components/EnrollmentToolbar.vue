<script setup>
const props = defineProps({
  search: { type: String, default: "" },
  status: { type: String, default: "" },
  semester: { type: String, default: "" },
  pageSize: { type: Number, default: 10 },
});

const emit = defineEmits([
  "update:search",
  "update:status",
  "update:semester",
  "update:pageSize",
]);

const set = (key, val) => emit(`update:${key}`, val);
</script>

<template>
  <section class="card">
    <div class="grid">
      <div class="field search">
        <h1 class="label">Search</h1>
        <input
          class="input"
          :value="search"
          @input="set('search', $event.target.value)"
          placeholder="Search NIM / Name / Course Code"
        />
        <div class="hint">Debounce: 400ms</div>
      </div>

      <div class="field">
        <label class="label">Status</label>
        <select class="input" :value="status" @change="set('status', $event.target.value)">
          <option value="">All</option>
          <option value="DRAFT">DRAFT</option>
          <option value="SUBMITTED">SUBMITTED</option>
          <option value="APPROVED">APPROVED</option>
          <option value="REJECTED">REJECTED</option>
        </select>
      </div>

      <div class="field">
        <label class="label">Semester</label>
        <select class="input" :value="semester" @change="set('semester', $event.target.value)">
          <option value="">All</option>
          <option value="GANJIL">GANJIL</option>
          <option value="GENAP">GENAP</option>
        </select>
      </div>

      <div class="field">
        <label class="label">Page Size</label>
        <select
          class="input"
          :value="pageSize"
          @change="set('pageSize', Number($event.target.value))"
        >
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
      </div>
    </div>
  </section>
</template>

<style scoped>
.card{
  background: rgba(255,255,255,.70);
  border: 1px solid rgba(15,23,42,.06);
  backdrop-filter: blur(12px);
  border-radius: 18px;
  padding: 16px;
  box-shadow: 0 16px 40px rgba(15,23,42,.06);
}

.grid{
  display:grid;
  grid-template-columns: 2fr 1fr 1fr 0.9fr; /* Search lebih lebar */
  gap: 12px 14px;
  align-items: start;
}

@media (max-width: 980px){
  .grid{ grid-template-columns: 1fr 1fr; }
  .search{ grid-column: 1 / -1; }
}
@media (max-width: 640px){
  .grid{ grid-template-columns: 1fr; }
}

.field{ min-width:0; }
.label{
  display:block;
  margin: 0 0 8px;
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
  background: rgba(255,255,255,.78);
  outline:none;
}

.input:focus{
  border-color: rgba(99,102,241,.95);
  box-shadow: 0 0 0 4px rgba(99,102,241,.14);
}

.hint{
  margin-top: 6px;
  font-size: 12px;
  color: #64748b;
}
</style>
