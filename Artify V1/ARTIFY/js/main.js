function showPage(id) {
  const pageMap = {
    home: 'index.html',
    catalogue: 'catalogue.html',
    artisans: 'artisans.html',
    events: 'events.html',
    gallery: 'gallery.html',
    register: 'register.html',
    login: 'login.html',
    forgot: 'forgot.html',
    profile: 'profile.html',
    'edit-profile': 'edit-profile.html',
    faq: 'faq.html',
    legal: 'legal.html',
    contact: 'contact.html',
    backoffice: 'backoffice.html'
  };
  const target = pageMap[id];
  if (target) { window.location.href = target; }
}

function selectTab(el) {
  el.closest('.profile-tabs').querySelectorAll('.ptab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
}

function toggleFaq(el) {
  el.closest('.faq-item').classList.toggle('open');
}

document.querySelectorAll('.cat-chip').forEach(chip => {
  chip.addEventListener('click', function() {
    this.closest('.categories').querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
    this.classList.add('active');
  });
});

document.querySelectorAll('.sidebar-item').forEach(item => {
  item.addEventListener('click', function() {
    document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
    this.classList.add('active');
  });
});
