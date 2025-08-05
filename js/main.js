// Ajoute ceci dans ton main.js ou dans un <script> en bas de page
document.querySelectorAll('a[data-confirm]').forEach(link => {
    link.addEventListener('click', function(e) {
        if (!confirm(this.getAttribute('data-confirm'))) {
            e.preventDefault();
        }
    });
});
const nextMatch = new Date('2025-10-19');
const el = document.getElementById('days');
function updateCountdown() {
  const now = new Date();
  const diff = nextMatch - now;
  const days = Math.floor(diff / (1000*60*60*24));
  el.textContent = days > 0 ? days : 0;
}
setInterval(updateCountdown, 1000*60*60);
updateCountdown();