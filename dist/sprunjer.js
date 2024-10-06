import { ref as e, computed as o, watchEffect as z, toValue as _ } from "vue";
import { a as j } from "./axios-CXDYiOMX.js";
const F = (s, v = {}, f = {}, m = 10, d = 0) => {
  const a = e(m), n = e(d), l = e(v), c = e(f), t = e({}), u = e(!1);
  async function i() {
    u.value = !0, j.get(_(s), {
      params: {
        size: a.value,
        page: n.value,
        sorts: l.value,
        filters: c.value
      }
    }).then((r) => {
      t.value = r.data, u.value = !1;
    }).catch((r) => {
      console.error(r);
    });
  }
  const p = o(() => Math.min(Math.ceil((t.value.count_filtered ?? 0) / a.value) - 1, 1)), h = o(() => t.value.count ?? 0), g = o(() => Math.min(n.value * a.value + 1, t.value.count ?? 0)), w = o(() => Math.min((n.value + 1) * a.value, t.value.count ?? 0)), M = o(() => t.value.count_filtered ?? 0), x = o(() => t.value.rows ?? []);
  function y() {
    console.log("Not yet implemented");
  }
  return z(() => {
    i();
  }), {
    dataUrl: s,
    size: a,
    page: n,
    sorts: l,
    filters: c,
    data: t,
    fetch: i,
    loading: u,
    downloadCsv: y,
    totalPages: p,
    countFiltered: M,
    count: h,
    rows: x,
    first: g,
    last: w
  };
};
export {
  F as useSprunjer
};
