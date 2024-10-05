import { defineStore as o } from "pinia";
import { a as f } from "./axios-CXDYiOMX.js";
const d = (n) => {
  const t = typeof n;
  return n !== null && (t === "object" || t === "function");
}, s = /* @__PURE__ */ new Set([
  "__proto__",
  "prototype",
  "constructor"
]), c = new Set("0123456789");
function h(n) {
  const t = [];
  let r = "", e = "start", i = !1;
  for (const a of n)
    switch (a) {
      case "\\": {
        if (e === "index")
          throw new Error("Invalid character in an index");
        if (e === "indexEnd")
          throw new Error("Invalid character after an index");
        i && (r += a), e = "property", i = !i;
        break;
      }
      case ".": {
        if (e === "index")
          throw new Error("Invalid character in an index");
        if (e === "indexEnd") {
          e = "property";
          break;
        }
        if (i) {
          i = !1, r += a;
          break;
        }
        if (s.has(r))
          return [];
        t.push(r), r = "", e = "property";
        break;
      }
      case "[": {
        if (e === "index")
          throw new Error("Invalid character in an index");
        if (e === "indexEnd") {
          e = "index";
          break;
        }
        if (i) {
          i = !1, r += a;
          break;
        }
        if (e === "property") {
          if (s.has(r))
            return [];
          t.push(r), r = "";
        }
        e = "index";
        break;
      }
      case "]": {
        if (e === "index") {
          t.push(Number.parseInt(r, 10)), r = "", e = "indexEnd";
          break;
        }
        if (e === "indexEnd")
          throw new Error("Invalid character after an index");
      }
      default: {
        if (e === "index" && !c.has(a))
          throw new Error("Invalid character in an index");
        if (e === "indexEnd")
          throw new Error("Invalid character after an index");
        e === "start" && (e = "property"), i && (i = !1, r += "\\"), r += a;
      }
    }
  switch (i && (r += "\\"), e) {
    case "property": {
      if (s.has(r))
        return [];
      t.push(r);
      break;
    }
    case "index":
      throw new Error("Index was not closed");
    case "start": {
      t.push("");
      break;
    }
  }
  return t;
}
function u(n, t) {
  if (typeof t != "number" && Array.isArray(n)) {
    const r = Number.parseInt(t, 10);
    return Number.isInteger(r) && n[r] === n[t];
  }
  return !1;
}
function p(n, t, r) {
  if (!d(n) || typeof t != "string")
    return r === void 0 ? n : r;
  const e = h(t);
  if (e.length === 0)
    return r;
  for (let i = 0; i < e.length; i++) {
    const a = e[i];
    if (u(n, a) ? n = i === e.length - 1 ? void 0 : null : n = n[a], n == null) {
      if (i !== e.length - 1)
        return r;
      break;
    }
  }
  return n === void 0 ? r : n;
}
const g = o("config", {
  persist: !0,
  state: () => ({
    config: {}
  }),
  getters: {
    get: (n) => (t, r) => p(n.config, t, r)
  },
  actions: {
    async load() {
      f.get("/api/config").then((n) => {
        this.config = n.data;
      });
    }
  }
});
export {
  g as useConfigStore
};
