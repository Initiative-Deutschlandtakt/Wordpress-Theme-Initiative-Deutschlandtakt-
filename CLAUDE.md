# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A classic (PHP-template) WordPress theme, **`idt-deutschlandtakt`**, in the corporate
design of the Initiative Deutschlandtakt verein — plus the Docker stack that runs it
locally and the notes for deploying it. The theme is the deliverable; everything else
in the repo exists to develop, preview and ship it.

```
theme/idt-deutschlandtakt/   das Theme — der eigentliche Inhalt des Repos
docker-compose.yml           lokaler Stack: MariaDB + WordPress + wp-cli
wp-cli/init.sh               One-shot-Bootstrap (Core installieren, Theme aktivieren)
bin/theme-zip.sh             baut dist/idt-deutschlandtakt-<version>.zip
bin/check-theme.sh           statische Prüfungen (Konventionen, Syntax, Palette)
bin/check-zip.sh             prüft das gebaute Zip auf Upload-Tauglichkeit
bin/smoke-test.sh            frisches WordPress + Theme + Seitenaufrufe
bin/php-symbols.php          Tokenizer-Helfer für bin/check-theme.sh
bin/block-meta.php           prüft die Blöcke mit eigener block.json
bin/knotendreieck-standbild.js  baut blocks/knotendreieck/standbild.svg neu
.github/workflows/           CI je Pull Request, Release beim Tag v<version>
README.md                    Benutzerdoku: starten, Bausteine, Redaktionswege
LICENSE                      GPLv2 — dieselbe Datei liegt auch im Theme (und im Zip)
todo.md                      Änderungsjournal (neuester Eintrag oben unter „Done")
```

There is no build step and no package manager. „Bauen" heißt hier nur: das Theme
als Zip packen (`./bin/theme-zip.sh`) — im Alltag übernimmt das GitHub Actions,
lokal ist es der Fallback.

Geprüft wird zweifach: im Browser gegen den lokalen Stack und über die Skripte,
die auch die CI ausführt. Vor einem Commit lohnt sich

```bash
./bin/check-theme.sh    # Konventionen, PHP-/JS-Syntax, Theme-Header, Palette
./bin/check-zip.sh      # Archivstruktur des Release-Zips
./bin/smoke-test.sh     # frisches WordPress, Theme aktivieren, Seiten abrufen
```

Die ersten beiden brauchen nur `php`, der Smoke-Test zusätzlich eine erreichbare
Datenbank. Jeder Pull Request fährt dieselben Prüfungen (`.github/workflows/ci.yml`)
und legt das gebaute Zip als Artefakt ab. Wer eine der vier Konventionen unten
ändert, ändert `bin/check-theme.sh` mit — dort sind sie festgeschrieben.

## Entwickeln

```bash
docker compose up -d          # http://localhost:8090, Admin unter /wp-admin
docker compose logs -f cli    # Fortschritt des ersten Bootstraps
```

`theme/idt-deutschlandtakt/` ist live in den Container gemountet — Datei speichern,
Browser neu laden. Ausnahme sind die Demo-Inhalte: sie werden nur einmal geseedet
(Option `idt_seeded`); zum Neu-Befüllen siehe README, Abschnitt „Demo-Inhalte neu
erzeugen".

## Vier Konventionen, die beim Ändern zählen

### 1. Version an zwei Stellen synchron halten
Der Theme-Header in `style.css` (`Version:`) und die Konstante `IDT_VERSION` in
`functions.php` müssen denselben Wert tragen. `IDT_VERSION` hängt am Cache-Busting
aller Assets — läuft sie dem Header hinterher, sehen Besucher altes CSS. `bin/theme-zip.sh`
liest die Nummer aus dem Header, das Zip erbt sie also.

### 2. Ein Baustein, drei Oberflächen — eine Render-Funktion
Jedes Gestaltungselement existiert als **Shortcode** (`inc/shortcodes.php`), als
**Block** im Inserter (`inc/blocks.php` + `assets/blocks.js`) und teils in fertigen
**Patterns** (`inc/patterns.php`). Die Blöcke sind *dynamisch*: sie registrieren nur
Attribute und Sidebar-Felder und rufen serverseitig dieselbe `idt_sc_*()`- bzw.
`idt_render_*()`-Funktion auf wie der Shortcode. Neue Bausteine deshalb immer so
anlegen — erst die Render-Funktion in `inc/shortcodes.php`, dann Shortcode und Block
darauf zeigen lassen. Zwei Render-Pfade für dasselbe Element laufen unweigerlich
auseinander (siehe die entsprechenden Einträge in `todo.md`).

Redaktion soll Bausteine über Sidebar-Felder bearbeiten, nicht über getippte
Shortcodes; die Shortcode-Syntax bleibt nur als Fallback bestehen.

Eine Ausnahme gibt es bei der *Oberfläche*, nicht beim Rendern: Bausteine, deren
Vorschau nicht Markup ist, sondern etwas Laufendes, passen nicht zu
`ServerSideRender` — das fordert bei jedem Tastendruck neues HTML an und setzt
die Bewegung dabei zurück. Solche Bausteine liegen in
`theme/idt-deutschlandtakt/blocks/<name>/` mit eigener `block.json` und eigener
`editor.js` (ebenfalls build-frei). Ihr Frontend-Markup kommt trotzdem aus
derselben `idt_render_*()`-Funktion in `inc/shortcodes.php` wie der Shortcode —
über einen `render_callback` beim `register_block_type()` in `inc/blocks.php`,
nicht über das Feld `render` der `block.json`: Das wertet WordPress erst ab 6.1
aus, der Theme-Header verspricht 6.0. Bisher betrifft das
`blocks/knotendreieck/` — die bewegte Grafik zum Knotenprinzip.
`bin/block-meta.php` prüft für diese Blöcke Namensraum, Textdomain, die
render-Datei, die Skript-Handles und dass die Attribut-Vorgaben in `block.json`
zu `idt_<name>_defaults()` passen.

### 3. Alles trägt einen `idt`-Präfix
PHP-Funktionen `idt_*`, Blöcke `idt/*`, CSS-Klassen `.idt-*`, Optionen und
Customizer-Einstellungen `idt_*`. WordPress-Themes teilen sich einen globalen
Namensraum mit jedem Plugin — der Präfix ist die einzige Trennung. JavaScript in
`assets/*.js` wird in eine IIFE gewickelt, damit nichts ins globale Scope leakt.

### 4. Design-Tokens statt Literalwerte
`style.css` Abschnitt 2 definiert das vollständige Markensystem als Custom Properties
auf `:root`: Markenfarben (inkl. CMYK-Referenz als Doku), Neutralrampe, semantische
Aliasse (`--surface`, `--text-*`, `--accent-*`), Typo-Skala, Abstände (4px-Basis),
Radien, Schatten, Motion, z-Index. Beim Erweitern diese Variablen nutzen. Die
Editor-Palette liegt in `idt_brand_palette()` (`functions.php`) — eine Quelle für
`editor-color-palette` und die Farbwähler der eigenen Blöcke; wer dort eine Farbe
ergänzt, ergänzt auch die passende Frontend-Klasse in Abschnitt 7c.

Das Stylesheet ist nach nummerierten Abschnitten gegliedert (1 Web-Fonts … 9 Footer).
Neue Regeln gehören in den thematisch passenden Abschnitt, nicht ans Dateiende.

## Die Splash-Bühne

`idt_render_splash()` (Shortcode `[splash]`) ist eine feste **1280×800**-Canvas,
zentriert in einer Bühne, die `assets/scale.js` per `--s` auf den Viewport skaliert.
Alle Positionen darin — die Diagonalen, das Logo, die Link-Pills — sind absolute
Pixelkoordinaten in diesem Koordinatensystem; die Geometriekommentare in `style.css`
Abschnitt 6 erklären die von Hand gerechneten Offsets am Horizontschnitt (`top: 465px`).
Im Editor ersetzt `assets/editor.css` das Skalierungs-Script durch eine CSS-Treppe,
weil Vorschaurahmen keine brauchbare Viewport-Höhe haben.

`idt_render_splash2()` (`[splash2]`, style.css 6b) ist die fluide Alternative ohne
Fixmaß und ohne Script — Flexbox, passt sich jedem Seitenverhältnis an. Beide
Varianten sind gleichwertig; pro Seite wird eine gewählt.

## Sprache

Quellkommentare, UI-Texte und die Dokumentation sind **auf Deutsch**. Beim Ändern von
Copy oder beim Ergänzen von Kommentaren dabei bleiben. Wesentliche Änderungen kommen
als neuer Eintrag oben unter „Done" in `todo.md` — dieses Journal ist die
Änderungsgeschichte des Themes.
