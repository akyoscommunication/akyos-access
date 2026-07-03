document.addEventListener('DOMContentLoaded', () => {
  const toc = document.querySelector('.js-toc');
  if (!toc) {
    return;
  }

  toc.querySelectorAll('.js-toc-link').forEach((link) => {
    link.addEventListener('click', (event) => {
      const href = link.getAttribute('href');
      if (!href?.startsWith('#')) {
        return;
      }

      const target = document.getElementById(href.slice(1));
      if (!target) {
        return;
      }

      event.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
});
