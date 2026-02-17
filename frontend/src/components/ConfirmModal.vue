<script setup>
const props = defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: "Confirm" },
  message: { type: String, default: "" },
  confirmText: { type: String, default: "Confirm" },
  cancelText: { type: String, default: "Cancel" },
});

const emit = defineEmits(["close", "confirm"]);
</script>

<template>
  <transition name="fade">
    <div v-if="props.open" class="backdrop" @click.self="emit('close')">
      <div class="modal">
        <div class="modal-title">{{ props.title }}</div>
        <div class="modal-msg">{{ props.message }}</div>

        <div class="actions">
          <button class="btn" @click="emit('close')">{{ props.cancelText }}</button>
          <button class="btn btn-danger" @click="emit('confirm')">{{ props.confirmText }}</button>
        </div>
      </div>
    </div>
  </transition>
</template>

<style scoped>
.backdrop{
  position: fixed;
  inset: 0;
  background: rgba(2,6,23,.45);
  display: grid;
  place-items: center;
  padding: 18px;
  z-index: 50;
}
.modal{
  width: min(520px, 100%);
  background: rgba(255,255,255,.92);
  border: 1px solid rgba(148,163,184,.28);
  border-radius: 18px;
  padding: 16px;
  box-shadow: 0 30px 80px rgba(15,23,42,.35);
  backdrop-filter: blur(10px);
}
.modal-title{ font-size: 16px; font-weight: 900; margin-bottom: 8px; }
.modal-msg{ color:#334155; font-size: 13px; white-space: pre-wrap; }
.actions{ margin-top: 14px; display:flex; justify-content:flex-end; gap:10px; }

.btn{
  padding: 9px 12px;
  border-radius: 12px;
  border: 1px solid rgba(148,163,184,.55);
  background: rgba(255,255,255,.7);
  cursor: pointer;
  font-weight: 700;
  color: #0f172a;
}
.btn-danger{
  border-color: rgba(239,68,68,.4);
  background: linear-gradient(135deg, rgba(239,68,68,.95), rgba(220,38,38,.95));
  color:#fff;
}

.fade-enter-active, .fade-leave-active { transition: opacity .18s ease, transform .18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
