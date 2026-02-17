// router/index.js
import { createRouter, createWebHistory } from "vue-router";
import EnrollmentsPage from "@/pages/EnrollmentsPage.vue";

const routes = [
  { path: "/", redirect: "/enrollments" }, // ✅ biar root aman
  { path: "/enrollments", component: EnrollmentsPage },
];

export default createRouter({
  history: createWebHistory(),
  routes,
});
