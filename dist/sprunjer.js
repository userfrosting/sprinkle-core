import { ref as a, computed as o, watchEffect as S, toValue as j } from "vue";
import { a as C } from "./axios-CXDYiOMX.js";
const N = (c, f = {}, d = {}, m = 10, p = 0) => {
  const n = a(m), s = a(p), l = a(f), i = a(d), e = a({}), r = a(!1);
  async function v() {
    r.value = !0, C.get(j(c), {
      params: {
        size: n.value,
        page: s.value,
        sorts: l.value,
        filters: i.value
      }
    }).then((t) => {
      e.value = t.data, r.value = !1;
    }).catch((t) => {
      console.error(t);
    });
  }
  const g = o(() => Math.max(Math.ceil((e.value.count_filtered ?? 0) / n.value) - 1, 0)), h = o(() => e.value.count ?? 0), w = o(() => Math.min(s.value * n.value + 1, e.value.count ?? 0)), M = o(() => Math.min((s.value + 1) * n.value, e.value.count_filtered ?? 0)), x = o(() => e.value.count_filtered ?? 0), _ = o(() => e.value.rows ?? []);
  function y() {
    console.log("Not yet implemented");
  }
  function z(t) {
    let u;
    l.value[t] === "asc" ? u = "desc" : l.value[t] === "desc" ? u = null : u = "asc", l.value[t] = u;
  }
  return S(() => {
    v();
  }), {
    dataUrl: c,
    size: n,
    page: s,
    sorts: l,
    filters: i,
    data: e,
    fetch: v,
    loading: r,
    downloadCsv: y,
    totalPages: g,
    countFiltered: x,
    count: h,
    rows: _,
    first: w,
    last: M,
    toggleSort: z
  };
};
export {
  N as useSprunjer
};
