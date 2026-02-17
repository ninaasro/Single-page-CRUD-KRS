import axios from "axios";

const baseURL =
  import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000";

export const api = axios.create({
  baseURL,
  headers: {
    Accept: "application/json",
  },
});

/**
 * helper: build query params aman
 * - filters: array/object -> JSON string
 * - sorts: array/object -> JSON string
 */
const normalizeQueryParams = (params = {}) => {
  const next = {
    ...params
  };

  // backend kamu decode dari JSON string
  if (next.filters && typeof next.filters !== "string") {
    next.filters = JSON.stringify(next.filters);
  }

  // advanced order: kirim JSON string juga
  if (next.sorts && typeof next.sorts !== "string") {
    next.sorts = JSON.stringify(next.sorts);
  }

  // bersihkan undefined/null biar query gak kotor
  Object.keys(next).forEach((k) => {
    if (next[k] === undefined || next[k] === null || next[k] === "") {
      delete next[k];
    }
  });

  return next;
};

// LIST
export const fetchEnrollments = async (params) => {
  const res = await api.get("/api/enrollments", {
    params: normalizeQueryParams(params),
  });
  return res.data;
};

// CREATE
export const createEnrollment = async (payload) => {
  const res = await api.post("/api/enrollments", payload);
  return res.data;
};

// PATCH STATUS
export const updateEnrollmentStatus = async (id, status) => {
  const res = await api.patch(`/api/enrollments/${id}`, {
    status
  });
  return res.data;
};

// UPDATE FULL
export const updateEnrollment = async (id, payload) => {
  const res = await api.put(`/api/enrollments/${id}`, payload);
  return res.data;
};

// DELETE
export const deleteEnrollment = async (id) => {
  const res = await api.delete(`/api/enrollments/${id}`);
  return res.data;
};
