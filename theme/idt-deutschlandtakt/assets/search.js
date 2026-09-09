/* Such-Overlay: Die Kopfleiste zeigt nur eine Lupe — ein Klick öffnet das
   Suchfeld als Ebene über der ganzen Seite (Markup: idt_render_search_overlay()
   in inc/search.php) und zeigt die Treffer schon beim Tippen. Die Vorschau
   holt fertiges Markup vom REST-Endpunkt idt/v1/suche, damit sie exakt so
   aussieht wie die vollständige Ergebnisseite (search.php).

   In eine IIFE gekapselt, damit nichts ins globale Scope leakt. */
(function () {
  var CFG = window.idtSearch || {};
  var I18N = CFG.i18n || {};
  var MIN = CFG.minChars || 2;
  var DEBOUNCE = 250;
  var ANIM = 260; /* muss zur CSS-Transition von .idt-searchbox passen */

  function ready(fn) {
    if (document.readyState !== 'loading') { fn(); }
    else { document.addEventListener('DOMContentLoaded', fn); }
  }

  ready(function () {
    var box = document.getElementById('idt-searchbox');
    var toggles = document.querySelectorAll('.idt-searchtoggle');
    if (!box || !toggles.length) { return; }

    var sheet = box.querySelector('.idt-searchbox__sheet');
    var field = box.querySelector('.idt-searchfield__input');
    var results = box.querySelector('.idt-searchbox__results');
    var closeBtn = box.querySelector('.idt-searchbox__close');
    var form = box.querySelector('.idt-searchfield');
    var lastFocus = null;
    var hideTimer = null;
    var debounceTimer = null;
    var controller = null;
    var seq = 0;

    /* --- Öffnen / Schließen ------------------------------------------- */
    function isOpen() { return box.classList.contains('is-open'); }

    function open() {
      if (isOpen()) { return; }
      lastFocus = document.activeElement;
      window.clearTimeout(hideTimer);
      box.hidden = false;
      /* Erst im nächsten Frame die Klasse setzen, damit die Einblend-
         Transition auch dann läuft, wenn das Element gerade erst aus
         [hidden] kommt. */
      window.requestAnimationFrame(function () {
        box.classList.add('is-open');
      });
      document.documentElement.classList.add('idt-searchbox-open');
      Array.prototype.forEach.call(toggles, function (t) {
        t.setAttribute('aria-expanded', 'true');
      });
      if (field) { field.focus(); field.select(); }
    }

    function close() {
      if (!isOpen()) { return; }
      box.classList.remove('is-open');
      document.documentElement.classList.remove('idt-searchbox-open');
      Array.prototype.forEach.call(toggles, function (t) {
        t.setAttribute('aria-expanded', 'false');
      });
      /* Nach der Ausblend-Transition wieder aus Tab-Reihenfolge und
         Screenreader-Baum nehmen. */
      hideTimer = window.setTimeout(function () { box.hidden = true; }, ANIM);
      if (lastFocus && typeof lastFocus.focus === 'function') { lastFocus.focus(); }
    }

    Array.prototype.forEach.call(toggles, function (t) {
      t.addEventListener('click', function (e) {
        /* Die Lupe ist ein Link auf die Ergebnisseite (Fallback ohne
           JavaScript) — mit JavaScript öffnet sie stattdessen das Overlay. */
        e.preventDefault();
        if (isOpen()) { close(); } else { open(); }
      });
    });
    if (closeBtn) { closeBtn.addEventListener('click', close); }

    /* Direkteinstieg über die Adresse …/#idt-searchbox (der Fallback-Anker der
       Lupe): Fragment entfernen, damit die :target-Regel nicht mit dem
       JavaScript-Zustand konkurriert, und das Overlay regulär öffnen. */
    if (window.location.hash === '#idt-searchbox') {
      if (window.history && window.history.replaceState) {
        window.history.replaceState(null, '', window.location.pathname + window.location.search);
      }
      open();
    }

    /* Klick auf die Fläche neben dem Suchblatt schließt ebenfalls. */
    box.addEventListener('mousedown', function (e) {
      if (e.target === box) { close(); }
    });

    document.addEventListener('keydown', function (e) {
      if (!isOpen()) { return; }
      if (e.key === 'Escape') { close(); return; }
      if (e.key === 'Tab') { trapFocus(e); }
    });

    /* Fokus bleibt im Overlay, solange es offen ist. */
    function trapFocus(e) {
      var focusables = sheet.querySelectorAll('a[href], button:not([disabled]), input, [tabindex]:not([tabindex="-1"])');
      if (!focusables.length) { return; }
      var first = focusables[0];
      var last = focusables[focusables.length - 1];
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault(); last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault(); first.focus();
      }
    }

    /* --- Live-Vorschau der Treffer ------------------------------------ */
    if (!field || !results || !CFG.endpoint) { return; }

    function setStatus(text, cls) {
      results.innerHTML = '<p class="idt-search-status' + (cls ? ' ' + cls : '') + '"></p>';
      results.firstChild.textContent = text;
    }

    function render(data) {
      var html = data.html || '';
      if (data.total > (data.shown || 0)) {
        var label = (I18N.more || 'Alle %s Treffer anzeigen').replace('%s', data.total);
        var link = document.createElement('a');
        link.className = 'pill pill--violet idt-searchbox__all';
        link.href = data.more;
        link.textContent = label;
        results.innerHTML = html;
        results.appendChild(link);
        return;
      }
      results.innerHTML = html;
    }

    function query(q) {
      var mine = ++seq;
      if (controller) { controller.abort(); }
      controller = ('AbortController' in window) ? new AbortController() : null;

      results.setAttribute('aria-busy', 'true');
      fetch(CFG.endpoint + '?q=' + encodeURIComponent(q), {
        credentials: 'same-origin',
        signal: controller ? controller.signal : undefined
      })
        .then(function (r) {
          if (!r.ok) { throw new Error('HTTP ' + r.status); }
          return r.json();
        })
        .then(function (data) {
          if (mine !== seq) { return; } /* veraltete Antwort verwerfen */
          results.setAttribute('aria-busy', 'false');
          render(data);
        })
        .catch(function (err) {
          if (err && err.name === 'AbortError') { return; }
          if (mine !== seq) { return; }
          results.setAttribute('aria-busy', 'false');
          setStatus(I18N.error || 'Vorschau nicht erreichbar.', 'is-error');
        });
    }

    field.addEventListener('input', function () {
      var q = field.value.trim();
      window.clearTimeout(debounceTimer);

      if (q.length < MIN) {
        seq++; /* laufende Antworten verwerfen */
        if (controller) { controller.abort(); controller = null; }
        results.innerHTML = '';
        results.setAttribute('aria-busy', 'false');
        return;
      }
      setStatus(I18N.loading || 'Suche läuft …');
      debounceTimer = window.setTimeout(function () { query(q); }, DEBOUNCE);
    });

    /* Leeres Feld nicht abschicken — sonst landet man auf einer leeren
       Ergebnisseite. */
    if (form) {
      form.addEventListener('submit', function (e) {
        if (!field.value.trim()) { e.preventDefault(); field.focus(); }
      });
    }
  });
})();
