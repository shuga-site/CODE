document.addEventListener('DOMContentLoaded', () => {
  const anchors = document.querySelectorAll('a');
  anchors.forEach(a => {
    a.addEventListener('focus', () => a.classList.add('focus'));
    a.addEventListener('blur', () => a.classList.remove('focus'));
  });
});
