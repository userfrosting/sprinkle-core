import { ref as t, computed as o, watchEffect as z, toValue as _ } from "vue";
import { a as j } from "./axios-CXDYiOMX.js";
const F = (s, i = {}, f = {}, m = 10, d = 0) => {
  const a = t(m), n = t(d), l = t(i), c = t(f), e = t({}), u = t(!1);
  async function v() {
    u.value = !0, j.get(_(s), {
      params: {
        size: a.value,
        page: n.value,
        sorts: l.value,
        filters: c.value
      }
    }).then((r) => {
      e.value = r.data, u.value = !1;
    }).catch((r) => {
      console.error(r);
    });
  }
  const p = o(() => Math.ceil((e.value.count_filtered ?? 0) / a.value) - 1), h = o(() => e.value.count ?? 0), g = o(() => Math.min(n.value * a.value + 1, e.value.count ?? 0)), w = o(() => Math.min((n.value + 1) * a.value, e.value.count ?? 0)), M = o(() => e.value.count_filtered ?? 0), x = o(() => e.value.rows ?? []);
  function y() {
    console.log("Not yet implemented");
  }
  return z(() => {
    v();
  }), {
    dataUrl: s,
    size: a,
    page: n,
    sorts: l,
    filters: c,
    data: e,
    fetch: v,
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
