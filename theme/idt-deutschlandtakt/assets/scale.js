/* Zusammenklappbares Hauptmenü (Hamburger-Toggle).
   In eine IIFE gekapselt, damit nichts ins globale Scope leakt. */
(function () {
  function ready(fn) {
    if (document.readyState !== 'loading') { fn(); }
    else { document.addEventListener('DOMContentLoaded', fn); }
  }
  ready(function () {
    var btn = document.querySelector('.nav-toggle');
    var nav = document.getElementById('main-nav');
    if (!btn || !nav) { return; }

    function setOpen(open) {
      nav.classList.toggle('is-open', open);
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    btn.addEventListener('click', function () {
      setOpen(btn.getAttribute('aria-expanded') !== 'true');
    });

    /* Auf Mobil nach Klick auf einen Link wieder schließen. */
    nav.addEventListener('click', function (e) {
      if (e.target.closest('a')) { setOpen(false); }
    });

    /* Mit Escape schließen. */
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { setOpen(false); }
    });

    /* Beim Wechsel auf Desktop-Breite zurücksetzen. */
    window.addEventListener('resize', function () {
      if (window.innerWidth > 900) { setOpen(false); }
    });
  });
})();

/* Horizont-Splash-Bühne (Baustein [splash]): skaliert die 1280×800-Bühne
   proportional, falls ein #idt-canvas vorhanden ist. */
(function () {
  function fit() {
    var c = document.getElementById('idt-canvas');
    if (!c) { return; }
    var stage = c.parentElement;
    /* Mobil (≤ 820px) übernimmt das CSS-Stapel-Layout — keine Skalierung.
       Inline-Maße aufräumen, damit die Media Query greift (auch nach
       einem Resize von Desktop- auf Mobilbreite). */
    if (window.innerWidth <= 820) {
      c.style.removeProperty('--s');
      c.style.width = '';
      stage.style.height = '';
      return;
    }
    var w = stage.clientWidth || window.innerWidth;
    var s = Math.min(w / 1280, window.innerHeight / 800);
    c.style.setProperty('--s', s);
    /* Randlose Bühne: Wenn die Höhe die Skalierung begrenzt (breite
       Viewports), die Canvas symmetrisch über 1280px hinaus verbreitern,
       bis sie skaliert die volle Stage-Breite deckt. Band und Balken
       (mittig positioniert, s. CSS) dehnen sich mit — kein „Karten"-Rand
       mehr links und rechts. */
    c.style.width = Math.max(1280, Math.ceil(w / s)) + 'px';
    /* Bühnenhöhe an die skalierte Canvas anpassen statt der starren
       vh-Vorgabe aus dem CSS — sonst bleibt auf schmalen Hochformat-
       Viewports (Handys) viel Leerraum ober-/unterhalb der Bühne stehen. */
    stage.style.height = Math.round(800 * s) + 'px';
  }
  window.addEventListener('resize', fit);
  window.addEventListener('load', fit);
  fit();
})();
