import { ref as a, computed as o, watchEffect as j, toValue as C } from "vue";
import { a as E } from "./axios-CXDYiOMX.js";
const O = (f, d = {}, m = {}, p = 10, g = 0) => {
  const c = a(f), n = a(p), s = a(g), l = a(d), v = a(m), e = a({}), r = a(!1);
  async function i() {
    r.value = !0, E.get(C(c), {
      params: {
        size: n.value,
        page: s.value,
        sorts: l.value,
        filters: v.value
      }
    }).then((t) => {
      e.value = t.data, r.value = !1;
    }).catch((t) => {
      console.error(t);
    });
  }
  const h = o(() => Math.max(Math.ceil((e.value.count_filtered ?? 0) / n.value) - 1, 0)), w = o(() => e.value.count ?? 0), M = o(() => Math.min(s.value * n.value + 1, e.value.count ?? 0)), x = o(() => Math.min((s.value + 1) * n.value, e.value.count ?? 0)), y = o(() => e.value.count_filtered ?? 0), z = o(() => e.value.rows ?? []);
  function S() {
    console.log("Not yet implemented");
  }
  function _(t) {
    let u;
    l.value[t] === "asc" ? u = "desc" : l.value[t] === "desc" ? u = null : u = "asc", l.value[t] = u;
  }
  return j(() => {
    i();
  }), {
    dataUrl: c,
    size: n,
    page: s,
    sorts: l,
    filters: v,
    data: e,
    fetch: i,
    loading: r,
    downloadCsv: S,
    totalPages: h,
    countFiltered: y,
    count: w,
    rows: z,
    first: M,
    last: x,
    toggleSort: _
  };
};
export {
  O as useSprunjer
};
