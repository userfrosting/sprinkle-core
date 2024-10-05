import { ref as t, computed as o, watchEffect as M, toValue as _ } from "vue";
import { a as j } from "./axios-CXDYiOMX.js";
const F = (u, f = {}, d = {}, m = 10, p = 0) => {
  const a = t(m), n = t(p), l = t(f), c = t(d), e = t({}), r = t(!1);
  async function v() {
    r.value = !0, j.get(_(u), {
      params: {
        size: a.value,
        page: n.value,
        sorts: l.value,
        filters: c.value
      }
    }).then((s) => {
      e.value = s.data, r.value = !1;
    }).catch((s) => {
      console.error(s);
    });
  }
  const g = o(() => Math.ceil(e.value.count_filtered / a.value) - 1), i = o(() => e.value.count), h = o(() => n.value * a.value + 1), w = o(() => Math.min((n.value + 1) * a.value, i.value)), x = o(() => e.value.count_filtered), y = o(() => e.value.rows);
  function z() {
    console.log("Not yet implemented");
  }
  return M(() => {
    v();
  }), {
    dataUrl: u,
    size: a,
    page: n,
    sorts: l,
    filters: c,
    data: e,
    fetch: v,
    loading: r,
    downloadCsv: z,
    totalPages: g,
    countFiltered: x,
    count: i,
    rows: y,
    first: h,
    last: w
  };
};
export {
  F as useSprunjer
};
