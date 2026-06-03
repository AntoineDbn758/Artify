/*
 * main.js - amelioration progressive du site Artify.
 * Le site reste fonctionnel sans JS : ce fichier ajoute juste du confort
 * (toggle menu mobile, preview image, accordeon FAQ, etc.).
 *
 * Charge en defer dans includes/header.php pour ne pas bloquer le rendu.
 */
(function () {
  'use strict';

  // Petit helper qui evite les NullPointer en mode "select-or-skip"
  function $(sel, root) { return (root || document).querySelector(sel); }
  function $$(sel, root) { return Array.from((root || document).querySelectorAll(sel)); }

  // === Accordeon FAQ ======================================================
  // Sur faq.php, chaque question peut s'ouvrir / se fermer au clic.
  // Necessite que le markup soit <details><summary>...</summary>...</details>
  // ou des classes .faq-item / .faq-q / .faq-a. On gere les deux.
  function setupFaqAccordion() {
    $$('.faq-item').forEach(function (item) {
      var q = item.querySelector('.faq-q');
      var a = item.querySelector('.faq-a');
      if (!q || !a) return;
      a.style.maxHeight = '0';
      a.style.overflow = 'hidden';
      a.style.transition = 'max-height 0.25s ease';
      q.style.cursor = 'pointer';
      q.addEventListener('click', function () {
        var isOpen = item.classList.toggle('is-open');
        a.style.maxHeight = isOpen ? a.scrollHeight + 'px' : '0';
      });
    });
  }

  // === Preview image lors d'un upload (artisan) ===========================
  // Sur produit_new.php / produit_edit.php, quand l'artisan choisit un
  // fichier, on lui montre tout de suite un apercu sans recharger la page.
  function setupImagePreview() {
    $$('input[type="file"][accept*="image"]').forEach(function (input) {
      var preview = document.createElement('img');
      preview.style.maxWidth = '200px';
      preview.style.maxHeight = '200px';
      preview.style.marginTop = '8px';
      preview.style.borderRadius = '8px';
      preview.style.display = 'none';
      input.parentNode.appendChild(preview);
      input.addEventListener('change', function () {
        var f = input.files && input.files[0];
        if (!f) { preview.style.display = 'none'; return; }
        preview.src = URL.createObjectURL(f);
        preview.style.display = 'block';
      });
    });
  }

  // === Quantite +/- sur la fiche produit ==================================
  // Au lieu de taper la quantite, l'utilisateur clique sur des boutons.
  // Plus rapide sur mobile.
  function setupQtyButtons() {
    $$('input[type="number"][name="quantite"]').forEach(function (input) {
      var wrap = document.createElement('span');
      wrap.style.display = 'inline-flex';
      wrap.style.gap = '4px';
      wrap.style.alignItems = 'center';
      var minus = document.createElement('button');
      var plus = document.createElement('button');
      minus.type = 'button'; plus.type = 'button';
      minus.textContent = '-'; plus.textContent = '+';
      [minus, plus].forEach(function (b) {
        b.className = 'qty-btn';
        b.style.width = '28px'; b.style.height = '28px';
        b.style.border = '1.5px solid var(--border, #ddd)';
        b.style.background = 'var(--surface, #fff)';
        b.style.borderRadius = '6px';
        b.style.cursor = 'pointer';
      });
      input.parentNode.insertBefore(wrap, input);
      wrap.appendChild(minus); wrap.appendChild(input); wrap.appendChild(plus);
      minus.addEventListener('click', function () {
        input.value = Math.max(parseInt(input.min || 1, 10), (parseInt(input.value, 10) || 1) - 1);
      });
      plus.addEventListener('click', function () {
        var max = parseInt(input.max || 999, 10);
        input.value = Math.min(max, (parseInt(input.value, 10) || 1) + 1);
      });
    });
  }

  // === Show / hide password ===============================================
  function setupPasswordToggle() {
    $$('input[type="password"]').forEach(function (input) {
      var wrap = document.createElement('div');
      wrap.style.position = 'relative';
      input.parentNode.insertBefore(wrap, input);
      wrap.appendChild(input);
      input.style.paddingRight = '62px';

      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'pwd-toggle';
      btn.textContent = 'Afficher';
      btn.style.cssText = 'position:absolute;right:8px;top:50%;transform:translateY(-50%);font-size:12px;background:transparent;border:none;color:var(--ocre,#C4855A);cursor:pointer;padding:0;line-height:1';
      wrap.appendChild(btn);

      btn.addEventListener('click', function () {
        if (input.type === 'password') {
          input.type = 'text'; btn.textContent = 'Masquer';
        } else {
          input.type = 'password'; btn.textContent = 'Afficher';
        }
      });
    });
  }

  // === Confirmation avant suppression (renforce le confirm() natif) =======
  // Pour les boutons "Supprimer", on garde le confirm() PHP mais on rend
  // visuellement plus clair que c'est destructif (red shake).
  function setupDeleteFeedback() {
    $$('button.btn-danger, .btn-delete').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        if (btn.dataset.confirmed === '1') return;
        // Le confirm() inline d'origine se charge de la vraie confirmation.
        // Ici on flash juste un effet pour signaler que l'action est lourde.
        btn.animate(
          [{ transform: 'translateX(0)' },
           { transform: 'translateX(-4px)' },
           { transform: 'translateX(4px)' },
           { transform: 'translateX(0)' }],
          { duration: 150, iterations: 1 }
        );
      });
    });
  }

  // === Lancement au DOMContentLoaded =====================================
  // On evite DOMContentLoaded si le script est charge en defer apres la
  // creation du body : on regarde document.readyState pour eviter de
  // rater le bon moment.
  // === Password strength meter ============================================
  function setupPasswordStrength() {
    $$('form[data-pwd-strength]').forEach(function (form) {
      var input = form.querySelector('#pwd') || form.querySelector('input[name="password"]');
      var bar   = form.querySelector('.pwd-bar');
      var rules = form.querySelector('.pwd-rules');
      if (!input || !bar) return;
      function evaluate() {
        var v = input.value || '';
        var checks = {
          len: v.length >= 8, upper: /[A-Z]/.test(v), lower: /[a-z]/.test(v),
          digit: /[0-9]/.test(v), special: /[^A-Za-z0-9]/.test(v),
        };
        var score = Object.values(checks).filter(Boolean).length;
        bar.className = 'pwd-bar s' + score;
        if (rules) rules.querySelectorAll('li').forEach(function (li) {
          li.classList.toggle('ok', !!checks[li.dataset.rule]);
        });
      }
      input.addEventListener('input', evaluate); evaluate();
    });
  }

  function boot() {
    setupFaqAccordion();
    setupImagePreview();
    setupQtyButtons();
    setupPasswordToggle();
    setupDeleteFeedback();
    setupPasswordStrength();
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
