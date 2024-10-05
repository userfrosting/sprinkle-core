import { ref as n, computed as t, watchEffect as h, toValue as w } from "vue";
import { a as _ } from "./axios-CXDYiOMX.js";
const z = (s) => {
  const o = n(10), a = n(0), v = n(""), e = n({}), r = n(!1);
  async function c() {
    r.value = !0, _.get(
      w(s) + "?size=" + o.value + "&page=" + a.value + "&sorts%5Boccurred_at%5D=desc"
    ).then((u) => {
      e.value = u.data, r.value = !1;
    }).catch((u) => {
      console.error(u);
    });
  }
  const i = t(() => Math.ceil(e.value.count_filtered / o.value) - 1), l = t(() => e.value.count), f = t(() => a.value * o.value + 1), d = t(() => Math.min((a.value + 1) * o.value, l.value)), m = t(() => e.value.count_filtered), p = t(() => e.value.rows);
  function g() {
    console.log("Not yet implemented");
  }
  return h(() => {
    c();
  }), {
    dataUrl: s,
    size: o,
    page: a,
    sorts: v,
    data: e,
    fetch: c,
    loading: r,
    downloadCsv: g,
    totalPages: i,
    countFiltered: m,
    count: l,
    rows: p,
    first: f,
    last: d
  };
};
export {
  z as useSprunjer
};
