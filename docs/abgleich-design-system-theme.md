# Abgleich: IDT Design System ↔ WordPress-Theme idt-deutschlandtakt
Stand: 2026-10-20. Es wurde weder das Artefakt noch das Theme geändert; diese Datei ist das einzige Ergebnis.
## Grundlage
- Design System (DS): Artefakt https://claude.ai/artifact/14Xq8ZVdYasy3EUQ8bLhGh, Version 1792493450-52cd. Gelesen: README, tokens.json, tokens.css, tokens/*.css, components/*/README.md, core|feedback|forms/*.jsx, Instagram-README, ui_kits/website (Nav, Footer, Home), Landing Page.html, Splash 3 - Horizont.html. Altlasten (manifest.json, migration-map.json, _ds_*, alte Komponentenkopien, Spezimen-Karten) bleiben außen vor.
- Theme: Checkout dieses Repos, Branch `main`, `theme/idt-deutschlandtakt/`, Version 2.7.1 (`style.css`-Header und `IDT_VERSION` stimmen überein).
- Live-Seite: nicht prüfbar. initiative-deutschlandtakt.de ist für diese Sitzung vom Egress-Proxy gesperrt (403). Im Repo gibt es weder Tags noch Releases. Ob die Seite 2.7.1 ausliefert, muss jemand mit Zugriff nachsehen: Quelltext der Startseite, `…/themes/idt-deutschlandtakt/style.css` (Header) oder `?ver=` an den Asset-URLs.
- Vorgehen: Die Tokens wurden per Skript verglichen (:root des Themes gegen tokens.css und tokens/spacing.css des DS, Werte normalisiert). Die Logos wurden pixelweise verglichen und die Schriften per SHA-256. Die Komponenten wurden von Hand gegen die CSS-Regeln und PHP-Render-Funktionen des Themes gelesen.
- Befund-Kürzel: gleich · abweichend · nur Theme · nur DS · Widerspruch (innerhalb des DS oder zwischen DS und Theme).
## 1 Tokens
Das Theme definiert 118 Variablen, das DS 147. Davon gibt es 112 in beiden: 109 sind identisch, 3 weichen nur in der Schreibweise ab. 6 gibt es nur im Theme und 35 nur im DS.
| Gegenstand | Theme | Design System | Befund |
|---|---|---|---|
| Markenfarben, Neutralrampe, Tints, Hover/Active, Status, semantische Aliasse (alle gemeinsamen Farben) | identische Hex-Werte | identisch | gleich |
| Schrift-Familien, Gewichte, Typo-Skala, Zeilenhöhen, Laufweiten, Spacing 1–10, Layout, Border-Breiten, Motion, z-sticky/z-overlay | identisch | identisch | gleich |
| `--radius-card` | `var(--radius-md)` (= 0) | `0` | gleich im Wert, anders definiert |
| `--shadow-block`, `--shadow-block-sm` | `… var(--idt-ink)` | `… #00373c` (tokens.json kann kein `var()`) | gleich im Wert |
| `--radius-bar` | 999px | fehlt | nur Theme |
| `--radius-soft` | 10px | fehlt | nur Theme |
| `--idt-grad-cyan-violet`, `--idt-grad-violet-cyan`, `--idt-grad-seite` | drei Verläufe, beide Richtungen, auch senkrecht | fehlen als Tokens (der Verlauf `--idt-gradient-accent` steht nur dort) | nur Theme |
| `--header-logo-h` | 38px, im Customizer überschreibbar | fehlt | nur Theme |
| `--idt-gradient-accent` (cyan → violett, 90°), `--idt-gradient-band` (96°), `-from`/`-to` | fehlen | vorhanden | nur DS |
| Print-Farben `--idt-*-print` (5) | fehlen | vorhanden, „nur Doku“ | nur DS |
| Datenviz: `--idt-chart-1…6`, `--idt-seq-1…5`, `--idt-diverge-*`, `--idt-highlight*`, `--idt-chart-grid/axis/track` (21) | fehlen | vorhanden | nur DS |
| `--z-base`, `--z-modal`, `--z-toast`, `--shadow-none`, `--space-0`, `--radius-none`, `--fs-mono-sm` | fehlen | vorhanden | nur DS |
| Dunkles Farbschema | keines | nur Theme „light“ | gleich |
## 2 Assets
| Gegenstand | Theme | Design System | Befund |
|---|---|---|---|
| InterVariable.woff2 | SHA-256 693b77d4…a8e3 | identisch | gleich |
| InterVariable-Italic.woff2 | SHA-256 e564f652…262a | identisch | gleich |
| Logo transparent (2560×1361) | `assets/logo-idt-transparent.png`, SHA-256 a29a6583…88ed | Datei-Hash identisch | gleich |
| Logo inverse (2560×1361) | `assets/logo-idt-inverse.png`, SHA-256 55ba3ad7…70c8 | Datei-Hash identisch | gleich |
| Logo auf Cremegrund (`logo-idt.png`, 404×256) | fehlt | vorhanden | nur DS |
| Site-Icon 512×512 | `assets/site-icon.png`: zwei diagonale Pillen (Violett, Cyan) auf Ink, die Balken des Horizont-Splash | `favicons/android-chrome-512x512.png`: waagerechte Gleisbalken mit Verlauf Cyan → Violett auf Ink | abweichend, andere Bildmarke |
| Favicon-Set (favicon.ico, 16, 32, apple-touch, 192, 512, site.webmanifest) | nur `site-icon.png` (WordPress erzeugt daraus die Größen); kein Manifest | vollständiges Set; README: „unverändert verwenden, Marke nie neu zeichnen“ | nur DS (Set), abweichend (Motiv) |
| Social-Vorschau 1200×630 | `assets/social-preview.png` | kein Gegenstück | nur Theme |
| Instagram-Vorlagen (SVG, ZIP, Generator, Templates) | fehlen | vorhanden | nur DS |
| Logo-Schutzzone, Mindestgröße, Vektor-Master | nicht geregelt | DS-README: nicht geliefert | offen auf beiden Seiten |
## 3 Komponenten
Das „Theme“ steht jeweils für Klasse und Render-Funktion (`inc/shortcodes.php`).
| Gegenstand | Theme | Design System | Befund |
|---|---|---|---|
| Button, Varianten | `.idt-btn--primary/secondary/outline/ghost/inverse` plus `--beige` (halbrund), `--gradient` (Verlaufsrand), `--bg` (freie Fläche) | primary, secondary, outline, ghost, inverse | abweichend: drei Varianten nur im Theme |
| Button, Ghost | transparent, Rand transparent; Hover Paper-2 | Fläche Paper-2, Rand Paper-2; Hover `idt-line` | abweichend |
| Button, Größen | eine Größe 11×18px / `fs-sm`, `--lg` 15×26px / `fs-body` | sm 9×18, md 14×26, lg 19×36 (`fs-h3`) | abweichend |
| Button, Hover/Press | Hover: 2px nach oben, Press: zurück auf 0 | Hover: Fläche dunkler, Press: 1px nach unten, kein Anheben; das DS-README sagt dagegen „Buttons heben sich 1–3px“ | abweichend; im DS widersprüchlich |
| Button, Rahmen und Ecke | 2px Ink, Radius 0 (`--radius-sm`), außer `--beige` (999px) | 2px, Radius 0 | gleich, außer beige |
| Button, deaktiviert | keine Regel gefunden | Deckkraft 0,45 | nur DS |
| Pill (`.pill`) | Outline, Uppercase, Verlaufsrand Violett → Cyan beim Hover, Radius 0; `--beige` rund | im DS-UI-Kit nicht vorhanden; `Splash 3 - Horizont.html` hat runde Pills (999px) mit demselben Verlaufsrand | nur Theme (Komponente); Splash-Seite im DS widerspricht dem DS-README |
| Card, Standard | `.idt-card`: 1px Haarlinie, `shadow-sm`, Hover `shadow-md` und 2px hoch; Padding `space-6` | `frame`: 2px Ink-Rahmen, flach. Haarlinie mit `shadow-sm` ist dort die Variante `soft`; Hover 3px hoch | abweichend |
| Card, Varianten | nur dieser eine Stil; `.idt-concept` (2px Ink, 6px farbige Oberkante) und `.idt-newscard` kommen aus anderen Funktionen | frame, soft, block, filled, inverse, dazu `accent` (6px Oberkante) | abweichend; Konzept-Karte entspricht `frame` + `accent` |
| Stat | `.idt-stat`: Zahl `fs-display-md` (36px), Zeilenhöhe 1, `ls-tight`, immer Violett; darunter ein Label in Mono, Uppercase, `text-muted` | Zahl `fs-display-2xl` (88px), Zeilenhöhe 0,92, `ls-tighter`, Farbe wählbar; Label in Inter semibold `fs-h3`; optional `sub` in Mono/Uppercase; Prop `inverse` | abweichend: Zahl 2,4× kleiner, Label = DS-`sub`, keine Farbwahl, kein Inverse |
| Callout | `.idt-callout`: 2px Ink, Block-Schatten `shadow-block-sm`, Fläche Paper oder weiche Fläche (cyan/violet/yellow), keine Kante, Padding `space-5`/`space-6` | 2px Ink, 8px Kante links in der Tonfarbe, weiche Fläche, kein Schatten; Töne info, action, highlight, success, danger; Titel und Icon | abweichend |
| Tag | `.idt-tag`: 11px Mono, Uppercase, 4×10px, 1px-Rand, Farben violet/cyan/yellow/ink; Text im Violett-Ton ist `#6E50FA` | sm 11px / md 13px; Violett-Text in `idt-violet-hover` (#5A3DE6); `solid`; zusätzlich neutral, success, danger | abweichend; Theme-Violett auf Violett-Soft hat 4,0:1 bei 11px (DS-Notiz: unter 4,5:1) |
| Tag, Gelb-Akzent | `idt_accent()` setzt für das Gelb-Icon das Literal `#C9A100` | kein Token | nur Theme (Literalwert) |
| Checkbox | keine eigenen Regeln | Quadrat 22px, 2px Ink, an: Violett mit Paper-Haken | nur DS |
| Input | nur das Suchfeld (`.idt-searchfield__input`) und der Fluent-Forms-Absende-Button; keine Feldregeln für Formulare, Kommentare | 2px Ink-Rahmen, Fokus Violett, Fehler Danger, Label, Hinweis | nur DS |
| Kopfzeile | sticky, durchscheinend mit Blur, Haarlinie unten, min. 68px, Logo 38px (`--header-logo-h`) | Papier, 2px Ink unten, 72px, Logo 46px | abweichend |
| Fußzeile | Ink, Logo 60px, Spaltenüberschriften Cyan in Mono, Links `on-ink-muted`; keine Kante oben gefunden | Ink, Logo 60px, gleiche Überschriften/Links; 3px Violett-Kante oben | abweichend (Kante) |
| Fokus, Auswahl | 2px Violett, Offset 2px; `::selection` Gelb/Ink | identisch | gleich |
| Überschriften mobil | h1 → `fs-h1`, h2 → `fs-h2` bei ≤ 640px (style.css ab Z. 2160) | identisch (base.css) | gleich |
## 4 Nur im Theme
| Gegenstand | Theme | Design System | Befund |
|---|---|---|---|
| Pill-Buttons | `.pill`, `--solid`, `--violet`, `--beige`, Pill-Stack | nur in `Splash 3 - Horizont.html` und `Landing Page.html`, nicht als Komponente | nur Theme |
| Marker | `.idt-mark` (Gelb, Cyan-Soft, Violett-Soft) als Textunterstreichung | nichts | nur Theme |
| Takt-Rhythmus | `.idt-takt`: Punkte und Striche, 999px | nichts | nur Theme |
| Diagonal | `.idt-diagonal`: Ink-Fläche mit Violett-Band (131°) und Cyan-Strich (41°, 999px) | im DS gibt es Diagonalen nur in Splash und Instagram (45°/41°) | nur Theme (als Baustein) |
| Eyebrow | Mono, Uppercase, `text-muted`; `.idt-eyebrow` setzt zusätzlich „→ “ in Cyan | gleiche Typografie, kein Pfeil | abweichend (Pfeil nur Theme) |
| Initiale | `.idt-dropcap::first-letter`, 3,4em, Violett | nichts | nur Theme |
| Social-Icons und -Leiste | `.idt-social__icon` (Outline, Verlaufsrand), `--beige` mit Radius 10px | nichts | nur Theme |
| Mail-Link | `.idt-email` mit Briefzeichen | nichts | nur Theme |
| Verlaufsseite | `body.idt-verlauf`: senkrechter Verlauf Violett → Cyan über die ganze Seite, beige Pills, Social-Leiste | nichts (das DS verbietet „page-wide wash“) | nur Theme; widerspricht einer DS-Regel |
| Horizont-Splash | `[splash]` (feste 1280×800-Bühne) und `[splash2]` (fluide) | `Splash 1 - Vollton`, `2 - Panel`, `3 - Horizont` als HTML-Seiten | beidseitig vorhanden, nicht als Komponente verglichen |
| Knotendreieck | Block mit Animation (`blocks/knotendreieck/`) | nichts | nur Theme |
| Block-Patterns, Shortcodes, Blöcke | `inc/patterns.php`, `inc/blocks.php` | nichts (WordPress-spezifisch) | nur Theme |
| Verlaufstrenner, Suchoverlay mit Verlaufskante, Suchtreffer mit 3px-Linkskante, Zitate mit 3px-Linkskante | siehe Regeln | nichts | nur Theme |
| Konzept-, News-Karten mit `bg=`-Fläche (auch Verlauf) | `[concept]`, `[newscard]` | nichts | nur Theme |
## 5 Nur im Design System
| Gegenstand | Theme | Design System | Befund |
|---|---|---|---|
| Print-Farben (CMYK-Referenz) | fehlen | 5 Tokens, nur Doku | nur DS |
| Datenviz-Palette (kategorial, sequenziell, divergierend, Highlight, Raster) | fehlt | 21 Tokens | nur DS |
| Checkbox, Input | fehlen (siehe 3) | vorhanden | nur DS |
| Card-Varianten `block`, `filled`, `inverse`, `soft` mit Namen | nicht benannt | vorhanden | nur DS |
| Tag `solid`, success, danger, neutral | fehlen | vorhanden | nur DS |
| Callout-Töne success, danger, Titel, Icon | fehlen | vorhanden | nur DS |
| Instagram-Vorlagen (Post, Story, hell/dunkel), Generator, ZIP | fehlen | vorhanden | nur DS |
| Favicon-Set und Manifest | nur ein Icon (andere Marke) | vorhanden | nur DS |
| Zusatz-Tokens (`z-base`, `z-modal`, `z-toast`, `shadow-none`, `space-0`, `radius-none`, `fs-mono-sm`) | fehlen | vorhanden | nur DS |
| UI-Kit Website (Nav, Footer, Home, Konzept, News, Mitmachen), Landing Page, Example Landing Page | nicht relevant | vorhanden | nur DS |
## 6 Regeln
„Quelle“ nennt, wo im DS die Aussage steht. Zwei der Regeln aus der ersten Durchsicht stehen nicht im DS-README, sondern an anderer Stelle (siehe Befund).
| Gegenstand | Theme | Design System | Befund |
|---|---|---|---|
| Verlaufsrichtung | Violett → Cyan: Pill-Hover-Rand (Z. 1356), `.idt-btn--gradient` (1588), Trenner (1957), Such-Kante (529), Block „Verlauf“ (1912), Option `bg="violet-cyan"`, senkrecht auf der Verlaufsseite (1245); Cyan → Violett nur als Option `bg="cyan-violet"` | Quelle `idt-gradient-accent`: „cyan → violet, always leading-to-trailing. Never reversed, never for body text, never as a page-wide wash.“ Logo und Favicon laufen Cyan → Violett. `Splash 3` und `Landing Page.html` im DS selbst benutzen Violett → Cyan für den Hover-Rand | Widerspruch Theme ↔ DS und im DS |
| Verlauf überhaupt | an 7 Stellen, dazu Flächenfüllung für Buttons und Konzept-Karten | README „Backgrounds“: „No gradients“; Instagram-README: „keine Verläufe“; zugleich Verlaufs-Tokens | Widerspruch im DS |
| „Alles eckig, keine Pills“ | Radius 0 für Buttons, Karten, Felder. Ausnahmen: `--radius-bar` 999px (beige Pill und Button, Splash-Balken, Takt-Punkte, Diagonal-Strich), `--radius-soft` 10px (beige Social-Kachel). Der Theme-Kommentar begründet das als Zitat der Wortmarke | README: „Corners are square (radius 0 throughout — no rounded buttons, tags, inputs or cards)“; Token `radius-pill`: „no pills in this brand“. `Splash 3` hat 999px-Pills und -Balken; Instagram-README verlangt Bänder „mit runden Enden“, aber „Ecken eckig“ | Widerspruch Theme ↔ DS und im DS |
| Karten mit Linkskante | `.idt-result` (Suchtreffer) und `blockquote` haben 3px Violett links; Callout und Karten sonst nicht | DS-README sagt dazu nichts. Der Satz stammt aus der Checkliste der Design-System-Vorlage („no left-border cards“). Das DS-Callout selbst hat 8px Kante links | Widerspruch im DS; für das Theme nur zwei Stellen betroffen |
| Card-Standard | Haarlinie + `shadow-sm` | „framed (2px ink), square, flat by default“; „Avoid soft drop shadows except `soft`/hover“ | Widerspruch Theme ↔ DS |
| Callout | Block-Schatten, keine Kante | „thick coloured left edge“ | Widerspruch Theme ↔ DS |
| Block-Schatten | an Callout, Konzept-Karte (Hover) und Suchtreffer (Hover) | „used on hero CTAs“ | abweichend |
| Hover/Press | Anheben bei Buttons (2px), Karten (2–3px) | README: Hover hebt 1–3px an, Press 1px nach unten; `Button.jsx` hebt nicht an | Widerspruch im DS |
| Eyebrow | mit „→ “ in Cyan | Mono, Uppercase, ohne Pfeil | abweichend |
| Kontrast | `text-faint` nur als Platzhalter im Suchfeld; Tag-Violett 4,0:1 bei 11px; Footer-Überschrift Cyan auf Ink (7,8:1) | DS-Notizen: `text-faint` 3,2:1, `idt-violet` auf Ink 2,6:1, `idt-success`/`danger` 4,1:1 nicht für Kleintext | Tag-Violett verstößt; Rest eingehalten (nur stichprobenartig geprüft) |
| Logo-Verwendung | als Bild eingesetzt, nie gesetzt | „fixed lockup, never re-typeset“ | gleich |
| Sprache | Deutsch; UI-Texte Sie-Form | Deutsch, Sie-Form | gleich |
## Fragenkatalog
Jede Frage steht offen, bis sie unter „Entscheidungen“ eingetragen ist. Empfehlung und Begründung stehen jeweils dabei.
### Grundsatz
**F1. Welche Seite ist je Bereich maßgeblich?**
Antworten: Theme · Design System · Fall für Fall (je Bereich).
Empfehlung: Fall für Fall, mit diesem Raster.
- Tokens: DS maßgeblich. Das Theme folgt, und die sechs Theme-Tokens wandern bei Zustimmung ins DS.
- Assets: DS maßgeblich (Logos sind schon gleich, nur die Icons weichen ab).
- Komponenten: je Komponente entscheiden (F4–F8).
- Nur-Theme-Elemente: Theme maßgeblich; das DS dokumentiert sie nach.
- Regeln: je Regel entscheiden (F2–F3, F10). Danach schreibt das DS-README, was gilt.
Begründung: Von den gemeinsamen Tokens sind 109 von 112 identisch, und eine Quelle verhindert Auseinanderlaufen. Bei Komponenten und Regeln steckt die Verlaufs- und Rundungsentscheidung dahinter, die nur der Verein treffen kann; das Theme ist die live ausgelieferte Wahrheit.
### Regeln, von F1 abhängig
**F2. Verlaufsrichtung.**
Antworten:
- (a) Das DS übernimmt Violett → Cyan als zulässig.
- (b) Das Theme stellt alles auf Cyan → Violett um.
- (c) Waagerecht gilt Cyan → Violett (Richtung von Logo und Favicon), der Hover-Rand wird entsprechend umgestellt. Die senkrechte Verlaufsseite (Violett oben, Cyan unten) bekommt im DS einen eigenen Token als dokumentierte Ausnahme.
Empfehlung: (c).
Begründung: Logo und Favicon laufen Cyan → Violett, und die Regel „nie umgekehrt“ stützt sich darauf. Die Verlaufsseite ist eine eigens gebaute Seitenvorlage (Theme-Abschnitt 6c), und eine senkrechte Fläche hat keine „Leserichtung“, die ein Logo-Bezug verletzen könnte. Mit (c) bleibt die DS-Regel erhalten, und das Theme ändert nur den Hover-Rand der Pills, `.idt-btn--gradient`, Trenner und Suchkante.
Folgefrage F2b: Darf die Verlaufsseite im DS als „page-wide wash“-Ausnahme stehen (ja/nein)? Empfehlung: ja, mit Beschränkung auf diese eine Seitenvorlage.
**F3. Runde Formen.**
Antworten:
- (a) Das DS führt `radius-bar` (999px) als Ausnahme ein: Logo-zitierende Balken und die beige Pill/Button-Variante auf farbigen Flächen. `radius-soft` fällt weg, die Social-Kachel wird eckig.
- (b) Das DS führt beide Ausnahmen ein.
- (c) Das Theme wird überall eckig.
Empfehlung: (a).
Begründung: Rund ist im DS selbst schon etabliert (Splash 3, Instagram-Bänder mit runden Enden), das README ist hier veraltet. `radius-soft` (10px) hat dagegen kein Vorbild im DS und keinen Bezug zur Wortmarke; der Theme-Kommentar rechtfertigt es nur mit „weiche Ecke“. Ein Ausnahmefall weniger macht die Regel haltbar.
**F10. Linkskante.** Gilt „keine Karten mit farbiger Linkskante“ als DS-Regel?
Antworten:
- (a) Ja, ins README schreiben. Dann verliert das DS-Callout seine Kante (siehe F5), und im Theme fallen die Kante bei `.idt-result` (Suchtreffer) und das Blockquote weg oder bleiben als Ausnahme für Zitate.
- (b) Nein, die Kante ist erlaubt; das DS-Callout bleibt.
- (c) Ja für Karten und Callouts, nein für Zitate (Blockquote).
Empfehlung: (c).
Begründung: Ein Zitat mit Strich ist eine typografische Konvention und keine Karte. Suchtreffer sind Karten und sollten dem Standard folgen.
### Komponenten
**F4. Card-Standard.**
Antworten:
- (a) DS-Standard `frame` (2px Ink) gilt auch im Theme; `.idt-card` wird umgestellt.
- (b) Theme-Standard bleibt, das DS führt ihn als Standard und `frame` als Variante.
- (c) Beides bleibt, Verwendung nach Rolle: Haarlinie (`soft`) für Listen und Beiträge, `frame` (+ Oberkante) für Konzept und Hervorhebung. Das DS-README sagt das ausdrücklich, das Theme bekommt Modifier `--frame`, `--block`.
Empfehlung: (c).
Begründung: Das Theme nutzt beide Stile längst (`.idt-card` und `.idt-concept`), das DS kennt beide. Dann braucht es nur eine Regel und keine Umgestaltung der Beitragslisten. Hover-Anhebung vereinheitlichen auf 3px (DS) statt 2px.
**F5. Callout.**
Antworten: (a) Theme-Form (Block-Schatten, keine Kante); (b) DS-Form (8px Kante, ohne Schatten); (c) beide als Varianten.
Empfehlung: (a), zusammen mit F10 (c).
Begründung: Der Block-Schatten ist das „Signature“-Element des DS und passt zur Karten-Sprache; die Linkskante kollidiert mit der Regel aus F10. Das DS behält die Töne success/danger und den Titel/Icon, die das Theme nicht hat.
**F6. Stat.**
Antworten: (a) Das DS gibt `Stat` zwei Größen (`lg` = `fs-display-2xl` für Kennzahlenbänder, `md` = `fs-display-md` für den Fließtext), das Theme bekommt `.idt-stat--lg`. (b) Das Theme wird auf 88px umgestellt. (c) Das DS stellt auf 36px um.
Empfehlung: (a).
Begründung: Beide Größen haben ihren Ort: 88px trägt kein Fließtext, 36px trägt kein Kennzahlenband. Zusätzlich: Das Mono-Label des Themes ist im DS die Zeile `sub`; das DS-Label (Inter semibold) bleibt als optionale Zeile, damit ein Stat dort zwei Zeilen haben kann.
**F7. Button.**
Teilfragen:
- F7a Varianten `beige` (rund) und `gradient` (Verlaufsrand) ins DS aufnehmen? Empfehlung: ja, als `onColor` und `gradient`; sie folgen aus F2 und F3.
- F7b Hover: Anheben um 2px (Theme, und das DS-README sagt das) oder nur Abdunkeln (`Button.jsx`)? Empfehlung: Anheben; `Button.jsx` und `Button/README.md` im DS anpassen. Dort widerspricht der Code dem eigenen README.
- F7c Größen: DS-Skala sm/md/lg übernehmen? Empfehlung: nicht ändern. Die Theme-Standardgröße (11×18px) liegt zwischen DS-sm und -md; ein Umstellen vergrößert alle Buttons der Seite. Das DS dokumentiert die Zuordnung (Theme-Standard ≈ sm, `--lg` ≈ md).
- F7d Ghost: Theme (transparent) oder DS (Paper-2-Fläche)? Empfehlung: DS übernimmt die transparente Form, weil sie auf allen Flächen funktioniert.
**F8. Tag.**
Antworten: (a) Theme übernimmt `idt-violet-hover` als Violett-Textfarbe (löst die 4,0:1 bei 11px). (b) Theme bleibt, Kontrast wird akzeptiert.
Empfehlung: (a). Zusätzlich das Literal `#C9A100` in `idt_accent()` als Token im DS führen (z. B. `idt-yellow-ink`) oder durch `idt-ink` ersetzen. `solid`, success und danger bleiben DS-only, bis das Theme sie braucht.
Begründung: Der Verstoß ist ein Barrierefreiheitsmangel und mit einer Zeile behoben.
**F9. Formularfelder.**
Antworten: (a) Das Theme setzt Input und Checkbox des DS in CSS um (Fluent Forms, Kommentarformular, Suche), als eigener Folgeauftrag. (b) Nicht umsetzen, solange das Theme keine eigenen Formulare hat. (c) Das DS-Input/Checkbox bleibt Referenz, das Theme übernimmt nur den Fokusring.
Empfehlung: (a), aber nicht in dieser Runde.
Begründung: Ein Mitgliedsantrag über Fluent Forms ist auf der Seite sichtbar und trägt aktuell Plugin-Standard. Es ist die größte sichtbare Lücke, aber ein größerer Eingriff.
**F11. Kopf- und Fußzeile.**
Antworten: (a) Das Theme bleibt maßgeblich, das UI-Kit des DS wird angepasst, `--header-logo-h` wird Token. (b) Das Theme übernimmt DS-Maße (72px, Logo 46px, 2px-Linie, 3px-Violett-Kante im Fuß). (c) nur die Fußkante übernehmen.
Empfehlung: (a).
Begründung: Die Kopfzeile ist über den Customizer einstellbar und live; das UI-Kit ist ein Entwurf. Die Violett-Kante im Fuß ist ein Detail ohne Regelcharakter.
### Elemente und Assets
**F12. Nur-Theme-Elemente ins DS dokumentieren?**
Antworten je Element: im DS aufnehmen · nur Theme · entfernen.
Empfehlung:
- Aufnehmen (als README-Abschnitt „Theme-Bausteine“, ohne React-Komponente): Pill, Marker, Takt-Rhythmus, Diagonal, Eyebrow-Pfeil, Initiale, Social-Icons und -Leiste, Mail-Link, Verlaufsseite, Horizont-Splash.
- Nur Theme: Knotendreieck (Animation, WordPress-Block), Block-Patterns, `bg=`-Fläche an Karten.
- Entfernen: nichts.
Begründung: Das DS soll beschreiben, was die Marke tatsächlich zeigt; WordPress-Mechanik gehört nicht ins Markensystem.
**F13. Icons.**
Antworten: (a) Das Theme ersetzt `site-icon.png` durch das DS-Favicon-Set (favicon.ico, 16, 32, apple-touch, 192, 512, Manifest). (b) Das Theme-Icon bleibt, das DS übernimmt es. (c) Nichts tun.
Empfehlung: (a).
Begründung: Das DS-README sagt: Set unverändert verwenden. Das Theme-Icon zeigt zwei diagonale Pillen, das DS-Favicon das Gleismotiv der Wortmarke. Auf der Live-Seite wäre beim Tab-Symbol zu prüfen, welches Icon bereits ausgeliefert wird. Das ist nur ohne Proxy-Sperre lösbar.
**F14. Tokens, die nur das DS hat.**
Antworten: (a) Das Theme übernimmt alle bis auf die Print-Farben. (b) Das Theme übernimmt nur die ohne Wirkung (`z-*`, `shadow-none`, `space-0`, `radius-none`, `fs-mono-sm`). (c) nichts.
Empfehlung: (a), Print-Farben nur als Kommentar. Das ist nach CLAUDE.md („das vollständige Markensystem“) der Sollzustand, die Datenviz-Tokens kosten nichts und sind für künftige Diagramme nötig.
Gegenrichtung: Die sechs Theme-Tokens (`radius-bar`, Verläufe, `--header-logo-h`, ggf. `radius-soft`) gehen ins DS, soweit F2/F3/F11 es erlauben.
**F15. Logo-Regeln.** Soll das DS die fehlenden Regeln (Schutzzone, Mindestgröße, Vektor-Master) beim Verein einfordern, oder reicht der Vermerk im README?
Empfehlung: Vermerk im README beibehalten und beim Verein anfragen; das Theme braucht dafür nichts.
### Offene Prüfung
**F16. Live-Version.** Wer prüft, ob initiative-deutschlandtakt.de 2.7.1 ausliefert? Das geht ohne Proxy-Sperre in einer Minute: Seitenquelltext, Suche nach `idt-deutschlandtakt/style.css?ver=`. Empfehlung: Sie nennen mir das Ergebnis oder öffnen den Host für diese Sitzung; für die Entscheidungen selbst ist es nicht nötig.
## Entscheidungen
Jede Entscheidung wird hier sofort eingetragen, mit Datum und Wortlaut der Antwort.
### Grundsatz (F1), 2026-10-20
Design System und WordPress-Theme müssen übereinstimmen. Das gilt für alle Bereiche, deshalb entfällt die Frage „welche Seite ist maßgeblich“. Jede Entscheidung wird in beiden umgesetzt: im Artefakt (nach dessen SKILL.md) und im Theme (eigener Branch). Eine Entscheidung ist erst abgeschlossen, wenn sie in Tokens, Komponenten und README des DS und in `style.css`, Render-Funktionen und `todo.md` des Themes steht.
### Vorgehen ab Runde 2
Jede Frage wird als Vergleich dargestellt (Theme links, DS rechts, gerendert), mit Optionen und deren Wirkung auf beide Seiten. Entschieden wird je Frage.
### Runde 2 (2026-10-20)
Jede Entscheidung gilt in Design System (DS) und Theme. „Wie vorgeschlagen“ bedeutet die Empfehlung aus dem Fragenkatalog und der Vergleichsseite.
| Frage | Entscheidung | Theme | Design System |
|---|---|---|---|
| F2 Verlaufsrichtung | wie vorgeschlagen (c): waagerecht Cyan → Violett, senkrechte Verlaufsseite als einzige Ausnahme (F2b ja) | Hover-Rand der Pills, Verlaufs-Button, Trenner, Suchkante und Block „Verlauf“ auf Cyan → Violett; Verlaufsseite bleibt Violett oben, Cyan unten | Token für den Seitenverlauf, Ausnahme im README, Splash 3 umstellen |
| F3 Runde Formen | (c) alles eckig. Umfang bei Bildmotiven offen, siehe Runde 3 | beige Pill, beiger Button, Social-Kachel eckig; `--radius-bar` und `--radius-soft` entfallen | Splash 3 eckig; README „Radius 0“ bleibt ohne Ausnahme |
| F10 Linkskante | Linkskante bleibt erlaubt und gilt als Standard (Auslegung, zu bestätigen) | Suchtreffer und Zitat behalten die Kante | Regel „keine Linkskante“ wird nicht ins README aufgenommen; Callout behält die Kante (siehe F5) |
| F4 Card | Theme-Karte (Haarlinie, `shadow-sm`, Hover) ist der Standard; die Farbe (Oberkante) ist optional | `.idt-card` bleibt; Farb-Oberkante als Modifier | `soft` wird Standard, `accent` bleibt optional; weitere Varianten offen, siehe Runde 3 |
| F5 Callout | DS-Form (Kante links), aber ohne eigene Hintergrundfarbe: nur die farbige Markierung | Block-Schatten und Tint-Flächen entfallen; Typen cyan/violet/yellow färben nur die Kante | Callout ohne Tint-Fläche, Fläche neutral; Töne und Titel bleiben |
| F6 Kennzahl | wie vorgeschlagen (a): zwei Größen | `.idt-stat--lg` (88px), Farbmodifier, optionale Label-Zeile | `size="md"` (36px) und `"lg"` (88px) |
| F7 Button | wie vorgeschlagen (a bis d) | Disabled-Regel ergänzen; beige und gradient bleiben (beige wird eckig, F3) | Varianten onColor und gradient ergänzen; Hover hebt 2px an, Press 0; Größen dokumentiert; Ghost transparent |
| F8 Tag | wie vorgeschlagen (a) | Textfarbe `--idt-violet-hover`; Literal `#C9A100` durch Token ersetzen | Token für das Gelb-Icon oder Ink |
| F9 Formularfelder | DS-Felder gelten | Input und Checkbox des DS in CSS umsetzen (Fluent Forms, Kommentare, Suche); eigener Folgeauftrag | keine Änderung |
| F11 Kopf- und Fußzeile | Website behält die Theme-Kopfzeile; andere Anwendungen dürfen die durchgehende Kopfzeile des DS nutzen | keine Änderung | beide Formen dokumentieren (Website, Anwendung); `--header-logo-h` als Token; Fußkante offen, siehe Runde 3 |
| F12 Bausteine nur im Theme | Takt-Rhythmus und Diagonal-Akzent entfallen. Marker, Initiale, Pill-Stack und Eyebrow (mit Pfeil) werden ins DS aufgenommen. Übrige Bausteine wie vorgeschlagen | `.idt-takt` und `.idt-diagonal` samt Shortcodes entfernen, Umgang mit bestehendem Inhalt offen | README-Abschnitt „Theme-Bausteine“ ohne Takt und Diagonal |
| F13 Icons | DS-Set gilt | `site-icon.png` ersetzen, Favicon-Set und Manifest ergänzen, `readme.txt` anpassen | keine Änderung |
| F14 Tokens | wie vorgeschlagen (a) | alle DS-Tokens außer Print (Print als Kommentar) | Theme-Tokens aufnehmen, soweit sie bleiben |
| F15 Logo-Regeln | wie vorgeschlagen | keine Änderung | Vermerk im README, beim Verein nachfragen |
| F16 Live-Version | offen | | |
