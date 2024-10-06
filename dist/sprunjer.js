import { ref as t, computed as o, watchEffect as M, toValue as _ } from "vue";
import { a as j } from "./axios-CXDYiOMX.js";
const F = (s, i = {}, f = {}, d = 10, m = 0) => {
  const a = t(d), n = t(m), l = t(i), c = t(f), e = t({}), u = t(!1);
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
  const p = o(() => Math.ceil((e.value.count_filtered ?? 0) / a.value) - 1), g = o(() => e.value.count ?? 0), h = o(() => n.value * a.value + 1), w = o(() => Math.min((n.value + 1) * a.value, e.value.count ?? 0)), x = o(() => e.value.count_filtered ?? 0), y = o(() => e.value.rows ?? []);
  function z() {
    console.log("Not yet implemented");
  }
  return M(() => {
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
    downloadCsv: z,
    totalPages: p,
    countFiltered: x,
    count: g,
    rows: y,
    first: h,
    last: w
  };
};
export {
  F as useSprunjer
};
