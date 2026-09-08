<?php
/**
 * admin/inc/article-form.php — formular creare/editare articol.
 * Așteaptă variabilele: $article (assoc array), $errors (array de string-uri),
 * $isEdit (bool), $categories (array).
 */
if (!defined('ADMIN_ACCESS')) {
    http_response_code(403);
    exit('Acces direct interzis.');
}

$article = $article ?? [];
$errors = $errors ?? [];
$isEdit = $isEdit ?? false;
$categories = $categories ?? [];

$v = function ($key, $default = '') use ($article) {
    return h($article[$key] ?? $default);
};
?>
<?php if ($errors): ?>
  <div class="flash flash-error">
    <ul>
      <?php foreach ($errors as $err): ?><li><?= h($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" action="save.php" enctype="multipart/form-data" id="articleForm" class="article-form">
  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
  <input type="hidden" name="slug_original" value="<?= isset($article['slug_original']) ? h($article['slug_original']) : $v('slug') ?>" />
  <input type="hidden" name="imagine_existing" value="<?= $v('imagine') ?>" />

  <div class="field">
    <label for="titlu">Titlu</label>
    <input type="text" id="titlu" name="titlu" value="<?= $v('titlu') ?>" required autofocus />
  </div>

  <div class="field">
    <label for="slug">Slug (adresa articolului)</label>
    <input type="text" id="slug" name="slug" value="<?= $v('slug') ?>" placeholder="se generează automat din titlu" />
    <p class="hint">catalincocos.ro/articol.html?slug=<span id="slugPreview"><?= $v('slug') ?></span> — schimbă-l doar dacă știi ce faci, schimbă adresa articolului.</p>
  </div>

  <div class="field-row">
    <div class="field">
      <label for="data">Data</label>
      <input type="date" id="data" name="data" value="<?= $v('data', date('Y-m-d')) ?>" required />
    </div>

    <div class="field">
      <label for="tip">Tip</label>
      <select id="tip" name="tip">
        <option value="articol" <?= ($article['tip'] ?? 'articol') === 'articol' ? 'selected' : '' ?>>Articol</option>
        <option value="pilon" <?= ($article['tip'] ?? '') === 'pilon' ? 'selected' : '' ?>>Articol pilon</option>
      </select>
    </div>
  </div>

  <?php $catIsNew = !empty($article['categorie']) && !in_array($article['categorie'], $categories, true); ?>
  <div class="field">
    <label for="categorie">Categorie</label>
    <select id="categorie" name="categorie_select">
      <?php foreach ($categories as $cat): ?>
        <option value="<?= h($cat) ?>" <?= ($article['categorie'] ?? '') === $cat ? 'selected' : '' ?>><?= h($cat) ?></option>
      <?php endforeach; ?>
      <option value="__new__" <?= $catIsNew ? 'selected' : '' ?>>+ altă categorie…</option>
    </select>
    <input type="text" id="categorie_noua" name="categorie_noua" placeholder="Numele noii categorii" value="<?= $catIsNew ? $v('categorie') : '' ?>" style="<?= $catIsNew ? '' : 'display:none;' ?>margin-top:0.5rem" />
  </div>

  <div class="field">
    <label for="rezumat">Rezumat <span class="hint-inline">(apare pe card-ul din blog și ca meta description — ideal 140-160 caractere)</span></label>
    <textarea id="rezumat" name="rezumat" rows="3" required><?= $v('rezumat') ?></textarea>
    <p class="hint"><span id="rezumatCount">0</span> caractere</p>
  </div>

  <fieldset class="field-group">
    <legend>SEO avansat <span class="hint-inline">(opțional — dacă lipsesc, se folosesc titlul și rezumatul de mai sus)</span></legend>

    <div class="field">
      <label for="seo_title">Titlu SEO <span class="hint-inline">(ce apare în Google — dacă e gol, se folosește titlul articolului)</span></label>
      <input type="text" id="seo_title" name="seo_title" value="<?= $v('seo_title') ?>" />
    </div>

    <div class="field">
      <label for="focus_keyphrase">Frază cheie principală</label>
      <input type="text" id="focus_keyphrase" name="focus_keyphrase" value="<?= $v('focus_keyphrase') ?>" />
    </div>

    <div class="field">
      <label for="keywords">Cuvinte cheie <span class="hint-inline">(separate prin virgulă)</span></label>
      <input type="text" id="keywords" name="keywords" value="<?= $v('keywords') ?>" />
    </div>
  </fieldset>

  <div class="field">
    <label for="imagine">Imagine principală (hero) <span class="hint-inline">— opțional, JPG sau PNG</span></label>
    <?php if (!empty($article['imagine'])): ?>
      <div class="current-image">
        <img src="../img/<?= $v('imagine') ?>" alt="" />
        <span>Imagine curentă: <?= $v('imagine') ?> — încarcă alta ca să o înlocuiești</span>
      </div>
    <?php endif; ?>
    <input type="file" id="imagine" name="imagine" accept="image/jpeg,image/png" />
  </div>

  <div class="field">
    <label for="imagine_alt">Text alternativ (alt) pentru imaginea principală <span class="hint-inline">— dacă lipsește, se folosește titlul articolului</span></label>
    <input type="text" id="imagine_alt" name="imagine_alt" value="<?= $v('imagine_alt') ?>" placeholder="Descriere scurtă a imaginii, pentru accesibilitate și SEO" />
  </div>

  <div class="field">
    <label for="continut">Conținut</label>
    <textarea id="continut" name="continut"><?= h($article['continut'] ?? '') ?></textarea>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvează modificările' : 'Publică articolul' ?></button>
    <a href="dashboard.php" class="btn btn-ghost">Anulează</a>
  </div>
</form>
