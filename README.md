# IDT Deutschlandtakt — lokales WordPress

WordPress-Theme im Corporate Design der Initiative Deutschlandtakt („Horizont"-Splash),
inklusive vollständigem Design-Token-System und spielerischen Text-Stilelementen.
Läuft komplett lokal über Docker.

## Aufbau des Repos

Der Inhalt des Repos ist das Theme; alles andere dient dazu, es lokal laufen zu
lassen und auszuliefern.

| Pfad | Inhalt |
|---|---|
| `theme/idt-deutschlandtakt/` | das Theme |
| `docker-compose.yml`, `wp-cli/init.sh` | lokaler Stack (MariaDB, WordPress, wp-cli) |
| `bin/theme-zip.sh` | baut das Release-Zip aus dem Theme |
| `bin/check-theme.sh`, `bin/check-zip.sh`, `bin/smoke-test.sh` | die Prüfungen, die auch in der CI laufen |
| `.github/workflows/` | CI für jeden Pull Request, Release beim Setzen eines Tags |
| `todo.md` | Änderungsjournal (neuester Eintrag oben) |

## Starten

```bash
docker compose up -d
```

Beim ersten Start installiert der `cli`-Container WordPress automatisch, aktiviert
das Theme und legt die Demo-Inhalte (Seiten, Beiträge, Menü) an. Das dauert ~1 Minute.
Fortschritt ansehen:

```bash
docker compose logs -f cli
```

Sobald „IDT Deutschlandtakt is ready" erscheint:

- **Website:** http://localhost:8090
- **Admin:**   http://localhost:8090/wp-admin — `admin` / `admin`
  (Wegwerf-Zugang des lokalen Containers)

## Stoppen / Zurücksetzen

```bash
docker compose down            # stoppen, Daten bleiben erhalten
docker compose down -v         # alles zurücksetzen (DB + WP-Core löschen)
```

Nach `down -v` wird beim nächsten `up` alles frisch installiert und neu befüllt.

## Theme-Zip beziehen

Das Zip für den Upload unter *Design → Themes → Hinzufügen → Theme hochladen*
entsteht in GitHub Actions — es muss niemand lokal bauen.

**Für eine Veröffentlichung** die Version in `style.css` und `functions.php`
hochzählen, mergen, dann einen Tag setzen:

```bash
git tag v2.0.22 && git push origin v2.0.22
```

Der Release-Workflow prüft, ob der Tag zur Theme-Version passt, baut das Zip und
hängt es an ein GitHub-Release — Download unter *Releases* im Repository.

**Für einen Zwischenstand** genügt der Pull Request: Jeder CI-Lauf legt das Zip
als Artefakt „idt-deutschlandtakt-zip" ab (30 Tage abrufbar, unten auf der Seite
des Workflow-Laufs). Ohne offenen Pull Request tut es *Actions → Release → Run
workflow*.

**Lokal**, wenn es schnell gehen muss:

```bash
./bin/theme-zip.sh
```

Das Skript liest die Versionsnummer aus dem Theme-Header (`style.css`) und legt
`dist/idt-deutschlandtakt-<version>.zip` an — mit dem Ordner `idt-deutschlandtakt/`
an der Wurzel des Archivs, genau so, wie WordPress es erwartet. `dist/` ist nicht
versioniert: Das Zip ist ein Erzeugnis und wird bei Bedarf neu gebaut.

## Prüfungen

Jeder Pull Request durchläuft `.github/workflows/ci.yml`. Dieselben Prüfungen
laufen lokal, ohne Installation:

```bash
./bin/check-theme.sh    # Konventionen, PHP-/JS-Syntax, Theme-Header, Palette
./bin/check-zip.sh      # baut das Zip und prüft die Archivstruktur
./bin/smoke-test.sh     # frisches WordPress, Theme aktivieren, Seiten abrufen
```

| Job | Was er prüft |
|---|---|
| Konventionen & Syntax | die vier Konventionen aus `CLAUDE.md` (Version an zwei Stellen synchron, jeder Block zeigt auf eine existierende Render-Funktion, `idt`-Präfix, Editor-Palette mit Token und Frontend-Klasse) sowie PHP-Syntax auf 8.0/8.2/8.3, JS-Syntax, Direktzugriffsschutz, Debug-Reste |
| Shell-Skripte & Compose-Datei | ShellCheck über `bin/*.sh` und `wp-cli/init.sh`, `docker compose config` |
| Release-Zip | Archivwurzel, Pflichtdateien, keine `.git`/`.DS_Store`, Version im Archiv — und legt das Zip als Artefakt ab |
| WordPress-Smoke-Test | installiert WordPress gegen MariaDB, aktiviert das Theme, seedet die Demo-Inhalte, rendert alle Blöcke, Shortcodes und Patterns und ruft Startseite, Seite, Beitrag, Suche, 404 und Login ab — nichts darf eine PHP-Meldung erzeugen |

`bin/smoke-test.sh` braucht eine erreichbare Datenbank; die Zugänge kommen aus
`IDT_DB_HOST`, `IDT_DB_NAME`, `IDT_DB_USER` und `IDT_DB_PASS` (Standard passt zum
lokalen Docker-Stack).

Damit ein Pull Request ohne grüne Prüfungen nicht gemergt werden kann, müssen die
Jobs einmalig unter *Settings → Branches → Branch protection rules* für `main` als
*Required status checks* eingetragen werden.

## Theme bearbeiten

Das Theme liegt unter `theme/idt-deutschlandtakt/` und ist live in den Container
gemountet — Datei speichern, Browser neu laden. (CSS-/HTML-Änderungen sofort
sichtbar; Änderungen an den Demo-Inhalten in `inc/demo-content.php` greifen nur
beim erstmaligen Seeden, siehe unten.)

### Demo-Inhalte neu erzeugen

Die Inhalte werden nur einmal angelegt (abgesichert über die Option `idt_seeded`).
Zum Neu-Befüllen:

```bash
docker compose run --rm cli wp option delete idt_seeded
docker compose run --rm cli wp eval 'idt_seed_demo_content();'
```

## Spielerische Stilelemente

Im Editor direkt im Text nutzbar (siehe Seite **Stilelemente** im Menü):

| Shortcode | Wirkung |
|---|---|
| `[eyebrow]Label[/eyebrow]` | Mono-Label über einer Überschrift |
| `[mark]Text[/mark]` | Texthighlight (Gelb; `color="cyan"`/`"violet"`) |
| `[lead]Text[/lead]` | hervorgehobener Einleitungsabsatz |
| `[takt count="8"]` | dekorativer Takt-Rhythmus |
| `[stat number="2008" label="gegründet"]` | große Kennzahl mit Label |
| `[pill href="…"]Text[/pill]` | Pill-Button (`style="solid"` / `style="violet"`) |
| `[callout type="cyan"]…[/callout]` | Hinweisbox (`cyan`/`violet`/`yellow`) |
| `[diagonal]…[/diagonal]` | Aussageblock mit durchgehenden Horizont-Diagonalen |
| `[card]…[/card]` | Karte mit Rahmen und Schatten |
| `[btn href="…" variant="primary"]…[/btn]` | Button — `primary`/`secondary`/`outline`/`gradient` (Violett→Cyan-Rahmen), `arrow="true"`; `bg=""` setzt die Fläche frei (s. u.) |
| `[concept color="…" icon="…" title="…" href="…"]…[/concept]` | Konzept-Karte; mit `href` klickbar; `bg=""` setzt die Fläche frei (s. u.) |
| `[cards cols="3"]…[/cards]` | Karten-Raster: legt mehrere Karten in gleiche Breiten/Höhen (`2`/`3`/`4`/`auto`) |
| `[neuigkeiten count="3"]` | dynamische Beiträge-Übersicht als News-Karten |
| `[themenblock bg="ink" title="…"]…[/themenblock]` | farbige Fläche mit Eyebrow, Überschrift, Texten und beliebig langer Linkliste; `bg` nimmt Markennamen (`ink`, `violet`, `cyan`, `paper` …) oder einen Hex-Wert |
| `[buttonstack bg="ink"]…[/buttonstack]` | Button-Stack: derselbe Baustein nur mit den Linkzeilen — ohne Eyebrow, Überschrift und Vortext; `bg` wie beim Themenblock |
| `[beitragsliste count="3"]` | dynamische Beitragsliste im Zeilen-Layout (Datum + Chip links, Titel und Anriss rechts) |

### Im Editor bearbeiten statt tippen

Die Shortcodes funktionieren weiter, sind aber **nicht** der empfohlene Weg.
Stattdessen gibt es zwei komfortable Wege — je nachdem, ob das Element ein
**Block** oder eine **Inline-Auszeichnung** ist:

**Block-Stilelemente als native Blöcke** (Lead, Takt, Kennzahl, Button, Pill,
Callout, Diagonal, Karte, Konzept-Karte, Karten-Raster, Einschub, Themenblock,
Button-Stack, News-Karte, Social-Leiste, Beiträge-Übersicht, Beitragsliste): In einem leeren Absatz `/` tippen und nach `dt` oder dem Elementnamen
suchen (z. B. `/Kennzahl`, `/Pill`), oder im Inserter (`+`) unter der Kategorie
**Deutschlandtakt**. Bearbeitet wird **ohne HTML/Shortcode** über Formularfelder
in der Seitenleiste (Text, Auswahl, Schalter, Schieberegler) mit **Live-Vorschau**.
Technisch sind das dynamische Blöcke (`inc/blocks.php` + `assets/blocks.js`), die
serverseitig die bestehenden Shortcode-Funktionen rendern. Fertige **Kompositionen**
(Kennzahlen-Reihe, Konzept-Karten, Themen-Karten, News-Karten, dunkler Einschub,
Themenblock mit Linkliste …)
liegen im Inserter unter Tab **Patterns** → **Deutschlandtakt**.

**Inline-Auszeichnungen** (Marker, Eyebrow-Label, Tag): Text markieren und in
der Formatierungsleiste über das **„▾ Weitere"-Menü** anwenden — wie Fett oder
Kursiv. Marker gibt es in Gelb, Cyan und Violett. Diese Formate erzeugen direkt
das fertige HTML (kein Shortcode) und sind dank `add_editor_style` schon im
Editor sichtbar. Registriert in `assets/editor-formats.js`.

### Mehrere Karten nebeneinander: das Karten-Raster

Einzeln eingefügte Karten behalten jede ihre eigene Breite und Höhe — nebeneinander
gestellt wirken sie dadurch ungleich. Für Karten-Reihen gibt es deshalb den Block
**Karten-Raster** (Inserter → **Deutschlandtakt**, oder `/Raster`): ein Container,
in den die Karten mit `+` eingesetzt werden. Alle Karten darin sind gleich breit
und gleich hoch, das Raster bricht auf schmalen Bildschirmen automatisch um
(Desktop 2/3/4 Spalten wählbar, Tablet zwei, Handy eine). Der frühere Weg über
den **Spalten**-Block ist dafür nicht mehr nötig.

Als Shortcode entspricht das `[cards cols="3"] … [/cards]` um die Karten herum.
Fertig bestückt gibt es das Raster als Patterns **Konzept-Karten** (drei Karten
mit Icon und farbiger Oberkante) und **Themen-Karten** (vier Einstiegskarten
ohne Icon).

Damit im klassischen Editor kein leerer Absatz um das Raster entsteht, sollte
der Shortcode allein in seinem Shortcode-Block stehen. Im Block-Editor stellt
sich die Frage nicht — dort wird der Inhalt nicht durch `wpautop` geschickt.

### Beitragsliste („Aktuelles")

Die Beitragsübersicht (Menüpunkt **Aktuelles**, in WordPress die unter
*Einstellungen → Lesen* gesetzte Beitragsseite) zeigt die Beiträge als ruhige
Liste statt als Karten: pro Beitrag eine Zeile mit **Datum und Chip links**,
**Titel und Anriss rechts**, dazwischen Haarlinien; über der Liste eine kräftige
Linie. Dieselbe Darstellung tragen die Schlagwort- und Kategorie-Archive.

Welche Chips erscheinen, richtet sich nach der Pflegepraxis: Hat ein Beitrag
**Kategorien** (z. B. „Position", „Pressemitteilung", „Verein"), stehen diese im
Chip — sonst die **Schlagwörter**. Die Farbe eines Chips hängt am Begriff, ein
Begriff sieht also überall im Auftritt gleich aus. Ein gesetztes **Beitragsbild**
erscheint klein in der linken Spalte unter Datum und Chip; ohne Bild bleibt die
Zeile rein textlich.

Als Abschnitt für beliebige Seiten (z. B. die Startseite) gibt es dieselbe Liste
als Block **Beitragsliste (Zeilen)** bzw. Shortcode
`[beitragsliste count="3" title="Aktuelles" more="Alle Beiträge"]` — dann mit
Kopfzeile links und dem Link **Alle Beiträge →** rechts, der auf die
Beitragsseite führt. Eingrenzen lässt sich die Auswahl wie bei `[neuigkeiten]`
über `tag=""`/`category=""` (Slugs, kommagetrennt); `chips="category|tag|none"`
bestimmt die Chips, `more=""` blendet den Link aus.

### Buttons

Im Editor wird bewusst nur das **Marken-Button-Set** angeboten: Primary (Violett),
Sekundär (Cyan), Outline und Gradient-Rahmen (Violett→Cyan). Der WordPress-
Standard-Button-Block ist im Editor ausgeblendet (`editor-formats.js`,
reversibel). Die Varianten `ghost`/`inverse` existieren weiter per CSS für
Sonderfälle (z. B. dunkle Flächen), werden aber nicht als Auswahl angeboten.

### Hintergrundfläche von Button und Konzept-Karte

Button und Konzept-Karte haben in der Seitenleiste das Feld **Hintergrund**.
Es überschreibt die Fläche, die der gewählte Stil bzw. das Papier vorgibt:

| Auswahl | Fläche |
|---|---|
| *Standard (aus dem Stil)* | wie bisher — Violett/Cyan beim Button, Papier bei der Karte |
| *Verlauf Cyan → Violett* | Markenverlauf, links Cyan, rechts Violett |
| *Verlauf Violett → Cyan* | derselbe Verlauf in Gegenrichtung |
| Markenfarben | Violett, Cyan, Gelb, Ink, Papier 1–3, die drei hellen Töne, Grau |

Als Shortcode heißt das Feld `bg` und nimmt zusätzlich einen freien Hex-Wert:
`[btn href="/mitmachen/" bg="cyan-violet"]Mitglied werden[/btn]`,
`[concept bg="#E8E3FF" title="Die Idee" href="/idee/"]…[/concept]`.

Die Schriftfarbe wird nicht eingestellt, sondern gerechnet: Das Theme vergleicht
den Kontrast der Fläche zu Papier und zu Ink und nimmt den besseren — bei
Verläufen gemessen am schwächeren Ende, damit die Schrift über den ganzen
Verlauf lesbar bleibt. Beim Verlauf Cyan→Violett gewinnt so die dunkle Schrift.

### Trenner (Trennelement)

Der WordPress-Block **Trenner** ist im Markenlook gestaltet und hat zwei eigene
Stile (Block markieren → Seitenleiste **Stile**):

| Stil | Wirkung |
|---|---|
| *(ohne)* | feine Linie im Rahmenton |
| **Verlauf (Violett → Cyan)** | 4 px starker Strich im Markenverlauf über die volle Textbreite |
| **Kurzer Strich** | kurze Akzentmarke (56 px) am linken Textrand — der Zwischenstrich zwischen zwei Abschnitten |

Wird im Farbbereich des Blocks stattdessen von Hand eine Farbe oder ein Verlauf
gewählt, gewinnt diese Wahl. Wichtig dabei: WordPress legt Farbe und Verlauf
beim Trenner als *Hintergrund* ab, zeichnet den Strich selbst aber als Rahmen —
ohne die Regeln in `style.css` (Abschnitt 7d) bleibt ein gewählter Verlauf
deshalb unsichtbar und der Trenner grau. Registriert sind die Stile in
`inc/blocks.php` (`idt_register_core_block_styles()`).

## Logo im Kopfmenü

Das Bild links oben im Menüband ist ohne Code-Änderung austauschbar und einstellbar —
beides unter **Design → Customizer → Website-Identität**:

| Einstellung | Wirkung |
|---|---|
| **Logo** | Bild aus der Mediathek (WordPress-Standard „Custom Logo"). Ohne eigenes Logo zeigt das Theme das mitgelieferte `assets/logo-idt-transparent.png`. |
| **Logo-Höhe im Kopfmenü (px)** | Darstellungshöhe im Menüband, 20–120 px, Vorgabe 38. Die Breite ergibt sich aus dem Seitenverhältnis. |

Die Höhe wird als CSS-Token `--header-logo-h` gesetzt (Vorgabe in `style.css`, abweichende
Werte als Inline-CSS über `idt_header_logo_css()` in `functions.php`) und im Customizer
dank `assets/customize-preview.js` sofort in der Vorschau sichtbar.

Zwei Sonderfälle bleiben davon unberührt: Der dunkle Footer nutzt weiterhin fest die
Inverse-Variante (`assets/logo-idt-inverse.png`), und die Splash-Bühne bringt ihr Logo
selbst mit — Seiten mit der Vorlage **„Menü ohne Logo"** blenden den Marken-Block im
Kopfmenü deshalb ganz aus (das Menü bleibt).

## Startseite

Die Homepage wird **nicht** mehr fest in `front-page.php` verdrahtet: Die Vorlage
rendert den Editor-Inhalt der Seite **„Startseite"** (`the_content()`), die mit dem
Design als Blöcke/Shortcodes befüllt ist (siehe `idt_content_startseite()` in
`inc/demo-content.php`). Dadurch ist die Startseite vollständig im Block-Editor
bearbeitbar. Die Beiträge-Reihe unten ist das dynamische `[neuigkeiten]`-Element.

## Suche

In der Kopfleiste steht rechts nur eine **Lupe**. Ein Klick öffnet die Suche als
Ebene **über der ganzen Seite** (Markup: `idt_render_search_overlay()` in
`theme/idt-deutschlandtakt/inc/search.php`, Verhalten: `assets/search.js`).
Schon während des Tippens erscheinen die Treffer als Karten; Schließen per ×,
`Esc` oder Klick daneben.

Die Vorschau holt ihre Treffer vom REST-Endpunkt **`/wp-json/idt/v1/suche?q=…`**
(öffentlich lesbar, nur veröffentlichte Beiträge und Seiten). Er liefert bereits
gerendertes Markup — dieselben Treffer-Karten (`.idt-result`), die auch die
Ergebnisseite `search.php` nutzt, sodass Vorschau und Ergebnisseite nicht
auseinanderlaufen können. Die Eingabetaste führt jederzeit auf die vollständige
Ergebnisseite mit Trefferzahl, Suchfeld zum Nachschärfen und Blätterfunktion.

Ohne JavaScript verlinkt die Lupe auf `#idt-searchbox`; eine `:target`-Regel in
`style.css` klappt das Overlay auch dann auf, das Formular darin führt regulär
zur Ergebnisseite. Das kompakte `searchform.php` bleibt für alles erhalten, was
WordPress selbst ausgibt (Widgets, Suchblock).

## Lizenz

Der Code des Themes steht unter der **GNU General Public License v2 oder später**
(`LICENSE`), wie es für WordPress-Themes vorgesehen ist. Dieselbe Datei liegt in
`theme/idt-deutschlandtakt/` und wandert damit in jedes gebaute Zip; die Angaben
im Theme-Header (`style.css`) und in `theme/idt-deutschlandtakt/readme.txt` sagen
dasselbe.

Zwei Bestandteile folgen eigenen Bedingungen:

| Bestandteil | Bedingung |
|---|---|
| Schrift **Inter** (`assets/fonts/InterVariable*.woff2`) | SIL Open Font License 1.1 — Lizenztext liegt als `assets/fonts/OFL.txt` bei und muss bei Weitergabe mitgeliefert werden |
| **Logo, Wortmarke, Bildmarken** der Initiative Deutschlandtakt (`assets/logo-idt-*.png`, `assets/site-icon.png`, `screenshot.png`) | nicht von der GPL erfasst; wer das Theme weiterverwendet, ersetzt sie durch eigene Grafiken (Einzelheiten in `theme/idt-deutschlandtakt/readme.txt`) |
