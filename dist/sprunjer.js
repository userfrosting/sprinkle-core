import { ref as a, computed as n, watchEffect as y, toValue as z } from "vue";
import { a as N } from "./axios-CXDYiOMX.js";
const _ = (c, v = {}, d = {}, g = 10, m = 0) => {
  const l = a(g), s = a(m), o = a(v), i = a(d);
  console.log("SPRUNJER DEBUG SORT", o, v);
  const e = a({}), r = a(!1);
  async function f() {
    r.value = !0, N.get(z(c), {
      params: {
        size: l.value,
        page: s.value,
        sorts: o.value,
        filters: i.value
      }
    }).then((t) => {
      e.value = t.data, r.value = !1;
    }).catch((t) => {
      console.error(t);
    });
  }
  const p = n(() => Math.max(Math.ceil((e.value.count_filtered ?? 0) / l.value) - 1, 0)), h = n(() => e.value.count ?? 0), w = n(() => Math.min(s.value * l.value + 1, e.value.count ?? 0)), M = n(() => Math.min((s.value + 1) * l.value, e.value.count ?? 0)), x = n(() => e.value.count_filtered ?? 0), E = n(() => e.value.rows ?? []);
  function R() {
    console.log("Not yet implemented");
  }
  function S(t) {
    let u;
    o.value[t] === "asc" ? u = "desc" : o.value[t] === "desc" ? u = null : u = "asc", o.value[t] = u;
  }
  return y(() => {
    f();
  }), {
    dataUrl: c,
    size: l,
    page: s,
    sorts: o,
    filters: i,
    data: e,
    fetch: f,
    loading: r,
    downloadCsv: R,
    totalPages: p,
    countFiltered: x,
    count: h,
    rows: E,
    first: w,
    last: M,
    toggleSort: S
  };
};
export {
  _ as useSprunjer
};
