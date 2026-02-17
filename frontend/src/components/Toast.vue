<script setup>
const props = defineProps({
  show: { type: Boolean, default: false },
  type: { type: String, default: "success" }, // success|error|info
  title: { type: String, default: "" },
  message: { type: String, default: "" },
});
</script>

<template>
  <transition name="fade">
    <div v-if="props.show" class="toast" :class="`toast-${props.type}`">
      <div class="toast-title">{{ props.title }}</div>
      <div class="toast-msg">{{ props.message }}</div>
    </div>
  </transition>
</template>

<style scoped>
.toast {
  position: fixed;
  top: 18px;
  right: 18px;
  width: min(520px, calc(100% - 36px));
  padding: 12px;
  border-radius: 16px;
  border: 1px solid rgba(148,163,184,.35);
  background: rgba(255,255,255,.92);
  box-shadow: 0 18px 60px rgba(15,23,42,.22);
  z-index: 60;
  backdrop-filter: blur(10px);
}
.toast-title { font-weight: 900; margin-bottom: 4px; }
.toast-msg { font-size: 12px; white-space: pre-wrap; color: #334155; }
.toast-success { border-color: rgba(16,185,129,.35); }
.toast-error { border-color: rgba(239,68,68,.35); }
.toast-info { border-color: rgba(99,102,241,.35); }

.fade-enter-active, .fade-leave-active { transition: opacity .18s ease, transform .18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
