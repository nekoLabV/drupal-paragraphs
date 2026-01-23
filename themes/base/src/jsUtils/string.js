/**
 * 将驼峰命名转换为连字符命名（kebab-case）
 * @param {string} str - 需要转换的字符串
 * @returns {string} - 转换后的字符串
 * @example
 * kebabCase('blockAlign') // 'block-align'
 * kebabCase('marginTop') // 'margin-top'
 */
export const kebabCase = str => {
  return str.replace(/([a-z0-9])([A-Z])/g, '$1-$2').toLowerCase()
}

/**
 * 将连字符命名转换为驼峰命名
 * @param {string} str - 需要转换的字符串
 * @returns {string} - 转换后的字符串
 * @example
 * camelCase('block-align') // 'blockAlign'
 * camelCase('margin_top') // 'marginTop'
 */
export const camelCase = str => {
  return str
    .replace(/[_-](\w)/g, (_, letter) => letter.toUpperCase())
    .replace(/^\w/, c => c.toLowerCase())
}
