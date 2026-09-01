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
| `[neuigkeiten count="3"]` | dynamische Beiträge-Übersicht als News-Karten |

### Im Editor bearbeiten statt tippen

Die Shortcodes funktionieren weiter, sind aber **nicht** der empfohlene Weg.
Stattdessen gibt es zwei komfortable Wege — je nachdem, ob das Element ein
**Block** oder eine **Inline-Auszeichnung** ist:

**Block-Stilelemente als native Blöcke** (Lead, Takt, Kennzahl, Button, Pill,
Callout, Diagonal, Karte, Konzept-Karte, Einschub, News-Karte, Beiträge-
Übersicht): In einem leeren Absatz `/` tippen und nach `dt` oder dem Elementnamen
suchen (z. B. `/Kennzahl`, `/Pill`), oder im Inserter (`+`) unter der Kategorie
**Deutschlandtakt**. Bearbeitet wird **ohne HTML/Shortcode** über Formularfelder
in der Seitenleiste (Text, Auswahl, Schalter, Schieberegler) mit **Live-Vorschau**.
Technisch sind das dynamische Blöcke (`inc/blocks.php` + `assets/blocks.js`), die
serverseitig die bestehenden Shortcode-Funktionen rendern. Fertige **Kompositionen**
(Kennzahlen-Reihe, Konzept-Karten, News-Karten, dunkler Einschub …) liegen im
Inserter unter Tab **Patterns** → **Deutschlandtakt**.

**Inline-Auszeichnungen** (Marker, Eyebrow-Label, Tag): Text markieren und in
der Formatierungsleiste über das **„▾ Weitere"-Menü** anwenden — wie Fett oder
Kursiv. Marker gibt es in Gelb, Cyan und Violett. Diese Formate erzeugen direkt
das fertige HTML (kein Shortcode) und sind dank `add_editor_style` schon im
Editor sichtbar. Registriert in `assets/editor-formats.js`.

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
