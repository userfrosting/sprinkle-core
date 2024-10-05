import { ref as t, computed as o, watchEffect as w, toValue as x } from "vue";
import { a as y } from "./axios-CXDYiOMX.js";
const _ = (u) => {
  const a = t(10), n = t(0), l = t({}), c = t({}), e = t({}), r = t(!1);
  async function v() {
    r.value = !0, y.get(x(u), {
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
  const f = o(() => Math.ceil(e.value.count_filtered / a.value) - 1), i = o(() => e.value.count), d = o(() => n.value * a.value + 1), m = o(() => Math.min((n.value + 1) * a.value, i.value)), p = o(() => e.value.count_filtered), g = o(() => e.value.rows);
  function h() {
    console.log("Not yet implemented");
  }
  return w(() => {
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
    downloadCsv: h,
    totalPages: f,
    countFiltered: p,
    count: i,
    rows: g,
    first: d,
    last: m
  };
};
export {
  _ as useSprunjer
};
