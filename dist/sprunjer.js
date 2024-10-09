import { ref as a, computed as o, watchEffect as _, toValue as j } from "vue";
import { a as C } from "./axios-CXDYiOMX.js";
const N = (c, f = {}, d = {}, m = 10, p = 0) => {
  const n = a(m), u = a(p), s = a(f), v = a(d), e = a({}), r = a(!1);
  async function i() {
    r.value = !0, C.get(j(c), {
      params: {
        size: n.value,
        page: u.value,
        sorts: s.value,
        filters: v.value
      }
    }).then((t) => {
      e.value = t.data, r.value = !1;
    }).catch((t) => {
      console.error(t);
    });
  }
  const g = o(() => Math.max(Math.ceil((e.value.count_filtered ?? 0) / n.value) - 1, 0)), h = o(() => e.value.count ?? 0), w = o(() => Math.min(u.value * n.value + 1, e.value.count ?? 0)), M = o(() => Math.min((u.value + 1) * n.value, e.value.count ?? 0)), x = o(() => e.value.count_filtered ?? 0), y = o(() => e.value.rows ?? []);
  function z() {
    console.log("Not yet implemented");
  }
  function S(t) {
    let l;
    s.value[t] === "asc" ? l = "desc" : s.value[t] === "desc" ? l = null : l = "asc", s.value[t] = l;
  }
  return _(() => {
    i();
  }), {
    dataUrl: c,
    size: n,
    page: u,
    sorts: s,
    filters: v,
    data: e,
    fetch: i,
    loading: r,
    downloadCsv: z,
    totalPages: g,
    countFiltered: x,
    count: h,
    rows: y,
    first: w,
    last: M,
    toggleSort: S
  };
};
export {
  N as useSprunjer
};
