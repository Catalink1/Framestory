(function () {
  "use strict";

  function slugifyClient(text) {
    var map = { ă: "a", â: "a", î: "i", ș: "s", ş: "s", ț: "t", ţ: "t" };
    text = text.toLowerCase().replace(/[ăâîșşțţ]/g, function (c) { return map[c] || c; });
    text = text.replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
    return text;
  }

  var form = document.getElementById("articleForm");
  if (!form) return;

  var titluEl = document.getElementById("titlu");
  var slugEl = document.getElementById("slug");
  var slugPreview = document.getElementById("slugPreview");
  var slugTouched = slugEl.value.trim() !== "";

  if (titluEl && slugEl) {
    slugEl.addEventListener("input", function () { slugTouched = true; });
    titluEl.addEventListener("input", function () {
      if (slugTouched) return;
      var s = slugifyClient(titluEl.value);
      slugEl.value = s;
      if (slugPreview) slugPreview.textContent = s;
    });
    slugEl.addEventListener("input", function () {
      if (slugPreview) slugPreview.textContent = slugEl.value;
    });
  }

  // categorie: toggle input pentru „altă categorie"
  var catSelect = document.getElementById("categorie");
  var catNew = document.getElementById("categorie_noua");
  function toggleCatNew() {
    if (!catSelect || !catNew) return;
    catNew.style.display = catSelect.value === "__new__" ? "block" : "none";
  }
  if (catSelect) {
    catSelect.addEventListener("change", toggleCatNew);
    toggleCatNew();
  }

  // rezumat: contor caractere
  var rezumatEl = document.getElementById("rezumat");
  var rezumatCount = document.getElementById("rezumatCount");
  function updateCount() {
    if (rezumatEl && rezumatCount) rezumatCount.textContent = rezumatEl.value.length;
  }
  if (rezumatEl) {
    rezumatEl.addEventListener("input", updateCount);
    updateCount();
  }

  // înainte de submit, dacă e o categorie nouă, o punem în câmpul "categorie_select"
  // (save.php citește categorie_select + categorie_noua, deci nu e nevoie de nimic aici)

  form.addEventListener("submit", function () {
    if (window.tinymce) tinymce.triggerSave();
  });

  // ── TinyMCE ──
  if (window.tinymce) {
    tinymce.init({
      selector: "#continut",
      height: 520,
      menubar: false,
      branding: false,
      plugins: "lists link blockquote code",
      toolbar:
        "undo redo | blocks | bold italic | bullist numlist | blockquote link | insertImage insertHighlight insertStats | code",
      block_formats: "Paragraf=p;Titlu H2=h2;Titlu H3=h3",
      extended_valid_elements: "div[class],picture,source[srcset|type],figure,figcaption",
      valid_children: "+div[picture|figure],+picture[source|img]",
      content_style:
        "body{font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.7;color:#252220;} " +
        "h2{font-size:1.3rem;margin:1.5rem 0 0.6rem} h3{font-size:1.1rem;margin:1.2rem 0 0.5rem} " +
        ".art-highlight{background:#f2f0eb;border-left:4px solid #9e7c50;border-radius:0 8px 8px 0;padding:1rem 1.4rem;margin:1rem 0} " +
        ".stat-cards{display:flex;gap:0.6rem;margin:1rem 0} .stat-card{background:#f2f0eb;border:1px solid #e5e0d6;border-radius:8px;padding:0.8rem;text-align:center;flex:1} " +
        ".art-inline-img img{max-width:100%;border-radius:8px}",
      setup: function (editor) {
        editor.ui.registry.addButton("insertImage", {
          text: "Imagine",
          tooltip: "Inserează o imagine în text",
          onAction: function () {
            var input = document.createElement("input");
            input.type = "file";
            input.accept = "image/jpeg,image/png";
            input.onchange = function () {
              var file = input.files[0];
              if (!file) return;
              var alt = window.prompt("Text alternativ pentru imagine (descriere scurtă, pentru SEO):", "");
              if (alt === null) return;

              var fd = new FormData();
              fd.append("file", file);
              fd.append("alt", alt);
              fd.append("base", (document.getElementById("slug").value || "imagine"));
              fd.append("csrf_token", document.querySelector('input[name="csrf_token"]').value);

              editor.setProgressState(true);
              fetch("upload-image.php", { method: "POST", body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                  editor.setProgressState(false);
                  if (res.error) {
                    alert("Eroare la încărcarea imaginii: " + res.error);
                    return;
                  }
                  editor.insertContent(res.html);
                })
                .catch(function () {
                  editor.setProgressState(false);
                  alert("Eroare de rețea la încărcarea imaginii.");
                });
            };
            input.click();
          },
        });

        editor.ui.registry.addButton("insertHighlight", {
          text: "Casetă evidențiată",
          tooltip: "Inserează o casetă evidențiată",
          onAction: function () {
            editor.insertContent(
              '<div class="art-highlight"><p><strong>Titlu:</strong> scrie aici textul evidențiat.</p></div>'
            );
          },
        });

        editor.ui.registry.addButton("insertStats", {
          text: "3 carduri statistici",
          tooltip: "Inserează 3 carduri cu cifre",
          onAction: function () {
            editor.insertContent(
              '<div class="stat-cards">' +
                '<div class="stat-card"><div class="sc-num">00%</div><div class="sc-label">etichetă</div><div class="sc-src">sursă</div></div>' +
                '<div class="stat-card"><div class="sc-num">00%</div><div class="sc-label">etichetă</div><div class="sc-src">sursă</div></div>' +
                '<div class="stat-card"><div class="sc-num">00%</div><div class="sc-label">etichetă</div><div class="sc-src">sursă</div></div>' +
              "</div>"
            );
          },
        });
      },
    });
  }
})();
