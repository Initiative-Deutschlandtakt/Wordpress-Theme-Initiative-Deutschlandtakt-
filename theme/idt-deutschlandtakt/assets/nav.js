/* Hauptmenü — Hamburger, aufklappende Punkte, Menütafel.
 *
 * Deckt beide Menüformen ab (Customizer → Website-Identität → Form des
 * Hauptmenüs, s. idt_nav_style() in functions.php):
 *
 *   'band'    — Menüband. Auf dem Desktop stehen die Punkte offen, unter
 *               900 px klappt der Hamburger sie als Leiste unter dem Kopf auf.
 *   'overlay' — Aufklappbares Menü. Die Punkte liegen auf jeder Breite hinter
 *               der Schaltfläche „Menü": auf dem Desktop fächern sie waagerecht
 *               auf, auf dem Telefon fährt eine Tafel von rechts ein.
 *
 * Geöffnet wird in beiden Fällen über dieselben zwei Klassen — `.is-open` am
 * Menü und `idt-nav-open` am <html> —, das Aussehen macht style.css (5, 5c).
 * Nur was die Tafel zur Ebene über der Seite macht (Abdunkler, gesperrtes
 * Scrollen, gefangener Tastaturfokus), gilt allein für 'overlay' auf schmalen
 * Viewports.
 *
 * In eine IIFE gekapselt, damit nichts ins globale Scope leakt.
 */
(function () {
  var CFG = window.idtNav || {};
  var I18N = CFG.i18n || {};
  var OVERLAY = 'overlay' === CFG.style;
  /* Muss zur Media Query in style.css passen: darunter wird aus dem
     waagerechten Ausfächern die Tafel von rechts. */
  var PANEL_MAX = 900;
  var ANIM = 360; /* = --dur-slow; danach darf der Abdunkler aus dem Baum. */

  function ready(fn) {
    if (document.readyState !== 'loading') { fn(); }
    else { document.addEventListener('DOMContentLoaded', fn); }
  }

  ready(function () {
    var btn = document.querySelector('.nav-toggle');
    var nav = document.getElementById('main-nav');
    if (!btn || !nav) { return; }

    var root = document.documentElement;
    var backdrop = document.querySelector('.nav-backdrop');
    var closeBtn = nav.querySelector('.main-nav__close');
    var hideTimer = null;

    /* Tafel-Modus = aufklappbares Menü auf schmalem Viewport. Nur dort ist das
       Menü eine Ebene über der Seite; auf dem Desktop fächert es im Kopf auf
       und darf Seite und Tastatur nicht sperren. */
    function isPanel() {
      return OVERLAY && window.innerWidth <= PANEL_MAX;
    }

    function isOpen() {
      return nav.classList.contains('is-open');
    }

    /* Alle Elemente der Tafel, die Tastaturfokus annehmen können. */
    function focusables() {
      var list = nav.querySelectorAll('a[href], button:not([disabled])');
      return Array.prototype.filter.call(list, function (el) {
        return el.offsetWidth > 0 || el.offsetHeight > 0;
      });
    }

    function setOpen(open) {
      if (open === isOpen()) { return; }

      /* Beim Öffnen zuerst den Abdunkler in den Fluss holen und einmal messen
         lassen: Ein Element, das im selben Frame aus display:none kommt und
         die Zielklasse bekommt, springt ohne Überblendung an. Das erzwungene
         Neuberechnen gibt der Transition einen Startwert. */
      if (backdrop && open) {
        window.clearTimeout(hideTimer);
        backdrop.hidden = false;
        void backdrop.offsetWidth;
      }

      nav.classList.toggle('is-open', open);
      root.classList.toggle('idt-nav-open', open);
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (I18N.open && I18N.close) {
        btn.setAttribute('aria-label', open ? I18N.close : I18N.open);
      }

      if (backdrop && !open) {
        /* Erst nach dem Ausblenden wieder aus Fluss, Tab-Reihenfolge und
           Screenreader-Baum nehmen — sonst springt der Abdunkler weg, bevor
           er verblasst ist. */
        window.clearTimeout(hideTimer);
        hideTimer = window.setTimeout(function () { backdrop.hidden = true; }, ANIM);
      }

      /* Die Seite hinter der Tafel nicht mitscrollen lassen. */
      root.classList.toggle('idt-nav-lock', open && isPanel());

      if (open && isPanel()) {
        var first = focusables()[0];
        if (first) { first.focus(); }
      } else if (!open) {
        /* Nach dem Schließen zurück auf die Schaltfläche — sonst steht der
           Fokus im Nichts. Nur, wenn er vorher in der Tafel lag. */
        if (nav.contains(document.activeElement)) { btn.focus(); }
      }
    }

    btn.addEventListener('click', function () {
      setOpen(!isOpen());
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', function () { setOpen(false); });
    }

    if (backdrop) {
      backdrop.addEventListener('click', function () { setOpen(false); });
    }

    /* Nach dem Klick auf einen Menüpunkt wieder schließen. Ausgenommen sind
       Elternpunkte ohne eigenes Ziel („#"), die nur ein Untermenü aufklappen. */
    nav.addEventListener('click', function (e) {
      var link = e.target.closest ? e.target.closest('a') : null;
      if (!link) { return; }
      var href = link.getAttribute('href');
      if (!href || '#' === href) { return; }
      setOpen(false);
    });

    document.addEventListener('keydown', function (e) {
      if ('Escape' === e.key || 'Esc' === e.key) {
        if (isOpen()) { setOpen(false); }
        return;
      }

      /* Tastaturfokus in der Tafel halten: Sie deckt die Seite ab, alles
         dahinter ist unerreichbar — der Fokus soll dort also nicht hinein
         wandern, sondern am Ende wieder an den Anfang springen. */
      if ('Tab' !== e.key || !isOpen() || !isPanel()) { return; }
      var items = focusables();
      if (!items.length) { return; }
      var first = items[0];
      var last = items[items.length - 1];

      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      } else if (!nav.contains(document.activeElement)) {
        e.preventDefault();
        first.focus();
      }
    });

    /* Beim Breitenwechsel aufräumen: Was als Tafel offen war, passt nach dem
       Wechsel auf Desktop-Breite nicht mehr — und umgekehrt bliebe die
       Scroll-Sperre des Telefons auf dem Desktop hängen. */
    window.addEventListener('resize', function () {
      if (!isOpen()) { return; }
      if (!OVERLAY && window.innerWidth > PANEL_MAX) { setOpen(false); return; }
      root.classList.toggle('idt-nav-lock', isPanel());
    });
  });
})();
