export const setElementAttr = (el, key, val) => {
  if (!el) {
    throw new Error(`setElementAttr: el is required.`)
  }
  if (!key) {
    throw new Error(`setElementAttr: key is required.`)
  }
  el[key] = val
}