---
title: Titlul articolului (devine H1 pe pagină) — scris pentru cititor, atrăgător, poate fi mai liber/creativ
seo_title: Titlul pentru Google (tab browser, rezultat căutare) — scris pentru motorul de căutare, cu fraza cheie cât mai aproape de început; de obicei diferit de title, nu identic
focus_keyphrase: fraza cheie principală
keywords: cuvânt1, cuvânt2, cuvânt3
excerpt: Rezumatul articolului, 140-160 caractere — apare pe cardul din blog ȘI ca meta description în Google
category: Numele categoriei (una nouă sau una existentă — Copywriting SEO, Strategie & Vizibilitate etc.)
date: 2026-01-01
type: articol
hero: nume-poza-principala.jpg
hero_alt: Descriere scurtă a imaginii principale, pentru accesibilitate și SEO — dacă lipsește, se folosește titlul articolului
---

Primul paragraf — răspunde COMPLET la subiect în primele ~200 de cuvinte (nu doar 2-3 propoziții) — atât motoarele clasice, cât și AI Overviews/ChatGPT/Perplexity evaluează mai ales începutul paginii ca să decidă dacă te citează.

## Primul subtitlu (H2)

Text normal, cu **bold** pentru accente, *italic* pentru nuanțe, și [linkuri către surse](https://exemplu.ro) sau [alte articole de pe blog](articol.html?slug=alt-articol-existent).

- Punct unu
- Punct doi
- Punct trei

![Descriere scurtă a imaginii, pentru accesibilitate](nume-poza-inline.jpg)

## Al doilea subtitlu (H2)

<div class="art-highlight"><p><strong>De reținut:</strong> o idee/regulă/citat important, scoasă în evidență.</p></div>

<div class="stat-cards">
  <div class="stat-card"><div class="sc-num">60%</div><div class="sc-label">etichetă scurtă</div><div class="sc-src">sursă</div></div>
  <div class="stat-card"><div class="sc-num">40%</div><div class="sc-label">etichetă scurtă</div><div class="sc-src">sursă</div></div>
  <div class="stat-card"><div class="sc-num">25%</div><div class="sc-label">etichetă scurtă</div><div class="sc-src">sursă</div></div>
</div>

## Ai dubii? Bine. Uite răspunsurile. (exemplu de titlu atractiv pentru secțiunea de întrebări — NU „Întrebări frecvente")

### O întrebare pe care ar pune-o cineva pe Google?

Răspuns direct și scurt, în 2-3 propoziții (bun pentru featured snippets / AEO).

Ultimul paragraf — încheiere + CTA spre [servicii](index.html#services), [portofoliu](index.html#portfolio) sau [contact](index.html#contact). (CTA-ul poate fi și altundeva în articol, vezi nota 3 de mai jos.)

<!--
  ACESTA E UN ȘABLON, NU UN ARTICOL REAL. Nu-l încărca așa cum e —
  înlocuiește tot textul de mai sus (titlu, paragrafe, subtitluri) cu
  articolul tău real, păstrând structura (blocul de metadate între ---,
  apoi textul), și abia apoi încarcă fișierul .md rezultat în
  admin/import-md.php — împreună cu pozele lui, dacă are, selectate
  deodată sau dintr-un folder întreg.

  Funcționează la fel indiferent cine scrie articolul sau cu ce cont de
  Claude (sau fără Claude deloc) — e doar text simplu.

  NOTE RAPIDE:

  1) Toate câmpurile din bloc (între cele două ---) sunt opționale în
     afară de "title" — dar completează-le pe toate, ca articolul să fie
     gata de publicat fără să mai completezi nimic manual în admin.

  2) Poze: dă-le nume clare (fără diacritice, fără spații — ex.
     "biroul-meu.jpg", nu "poza mea 2.jpg") și încarcă-le ODATĂ cu acest
     fișier .md. Cea numită la "hero:" devine imaginea principală, cu
     textul alternativ din "hero_alt:" (dacă lipsește, se folosește
     titlul articolului — dar mai bine scrie unul descriptiv). Orice
     ![descriere](nume.jpg) din text, dacă poza a fost încărcată, devine
     imagine inline automat — descrierea dintre paranteze pătrate E alt
     text-ul ei, deja funcțional, nu mai trebuie completat separat.

  3) SEO + AEO + GEO — reguli de conținut, nu doar de format (actualizate
     septembrie 2026, pe baza schimbărilor reale confirmate de Google):
     - title (H1) vs seo_title: NU trebuie să fie identice. H1 e pentru
       cititor — poate fi mai atractiv, mai liber, chiar provocator.
       seo_title e pentru motorul de căutare — cu fraza cheie cât mai
       aproape de început, clar despre ce e articolul, sub ~60 caractere
       ca să nu se taie în Google. Scrie-le pe fiecare cu scopul lui.
     - SEO: fraza cheie principală în titlu, primul paragraf și cel puțin
       un H2. Minim 2 linkuri către alte articole + 3 spre index.html
       (#services, #portfolio, #contact). Context și profunzime bat
       potrivirea mecanică de cuvinte cheie — Google evaluează acum mult
       mai mult autoritatea tematică și claritatea structurii.
     - AEO: mulți utilizatori pornesc azi de la un răspuns generat de AI
       (AI Overviews, ChatGPT, Perplexity), nu de la o listă de linkuri.
       O secțiune de întrebări (H3 = întrebarea, răspuns concis imediat
       după) NU e obligatorie la fiecare articol — dar când există,
       titlul ei (H2) trebuie să fie atractiv, niciodată literalmente
       „Întrebări frecvente" (vezi exemplul de mai sus). IMPORTANT: din
       7 mai 2026 Google a retras complet FAQ rich results din
       rezultatele căutării — casetele extensibile de întrebări nu mai
       apar în SERP, indiferent de schema markup. Deci nu mai scrii FAQ
       ca să „prinzi" spațiu suplimentar în Google — o scrii doar dacă
       chiar ajută cititorul să înțeleagă rapid ceva concret.
     - GEO: citează surse reale cu link extern, folosește cifre concrete
       (blocul stat-cards), scrie afirmativ ("X reduce Y cu Z%"), nu vag
       ("poate ajuta"). Conținutul original — date proprii, exemple
       reale, opinie argumentată — e citat de AI; parafrazarea genericului
       altcuiva, nu.
     - CTA: OBLIGATORIU în fiecare articol (spre servicii/portofoliu/
       contact). De obicei la final, dar nu neapărat — poate fi integrat
       editorial, atractiv și logic, oriunde în text unde are sens.

  4) Experiență reală + prospețime (regulă nouă, din actualizarea de
     core Google din martie 2026 — a amplificat E-E-A-T mai mult ca
     oricând):
     - Detalii concrete, verificabile, de primă mână bat conținutul
       „comprehensiv" dar impersonal. Exemple reale, cifre proprii,
       nume de proiecte/clienți (unde se poate), o opinie clar asumată —
       nu generalități de tipul „este important să..." care ar putea fi
       scrise despre orice subiect de oricine.
     - EVITĂ vocea „parafrazare AI" — propoziții corecte gramatical dar
       fără nimic specific dedesubt. Conținutul care sună generic a
       pierdut trafic semnificativ la actualizările din 2026; conținutul
       cu date/studii proprii a câștigat vizibilitate.
     - Articolele vechi merită revizuite din când în când (fapte, cifre,
       exemple aduse la zi) — paginile actualizate recent sunt citate
       disproporționat mai des de motoarele AI decât cele neatinse de ani.

  5) Lungime: minim ~900 cuvinte pentru un articol obișnuit, 3000-7000+
     pentru un articol "pilon" (type: pilon în loc de type: articol).
-->
