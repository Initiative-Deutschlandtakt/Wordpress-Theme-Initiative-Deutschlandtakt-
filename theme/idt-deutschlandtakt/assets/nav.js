/* Hauptmenü — Hamburger, aufklappende Punkte, Menütafel.
 *
 * Deckt beide Menüformen ab (Customizer → Website-Identität → Form des
 * Hauptmenüs, s. idt_nav_style() in functions.php):
 *
 *   'band'    — Menüband. Auf dem Desktop stehen die Punkte offen, unter
 *               900 px klappt der Hamburger sie als Leiste unter dem Kopf auf.
 *   'overlay' — Aufklappbares Menü. Die Punkte liegen auf jeder Breite hinter
 *               drei Strichen: auf dem Desktop fächern sie waagerecht auf, auf
 *               dem Telefon nimmt eine Tafel den ganzen Bildschirm. Die Suche
 *               steht dort als letzter Punkt im Menü statt als Lupe im Kopf.
 *
 * Geöffnet wird in beiden Fällen über dieselben zwei Klassen — `.is-open` am
 * Menü und `idt-nav-open` am <html> —, das Aussehen macht style.css (5, 5c).
 * Neben dem Klick auf die drei Striche öffnet und schließt die Taste **M** das
 * Menü, solange der Fokus nicht in einem Eingabefeld steht.
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
     waagerechten Ausfächern die bildschirmfüllende Tafel. */
  var PANEL_MAX = 900;
  /* Tastaturkürzel fürs Auf- und Zuklappen. Ein nackter Buchstabe ohne
     Zusatztaste — deshalb weiter unten die Prüfung, ob gerade jemand tippt. */
  var KEY = 'm';

  function ready(fn) {
    if (document.readyState !== 'loading') { fn(); }
    else { document.addEventListener('DOMContentLoaded', fn); }
  }

  ready(function () {
    var btn = document.querySelector('.nav-toggle');
    var nav = document.getElementById('main-nav');
    if (!btn || !nav) { return; }

    var root = document.documentElement;
    /* Auf dem Telefon deckt die Tafel den Kopf nicht mit ab — Logo und Kreuz
       bleiben darüber stehen. Der Tastaturfokus darf deshalb im ganzen
       Menüband kreisen, nicht nur in der Punkteliste. */
    var header = btn.closest('.site-header') || nav;

    /* Tafel-Modus = aufklappbares Menü auf schmalem Viewport. Nur dort ist das
       Menü eine Ebene über der Seite; auf dem Desktop fächert es im Kopf auf
       und darf Seite und Tastatur nicht sperren. */
    function isPanel() {
      return OVERLAY && window.innerWidth <= PANEL_MAX;
    }

    function isOpen() {
      return nav.classList.contains('is-open');
    }

    /* Alle Elemente auf der Tafel-Ebene, die Tastaturfokus annehmen können. */
    function focusables() {
      var list = header.querySelectorAll('a[href], button:not([disabled])');
      return Array.prototype.filter.call(list, function (el) {
        return el.offsetWidth > 0 || el.offsetHeight > 0;
      });
    }

    function setOpen(open) {
      if (open === isOpen()) { return; }

      nav.classList.toggle('is-open', open);
      root.classList.toggle('idt-nav-open', open);
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (I18N.open && I18N.close) {
        btn.setAttribute('aria-label', open ? I18N.close : I18N.open);
      }

      /* Die Seite hinter der Tafel nicht mitscrollen lassen. */
      root.classList.toggle('idt-nav-lock', open && isPanel());

      if (open && isPanel()) {
        /* Auf den ersten Menüpunkt, nicht auf das erste fokussierbare Element
           des Menübands — das wäre das Logo, und dort zu landen sagt nichts
           darüber, dass sich gerade ein Menü geöffnet hat. */
        var first = nav.querySelector('a[href], button:not([disabled])');
        if (first) { first.focus(); }
      } else if (!open) {
        /* Nach dem Schließen zurück auf die Schaltfläche — sonst steht der
           Fokus im Nichts. Nur, wenn er vorher in der Punkteliste lag. */
        if (nav.contains(document.activeElement)) { btn.focus(); }
      }
    }

    btn.addEventListener('click', function () {
      setOpen(!isOpen());
    });

    /* --- Tastaturkürzel M ---------------------------------------------
     *
     * Die Striche sind auf dem Desktop beim Menüband gar nicht da und beim
     * aufklappbaren Menü klein und weit rechts — wer viel mit der Tastatur
     * arbeitet, kommt mit einem Buchstaben schneller hin. Ein nackter
     * Buchstabe kollidiert allerdings mit allem, was Text entgegennimmt;
     * die drei Prüfungen unten halten ihn davon fern. */

    /* Nimmt das Element gerade Text entgegen? Dann gehört das M dort hin. */
    function isTyping(el) {
      if (!el) { return false; }
      if (el.isContentEditable) { return true; }
      var tag = (el.tagName || '').toLowerCase();
      return 'input' === tag || 'textarea' === tag || 'select' === tag;
    }

    /* Die Striche sind der Anker des Kürzels: Wo sie nicht zu sehen sind —
       Menüband auf dem Desktop, Seitenvorlage ohne Menüband —, stehen die
       Punkte entweder ohnehin offen oder es gibt kein Menü zum Aufklappen. */
    function toggleVisible() {
      return btn.offsetWidth > 0 || btn.offsetHeight > 0;
    }

    document.addEventListener('keydown', function (e) {
      if (e.key !== KEY && e.key !== KEY.toUpperCase()) { return; }
      /* Zusatztasten gehören Browser und Betriebssystem (Strg+M, Cmd+M …).
         Umschalt bleibt erlaubt, sonst fiele ein großes M unter den Tisch. */
      if (e.ctrlKey || e.metaKey || e.altKey || e.isComposing) { return; }
      if (isTyping(e.target) || isTyping(document.activeElement)) { return; }
      /* Über der Seite liegt die Suche — dort hat das Menü nichts zu suchen,
         auch wenn der Fokus gerade nicht im Suchfeld steht (assets/search.js
         setzt die Klasse, solange das Overlay offen ist). */
      if (root.classList.contains('idt-searchbox-open')) { return; }
      if (!toggleVisible()) { return; }

      e.preventDefault();
      setOpen(!isOpen());
    });

    /* Erst jetzt ankündigen, nicht schon im Markup (header.php): Ohne
       JavaScript gibt es das Kürzel nicht, und ein Versprechen, das die Taste
       nicht einlöst, ist schlimmer als keins. */
    btn.setAttribute('aria-keyshortcuts', KEY.toUpperCase());
    if (I18N.key) { btn.setAttribute('title', I18N.key); }

    /* Hier hing bis v2.3.2 ein Scroll-Beobachter, der ab 40 px die Klasse
       `.idt-nav-scrolled` setzte. Daran hingen zuletzt nur noch die
       Papierflächen unter Marke und Strichen und das Ausblenden der Marke
       auf dem Telefon — alles drei ist weg (style.css 5c): Der Kopf sieht
       beim aufklappbaren Menü gescrollt aus wie am Seitenanfang. Ohne
       Abnehmer ist auch der Beobachter entfallen, mitsamt seinem
       Scroll-Listener. */

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
      } else if (!header.contains(document.activeElement)) {
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
