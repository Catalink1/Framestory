# Articole — draft-uri, gata de import în admin

Fiecare articol are propriul folder, cu nume descriptiv (slug-like), nu numerotat — `articole/numele-articolului/`. În folder: `articol.md` (formatul din [`admin/TEMPLATE-articol.md`](../admin/TEMPLATE-articol.md)) + pozele lui, dacă are.

Pentru publicare: deschizi `admin/import-md.php` din panoul de admin, alegi „Alege un folder întreg" și selectezi direct folderul articolului — `.md`-ul și pozele se încarcă împreună.

Aceste foldere sunt drafturi de lucru, nu fac parte din site-ul public livrat (nu sunt legate din `index.html`/`blog.html`).
