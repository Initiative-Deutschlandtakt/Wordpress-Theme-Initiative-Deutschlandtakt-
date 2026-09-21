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
| `bin/knotendreieck-standbild.js` | baut das Standbild der Knotendreieck-Grafik neu (Node) |
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

**Für einen Zwischenstand** genügt der Pull Request: Jeder CI-Lauf legt das
Theme als Artefakt „idt-deutschlandtakt-<version>" ab (30 Tage abrufbar, unten
auf der Seite des Workflow-Laufs). Ohne offenen Pull Request tut es
*Actions → Release → Run workflow*.

Der Download ist direkt hochladefertig — **nicht vorher entpacken.** Dahinter
steckt eine Eigenheit von GitHub: Ein Artefakt wird beim Herunterladen immer neu
in ein Zip gepackt. Läge dort ein fertiges Zip, käme es als Zip-im-Zip an, und
der Theme-Upload lehnte es mit *„Dem Theme fehlt das Stylesheet style.css"* ab —
in der Archivwurzel stünde ja nur ein weiteres Zip. Die CI lädt deshalb den
*Inhalt* des geprüften Archivs hoch; GitHubs Verpacken stellt daraus beim
Herunterladen wieder genau das Zip her, das WordPress erwartet. Dass das so
bleibt, prüft der Job „Release-Zip" selbst: Er lädt sein eigenes Artefakt zurück
und bricht ab, wenn darin `idt-deutschlandtakt/style.css` fehlt.

Das **Release-Asset** unter *Releases* ist davon nicht betroffen: Es wird als
Datei ausgeliefert und nicht neu verpackt.

**Das Zip nicht auspacken und neu packen.** Das klingt selbstverständlich,
passiert unter macOS aber von allein: Safari entpackt geladene Archive, wenn
„Sichere Dateien nach dem Laden öffnen" aktiv ist, und wer den Ordner danach
im Finder wieder komprimiert, bekommt ein Archiv mit `__MACOSX`-Beiwerk und
einem Ordner, der `idt-deutschlandtakt 2` oder `idt-deutschlandtakt 4` heißt —
die Zählung, die macOS an mehrfach geladene Dateien hängt. WordPress benennt
das Themeverzeichnis nach diesem Ordner und legt damit ein **zweites,
eigenständiges Theme** an, das in der Liste genauso heißt wie das alte. Wer
dann nicht umschaltet, arbeitet weiter mit der alten Fassung und wundert sich,
wo die neuen Bausteine sind.

Symptom: Unter *Design → Themes* stehen mehrere Kacheln „IDT Deutschlandtakt".
Abhilfe: Die überzähligen löschen (die aktive lässt sich nicht löschen, notfalls
kurz ein anderes Theme aktivieren) und das unveränderte Zip hochladen —
WordPress erkennt `idt-deutschlandtakt` dann als vorhanden und bietet
*„Das Vorhandene durch das Hochgeladene ersetzen"* an. Nur so bleibt der
Aktualisierungspfad heil.

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
| `[pill href="…"]Text[/pill]` | Pill-Button (`style="solid"` / `style="violet"` / `style="beige"`) |
| `[callout type="cyan"]…[/callout]` | Hinweisbox (`cyan`/`violet`/`yellow`) |
| `[diagonal]…[/diagonal]` | Aussageblock mit durchgehenden Horizont-Diagonalen |
| `[card]…[/card]` | Karte mit Rahmen und Schatten |
| `[btn href="…" variant="primary"]…[/btn]` | Button — `primary`/`secondary`/`outline`/`gradient` (Violett→Cyan-Rahmen)/`beige`, `arrow="true"`; `bg=""` setzt die Fläche frei (s. u.) |
| `[concept color="…" icon="…" title="…" href="…"]…[/concept]` | Konzept-Karte; mit `href` klickbar; `bg=""` setzt die Fläche frei (s. u.) |
| `[cards cols="3"]…[/cards]` | Karten-Raster: legt mehrere Karten in gleiche Breiten/Höhen (`2`/`3`/`4`/`auto`) |
| `[neuigkeiten count="3"]` | dynamische Beiträge-Übersicht als News-Karten |
| `[themenblock bg="ink" title="…"]…[/themenblock]` | farbige Fläche mit Eyebrow, Überschrift, Texten und beliebig langer Linkliste; `bg` nimmt Markennamen (`ink`, `violet`, `cyan`, `paper` …) oder einen Hex-Wert |
| `[buttonstack bg="ink"]…[/buttonstack]` | Button-Stack: derselbe Baustein nur mit den Linkzeilen — ohne Eyebrow, Überschrift und Vortext; `bg` wie beim Themenblock |
| `[beitragsliste count="3"]` | dynamische Beitragsliste im Zeilen-Layout (Datum + Chip links, Titel und Anriss rechts) |
| `[socialrow style="beige" align="center"]…[/socialrow]` | Social-Leiste, eine Zeile je Icon („Plattform \| Link"); Plattformen: `x`, `facebook`, `instagram`, `linkedin`, `youtube`, `mastodon`, `bluesky`, `rss`, `mail` (dort statt der URL die Adresse — die Kachel wird zum `mailto:`-Link); `style="beige"` für farbige Flächen |
| `[email]adresse@example.org[/email]` | Mail-Link: baut aus der Adresse den `mailto:`-Link und zeigt sie an; `address="…"` plus Inhalt für eine eigene Beschriftung, `subject="…"` belegt die Betreffzeile vor, `icon="false"` lässt das Briefzeichen weg |
| `[logo size="120" color="paper"]` | Logo-Sperrsatz: dreizeilige Wortmarke, rechts daneben das Signet (s. u.) |

### Im Editor bearbeiten statt tippen

Die Shortcodes funktionieren weiter, sind aber **nicht** der empfohlene Weg.
Stattdessen gibt es zwei komfortable Wege — je nachdem, ob das Element ein
**Block** oder eine **Inline-Auszeichnung** ist:

**Block-Stilelemente als native Blöcke** (Lead, Takt, Kennzahl, Button, Pill,
Callout, Diagonal, Karte, Konzept-Karte, Karten-Raster, Einschub, Themenblock,
Button-Stack, News-Karte, Social-Leiste, Mail-Link, Beiträge-Übersicht, Beitragsliste): In einem leeren Absatz `/` tippen und nach `dt` oder dem Elementnamen
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

### Mail-Link (Kontaktangaben)

Für Kontaktwege in Impressum, Kontaktseite oder Fließtext gibt es den Baustein
**Mail-Link** — Block *Mail-Link* im Inserter (Kategorie *Deutschlandtakt*) bzw.
`[email]…[/email]`. Er baut aus der Adresse den `mailto:`-Link, den man sonst von
Hand als HTML einsetzen müsste:

```
Post: Initiative Deutschlandtakt e. V., Schützengasse 18, 01067 Dresden
Mail: [email]kontakt@initiative-deutschlandtakt.de[/email]
```

| Feld / Attribut | Wirkung |
|---|---|
| **E-Mail-Adresse** (`address`) | die Adresse; im Shortcode darf sie auch einfach zwischen den Klammern stehen |
| **Beschriftung** (Inhalt) | leer = die Adresse wird angezeigt; sonst der eingegebene Text (z. B. „Schreib uns") |
| **Betreff vorbelegen** (`subject`) | füllt die Betreffzeile im Mailprogramm des Besuchers vor |
| **Briefzeichen anzeigen** (`icon="false"`) | Schalter für das Briefumschlag-Zeichen vor der Adresse — aus, wenn der Link mitten im Satz steht |

`[mail]…[/mail]` ist dieselbe Schreibweise mit kürzerem Namen. Die Adresse steht
im Quelltext nur als HTML-Entities (`antispambot()`), sodass Adress-Sammler dort
kein zusammenhängendes `name@domain` finden; im Browser sieht und klickt man die
normale Adresse. Soll der Kontaktweg eine Schaltfläche sein statt ein Link im
Text, nimmt auch der Button eine Mailadresse:
`[btn href="mailto:kontakt@…" variant="primary"]Schreib uns[/btn]`.

### Bildunterschriften

Bilder im Text kommen aus dem WordPress-Bildblock; eine eigene Fassung braucht
das Theme dafür nicht. Die Beschreibung schreibt man in das Feld **Bildunterschrift**
direkt unter dem Bild im Editor — daraus wird ein `figcaption` innerhalb der
Abbildung, also Text, der markierbar und durchsuchbar bleibt und den
Screenreader im Zusammenhang mit dem Bild vorliest.

Das Theme setzt diese Zeile **kursiv**, eine Stufe kleiner und in gedämpftem
Grau, links auf die Textkante, mit engem Abstand zum Bild: So gehört sie sichtbar
zum Bild und wird nicht als Anfang des nächsten Absatzes gelesen. Für die
Bildunterschrift des Knotendreiecks gilt dieselbe Optik, dort nur mittig unter
der mittigen Grafik. Geregelt ist das in `style.css` Abschnitt 8; wer es ändert,
ändert es dort einmal für alle Bausteine.

Davon zu unterscheiden ist der **Alternativtext** in der Mediathek: Er beschreibt
das Bild für alle, die es nicht sehen, und *ersetzt* es — er erscheint nicht auf
der Seite. Beide Felder gehören gefüllt, mit verschiedenen Texten: Der
Alternativtext sagt, was zu sehen ist, die Bildunterschrift sagt, was es bedeutet.

### Knotendreieck (bewegte Grafik zum Knotenprinzip)

Der Block **Knotendreieck** (Inserter, Kategorie *Deutschlandtakt*) zeigt drei
Knotenbahnhöfe, drei Linien und die Züge, die sich zur Minute :00 und :30 in
jedem Knoten treffen. Fertig zusammengesetzt mit Dachzeile, Überschrift und
Einleitung gibt es ihn als Vorlage **„Knotenprinzip erklärt"**.

In der Seitenleiste stehen zwei Bereiche:

| Bereich | Felder |
|---|---|
| **Beschriftung** | Überschrift im Bild, Zeile unten links, die drei Knotennamen, Bildunterschrift |
| **Bewegung** | Dauer eines Stundenzyklus (8–24 Sekunden), Autostart, Fahrzeuge als Striche oder Punkte |

Die Überschrift sitzt bewusst **im** Bild: So bleibt sie dabei, wenn jemand die
Grafik in einen Vortrag oder in soziale Medien zieht. Ab etwa zwanzig Zeichen
verkleinert sie sich automatisch, statt aus dem Rahmen zu laufen; dasselbe gilt
für lange Knotennamen. Die Bildunterschrift steht dagegen als `figcaption`
**unter** der Grafik und bleibt damit durchsuchbar und für Screenreader lesbar.

Im Editor läuft die Animation nicht von selbst — sonst zappelt beim Schreiben
die halbe Seite; der Play-Knopf unter der Grafik zeigt sie. Auf der Website
startet sie, sobald die Grafik im Sichtfeld steht. Weiter gilt:

* Play-/Pause-Knopf, Pfeiltasten (mit Umschalt in Fünf-Minuten-Schritten),
  Pos1, Ende und Leertaste bedienen den Zeitregler.
* Sobald jemand den Regler anfasst, stoppt der Lauf und startet nicht von
  selbst wieder.
* Bei systemweit reduzierter Bewegung läuft nichts an, die Grafik steht auf
  Minute :30. Vor dem Drucken friert sie ebenfalls dort ein.
* Ohne JavaScript erscheint statt der Grafik das Standbild
  `blocks/knotendreieck/standbild.svg`.

**Nicht einstellbar sind die Fahrzeiten** 28 / 28 / 57 Minuten und die
Knotenfenster :28–:32 und :58–:02 — sie hängen zusammen. Eine frei geänderte
Minutenzahl würde nur die Beschriftung ändern, nicht den Fahrplan; das Bild
behauptete dann etwas anderes, als die Bewegung zeigt.

Als Rückfallebene gibt es den Shortcode
`[knotendreieck titel="…" oben="…" links="…" rechts="…" unten="…" sekunden="13.5" autoplay="ja" fahrzeuge="Striche" bildunterschrift="…"]`.
Im Editor gehört aber der Block benutzt — dort stehen dieselben Felder.

Die Dateien liegen in `theme/idt-deutschlandtakt/blocks/knotendreieck/`
(`block.json`, `editor.js`, `view.js`, `standbild.svg`), das Markup baut
`idt_render_knotendreieck()` in `inc/shortcodes.php`. Gezeichnet
wird im Browser — Vanilla JS im Shadow DOM, kein Build-Schritt. Wer an
Geometrie, Farben oder Vorgabetexten in `view.js` etwas ändert, baut danach das
Standbild neu und commitet es mit:

```bash
./bin/knotendreieck-standbild.js
```

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

## Form des Hauptmenüs

Dasselbe WP-Menü (Position *primary*) lässt sich auf zwei Arten zeigen —
umstellbar unter **Design → Customizer → Website-Identität → Form des Hauptmenüs**:

| Einstellung | Desktop | Smartphone |
|---|---|---|
| **Menüband** (Vorgabe) | Die Menüpunkte stehen offen nebeneinander, Untermenüs klappen bei Hover auf. | Ab 900 px abwärts klappt der Hamburger sie als Leiste unter dem Kopf auf. |
| **Aufklappbar** | Die Punkte liegen hinter **drei Strichen**; ein Klick fächert sie waagerecht auf, einer nach dem anderen. | Die Markentinte nimmt den ganzen Bildschirm ein, die Punkte stehen rechtsbündig darunter. |

Bei der aufklappbaren Form **entfällt der Balken im Kopf** — auch beim Scrollen.
Kein Hintergrund, keine Trennlinie, keine Mattscheibe: nur das Logo links und
drei Striche rechts, frei über der Seite. Vom Menüband bleibt damit nichts
stehen, was nach Leiste aussieht.

Damit Schrift und Kopf sich trotzdem nicht ins Gehege kommen, trägt beim
Scrollen **jedes Stück seinen eigenen Grund**: Die Striche bekommen eine
Papierfläche untergelegt, das Logo ebenso. Auf dem papierfarbenen Grund des
Themes sieht man diese Flächen nicht — sie decken nur den Text dahinter ab;
über einem Bild tragen sie. Auf dem Telefon ist der Kopf zu schmal dafür:
Dort **weicht das Logo beim Scrollen ganz**, sonst reichte seine Fläche fast
bis zu den Strichen und der Balken wäre wieder da, nur in zwei Teilen. Am
Seitenanfang und im offenen Menü steht die Marke wie gewohnt.

Die **Suche steht bei dieser Form im Menü**, nicht im Kopf: als letzter Punkt
unter den Menüpunkten, mit Lupe und dem Wort „Suche". Sie öffnet dasselbe
Such-Overlay wie die Lupe im Menüband (s. Abschnitt *Suche*). Ohne JavaScript
tauschen die beiden zurück — dazu unten mehr.

Das zweite Kennzeichen: **Beim Öffnen kippt der Kopf auf Markentinte** — Logo,
Punkte und Kreuz werden hell, die Seite tritt zurück. Auf dem Telefon wächst
dieselbe Fläche über den ganzen Bildschirm; Logo und Kreuz bleiben dabei stehen,
wo sie waren, und gehen nahtlos in sie über.

Beide Formen bedienen sich gleich: Esc schließt, ein Klick auf einen Menüpunkt
schließt. Auf dem Vollbild kommt dazu, dass die Seite dahinter stillsteht und
der Tastaturfokus im Menü bleibt, bis es geschlossen ist. Untermenüs erscheinen
auf dem Desktop als Flyout, auf dem Telefon eingerückt und dauerhaft offen —
dort gibt es kein Hover.

Die Wahl ändert nur Body-Klasse und Stylesheet-Abschnitt, nicht das Markup:
Beide Formen rendern dasselbe `wp_nav_menu()` aus `header.php`. Das Aussehen
steht in `style.css` (Abschnitt 5 Menüband, 5c aufklappbar), das Verhalten in
`assets/nav.js`.

Ohne JavaScript bleibt das Menü erreichbar: Die aufklappbare Form versteckt die
Punkte nur, wenn `idt_nav_js_flag()` die Klasse `idt-js` gesetzt hat — sonst
stehen sie offen wie im Menüband, der Kopf behält seinen Balken und die
Schaltfläche ihre Kante. Auch die **Suche wandert dann zurück in den Kopf**:
Der Punkt im Menü entfällt, die Lupe neben den Strichen erscheint. Ein
Suchpunkt in einem Menü, das sich ohne JavaScript nicht öffnen lässt, wäre
sonst auf dem Telefon gar nicht mehr zu erreichen.

**Ein eigenes Logo braucht eine helle Fassung.** Auf der Tinte tauscht das Theme
das mitgelieferte Logo gegen `assets/logo-idt-inverse.png` (dieselbe Datei, die
der Footer nutzt). Für ein eigenes Logo aus *Website-Identität* gibt es keine
solche Zweitfassung — wer dort ein Logo mit dunkler Schrift hinterlegt, sollte
entweder eines wählen, das auf hellem **und** dunklem Grund trägt, oder beim
Menüband bleiben.

**Zur Abwägung:** Das Menüband zeigt, was es auf der Seite gibt, ohne dass jemand
klicken muss; die aufklappbare Form macht den Kopf ruhiger, kostet aber genau
diese Sichtbarkeit. Für eine Vereinsseite, auf der Besucher etwas *suchen*, ist
das Menüband deshalb die Vorgabe geblieben.

## Startseite

Die Homepage wird **nicht** mehr fest in `front-page.php` verdrahtet: Die Vorlage
rendert den Editor-Inhalt der Seite **„Startseite"** (`the_content()`), die mit dem
Design als Blöcke/Shortcodes befüllt ist (siehe `idt_content_startseite()` in
`inc/demo-content.php`). Dadurch ist die Startseite vollständig im Block-Editor
bearbeitbar. Die Beiträge-Reihe unten ist das dynamische `[neuigkeiten]`-Element.

## Verlaufsseite (Linkseite)

Für Linkseiten („Link in Bio"), Kampagnen- und QR-Code-Ziele gibt es die
Seitenvorlage **„Verlaufsseite (ohne Kopf und Fuß)"** (Seite bearbeiten →
Seitenleiste → *Seite* → **Vorlage**). Sie zeigt **kein Menüband und keinen
Footer**, sondern nur den Inhalt der Seite — mittig in einer schmalen Spalte auf
dem senkrechten Markenverlauf **Violett (oben) → Cyan (unten)**.

Fertig bestückt liegt der Inhalt als Vorlage **Verlaufsseite (Logo, Buttons,
Social-Leiste)** im Inserter unter Tab **Vorlagen** → **Deutschlandtakt**:
Logo-Sperrsatz, drei beige Buttons untereinander, darunter die beige
Social-Leiste. Nach dem Einfügen in der Seitenleiste je Zeile Beschriftung und
Ziel eintragen.

Drei Bausteine gehören dazu; sie funktionieren auch auf jeder anderen farbigen
oder dunklen Fläche:

| Baustein | Block / Shortcode | Wirkung |
|---|---|---|
| **Logo (Wortmarke + Signet)** | `/Logo`, `[logo]` | Dreizeilige Wortmarke, rechts daneben das Signet im Kreis. Echter Text in Inter statt Bild — scharf in jeder Größe und vorlesbar. Felder: Größe des Signets (alle übrigen Maße folgen ihr), Schriftfarbe (von der Fläche erben / Papier / Ink), Ausrichtung, optionaler Link. |
| **Beige Buttons** | Stil **Beige** an Button, Pill-Button und Pill-Button-Stack | Gefüllte Papierfläche mit halbrunden Enden. Die runde Form ist hier kein Bruch mit dem sonst eckigen Design, sondern das Zitat der Wortmarke. Gestapelt (Pill-Button-Stack) sind das die breiten Schaltflächen der Linkseite. |
| **Beige Social-Leiste** | Social-Leiste, Stil **Beige** | Papierfarbene Kacheln mit cyanem Zeichen, über das Feld **Ausrichtung** links/mittig/rechts. Neben den Profilen steht mit der Plattform `mail` auch eine **Mail-Kachel** zur Verfügung: In die Zeile kommt dann statt der URL die Adresse, die Kachel wird zum `mailto:`-Link. |

Im Editor steht der Inhalt weiterhin auf weißem Grund — die Verlaufsfläche
bringt die Seitenvorlage mit, nicht der Block. Damit die hellen Bausteine dort
nicht unsichtbar werden, unterlegt `assets/editor.css` sie im Editor mit
demselben Verlauf; im Frontend passiert das nicht.

## Suche

Die Suche hat genau einen Einstieg: im Menüband die **Lupe** rechts in der
Kopfleiste, beim aufklappbaren Menü der **Suchpunkt im Menü** (s. Abschnitt
*Form des Hauptmenüs*). Ein Klick öffnet die Suche als Ebene **über der ganzen
Seite** (Markup: `idt_render_search_overlay()` in
`theme/idt-deutschlandtakt/inc/search.php`, Verhalten: `assets/search.js`).
Schon während des Tippens erscheinen die Treffer als Karten; Schließen per ×,
`Esc` oder Klick daneben.

Die Vorschau holt ihre Treffer vom REST-Endpunkt **`/wp-json/idt/v1/suche?q=…`**
(öffentlich lesbar, nur veröffentlichte Beiträge und Seiten). Er liefert bereits
gerendertes Markup — dieselben Treffer-Karten (`.idt-result`), die auch die
Ergebnisseite `search.php` nutzt, sodass Vorschau und Ergebnisseite nicht
auseinanderlaufen können. Die Eingabetaste führt jederzeit auf die vollständige
Ergebnisseite mit Trefferzahl, Suchfeld zum Nachschärfen und Blätterfunktion.

Ohne JavaScript verlinkt der Einstieg auf `#idt-searchbox`; eine `:target`-Regel
in `style.css` klappt das Overlay auch dann auf, das Formular darin führt regulär
zur Ergebnisseite. In der aufklappbaren Form steht dafür wieder die Lupe im Kopf
(s. oben). Das kompakte `searchform.php` bleibt für alles erhalten, was
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
