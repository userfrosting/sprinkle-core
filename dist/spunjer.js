import { ref as a, computed as t, watchEffect as h, toValue as w } from "vue";
import { a as _ } from "./axios-CXDYiOMX.js";
const z = (c) => {
  const o = a(10), n = a(0), v = a("[occurred_at]=desc"), e = a({}), r = a(!1);
  async function s() {
    r.value = !0, _.get(
      w(c) + "?size=" + o.value + "&page=" + n.value + "&sorts%5Boccurred_at%5D=desc"
    ).then((u) => {
      e.value = u.data, r.value = !1;
    }).catch((u) => {
      console.error(u);
    });
  }
  const i = t(() => Math.ceil(e.value.count_filtered / o.value) - 1), l = t(() => e.value.count), d = t(() => n.value * o.value + 1), f = t(() => Math.min((n.value + 1) * o.value, l.value)), m = t(() => e.value.count_filtered), p = t(() => e.value.rows);
  function g() {
    console.log("Not yet implemented");
  }
  return h(() => {
    s();
  }), {
    dataUrl: c,
    size: o,
    page: n,
    sorts: v,
    data: e,
    fetch: s,
    loading: r,
    downloadCsv: g,
    totalPages: i,
    countFiltered: m,
    count: l,
    rows: p,
    first: d,
    last: f
  };
};
export {
  z as useSprunjer
};
