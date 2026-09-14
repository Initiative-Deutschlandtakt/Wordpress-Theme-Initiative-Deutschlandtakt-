/* ===========================================================================
   Initiative Deutschlandtakt — Knotendreieck
   ---------------------------------------------------------------------------
   Interaktive Grafik zum Knotenprinzip: drei Knoten, drei Linien, Abfahrten
   zur Minute :02 und :32. Custom Element, Vanilla JS, Shadow DOM — kein
   Build-Schritt, keine Abhaengigkeiten.

   Die Datei ist die Sichtseite des Blocks idt/knotendreieck; das Markup
   erzeugt idt_render_knotendreieck() in inc/shortcodes.php, registriert wird
   der Block in inc/blocks.php. Sie laeuft im Frontend und im Editor
   (block.json "script" laedt beides), deshalb steht hier nichts, was den
   Editor stoeren wuerde: der Autostart haengt am Attribut autoplay.

   Einbindung:  <idt-knotendreieck></idt-knotendreieck>
   Attribute:   cycle-seconds="13.5"   Dauer eines Stundenzyklus (8–24)
                autoplay="false"       Autostart abschalten
                vehicle-style="Punkte" statt "Striche"
                grafik-titel, subzeile, knoten-oben, knoten-links,
                knoten-rechts                      Beschriftungen

   Schrift: geerbt aus dem Dokument. @font-face wirkt dokumentweit, auch im
   Shadow DOM — die Datei bringt keine Fonts mit, sie nennt nur dieselbe
   Familie wie --font-sans in style.css.

   Farben: die Markenfarben aus style.css Abschnitt 2 (--idt-ink, --idt-paper,
   --idt-violet, --idt-cyan). Sie stehen hier als Literale, weil dieselben
   Funktionen das Standbild standbild.svg erzeugen — eine per <img> geladene
   SVG-Datei sieht die Custom Properties des Dokuments nicht. Aendert sich eine
   Markenfarbe, aendern sich die fuenf Konstanten unten und das Standbild wird
   mit `./bin/knotendreieck-standbild.js` neu gebaut.

   Fahrplan: Halbstundentakt, Abfahrt :02 und :32 an jedem Knoten, in beide
   Richtungen. Kanten 28 / 28 / 57 Minuten. Knotenfenster :28–:32 und :58–:02.
   ========================================================================= */

(function () {
  'use strict';

  var INK    = '#00373C';   // --idt-ink
  var CREME  = '#FFF6F0';   // --idt-paper
  var VIOLET = '#6E50FA';   // --idt-violet
  var CYAN   = '#00DCFA';   // --idt-cyan
  // Die dritte Kante braucht eine eigene Farbe: Mischung aus Cyan und
  // Violett, damit sie zu beiden passt und sich von beiden unterscheidet.
  var BLUE   = '#3796FA';

  var EDGES = {
    HM: { pts: [[500, 310], [240, 700]], color: VIOLET },
    HK: { pts: [[500, 310], [760, 700]], color: CYAN },
    MK: { pts: [[240, 700], [420, 880], [580, 880], [760, 700]], color: BLUE }
  };

  // Ein Fahrzeug je Umlauf: es erscheint im Ankunftsmoment seines Vorgaengers
  // am selben Knoten, wechselt waehrend der Haltezeit die Seite und
  // verschwindet, wenn es ankommt — dort uebernimmt es der naechste Umlauf.
  var RUNS = [
    { e: 'HM', dir:  1, w0: 60, dep:  62, arr:  90, w1:  90 },
    { e: 'HM', dir:  1, w0: 30, dep:  32, arr:  60, w1:  60 },
    { e: 'HM', dir: -1, w0: 60, dep:  62, arr:  90, w1:  90 },
    { e: 'HM', dir: -1, w0: 30, dep:  32, arr:  60, w1:  60 },
    { e: 'HK', dir:  1, w0: 60, dep:  62, arr:  90, w1:  90 },
    { e: 'HK', dir:  1, w0: 30, dep:  32, arr:  60, w1:  60 },
    { e: 'HK', dir: -1, w0: 60, dep:  62, arr:  90, w1:  90 },
    { e: 'HK', dir: -1, w0: 30, dep:  32, arr:  60, w1:  60 },
    { e: 'MK', dir:  1, w0: 29, dep:  32, arr:  89, w1:  89 },
    { e: 'MK', dir:  1, w0: 59, dep:  62, arr: 119, w1: 119 },
    { e: 'MK', dir: -1, w0: 29, dep:  32, arr:  89, w1:  89 },
    { e: 'MK', dir: -1, w0: 59, dep:  62, arr: 119, w1: 119 }
  ];

  // Im Editor aenderbare Texte. Der Fahrplan gehoert bewusst nicht dazu:
  // 28/28/57 und die Knotenfenster haengen zusammen, eine frei geaenderte
  // Minutenzahl wuerde die Beschriftung von der Animation abkoppeln.
  var DEFAULTS = {
    title: 'Das Knotenprinzip',
    subline: 'Knoten :00 und :30',
    nodeTop: 'Hollerbrück',
    nodeLeft: 'Mardingen',
    nodeRight: 'Kirchsee'
  };

  function cfg(o) {
    var c = {}, k;
    for (k in DEFAULTS) c[k] = (o && typeof o[k] === 'string' && o[k].length) ? o[k] : DEFAULTS[k];
    return c;
  }

  // Lange Texte werden verkleinert statt abgeschnitten.
  function fit(size, text, budget) {
    var l = String(text).length;
    return l > budget ? Math.round(size * budget / l) : size;
  }

  function esc(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  var DWELL = [[28, 32], [58, 62]];   // Knotenfenster
  var PARK = 72;                      // Abstand des Halteplatzes vom Knoten
  var HOLD = 0.28;                    // Zeitlupenfaktor im Knotenfenster
  var SWITCH = 40;                    // Weg des Seitenwechsels hinter dem Knoten
  var FREEZE = 30;                    // Standbild: Minute :30

  /* ---- Geometrie ------------------------------------------------------- */

  function geom(e) {
    if (!e._g) {
      var segs = [], L = 0;
      for (var i = 0; i < e.pts.length - 1; i++) {
        var a = e.pts[i], b = e.pts[i + 1];
        var dx = b[0] - a[0], dy = b[1] - a[1], l = Math.sqrt(dx * dx + dy * dy);
        segs.push({ a: a, dx: dx, dy: dy, l: l, s0: L });
        L += l;
      }
      e._g = { segs: segs, L: L };
    }
    return e._g;
  }

  function pointAt(e, s) {
    var g = geom(e);
    s = Math.max(0, Math.min(g.L, s));
    var seg = g.segs[g.segs.length - 1];
    for (var i = 0; i < g.segs.length; i++) {
      if (s <= g.segs[i].s0 + g.segs[i].l) { seg = g.segs[i]; break; }
    }
    var u = (s - seg.s0) / seg.l;
    return {
      x: seg.a[0] + seg.dx * u,
      y: seg.a[1] + seg.dy * u,
      nx: -seg.dy / seg.l,
      ny: seg.dx / seg.l,
      ang: Math.atan2(seg.dy, seg.dx) * 180 / Math.PI
    };
  }

  /* ---- Takt ------------------------------------------------------------ */

  function dwell(t) {
    for (var i = 0; i < DWELL.length; i++) {
      for (var k = -1; k <= 1; k++) {
        var tt = t + 60 * k, w = DWELL[i];
        if (tt >= w[0] && tt <= w[1]) {
          return Math.sin(Math.PI * (tt - w[0]) / (w[1] - w[0]));
        }
      }
    }
    return 0;
  }

  function glow(t) {
    var i = dwell(t);
    return { r: +(27 + 32 * i).toFixed(1), op: Math.round(i * 55) / 100 };
  }

  // Waehrend der Haltezeit laeuft die Zeit langsamer, damit der Umstieg
  // lesbar wird; norm() haelt die Gesamtdauer des Zyklus konstant.
  function speed(t) {
    var s = 1;
    for (var i = 0; i < DWELL.length; i++) {
      for (var k = -1; k <= 1; k++) {
        var tt = t + 60 * k, w = DWELL[i];
        if (tt >= w[0] && tt <= w[1]) {
          var f = 1 - (1 - HOLD) * Math.sin(Math.PI * (tt - w[0]) / (w[1] - w[0]));
          if (f < s) s = f;
        }
      }
    }
    return s;
  }

  var _norm = null;
  function norm() {
    if (_norm) return _norm;
    var acc = 0, n = 3000;
    for (var i = 0; i < n; i++) acc += (60 / n) / speed((i + 0.5) * 60 / n);
    _norm = acc / 60;
    return _norm;
  }

  /* ---- Fahrzeuge ------------------------------------------------------- */

  function vehiclesMarkup(t, dots) {
    var w = dots ? 16 : 34, h = dots ? 16 : 14;
    var out = '';
    for (var ri = 0; ri < RUNS.length; ri++) {
      var r = RUNS[ri], e = EDGES[r.e], L = geom(e).L;
      for (var k = -1; k <= 1; k++) {
        var tt = t + 60 * k;
        if (tt < r.w0 || tt > r.w1) continue;
        var s, side = 1;
        if (tt <= r.dep) { s = PARK; side = -1; }
        else if (tt >= r.arr) { s = L - PARK; }
        else { s = PARK + (L - 2 * PARK) * (tt - r.dep) / (r.arr - r.dep); }
        // Seitenwechsel ueber eine unsichtbare Weiche kurz hinter dem Knoten
        if (s < PARK + SWITCH) {
          var u = Math.max(0, (s - PARK) / SWITCH);
          side = 2 * (u * u * (3 - 2 * u)) - 1;
        }
        if (r.dir < 0) s = L - s;
        var p = pointAt(e, s);
        var off = 9.5 * r.dir * side;
        var a = (r.dir > 0 ? p.ang : p.ang + 180) * Math.PI / 180;
        var cx = p.x + p.nx * off, cy = p.y + p.ny * off;
        var ux = Math.cos(a), uy = Math.sin(a), hw = w / 2, hh = h / 2;
        var pts = corner(cx, cy, ux, uy, hw, hh, 1, 1) + ' ' +
                  corner(cx, cy, ux, uy, hw, hh, 1, -1) + ' ' +
                  corner(cx, cy, ux, uy, hw, hh, -1, -1) + ' ' +
                  corner(cx, cy, ux, uy, hw, hh, -1, 1);
        out += '<polygon points="' + pts + '" fill="' + e.color +
               '" stroke="' + CREME + '" stroke-width="3.5"/>';
      }
    }
    return out;
  }

  function corner(cx, cy, ux, uy, hw, hh, sl, sh) {
    return (cx + ux * hw * sl - uy * hh * sh).toFixed(1) + ',' +
           (cy + uy * hw * sl + ux * hh * sh).toFixed(1);
  }

  /* ---- Statisches Grundgeruest ----------------------------------------- */

  function clockTicks() {
    var out = '<line x1="500" y1="499" x2="500" y2="490" stroke="' + INK + '" stroke-width="7"/>';
    var t = [
      [539.5, 509.6, 544, 501.8], [568.4, 538.5, 576.2, 534], [579, 578, 588, 578],
      [568.4, 617.5, 576.2, 622], [539.5, 646.4, 544, 654.2], [500, 657, 500, 666],
      [460.5, 646.4, 456, 654.2], [431.6, 617.5, 423.8, 622], [421, 578, 412, 578],
      [431.6, 538.5, 423.8, 534], [460.5, 509.6, 456, 501.8]
    ];
    for (var i = 0; i < t.length; i++) {
      out += '<line x1="' + t[i][0] + '" y1="' + t[i][1] + '" x2="' + t[i][2] +
             '" y2="' + t[i][3] + '" stroke="' + INK + '" stroke-width="3"/>';
    }
    return out;
  }

  function nodeLabel(x, y, size, text) {
    size = fit(size, text, 12);
    return '<text x="' + x + '" y="' + y + '" font-size="' + size +
           '" font-weight="700" letter-spacing="-0.02em" text-anchor="middle" fill="' +
           INK + '">' + esc(text) + '</text>';
  }

  function edgeLabel(x, y, anchor, text) {
    return '<text x="' + x + '" y="' + y + '" font-size="36" font-weight="600" ' +
           'letter-spacing="0.1em" text-anchor="' + anchor + '" fill="' + INK +
           '" style="font-variant-numeric:tabular-nums">' + text + '</text>';
  }

  // Alles ausser Fahrzeugen, Zeiger und Leuchtring.
  function staticMarkup(c) {
    c = cfg(c);
    return '' +
      '<text x="58" y="118" font-size="' + fit(60, c.title, 20) + '" font-weight="800" letter-spacing="-0.03em" fill="' + INK + '">' + esc(c.title) + '</text>' +
      '<text x="58" y="974" font-size="' + fit(34, c.subline, 24) + '" font-weight="500" fill="' + INK + '">' + esc(c.subline) + '</text>' +

      '<polyline points="240,700 420,880 580,880 760,700" fill="none" stroke="' + EDGES.MK.color + '" stroke-width="9" stroke-linejoin="miter"/>' +
      '<polyline points="500,310 240,700" fill="none" stroke="' + EDGES.HM.color + '" stroke-width="9"/>' +
      '<polyline points="500,310 760,700" fill="none" stroke="' + EDGES.HK.color + '" stroke-width="9"/>' +

      edgeLabel(345, 506, 'end', '28 MIN') +
      edgeLabel(655, 506, 'start', '28 MIN') +
      edgeLabel(500, 930, 'middle', '57 MIN') +

      '<circle cx="500" cy="578" r="88" fill="' + CREME + '" stroke="' + INK + '" stroke-width="4"/>' +
      clockTicks();
  }

  function nodeCircles(c) {
    c = cfg(c);
    return '<circle cx="500" cy="310" r="27" fill="' + CREME + '" stroke="' + INK + '" stroke-width="8"/>' +
           '<circle cx="240" cy="700" r="27" fill="' + CREME + '" stroke="' + INK + '" stroke-width="8"/>' +
           '<circle cx="760" cy="700" r="27" fill="' + CREME + '" stroke="' + INK + '" stroke-width="8"/>' +
           nodeLabel(500, 218, 42, c.nodeTop) +
           nodeLabel(175, 824, 38, c.nodeLeft) +
           nodeLabel(825, 824, 38, c.nodeRight);
  }

  // Vollstaendiges SVG fuer einen festen Zeitpunkt — auch als Standbild-Export
  // und als serverseitiges Fallback verwendbar.
  function fullSvg(t, dots, c) {
    c = cfg(c);
    var g = glow(t);
    var hx = (500 + 66 * Math.sin(t * Math.PI / 30)).toFixed(2);
    var hy = (578 - 66 * Math.cos(t * Math.PI / 30)).toFixed(2);
    // Anders als im Shadow DOM erbt eine per <img> geladene SVG-Datei nichts
    // vom Dokument — Schrift also hier setzen, sonst steht das Standbild in
    // der Serifen-Vorgabe des Browsers.
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" ' +
      'font-family="InterVariable, Inter, system-ui, sans-serif" ' +
      'role="img" aria-label="Modell eines Knotendreiecks: drei Knotenbahnhöfe, drei Linien, ' +
      'Abfahrten zur Minute :02 und :32">' +
      '<rect width="1000" height="1000" fill="' + CREME + '"/>' +
      '<rect x="2" y="2" width="996" height="996" fill="none" stroke="' + INK + '" stroke-width="4"/>' +
      staticMarkup(c) +
      '<line x1="500" y1="578" x2="' + hx + '" y2="' + hy + '" stroke="' + INK + '" stroke-width="7"/>' +
      '<circle cx="500" cy="578" r="9" fill="' + INK + '"/>' +
      vehiclesMarkup(t, !!dots) +
      glowMarkup(g) +
      nodeCircles(c) +
      '</svg>';
  }

  function glowMarkup(g) {
    var c = [[500, 310], [240, 700], [760, 700]], out = '';
    for (var i = 0; i < c.length; i++) {
      out += '<circle cx="' + c[i][0] + '" cy="' + c[i][1] + '" r="' + g.r +
             '" opacity="' + g.op + '" fill="none" stroke="' + INK + '" stroke-width="5"/>';
    }
    return out;
  }

  /* ---- Custom Element -------------------------------------------------- */

  var CSS = '' +
    ':host { display:block; max-width:800px; min-width:300px; margin:0 auto;' +
    ' font-family:InterVariable, Inter, system-ui, sans-serif; color:' + INK + '; }' +
    '.frame { border:2px solid ' + INK + '; background:' + CREME + '; }' +
    'svg.stage { display:block; width:100%; height:auto; }' +
    '.bar { display:flex; align-items:flex-start; gap:14px; margin-top:16px; }' +
    '.play { flex:0 0 auto; width:44px; height:44px; padding:0; border:2px solid ' + INK + ';' +
    ' background:' + CREME + '; color:' + INK + '; cursor:pointer; display:flex;' +
    ' align-items:center; justify-content:center; border-radius:0; }' +
    '.play:hover { background:' + INK + '; color:' + CREME + '; }' +
    '.play:focus-visible { outline:2px solid ' + VIOLET + '; outline-offset:2px; }' +
    '.col { flex:1 1 auto; min-width:0; }' +
    '.track { position:relative; height:12px; margin-top:16px; border:2px solid ' + INK + ';' +
    ' background:' + CREME + '; cursor:pointer; touch-action:none; }' +
    '.track:focus-visible { outline:2px solid ' + VIOLET + '; outline-offset:3px; }' +
    '.fill { position:absolute; left:0; top:0; bottom:0; background:' + INK + '; }' +
    '.thumb { position:absolute; top:-9px; width:16px; height:26px; background:' + INK + ';' +
    ' border:2px solid ' + CREME + '; transform:translateX(-50%); box-sizing:border-box; }' +
    '.scale { position:relative; height:28px; margin-top:10px; }' +
    '.scale i { position:absolute; top:0; width:2px; background:' + INK + '; transform:translateX(-50%); }' +
    '.scale i.maj { height:9px; } .scale i.min { height:5px; opacity:0.45; }' +
    '.scale b { position:absolute; top:13px; transform:translateX(-50%); font-size:12px;' +
    ' font-weight:600; letter-spacing:0.08em; font-variant-numeric:tabular-nums; }' +
    '.read { flex:0 0 auto; min-width:52px; margin-top:12px; text-align:right; font-size:16px;' +
    ' font-weight:700; letter-spacing:0.04em; font-variant-numeric:tabular-nums; }';

  var PLAY = 'M7 4l13 8-13 8z';
  var PAUSE = 'M6 4h4v16H6zM14 4h4v16h-4z';

  function scaleMarkup() {
    var out = '';
    for (var i = 0; i <= 12; i++) {
      var p = (i * 100 / 12).toFixed(3);
      out += '<i class="' + (i % 3 === 0 ? 'maj' : 'min') + '" style="left:' + p + '%"></i>';
    }
    ['0', '15', '30', '45', '60'].forEach(function (l, i) {
      out += '<b style="left:' + (i * 25) + '%">' + l + '</b>';
    });
    return out;
  }

  if (typeof window !== 'undefined' && window.customElements && !customElements.get('idt-knotendreieck')) {

    class IdtKnotendreieck extends HTMLElement {
      connectedCallback() {
        if (this._built) return;
        this._built = true;

        this.t = FREEZE;
        this.playing = false;
        this.touched = false;
        this.raf = null;
        this.dots = (this.getAttribute('vehicle-style') || 'Striche') === 'Punkte';
        var c = parseFloat(this.getAttribute('cycle-seconds'));
        this.cycle = c > 0 ? c : 13.5;
        this.reduced = typeof matchMedia === 'function' &&
          matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.cfg = cfg({
          title: this.getAttribute('grafik-titel'),
          subline: this.getAttribute('subzeile'),
          nodeTop: this.getAttribute('knoten-oben'),
          nodeLeft: this.getAttribute('knoten-links'),
          nodeRight: this.getAttribute('knoten-rechts')
        });

        var root = this.attachShadow({ mode: 'open' });
        root.innerHTML =
          '<style>' + CSS + '</style>' +
          '<div class="frame"><svg class="stage" viewBox="0 0 1000 1000" role="img" ' +
          'aria-label="Modell eines Knotendreiecks: drei Knotenbahnhöfe, drei Linien, ' +
          'Abfahrten zur Minute :02 und :32">' +
          staticMarkup(this.cfg) +
          '<line class="hand" x1="500" y1="578" x2="500" y2="512" stroke="' + INK + '" stroke-width="7"/>' +
          '<circle cx="500" cy="578" r="9" fill="' + INK + '"/>' +
          '<g class="veh"></g><g class="glow"></g>' +
          nodeCircles(this.cfg) +
          '</svg></div>' +
          '<div class="bar">' +
          '<button class="play" type="button" aria-label="Abspielen">' +
          '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">' +
          '<path d="' + PLAY + '" fill="currentColor"/></svg></button>' +
          '<div class="col">' +
          '<div class="track" role="slider" tabindex="0" aria-label="Minute der Stunde" ' +
          'aria-valuemin="0" aria-valuemax="60" aria-valuenow="30" aria-valuetext="Minute 30">' +
          '<div class="fill"></div><div class="thumb"></div></div>' +
          '<div class="scale">' + scaleMarkup() + '</div>' +
          '</div><div class="read">:30</div></div>';

        this.$hand = root.querySelector('.hand');
        this.$veh = root.querySelector('.veh');
        this.$glow = root.querySelector('.glow');
        this.$track = root.querySelector('.track');
        this.$fill = root.querySelector('.fill');
        this.$thumb = root.querySelector('.thumb');
        this.$read = root.querySelector('.read');
        this.$play = root.querySelector('.play');
        this.$icon = root.querySelector('.play path');

        this.$play.addEventListener('click', this.toggle.bind(this));
        this.$track.addEventListener('pointerdown', this.onDown.bind(this));
        this.$track.addEventListener('pointermove', this.onMove.bind(this));
        this.$track.addEventListener('pointerup', this.onUp.bind(this));
        this.$track.addEventListener('keydown', this.onKey.bind(this));

        this.onPrint = function () { this.stop(); this.set(FREEZE); }.bind(this);
        addEventListener('beforeprint', this.onPrint);

        this.set(FREEZE);
        if (this.reduced || this.getAttribute('autoplay') === 'false') return;
        this.gate();
      }

      disconnectedCallback() {
        this.stop();
        this.ungate();
        removeEventListener('beforeprint', this.onPrint);
      }

      /* -- Autostart beim ersten Sichtbarwerden -- */
      gate() {
        var self = this;
        if (typeof IntersectionObserver === 'function') {
          this.io = new IntersectionObserver(function (es) {
            for (var i = 0; i < es.length; i++) if (es[i].isIntersecting) self.startOnce();
          }, { threshold: 0.35 });
          this.io.observe(this);
        }
        // IntersectionObserver meldet in geclippten Frames nichts — Rect-Fallback
        this.check = function () {
          var r = self.getBoundingClientRect();
          if (r.bottom > 0 && r.top < (innerHeight || 0) && r.height > 0) self.startOnce();
        };
        addEventListener('scroll', this.check, { passive: true });
        addEventListener('resize', this.check, { passive: true });
        this.fallback = setTimeout(this.check, 900);
      }

      ungate() {
        if (this.io) { this.io.disconnect(); this.io = null; }
        if (this.fallback) { clearTimeout(this.fallback); this.fallback = null; }
        if (this.check) {
          removeEventListener('scroll', this.check);
          removeEventListener('resize', this.check);
          this.check = null;
        }
      }

      startOnce() {
        if (this.touched || this.playing) return;
        this.ungate();
        this.start();
      }

      start() {
        if (this.raf) return;
        this.playing = true;
        this.last = null;
        this.$icon.setAttribute('d', PAUSE);
        this.$play.setAttribute('aria-label', 'Pause');
        this.raf = requestAnimationFrame(this.tick.bind(this));
      }

      stop() {
        if (this.raf) cancelAnimationFrame(this.raf);
        this.raf = null;
        this.playing = false;
        if (this.$icon) {
          this.$icon.setAttribute('d', PLAY);
          this.$play.setAttribute('aria-label', 'Abspielen');
        }
      }

      toggle() {
        if (this.playing) this.stop();
        else { this.touched = false; this.start(); }
      }

      tick(ts) {
        if (this.last == null) this.last = ts;
        var dt = Math.min(0.1, (ts - this.last) / 1000);
        this.last = ts;
        var t = this.t + dt * (60 / this.cycle) * norm() * speed(this.t);
        while (t >= 60) t -= 60;
        this.set(t);
        this.raf = requestAnimationFrame(this.tick.bind(this));
      }

      /* -- Zustand zeichnen -- */
      set(t) {
        this.t = t;
        var g = glow(t);
        var hx = (500 + 66 * Math.sin(t * Math.PI / 30)).toFixed(2);
        var hy = (578 - 66 * Math.cos(t * Math.PI / 30)).toFixed(2);
        this.$hand.setAttribute('x2', hx);
        this.$hand.setAttribute('y2', hy);
        this.$veh.innerHTML = vehiclesMarkup(t, this.dots);
        this.$glow.innerHTML = glowMarkup(g);
        var m = Math.floor(t + 0.0001);
        var pct = (t / 60) * 100;
        this.$fill.style.width = pct + '%';
        this.$thumb.style.left = pct + '%';
        this.$read.textContent = ':' + (m < 10 ? '0' + m : m);
        this.$track.setAttribute('aria-valuenow', m);
        this.$track.setAttribute('aria-valuetext', 'Minute ' + m);
      }

      /* -- Bedienung -- */
      pauseForUser() { this.touched = true; this.ungate(); this.stop(); }

      seek(clientX) {
        var r = this.$track.getBoundingClientRect();
        var p = Math.max(0, Math.min(1, (clientX - r.left) / r.width));
        this.set(p * 60);
      }

      onDown(ev) {
        this.pauseForUser();
        this.dragging = true;
        try { this.$track.setPointerCapture(ev.pointerId); } catch (e) {}
        this.seek(ev.clientX);
      }

      onMove(ev) { if (this.dragging) this.seek(ev.clientX); }

      onUp(ev) {
        this.dragging = false;
        try { this.$track.releasePointerCapture(ev.pointerId); } catch (e) {}
      }

      onKey(ev) {
        var step = ev.shiftKey ? 5 : 1, d = 0;
        if (ev.key === 'ArrowRight' || ev.key === 'ArrowUp') d = step;
        else if (ev.key === 'ArrowLeft' || ev.key === 'ArrowDown') d = -step;
        else if (ev.key === 'Home') d = -this.t;
        else if (ev.key === 'End') d = 60 - this.t - 0.001;
        else if (ev.key === ' ' || ev.key === 'Enter') { ev.preventDefault(); this.toggle(); return; }
        else return;
        ev.preventDefault();
        this.pauseForUser();
        var t = this.t + d;
        while (t >= 60) t -= 60;
        while (t < 0) t += 60;
        this.set(t);
      }
    }

    customElements.define('idt-knotendreieck', IdtKnotendreieck);
  }

  // Standbild-Export fuer Node (Fallback-SVG, Social Media, Druck)
  if (typeof module === 'object' && module.exports) {
    module.exports = { fullSvg: fullSvg, vehiclesMarkup: vehiclesMarkup, glow: glow };
  }
})();
