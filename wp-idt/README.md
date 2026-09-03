# IDT Deutschlandtakt — lokales WordPress

WordPress-Theme im Corporate Design der Initiative Deutschlandtakt („Horizont"-Splash),
inklusive vollständigem Design-Token-System und spielerischen Text-Stilelementen.
Läuft komplett lokal über Docker.

## Starten

```bash
cd wp-idt
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
- **Admin:**   http://localhost:8090/wp-admin — Benutzer `admin`
  (Passwort: seit die Instanz öffentlich ist nicht mehr `admin`,
  siehe `deploy/DEPLOY.md` → „Zugangsdaten der Testseite")

## Öffentliche Testseite

Dieselbe Instanz wird zusätzlich als öffentliche Testseite unter
**https://testseite.example** ausgeliefert (DNS A/AAAA bei
webgo → diese Maschine). Vor WordPress sitzt ein **Caddy**-Container
(`caddy/Caddyfile`) auf Port 80/443: TLS-Terminierung mit automatischem
Let's-Encrypt-Zertifikat (inkl. Erneuerung) und HTTP→HTTPS-Redirect.

Die Site-URL folgt dem Host-Header (Allowlist in `docker-compose.yml` →
`WORDPRESS_CONFIG_EXTRA`), darum funktionieren `localhost:8090` (HTTP, Dev)
und die Domain (HTTPS) parallel. Die URLs in der Datenbank (Inhalte/GUIDs)
zeigen auf die öffentliche Domain.

Härtung, weil öffentlich: eigenes Admin-Passwort (siehe
`deploy/DEPLOY.md` → „Zugangsdaten"), `blog_public=0` (noindex),
`WORDPRESS_DEBUG=0`, `DISALLOW_FILE_EDIT`. In der Hetzner Cloud Firewall
müssen eingehend TCP 80 **und** 443 offen sein.

Hinweis: Die frühere Subdomain `new_t` (Unterstrich) wurde verworfen,
weil Let's Encrypt dafür keine Zertifikate ausstellt.

## Stoppen / Zurücksetzen

```bash
docker compose down            # stoppen, Daten bleiben erhalten
docker compose down -v         # alles zurücksetzen (DB + WP-Core löschen)
```

Nach `down -v` wird beim nächsten `up` alles frisch installiert und neu befüllt.

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
| `[btn href="…" variant="primary"]…[/btn]` | Button — `primary`/`secondary`/`outline`/`gradient` (Violett→Cyan-Rahmen), `arrow="true"` |
| `[concept color="…" icon="…" title="…" href="…"]…[/concept]` | Konzept-Karte; mit `href` klickbar |
| `[cards cols="3"]…[/cards]` | Karten-Raster: legt mehrere Karten in gleiche Breiten/Höhen (`2`/`3`/`4`/`auto`) |
| `[neuigkeiten count="3"]` | dynamische Beiträge-Übersicht als News-Karten |
| `[beitragsliste count="3"]` | dynamische Beitragsliste im Zeilen-Layout (Datum + Chip links, Titel und Anriss rechts) |

### Im Editor bearbeiten statt tippen

Die Shortcodes funktionieren weiter, sind aber **nicht** der empfohlene Weg.
Stattdessen gibt es zwei komfortable Wege — je nachdem, ob das Element ein
**Block** oder eine **Inline-Auszeichnung** ist:

**Block-Stilelemente als native Blöcke** (Lead, Takt, Kennzahl, Button, Pill,
Callout, Diagonal, Karte, Konzept-Karte, Karten-Raster, Einschub, News-Karte,
Beiträge-Übersicht, Beitragsliste): In einem leeren Absatz `/` tippen und nach `dt` oder dem Elementnamen
suchen (z. B. `/Kennzahl`, `/Pill`), oder im Inserter (`+`) unter der Kategorie
**Deutschlandtakt**. Bearbeitet wird **ohne HTML/Shortcode** über Formularfelder
in der Seitenleiste (Text, Auswahl, Schalter, Schieberegler) mit **Live-Vorschau**.
Technisch sind das dynamische Blöcke (`inc/blocks.php` + `assets/blocks.js`), die
serverseitig die bestehenden Shortcode-Funktionen rendern. Fertige **Kompositionen**
(Kennzahlen-Reihe, Konzept-Karten, Themen-Karten, News-Karten, dunkler Einschub …)
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
