/*
 * main.js - amelioration progressive du site Artify.
 * Le site reste fonctionnel sans JS : ce fichier ajoute juste du confort
 * (toggle menu mobile, preview image, accordeon FAQ, etc.).
 *
 * Charge en defer dans includes/header.php pour ne pas bloquer le rendu.
 */
// IIFE : on isole tout le code dans une fonction pour ne pas polluer le scope global.
(function () {
  'use strict';

  // Petit helper qui evite les NullPointer en mode "select-or-skip"
  // $ = un seul element, $$ = tableau d'elements (Array.from pour avoir .forEach).
  function $(sel, root) { return (root || document).querySelector(sel); }
  function $$(sel, root) { return Array.from((root || document).querySelectorAll(sel)); }

  // === Menu mobile (hamburger) ===========================================
  // Sur petits ecrans, .nav-links est cache par defaut. On le toggle quand
  // l'utilisateur clique sur le bouton hamburger ajoute dynamiquement.
  function setupMobileMenu() {
    // On accepte deux selecteurs de nav pour rester compatible si le markup evolue.
    var nav = $('nav.site-nav') || $('nav');
    var links = $('.nav-links');
    // Sortie silencieuse si la page n'a pas de nav (pages d'erreur par exemple).
    if (!nav || !links) return;
    // Bouton burger cree en JS : evite d'avoir a le mettre dans chaque template PHP.
    var btn = document.createElement('button');
    btn.className = 'nav-burger';
    // aria-label pour accessibilite (lecteurs d'ecran).
    btn.setAttribute('aria-label', 'Ouvrir le menu');
    btn.innerHTML = '<span></span><span></span><span></span>';
    nav.appendChild(btn);
    // Toggle des classes : CSS s'occupe d'animer et d'afficher / masquer les liens.
    btn.addEventListener('click', function () {
      links.classList.toggle('is-open');
      btn.classList.toggle('is-active');
    });
  }

  // === Accordeon FAQ ======================================================
  // Sur faq.php, chaque question peut s'ouvrir / se fermer au clic.
  // Necessite que le markup soit <details><summary>...</summary>...</details>
  // ou des classes .faq-item / .faq-q / .faq-a. On gere les deux.
  function setupFaqAccordion() {
    $$('.faq-item').forEach(function (item) {
      var q = item.querySelector('.faq-q');
      var a = item.querySelector('.faq-a');
      // Pas de question ou pas de reponse : on saute cet item.
      if (!q || !a) return;
      // Etat initial ferme : maxHeight=0 + overflow=hidden.
      a.style.maxHeight = '0';
      a.style.overflow = 'hidden';
      // Transition CSS animee a l'ouverture/fermeture.
      a.style.transition = 'max-height 0.25s ease';
      q.style.cursor = 'pointer';
      q.addEventListener('click', function () {
        var isOpen = item.classList.toggle('is-open');
        // scrollHeight = hauteur reelle du contenu, sinon 0 pour fermer.
        a.style.maxHeight = isOpen ? a.scrollHeight + 'px' : '0';
      });
    });
  }

  // === Preview image lors d'un upload (artisan) ===========================
  // Sur produit_new.php / produit_edit.php, quand l'artisan choisit un
  // fichier, on lui montre tout de suite un apercu sans recharger la page.
  function setupImagePreview() {
    // Selecteur cible uniquement les inputs file qui acceptent une image.
    $$('input[type="file"][accept*="image"]').forEach(function (input) {
      // Element <img> cree pour montrer l'apercu, masque par defaut.
      var preview = document.createElement('img');
      preview.style.maxWidth = '200px';
      preview.style.maxHeight = '200px';
      preview.style.marginTop = '8px';
      preview.style.borderRadius = '8px';
      preview.style.display = 'none';
      input.parentNode.appendChild(preview);
      input.addEventListener('change', function () {
        var f = input.files && input.files[0];
        // Pas de fichier selectionne : on cache l'apercu (reset).
        if (!f) { preview.style.display = 'none'; return; }
        // URL.createObjectURL : reference locale au fichier, pas d'upload.
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
      // Wrapper inline-flex pour aligner moins / input / plus sur une ligne.
      var wrap = document.createElement('span');
      wrap.style.display = 'inline-flex';
      wrap.style.gap = '4px';
      wrap.style.alignItems = 'center';
      var minus = document.createElement('button');
      var plus = document.createElement('button');
      // type=button pour ne PAS submit le formulaire au clic.
      minus.type = 'button'; plus.type = 'button';
      minus.textContent = '-'; plus.textContent = '+';
      // Style commun appliquee en boucle pour eviter la duplication.
      [minus, plus].forEach(function (b) {
        b.className = 'qty-btn';
        b.style.width = '28px'; b.style.height = '28px';
        b.style.border = '1.5px solid var(--border, #ddd)';
        b.style.background = 'var(--surface, #fff)';
        b.style.borderRadius = '6px';
        b.style.cursor = 'pointer';
      });
      // On insere le wrap avant l'input puis on glisse l'input au milieu.
      input.parentNode.insertBefore(wrap, input);
      wrap.appendChild(minus); wrap.appendChild(input); wrap.appendChild(plus);
      // Math.max avec input.min : on respecte le min HTML defini cote serveur.
      minus.addEventListener('click', function () {
        input.value = Math.max(parseInt(input.min || 1, 10), (parseInt(input.value, 10) || 1) - 1);
      });
      // Plus borne au max (stock), 999 si pas defini.
      plus.addEventListener('click', function () {
        var max = parseInt(input.max || 999, 10);
        input.value = Math.min(max, (parseInt(input.value, 10) || 1) + 1);
      });
    });
  }

  // === Loading state sur le bouton Commander ==============================
  // Stripe peut prendre 1-2s pour creer la session. On evite que
  // l'utilisateur clique 3 fois en lui montrant que c'est parti.
  function setupSubmitLoading() {
    // Cible uniquement les formulaires qui pointent vers commande_new.
    $$('form[action*="commande_new"]').forEach(function (form) {
      form.addEventListener('submit', function () {
        var btn = form.querySelector('button[type="submit"]');
        // Garde anti double-clic : si deja en cours, on ne refait rien.
        if (!btn || btn.dataset.loading === '1') return;
        btn.dataset.loading = '1';
        // On garde le label original pour pouvoir restaurer si besoin.
        btn.dataset.originalText = btn.textContent;
        btn.textContent = 'Redirection vers le paiement...';
        // disabled = empeche un re-clic pendant l'appel Stripe.
        btn.disabled = true;
      });
    });
  }

  // === Show / hide password ===============================================
  // Petit bouton oeil a cote des champs mot de passe pour verifier ce qu'on
  // tape (surtout utile sur mobile ou les fautes de frappe sont frequentes).
  function setupPasswordToggle() {
    $$('input[type="password"]').forEach(function (input) {
      // Bouton "Afficher / Masquer" injecte juste apres chaque input password.
      var btn = document.createElement('button');
      // type=button : evite de submit le form au clic.
      btn.type = 'button';
      btn.className = 'pwd-toggle';
      btn.textContent = 'Afficher';
      btn.style.marginLeft = '6px';
      btn.style.fontSize = '12px';
      btn.style.background = 'transparent';
      btn.style.border = 'none';
      btn.style.color = 'var(--ocre, #C4855A)';
      btn.style.cursor = 'pointer';
      input.parentNode.insertBefore(btn, input.nextSibling);
      // Toggle du type d'input + libelle du bouton.
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
  // Orchestrateur : appelle chaque setup une seule fois au demarrage.
  function boot() {
    setupMobileMenu();
    setupFaqAccordion();
    setupImagePreview();
    setupQtyButtons();
    setupSubmitLoading();
    setupPasswordToggle();
    setupDeleteFeedback();
  }
  // Si le DOM n'est pas encore pret on attend l'event, sinon on lance directement.
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
