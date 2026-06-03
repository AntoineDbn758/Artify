<div class="crumb"><a href="forum.php">Forum</a> &rsaquo; Nouveau sujet</div>
<div class="form-card">
  <h1>Nouveau sujet</h1>
  <form method="post" action="forum.php?action=store">
    <div class="form-row"><label>Titre *</label>
      <input type="text" name="titre" required maxlength="200" placeholder="Une question, un retour, une annonce…"></div>
    <div class="form-row"><label>Catégorie</label>
      <select name="categorie">
        <option value="general">Général</option>
        <option value="artisanat">Artisanat</option>
        <option value="technique">Technique</option>
        <option value="annonces">Annonces</option>
        <option value="aide">Aide</option>
      </select>
    </div>
    <div class="form-row"><label>Message *</label>
      <textarea name="contenu" required rows="6"></textarea></div>
    <div class="form-actions">
      <button class="btn-primary" type="submit">Publier</button>
      <a class="btn-ghost" href="forum.php">Annuler</a>
    </div>
  </form>
</div>
