/**
 * a11y.js — Panneau d'accessibilité.
 * Active/désactive les modes "dyslexie", "haut contraste", "réduction des animations".
 * Permet aussi d'ajuster la taille de police globale (A-, A, A+).
 * Les préférences sont persistées dans localStorage.
 */
(function () {
  'use strict';

  var KEY_MODE = 'a11y';
  var KEY_SIZE = 'a11y-size';
  var MODES = ['dyslexia', 'high-contrast', 'reduced-motion'];
  var SIZES = ['13px', '15px', '17px', '20px'];

  function getModes() {
    return (localStorage.getItem(KEY_MODE) || '').split(' ').filter(Boolean);
  }
  function setModes(modes) {
    var v = modes.join(' ').trim();
    if (v) localStorage.setItem(KEY_MODE, v);
    else localStorage.removeItem(KEY_MODE);
    document.documentElement.setAttribute('data-a11y', v);
  }
  function toggleMode(m) {
    var modes = getModes();
    var i = modes.indexOf(m);
    if (i >= 0) modes.splice(i, 1); else modes.push(m);
    setModes(modes);
    refresh();
  }
  function setSize(px) {
    if (px) { localStorage.setItem(KEY_SIZE, px); document.documentElement.style.fontSize = px; }
    else    { localStorage.removeItem(KEY_SIZE); document.documentElement.style.fontSize = ''; }
    refresh();
  }

  var panel = null;
  function buildPanel() {
    panel = document.createElement('div');
    panel.className = 'a11y-panel';
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', "Options d'accessibilité");
    panel.innerHTML =
      '<button class="a11y-close" aria-label="Fermer">&times;</button>' +
      '<h3>Accessibilité</h3>' +
      '<div class="a11y-row">Police dyslexie <button data-mode="dyslexia">Activer</button></div>' +
      '<div class="a11y-row">Haut contraste <button data-mode="high-contrast">Activer</button></div>' +
      '<div class="a11y-row">Réduire les animations <button data-mode="reduced-motion">Activer</button></div>' +
      '<div class="a11y-row">Taille du texte <span class="a11y-size-btns">' +
        '<button data-size="13px">A-</button>' +
        '<button data-size="15px">A</button>' +
        '<button data-size="17px">A+</button>' +
        '<button data-size="20px">A++</button>' +
      '</span></div>' +
      '<div class="a11y-row"><button data-reset>Réinitialiser</button></div>';
    document.body.appendChild(panel);
    panel.addEventListener('click', function (e) {
      var t = e.target;
      if (t.classList.contains('a11y-close')) { panel.classList.remove('open'); return; }
      if (t.dataset.mode) { toggleMode(t.dataset.mode); }
      else if (t.dataset.size) { setSize(t.dataset.size); }
      else if (t.hasAttribute('data-reset')) { setModes([]); setSize(''); }
    });
  }

  function refresh() {
    if (!panel) return;
    var modes = getModes();
    panel.querySelectorAll('[data-mode]').forEach(function (b) {
      var on = modes.indexOf(b.dataset.mode) >= 0;
      b.classList.toggle('active', on);
      b.textContent = on ? 'Activé' : 'Activer';
    });
    var size = localStorage.getItem(KEY_SIZE) || '';
    panel.querySelectorAll('[data-size]').forEach(function (b) {
      b.classList.toggle('active', b.dataset.size === size);
    });
  }

  function boot() {
    var skip = document.createElement('a');
    skip.href = '#main'; skip.className = 'skip-link';
    skip.textContent = 'Aller au contenu principal';
    document.body.insertBefore(skip, document.body.firstChild);

    buildPanel();
    refresh();
    var btn = document.getElementById('a11y-btn');
    if (btn) {
      btn.addEventListener('click', function () {
        panel.classList.toggle('open');
        if (panel.classList.contains('open')) refresh();
      });
    }
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
