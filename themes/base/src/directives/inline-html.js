export const inlineHtml = {
  mounted(el, binding) {
    if (!binding.value) return
    
    // 创建临时容器
    const temp = document.createElement('div')
    temp.innerHTML = binding.value
    
    // 获取父元素
    const parent = el.parentNode
    
    // 将HTML内容插入到当前元素之前
    while (temp.firstChild) {
      parent.insertBefore(temp.firstChild, el)
    }
    
    // 移除原始元素
    parent.removeChild(el)
  }
}