# Todo

_(keine offenen Aufgaben)_

## Done

- **Mail-Link als eigener Baustein (v2.3.0).** Rückmeldung aus dem Verein: Bei
  den Kontaktangaben („Post: … / Mail: kontakt@…") fehlte der Weg zur
  verlinkten Adresse — `[email]…[/email]` gab es nicht, und von Hand bliebe nur
  rohes `<a href="mailto:…">` im Editor. Neu ist deshalb der Baustein
  **Mail-Link**: Shortcode `[email]` (kurz auch `[mail]`) und Block *Mail-Link*
  im Inserter, beide über dieselbe `idt_sc_email()` (Konvention 2).
  - **Die Adresse darf zwischen den Klammern stehen.** `[email]kontakt@…[/email]`
    ist der gedachte Normalfall — genau so, wie es in der Rückmeldung getippt
    war. Mit `address="…"` wird der Inhalt die Beschriftung, `subject="…"`
    belegt die Betreffzeile vor, `icon="false"` lässt das Briefzeichen weg.
  - **Verschleiert im Quelltext.** Die Adresse läuft durch `antispambot()`, steht
    also nur als HTML-Entities in der Seite; Adress-Sammler finden dort kein
    zusammenhängendes `name@domain`, der Browser setzt sie beim Anzeigen wieder
    zusammen. Der `href` ist darum mit `esc_attr()` abgesichert und *nicht* mit
    `esc_url()`: Letzteres schreibt das `&` der Entities zu `&#038;` um und
    zerlegt den Link — nachgestellt am Prüfstand, der den `href` zurück in die
    Klartextadresse auflöst.
  - **Ungültige Eingabe bleibt Text.** Was `sanitize_email()` nicht durchlässt,
    wird als Text ausgegeben statt als tauber Link — so fällt der Tippfehler in
    der Vorschau auf, nicht erst dem Besucher.
  - Neues Briefzeichen in `idt_icon()` ('mail'), Stil in `style.css` 7: der Link
    bleibt ein Inline-Element (Zeichen per `vertical-align` auf der
    Schriftlinie), damit er im Fließtext normal umbricht; unterstrichen wird beim
    Hover nur die Adresse. Das Impressum der Demo-Inhalte und die Seite
    „Stilelemente" nutzen jetzt den Baustein.
  - Geprüft mit `check-theme.sh` und `check-zip.sh` (beide grün) sowie einem
    Prüfstand für die Render-Funktion (Adresse im Inhalt, eigene Beschriftung,
    Betreff, ohne Zeichen, ungültige Eingabe). `smoke-test.sh` lief hier nicht:
    `api.wordpress.org` ist aus dieser Umgebung nicht erreichbar.

- **Aufklappbares Menü: Kopf ohne Balken, Suche ins Menü (v2.2.2).** Rückmeldung
  aus dem Verein zum Stand v2.2.1: „Aber der Balken ist weiter da. Geht der
  transparent? Und kann man nur die drei Striche fürs Menü machen? Lupe auch
  weg, reicht im Menü." Genau das ist jetzt umgesetzt — nur für die
  aufklappbare Form, das Menüband bleibt unberührt.
  - **Der Balken verschwindet auch beim Scrollen.** Die Papierfläche unter dem
    ganzen Kopf, die v2.2.1 gegen die durchlaufende Schrift eingeführt hatte,
    war der Balken durch die Hintertür. Statt einer Fläche für alles trägt
    jetzt jedes Stück seinen eigenen Grund: Striche und Logo bekommen beim
    Scrollen je eine Papierfläche in ihrer eigenen Form. Auf dem
    papierfarbenen Seitengrund sieht man sie nicht — sie decken nur den Text
    dahinter ab; über einem Bild tragen sie die Marke. Bewusst ohne Haarlinie:
    Mit Kante zeichnete die Fläche auf Papier wieder genau den Kasten, der weg
    sollte (nachgestellt bei 390 px).
  - **Auf dem Telefon weicht das Logo.** Dort ist der Kopf zu schmal für die
    Fläche unter der Marke — sie reichte fast bis zu den Strichen, und der
    Balken wäre wieder da, nur in zwei Teilen. Am Seitenanfang und im offenen
    Menü steht die Marke wie gewohnt. Auf dem Desktop bleibt sie stehen; dort
    genügt ihre eigene Fläche.
  - **Nur noch die drei Striche.** Rahmen und das Wort „Menü" sind weg, das
    Markup der Beschriftung ebenfalls. Der Rahmen bleibt in der Breite stehen
    und wird nur durchsichtig, damit die Tippfläche ihre 46×42 px behält und
    beim Umschalten der Menüform nichts springt.
  - **Die Suche steht im Menü.** Als letzter Punkt unter den Menüpunkten, mit
    Lupe und dem Wort „Suche"; sie öffnet dasselbe Overlay wie die Lupe im
    Menüband. Bewusst ein Geschwister der Menüliste und kein Listenpunkt:
    `wp_nav_menu()` gibt ohne zugewiesenes Menü gar nichts aus, als `<li>`
    verschwände die Suche mit.
  - **Vier Fehler, die erst der Browser gezeigt hat** (nachgemessen bei
    320×568, 390×844, 768×1024, 844×390 und 1280×800, mit und ohne
    JavaScript, dazu Tastatur, Esc und reduzierte Bewegung):
    - Das Logo blendete auf dem Telefon nicht aus: `.idt-js .idt-nav-scrolled`
      schreibt zwei Klassen desselben `<html>`-Elements als Verschachtelung und
      trifft damit nichts. Richtig ist `.idt-js.idt-nav-scrolled`.
    - Der durchsichtige Kopf fing Klicks ab, die dem Text darunter galten. Er
      lässt sie jetzt durch (`pointer-events`), Marke, Menü und Schaltfläche
      holen sie sich einzeln zurück — und der offene Kopf nimmt sie wieder an,
      damit nichts durch die Tafel hindurchfällt.
    - Auf dem Desktop lief der Fließtext mitten durch die Wortmarke, beides
      unlesbar (nachgestellt bei 1280×800). Erst daraufhin hat auch das Logo
      seine eigene Fläche bekommen.
    - Die helle Fassung des Logos saß nach der neuen Polsterung des
      Marken-Blocks um ebendiese versetzt neben der dunklen. Ihre Versatzwerte
      müssen mit der Polsterung zusammen gepflegt werden; ein Kommentar sagt
      das jetzt.
  - **Ohne JavaScript tauscht die Suche zurück in den Kopf.** Das Menü lässt
    sich dann nicht öffnen — ein Suchpunkt darin wäre auf dem Telefon gar nicht
    mehr zu erreichen. Die Lupe steht deshalb weiter im Markup und wird nur
    ausgeblendet, sobald `idt-js` gesetzt ist; der Punkt im Menü umgekehrt.
    Ebenso behält die Schaltfläche ohne JavaScript ihre Kante, passend zum
    Balken, auf den die Form dort ohnehin zurückfällt.
  - Nach dem Schließen der Suche geht der Fokus auf die Menü-Schaltfläche,
    wenn der Suchpunkt inzwischen im geschlossenen Menü liegt — sonst fiele er
    auf `<body>`. Geprüft wird über `visibility`, weil ein Element in einem
    versteckten Vorfahren weiterhin eine Breite meldet.

- **Aufklappbares Menü: Kopf ohne Balken (v2.2.1).** Rückmeldung aus dem
  Verein: Wenn das Menü ohnehin hinter einer Schaltfläche liegt, braucht der
  Kopf das Band nicht mehr — links das Logo, rechts „Menü", sonst nichts. So
  hält es auch das Vorbild (`#header` dort: `position: fixed`, transparent,
  ohne Rahmen). Bei der aufklappbaren Form entfallen deshalb Hintergrund,
  Trennlinie und Mattscheibe; das Menüband als Vorgabe bleibt unberührt.
  - **Der Haken war das Scrollen.** Der Kopf klebt oben — ohne Fläche lief die
    Schrift mitten durch die Wortmarke (nachgestellt bei 1280×800: die
    Überschrift „Warum das Knotenprinzip trägt" kreuzte das Logo). Ab 40 px
    Scrollhöhe legt sich jetzt die volle Papierfläche darunter. Am
    Seitenanfang, wo man zuerst hinsieht, bleibt der Kopf frei.
  - **Deckendes Papier statt Mattscheibe.** Auf dem papierfarbenen Grund des
    Themes sieht beides gleich aus, aber ein `backdrop-filter` würde das
    Menüband zum Bezugsrahmen aller `position: fixed`-Nachfahren machen — und
    die Menütafel auf dem Telefon wäre wieder kopfhoch statt bildschirmhoch.
    Nachgemessen mitten auf einer gescrollten Seite: 390×844, also voll.
  - **Ohne JavaScript behält der Kopf seinen Balken.** Die Papierfläche kann
    nur `assets/nav.js` einblenden; sonst bliebe der Kopf dauerhaft
    transparent und die Schrift liefe durchs Logo. Die Transparenz hängt
    deshalb an derselben `idt-js`-Kennung wie das Verstecken der Menüpunkte.
  - Nachgemessen bei 320×568, 390×844, 768×1024, 844×390, 901, 1024, 1280 und
    1920 px, dazu Tastaturfokus, Esc und das Menüband als Regression (Balken,
    Mattscheibe und Hamburger-Maße dort unverändert, keine Scroll-Klasse).

- **Zweite Menüform: aufklappbares Menü als Vorschlag (v2.2.0).** Nach dem
  Vorbild von patrickheypeter.net liegt das Hauptmenü wahlweise hinter einer
  Schaltfläche „Menü" statt offen im Band. Umstellbar unter *Design →
  Customizer → Website-Identität → Form des Hauptmenüs*; Vorgabe bleibt das
  Menüband, bestehende Seiten ändern sich also nicht von allein.
  - **Kern ist der Farbumschlag, nicht das Aufklappen.** Beim Öffnen kippt das
    Menüband auf Markentinte: Logo (Inverse-Fassung), Punkte und Kreuz werden
    hell, die Seite tritt zurück. Das war aus dem Stylesheet des Vorbilds nicht
    zu sehen — es steht in einem `header::after`, das erst im laufenden Browser
    sichtbar wird. Ein erster Entwurf ohne den Umschlag traf die Sache deshalb
    nicht und ist verworfen.
  - **Desktop:** Die Punkte fächern waagerecht aus dem Hamburger auf, mit 60 ms
    Versatz je Punkt. Der Platz ist vorher reserviert (`visibility` statt
    `display`), damit Logo und Aktionsleiste beim Öffnen nicht springen. Das
    Wort „Menü" weicht zusammen, die Balken werden zum Kreuz.
  - **Telefon:** Dieselbe Tinte nimmt den ganzen Bildschirm ein, die Punkte
    stehen rechtsbündig darunter. Logo und Kreuz bleiben stehen, wo sie waren.
    Kein Seitenpanel und kein Abdunkler — die Fläche ist selbst der Grund.
    Anders als das Vorbild stellt die Tafel die Seite dahinter still und hält
    den Tastaturfokus fest, bis sie geschlossen ist.
  - **Vier Fehler, die erst der Browser gezeigt hat** (nachgestellt bei 320×568,
    390×844, 768×1024, 844×390, 901, 1024, 1280 und 1920 px):
    - Die Tafel blieb kopfhoch statt bildschirmhoch (104 px statt 844 px): Das
      `backdrop-filter` des Menübands macht dieses zum Bezugsrahmen aller
      `position: fixed`-Nachfahren. Der Filter entfällt jetzt, solange das Menü
      offen ist — im Menüband bleibt er unberührt.
    - Innerhalb des Menübands lag die Tafel über Logo und Aktionsleiste und
      deckte das Kreuz zu, mit dem man wieder herauskommt.
    - Ein Abdunkler mit `display: block` im Media Query überstimmte die Vorgabe
      seines `hidden`-Attributs und schluckte im Ruhezustand jeden Klick. Mit
      der bildschirmfüllenden Tafel ist er ganz entfallen.
    - Im Querformat und auf kleinen Displays rutschten die letzten Menüpunkte
      unter den Rand. Zwei Größenstufen (≤ 640 px und ≤ 440 px Höhe) bringen
      sie wieder ins Bild.
  - **Ohne JavaScript bleibt das Menü erreichbar.** Das Verstecken hängt an der
    Klasse `idt-js`, die `idt_nav_js_flag()` im `<head>` setzt — fehlt sie,
    stehen die Punkte offen da statt unerreichbar hinter einer toten
    Schaltfläche.
  - Ein eigenes Logo aus „Website-Identität" bekommt keine Inverse-Fassung;
    dafür gibt es in WordPress kein Gegenstück. Im README steht, was das heißt.
  - Die Menü-Logik ist aus `assets/scale.js` nach `assets/nav.js` gezogen;
    `scale.js` macht jetzt nur noch, was sein Name sagt. Das Menüband ist
    unverändert geblieben (nachgemessen gegen den Stand davor: gleiche
    Darstellung, Mattscheibe erhalten, Hamburger weiterhin 46×42 wie die Lupe
    daneben).

- **Verlaufsseite lief auf dem Telefon seitlich aus dem Bild (v2.1.1).** Auf der
  Live-Seite stand der erste Button über den rechten Rand hinaus, und Logo wie
  Social-Leiste saßen dadurch sichtbar außermittig. Nachgestellt bei 390 px
  Viewport: Das Dokument war 442 px breit — 52 px Überlauf.
  - **Ursache war `white-space: nowrap` am Pill-Button.** Das ist für einzeln
    stehende Pills richtig (sie sollen nicht mitten im Wort brechen), im
    Button-Stack aber nicht: Dort ist jeder Button eine Zeile über die volle
    Breite, und eine lange Beschriftung wie „Unsere Agenda für die Schiene
    2035" schob die ganze Spalte hinaus. Im Stack brechen die Beschriftungen
    jetzt um (`.idt-pillstack .pill`), der Stack selbst bekommt
    `max-width: 100%`.
  - **Die Breitenformel des Logo-Sperrsatzes war zu knapp.** Sie stammte aus
    einer Messung vor der Feinjustierung der Wortmarke; seitdem ist der
    Sperrsatz 3,81 statt 3,7 Signet-Durchmesser breit. Der Teiler steht auf 3,9
    (etwas Reserve), und wo Container-Queries zur Verfügung stehen, rechnet die
    Größe mit der Breite der Spalte (`100cqw`) statt mit der des Fensters —
    damit stimmt sie auch, wenn ein Scrollbalken Platz wegnimmt oder das Logo
    in einer schmaleren Spalte steht.
  - **Die Inhaltsspalte nimmt jetzt die volle Bühnenbreite ein.** `.entry`
    zentriert sich über automatische Seitenränder; im Flex-Container der Bühne
    schrumpfte der Inhalt dadurch auf seine natürliche Breite (418 px), und
    Button-Stack wie Logo richteten sich nach dieser Restbreite statt nach der
    Spalte — auf dem Desktop war das Logo deshalb kleiner als eingestellt.
  - Nachgemessen im Browser bei 320, 360, 375, 390, 412, 414, 430, 480, 600,
    768, 1024 und 1280 px: kein waagerechter Überlauf mehr, kein Element außer
    halb des Viewports, die Wortmarke wird nirgends gequetscht.

- **Knotendreieck blieb auf WordPress vor 6.1 im Frontend leer.** Der Block
  registrierte seine Ausgabe über das Feld `"render": "file:./render.php"` der
  `block.json`. Das wertet WordPress erst ab 6.1 aus — der Theme-Header
  verspricht aber `Requires at least: 6.0`. Auf einer solchen Installation stand
  der Block im Inserter, bekam aber keinen `render_callback` und gab im Frontend
  nichts aus. Nachgestellt in einer echten 6.0-Instanz: `render_callback: FEHLT`,
  Markup 0 Zeichen; der Shortcode `[knotendreieck]` funktionierte weiter.
  - **Die Brücke ist jetzt PHP.** `register_block_type()` in `inc/blocks.php`
    bekommt `'render_callback' => 'idt_knotendreieck_render_block'`; die Funktion
    reicht die Attribute an `idt_render_knotendreieck()` weiter. Damit genügt,
    was jede `block.json`-fähige WordPress-Version kann, und der Block
    registriert sich wie alle anderen Bausteine des Themes. `render` in der
    `block.json` und die Datei `blocks/knotendreieck/render.php` entfallen.
  - **Der Smoke-Test merkt das künftig.** `bin/smoke-render.php` prüfte bisher
    nur, ob ein Block *irgendetwas* zurückgibt, und Blöcke mit eigener
    `block.json` fielen ohnehin durch die Prüfung von `idt_blocks_config()`
    hindurch. Jetzt wird für jede `blocks/*/block.json` verlangt, dass der Block
    mit seinen Vorgabewerten Markup erzeugt. Gegenprobe in einer 6.0-Instanz:
    ohne den Fix schlägt der Test mit „rendert mit seinen Vorgabewerten nichts"
    fehl, mit dem Fix läuft er durch — ebenso unter 6.9.7.
  - Geprüft wurde beides in echten Instanzen (WordPress-Core von GitHub, weil
    `api.wordpress.org` aus dieser Umgebung gesperrt ist): Block im Inserter,
    Vorschau im Editor, Attribute im Frontend-Markup, Shortcode.

- **README: warum plötzlich zwei Themes „IDT Deutschlandtakt" in der Liste
  stehen.** Wird das Release-Zip unter macOS ausgepackt und im Finder neu
  komprimiert, heißt der Ordner darin `idt-deutschlandtakt 2` oder `… 4` — die
  Zählung für mehrfach geladene Dateien. WordPress benennt das Themeverzeichnis
  danach und legt ein zweites, eigenständiges Theme mit demselben Anzeigenamen
  an; aktiv bleibt das alte, und die neuen Bausteine fehlen scheinbar. Der
  Abschnitt „Theme-Zip beziehen" sagt das jetzt samt Abhilfe.
- **Verlaufsseite, beige Bausteine und der Logo-Sperrsatz (v2.1.0).** Neue
  Seitenvorlage **„Verlaufsseite (ohne Kopf und Fuß)"** (`page-verlauf.php`):
  kein Menüband, kein Footer, nur der Inhalt der Seite — mittig in einer
  schmalen Spalte auf dem senkrechten Markenverlauf Violett → Cyan. Gedacht
  für Linkseiten, Kampagnen- und QR-Code-Ziele.
  - **Ausgeblendet wird über den Template-Slug**, wie schon bei „Ohne Menüband"
    und „Menü ohne Logo". Die drei Prüfungen lagen als kopierte
    `is_singular()`-Zeilen in `header.php`; sie stehen jetzt einmal in
    `idt_page_template_is()` (`functions.php`), daneben `idt_is_verlauf_page()`
    für die beiden Stellen in `header.php` und `footer.php`. Die Verlaufsfläche
    selbst hängt an der Body-Klasse `idt-verlauf` (`idt_body_class()`), damit
    sie die ganze Seite färbt und nicht nur den Inhaltsbereich.
  - **Logo-Sperrsatz als Baustein** — die Variante der Marke für dunkle und
    farbige Flächen: dreizeilige Wortmarke links, Signet im Kreis rechts.
    Shortcode `[logo]`, Block „Logo (Wortmarke + Signet)", eine Render-Funktion
    (`idt_render_logo()` in `inc/shortcodes.php`, Konvention 2). Das Signet ist
    Inline-SVG in einem 148er-Koordinatensystem und nutzt die Farbtoken
    (Konvention 4); die Wortmarke ist echter Text in Inter, kein Bild — scharf
    in jeder Größe, vorlesbar und in der Schriftfarbe der Fläche. Alle Maße
    hängen an einer Variablen (`--logo-size`, begrenzt durch `--logo-fit`),
    die Proportionen sind am Entwurf gemessen.
  - **Beige Varianten** für Pill-Button, Button und Social-Kachel: gefüllte
    Papierfläche, halbrunde Enden bzw. weiche Ecke. Dafür zwei neue Radien-Token
    (`--radius-bar`, `--radius-soft`) — die eckige Regel des Themes bleibt, die
    runde Form zitiert wie die übrigen Ausnahmen die Wortmarke. Die
    Social-Leiste bekam dazu ein Feld **Ausrichtung** und gibt ihren Stil an die
    Icons weiter.
  - **Wo die Varianten stehen, entscheidet die Kaskade.** Sie liegen bei ihren
    Geschwistern in den Abschnitten 7 und 7b, nicht im neuen Abschnitt 6c:
    gleiche Spezifität, also gewinnt die spätere Regel — vor den Grundregeln
    wäre `.pill--beige` wirkungslos geblieben.
  - **Vorlage „Verlaufsseite"** (`inc/patterns.php`) setzt Logo, drei beige
    Buttons und die beige Social-Leiste fertig zusammen. Im Editor unterlegt
    `assets/editor.css` die hellen Bausteine mit demselben Verlauf — sonst
    stünden sie unsichtbar auf dem weißen Editor-Grund.
  - **Mindestbreite des Pill-Stacks** wird als `min(…, 100%)` ausgegeben: Auf
    dem Telefon schrumpft der Stack mit, statt über den Rand zu laufen.

- **Theme-Download aus der CI ist wieder hochladefertig.** Der WordPress-Upload
  brach mit „Dem Theme fehlt das Stylesheet style.css" ab — nicht wegen des
  Zips, sondern wegen seiner Verpackung: GitHub packt jedes Artefakt beim
  Herunterladen erneut in ein Zip. Das hochgeladene Release-Zip kam deshalb als
  Zip-im-Zip an, und in der Archivwurzel stand für WordPress nur ein weiteres
  Zip. Das betraf jeden Artefakt-Download seit jeher, nicht erst v2.0.25.
  - **Der Inhalt wandert ins Artefakt, nicht das Archiv.** `ci.yml` packt das
    von `bin/check-zip.sh` geprüfte Zip aus und lädt `dist/paket/*` hoch. Das
    Sternchen ist der Punkt: Ab dem ersten Wildcard behält `upload-artifact`
    die Verzeichnisstruktur, der Ordner `idt-deutschlandtakt/` bleibt also
    erhalten. GitHubs Verpacken stellt beim Herunterladen wieder genau das
    Archiv her, das der Theme-Upload erwartet. Der Artefaktname trägt jetzt die
    Versionsnummer und ist damit zugleich der Dateiname des Downloads.
  - **Die CI prüft ihr eigenes Paket.** Der Job „Release-Zip" lädt sein
    Artefakt direkt wieder herunter und bricht ab, wenn darin
    `idt-deutschlandtakt/style.css` fehlt. Ob der Download taugt, soll die CI
    sagen und nicht das WordPress-Backend.
  - **Das Release-Asset war nie betroffen** — es wird als Datei ausgeliefert
    und nicht neu verpackt. In `release.yml` liest jetzt ein Schritt die
    Theme-Version einmal in `THEME_VERSION`; vorher stand sie zweimal da und
    beim manuellen Start gar nicht zur Verfügung.
  - **`release.yml` lädt kein eigenes Artefakt mehr hoch.** Der Workflow ruft
    `ci.yml` auf, und deren Job „Release-Zip“ legt das Artefakt bereits in
    denselben Lauf — auch beim manuellen Start. Ein zweites gleichen Namens
    hätte den Release mit „409 Conflict“ abgebrochen, denn Artefakte sind seit
    `upload-artifact@v4` unveränderlich. Aufgefallen wäre das erst beim ersten
    Tag, weil es im Repo bis dahin weder Tags noch Releases gab.
  - Das Theme selbst ist unverändert, die Version bleibt deshalb bei 2.0.25.

- **Knotendreieck als Block (v2.0.25).** Die bewegte Grafik zum Knotenprinzip —
  drei Knotenbahnhöfe, drei Linien, Züge, die sich zur Minute :00 und :30
  treffen — ist jetzt ein Baustein des Themes. Überschrift, Knotennamen,
  Unterzeile und Bildunterschrift stehen im Editor in der Seitenleiste, dazu
  Zyklusdauer, Autostart und die Form der Fahrzeuge. Kein Build-Schritt, keine
  Abhängigkeiten: Vanilla JS im Shadow DOM (`blocks/knotendreieck/view.js`).
  - **Ein Render-Pfad** (Konvention 2): das Frontend-Markup kommt aus
    `idt_render_knotendreieck()` in `inc/shortcodes.php`. Der Block ruft sie
    über `blocks/knotendreieck/render.php` auf, der Shortcode
    `[knotendreieck]` über `idt_sc_knotendreieck()` — dieselbe Funktion, eine
    Escaping-Stelle, eine Begrenzung der Zyklusdauer auf 8–24 Sekunden.
  - **Eigene Editor-Oberfläche statt `idt_blocks_config()`.** Das ist die erste
    Abweichung von der gemeinsamen `assets/blocks.js` — aus einem Grund: deren
    Vorschau läuft über `ServerSideRender`, der bei jedem Tastendruck neues HTML
    anfordert und damit eine laufende Animation zurücksetzt. Der Block bringt
    deshalb `block.json` und eine ebenfalls build-freie `editor.js` mit und
    setzt das Custom Element direkt ein. `script` statt `viewScript` in der
    `block.json` ist Absicht: so lädt WordPress `view.js` auch im Editorrahmen,
    und die Vorschau zeigt dieselbe Grafik wie die Seite — nur ohne Autostart,
    damit beim Schreiben nichts im Augenwinkel zappelt.
  - **Markenfarben statt Näherungswerte** (Konvention 4). Die Grafik kam mit
    eigenen Tönen (`#1B3A3E`, `#FDF3EE`, `#5B3EE8`, `#45C6F0`); das waren
    sichtbar Annäherungen an die Marke. Sie stehen jetzt auf `--idt-ink`,
    `--idt-paper`, `--idt-violet` und `--idt-cyan`; die dritte Kante bekam mit
    `#3796FA` eine Mischung aus Cyan und Violett. Die Werte stehen als fünf
    Konstanten am Kopf von `view.js` und nicht als `var(--…)`, weil dieselben
    Funktionen das Standbild erzeugen — eine per `<img>` geladene SVG-Datei
    sieht die Custom Properties des Dokuments nicht.
  - **Standbild wird gebaut, nicht gepflegt.** `bin/knotendreieck-standbild.js`
    ruft `fullSvg(30)` aus `view.js` unter Node auf und schreibt
    `blocks/knotendreieck/standbild.svg` — das, was ohne JavaScript im
    `<noscript>` erscheint. Zwei Zeichenwege für dasselbe Bild liefen
    unweigerlich auseinander. Es trägt deshalb die Vorgabetexte, nicht die
    eingestellten; die Bildbeschreibung bleibt darum allgemein.
  - **Fest bleiben die Fahrzeiten** 28 / 28 / 57 Minuten und die Knotenfenster
    :28–:32 und :58–:02. Sie hängen zusammen: eine frei geänderte Minutenzahl
    würde nur die Beschriftung ändern, nicht den Fahrplan — das Bild behauptete
    dann etwas anderes, als die Bewegung zeigt. Ein Hinweis dazu steht in der
    Seitenleiste.
  - **Bedienbar und ruhig.** Play-/Pause-Knopf, Zeitregler mit Pfeiltasten
    (Umschalt = fünf Minuten), Pos1, Ende und Leertaste; Autostart erst beim
    Sichtbarwerden und nie wieder, sobald jemand den Regler angefasst hat. Bei
    systemweit reduzierter Bewegung und vor dem Drucken steht die Grafik auf
    Minute :30. Die Bildunterschrift steht als `figcaption` unter dem Bild und
    bleibt damit durchsuchbar; die Überschrift sitzt im Bild, damit sie
    mitwandert, wenn jemand die Grafik weiterverwendet.
  - **Dazu:** Vorlage „Knotenprinzip erklärt" (`inc/patterns.php`), Abschnitt 7e
    in `style.css` für Figur, Bildunterschrift und Standbild, und zwei
    erweiterte Prüfungen — `bin/check-theme.sh` nimmt `blocks/` bei
    JS-Syntax, IIFE-Kapselung, Debug-Resten und ABSPATH-Guard mit und ruft neu
    `bin/block-meta.php` auf, das für jede `block.json` Namensraum, Textdomain,
    render-Datei, Skript-Handles und die Deckung der Attribut-Vorgaben mit
    `idt_<name>_defaults()` prüft.

- **Eckiges Design — alle gerundeten Ecken entfernt (v2.0.24).** Schaltflächen,
  Karten, Felder und Flächen laufen jetzt durchgehend mit geraden Kanten aus.
  Zentral gelöst über die Radius-Token in `style.css` Abschnitt 2: `--radius-xs`
  bis `--radius-pill` stehen auf `0`, die benannte Abstufung bleibt als eine
  Stellschraube erhalten. Dazu die Stellen mit fest eingetragenen Werten:
  - **Buttons in allen Formen.** Der `.pill`-Button samt Gradient-Rahmen
    (`::before`), der Pill-Stack, der Social-Icon-Button (vormals kreisrund,
    jetzt quadratisch), `.idt-btn` in allen Varianten (über `--radius-sm`) und
    die WordPress-Kern-Schaltfläche (`core/button`, `core/file`), die Core mit
    rundem Rahmen ausliefert — neue Regel in Abschnitt 7b. Ein im Editor selbst
    gesetzter Radius steht inline am Element und gewinnt weiterhin. Die
    Klassennamen `.pill`/`.idt-pillstack` bleiben, weil Shortcodes, Blöcke und
    bestehende Inhalte sie tragen.
  - **Übrige Oberfläche.** Hamburger-Balken, Suchfeld und Such-Button,
    Schließen-Knopf des Suchoverlays, Dropdown-Menü, Karten, Callout,
    Konzept-Karte, News-Karte samt Bildkante, Chips, Themenblock, dunkler
    Einschub, Inhaltsbilder, Beitragsbilder und der Skip-Link.
  - **Trenner** (`core/separator`, Blockstile „Verlauf" und „Kurzer Strich")
    enden gerade statt rund.
  - **Bewusst rund geblieben** sind die Formen, die die Wortmarke zitieren: die
    Diagonalbalken der Splash-Bühne (`.bar`), der Diagonal-Akzent in
    `.idt-diagonal` und die Takt-Punkte (`.idt-takt`). Das Logo selbst ist eine
    Rasterdatei mit runden Balkenenden; eckige CSS-Balken direkt darunter würden
    wie ein Fehler aussehen.
- **Hintergrundfläche für Button und Konzept-Karte einstellbar (v2.0.23).** Die
  Fläche eines Buttons war bisher an seine Variante gebunden (Violett, Cyan,
  Outline), die Konzept-Karte lag immer auf Papier. Beide haben jetzt in der
  Seitenleiste das Feld **Hintergrund** — Markenfarben, die hellen Töne, Ink,
  Papier 1–3 und Grau, dazu die beiden **Markenverläufe Cyan→Violett** und
  Violett→Cyan. Als Shortcode ist es `bg=""` und nimmt zusätzlich einen freien
  Hex-Wert.
  - **Eine Stelle entscheidet über die Schriftfarbe.** `idt_surface_fill()`
    übersetzt eine `bg`-Angabe in eine Füllung und sagt dazu, ob die Fläche
    helle Schrift braucht. Dafür kann `idt_surface_is_dark()` jetzt statt einer
    Farbe eine Liste von Stützfarben bewerten: Bei Verläufen zählt das
    schwächste Ende, es gewinnt die Schriftfarbe mit dem besseren schlechtesten
    Kontrast. Beim Verlauf Cyan→Violett ist das die dunkle Schrift — helle
    Schrift stünde am Cyan-Ende bei 1,6:1. Das bisherige Kontrastverhältnis
    steckt als `idt_color_contrast()` in einer eigenen Funktion, `[themenblock]`
    und `[buttonstack]` verhalten sich unverändert.
  - **Die Verläufe sind Tokens, keine Literale** (Konvention 4): `--idt-grad-cyan-violet`
    und `--idt-grad-violet-cyan` stehen in `style.css` Abschnitt 2, PHP gibt nur
    `var(--idt-grad-…)` aus. `idt_surface_gradients()` ist die Liste, aus der
    sowohl Shortcode als auch Auswahlfeld schöpfen — wer einen Verlauf ergänzt,
    ergänzt Token und `idt_bg_options()` mit.
  - **Ein Render-Pfad** (Konvention 2): Block und Shortcode laufen weiter über
    dieselben `idt_sc_btn()`/`idt_sc_concept()`; die Blöcke bekommen nur ein
    Auswahlfeld mehr, das serverseitig gerendert wird.
  - Der Gradient-Rahmen (`variant="gradient"`) und eine eigene Fläche schließen
    sich aus — die Maske des Rahmens würde die Füllung verdecken, deshalb
    entfällt sie, sobald `bg` gesetzt ist.

- **Repo für die Veröffentlichung vorbereitet (v2.0.22).** Das Repository soll
  öffentlich werden; dafür ist zweierlei nötig — nichts Vertrauliches im Baum,
  und eine Lizenz, unter der das Theme überhaupt weitergegeben werden darf.
  - **Öffentliche Testseite vollständig entfernt.** `caddy/` (Reverse-Proxy, TLS,
    ACME-Kontakt) und `deploy/` (Anleitung, `wp-config`-Schnipsel,
    Basic-Auth-`.htaccess`) sind gelöscht, im `docker-compose.yml` fällt der
    Caddy-Dienst samt seinen Volumes und der Host-Allowlist in
    `WORDPRESS_CONFIG_EXTRA` weg. Der Stack ist damit wieder das, was er sein
    soll: eine lokale Entwicklungsumgebung unter `localhost:8090`. README und
    `CLAUDE.md` beschreiben keine Testseite mehr, der Admin-Zugang ist wieder der
    Wegwerf-Zugang `admin`/`admin` des Containers. `bin/check-theme.sh` suchte
    PHP-Dateien auch unter `deploy/` — der Pfad ist mit entfernt.
  - **Zugangsdaten aus der Historie getilgt.** `deploy/DEPLOY.md` nannte die
    Passwörter der Testseite im Klartext, und der Initial Commit trug ein
    `new_t-deploy-paket.tar.gz` mit vollständigem Datenbank-Dump (Passwort-Hash
    des Admins) und `.htpasswd`. Beides ist per `git filter-repo` aus allen
    Commits entfernt, die Passwörter sind unabhängig davon zu wechseln.
  - **Lizenz gesetzt.** Das Theme steht unter **GPLv2 oder später**: `LICENSE` im
    Repo und im Theme (wandert damit ins Zip), `License:`/`License URI:` im
    Theme-Header, dazu `readme.txt` nach WordPress-Konvention mit Copyright,
    Schrift-Nachweis und dem Hinweis, dass Logo und Wortmarke des Vereins
    **nicht** von der GPL erfasst sind.
  - **Inter-Schrift korrekt nachgewiesen.** Die beiden selbst gehosteten
    `.woff2`-Dateien stehen unter der SIL Open Font License 1.1, die verlangt,
    dass der Lizenztext mitgeliefert wird — er liegt jetzt als
    `assets/fonts/OFL.txt` im Theme und im Zip.
  - **Die Prüfskripte halten das fest:** `bin/check-theme.sh` verlangt `LICENSE`,
    `readme.txt` und `assets/fonts/OFL.txt` sowie die beiden Lizenz-Header-Felder,
    `bin/check-zip.sh` prüft, dass alle drei wirklich im Archiv liegen.

- **Zip-Erzeugung und Prüfungen in GitHub Actions.** Das Release-Zip entstand bisher
  nur, wenn jemand `bin/theme-zip.sh` auf seinem Rechner ausführte — mit Checkout,
  Shell und `zip` als Voraussetzung, und ohne dass das Ergebnis irgendwo ankam.
  Neu baut die CI es mit: Jeder Pull Request legt das Zip als Artefakt
  „idt-deutschlandtakt-zip" ab, ein Tag `v<version>` erzeugt über
  `.github/workflows/release.yml` ein GitHub-Release mit dem Zip als Asset. Der
  Release-Workflow bricht ab, wenn der Tag nicht zur Version im Theme-Header passt.
  Das lokale Skript bleibt unverändert der Fallback — die CI ruft dasselbe auf.
  - **Prüfungen, die jeder Pull Request bestehen muss** (`.github/workflows/ci.yml`),
    in vier Jobs: *Konventionen & Syntax* auf PHP 8.0/8.2/8.3, *Shell-Skripte &
    Compose-Datei* (ShellCheck, `docker compose config`), *Release-Zip*
    (Archivstruktur) und *WordPress-Smoke-Test* auf PHP 8.2/8.3.
  - **Die vier Konventionen aus `CLAUDE.md` sind jetzt maschinell geprüft**
    (`bin/check-theme.sh`): Version in `style.css` gegen `IDT_VERSION`, jeder
    `idt_sc_*()`/`idt_render_*()`-Aufruf gegen die tatsächlich deklarierten
    Funktionen, `idt`-Präfix auf Funktionen, Blöcken und Textdomain, und jede Farbe
    aus `idt_brand_palette()` gegen Token und `.has-…`-Klassen in `style.css` 7c.
    Ausgewertet wird über den PHP-Tokenizer (`bin/php-symbols.php`), damit
    Kommentare und Variablen keine Fehlalarme auslösen. Dazu Theme-Header,
    ABSPATH-Guards, `console.log`-/`var_dump`-Reste und JS-Syntax.
  - **Der Smoke-Test** (`bin/smoke-test.sh`) installiert ein frisches WordPress
    gegen MariaDB, aktiviert das Theme, seedet die Demo-Inhalte, rendert über
    `bin/smoke-render.php` jeden Block, jeden Shortcode und jedes Pattern und ruft
    Startseite, Unterseite, Beitrag, Suche, 404 und Login über HTTP ab. Warnungen,
    Notices und Deprecated-Meldungen aus dem Theme gelten als Fehler, `debug.log`
    muss leer bleiben — das fängt genau die Fälle, die sonst erst als weiße Seite
    auffallen.
  - Alle Prüfungen laufen unverändert lokal (`./bin/check-theme.sh`,
    `./bin/check-zip.sh`, `./bin/smoke-test.sh`) — eine Fassung für CI und Rechner.
  - Damit ein roter Lauf das Mergen wirklich verhindert, müssen die Jobs einmalig
    unter *Settings → Branches* als *Required status checks* für `main` eingetragen
    werden; das ist eine Repo-Einstellung, keine Datei.

- **Button-Stack: eckig und mobil randlos (v2.0.21).** Der Baustein ist eine Reihe gestapelter Schaltflächen, keine Karte — die gerundeten Ecken (`--radius-lg`, vom Themenblock geerbt) haben die durchlaufenden Haarlinien an den Rändern angeschnitten. Die Fläche rendert jetzt **eckig** (`border-radius: 0`).
  - **Bis 640px zieht die Fläche bis an die Viewportkanten** (Full-Bleed-Breakout `width: 100vw; margin-inline: calc(50% - 50vw)`, dieselbe Technik wie bei `.stage`/`.stage2`), auch wenn der Baustein im Seiteninhalt (`.container`/`.entry` mit max-width und Gutter) sitzt. Das Innenmaß der Zeilen fällt dabei von `--tb-pad` auf den Seiten-Gutter zurück, damit die Beschriftungen weiter mit dem übrigen Seitentext fluchten. Der Themenblock bleibt unverändert gerundet und im Textmaß.

- **Repo auf das Theme zusammengezogen.** Das Repository war noch nach seinem
  Ursprung sortiert — der Splash als eingebettetes HTML-Fragment für die fremde
  Seite `page-id-992` — und trug den ganzen Vorlauf mit: `landing.html`/`landing.css`
  („Splash 3 – Horizont") und `landing-2.html`/`landing-2.css` („Splash 4 – Zentriert")
  samt eigener `assets/`-Kopie der Logos, dazu drei Word-Vereinsvorlagen (`*.dotx`)
  und ein loses Logo-PNG. Beide Splash-Varianten leben seit v2.0.10 als Theme-Bausteine
  weiter (`idt_render_splash()`/`[splash]` und `idt_render_splash2()`/`[splash2]`), die
  Fragmente waren also nur noch eine zweite, driftende Fassung derselben Gestaltung.
  Sie sind gelöscht; über die Git-Historie bleiben sie auffindbar.
  - **`wp-idt/` ist die Wurzel geworden.** Theme, Docker-Stack, Caddy, wp-cli und
    `deploy/` lagen eine Ebene tief unter einem Ordner, der nur noch existierte, um
    sie von den Alt-Dateien zu trennen. Der Start-Befehl braucht dadurch kein
    `cd wp-idt` mehr.
  - **Build-Artefakte raus, Build-Skript rein.** `idt-deutschlandtakt-2.0.20.zip`,
    `new_t-deploy-paket.tar.gz` und `uploads.tar.gz` (~1,3 MB Binärdaten) waren
    eingecheckt und mussten bei jedem Release von Hand neu gepackt werden. Neu ist
    `bin/theme-zip.sh`: liest die Version aus dem Theme-Header, packt
    `dist/idt-deutschlandtakt-<version>.zip` mit dem Themeordner an der Archivwurzel.
    `dist/`, `*.zip` und `*.tar.gz` stehen jetzt in der `.gitignore`.
  - **`deploy/startseite-vorlage.html` gelöscht.** Eine unverlinkte Kopie des
    Startseiten-Markups, noch aus `[shortcode]`-Blöcken gebaut. Die gültige Fassung
    ist `idt_content_startseite()` in `inc/demo-content.php`; die Kopie hätte beim
    nächsten Startseiten-Umbau still veraltet. `deploy/DEPLOY.md` beschreibt jetzt,
    wie Theme-Zip und Datenbank-Dump erzeugt werden, statt sie als mitgelieferte
    Dateien aufzulisten.
  - **`CLAUDE.md` neu geschrieben.** Sie beschrieb noch das Fragment-Projekt samt
    `.page-id-992 .lp-root`-Scoping und dem `__ASSET__`-Deploy-Platzhalter — beides
    im Theme gegenstandslos. Jetzt: Repo-Aufbau, der lokale Stack und die vier
    Konventionen, die beim Ändern zählen (Version an zwei Stellen synchron,
    ein Baustein/eine Render-Funktion für Shortcode und Block, `idt`-Präfix,
    Design-Tokens statt Literalwerte).

- **Button-Stack (v2.0.20).** Der Themenblock, reduziert auf seine Linkliste: eine farbige Fläche, die nur aus den ganzflächig klickbaren Zeilen besteht (Titel, optionale Kurzbeschreibung, Pfeil, Haarlinien) — **ohne Eyebrow, Überschrift und Vortext**. Farbwahl und Verhalten bleiben identisch: `bg` nimmt Markennamen oder Hex-Wert, `idt_surface_is_dark()` wählt weiterhin das kontrastreichere Schema (`--dark`/`--light`), Nebentexte und Linien bleiben durchscheinende Abstufungen der Schriftfarbe. Block „Button-Stack (Linkliste)" (`idt/buttonstack`, Farbwähler + Link-Zeilen in der Seitenleiste), Shortcode `[buttonstack bg="ink"]Beschriftung | Link | Beschreibung[/buttonstack]`, Vorlage „Button-Stack (Linkliste ohne Kopf)" und ein Abschnitt auf der Seite **Stilelemente** (dunkle und helle Variante).
  - Das Zeilen-Markup teilen sich beide Bausteine jetzt über den neuen Helfer `idt_link_list_html()` (`inc/shortcodes.php`), damit Themenblock und Button-Stack nicht auseinanderlaufen. CSS: `.idt-buttonstack` erbt das komplette Farbschema von `.idt-themenblock` und hebt nur auf, was der fehlende Kopf betrifft — kein Innenmaß an der Fläche, Liste bündig statt mit negativen Rändern, keine Trennlinie über der ersten Zeile.
  - **Senkrechte Akzentleiste entfernt.** Die Linkliste des Themenblocks (v2.0.19) wurde links von einer 3 px breiten Leiste (`--tb-rail`, `.idt-themenblock__list::before`) begleitet; die Haarlinien zwischen den Zeilen tragen die Gliederung bereits, die Leiste war ein Element zu viel. Sie ist samt Farbtoken ersatzlos gestrichen — der Eintrag zu v2.0.19 weiter unten beschreibt sie noch, dort gilt seit v2.0.20 diese Zeile.

- **Beitragsübersicht als Zeilen-Liste (v2.0.19).** „Aktuelles" zeigte die Beiträge als gestapelte Blöcke mit großem Beitragsbild, Überschrift, vollem Anriss, Schlagwort-Chips und „Weiterlesen"-Link — viel Fläche pro Beitrag, wenig Übersicht. Neu ist ein ruhiges **Zeilen-Layout**: links Datum und Chip(s), rechts Titel und ein auf 26 Wörter gekürzter Anriss, dazwischen Haarlinien, über der Liste eine kräftige Linie im Markenton. Gerendert von `idt_render_postlist()` (`inc/shortcodes.php`), genutzt von `index.php` (Beitragsübersicht, Schlagwort- und Kategorie-Archive) und vom neuen Shortcode **`[beitragsliste]`** bzw. Block **„Beitragsliste (Zeilen)"** — dort zusätzlich mit Kopfzeile („Aktuelles") und dem Link „Alle Beiträge →" auf die Beitragsseite, also als Abschnitt für beliebige Seiten einsetzbar.
  - **Chips zeigen jetzt auch Kategorien.** `idt_post_tags_html()` konnte nur Schlagwörter; der neue generische Helfer `idt_post_terms_html()` rendert Chips beliebiger Taxonomien (die Standardkategorie „Allgemein" bleibt außen vor), `idt_post_tags_html()` bleibt als Alias erhalten. Die Liste wählt automatisch: vorhandene Kategorien (die Beitragsart wie „Position", „Pressemitteilung", „Verein") haben Vorrang, sonst die Schlagwörter — per `chips=""` auch fest wählbar.
  - **Beitragsbilder bleiben erhalten**, aber kleiner: statt 16:9 über die volle Breite stehen sie jetzt in der schmalen linken Spalte unter Datum und Chip. Auf schmalen Bildschirmen klappt die Zeile auf eine Spalte um (Datum und Chip nebeneinander über dem Titel).
  - Die Beitragsauswahl von `[neuigkeiten]` (Anzahl + `tag=""`/`category=""`) steckt jetzt im gemeinsamen Helfer `idt_query_posts_by_terms()`, den beide Bausteine nutzen. CSS: neuer Block `.idt-postlist*` ersetzt die nicht mehr genutzten `.post-list*`-Regeln.

- **Vorlagen-Vorschau zeigt jetzt die Bausteine statt Shortcode-Text (v2.0.19).** In der Seitenleiste „Vorlagen" war von den neun Vorlagen praktisch nichts zu erkennen. Vier Ursachen, alle behoben:
  - **Shortcode-Blöcke in den Vorlagen.** Sechs Vorlagen bestanden aus `[shortcode]`-Blöcken. Der WordPress-Block „Shortcode" zeigt im Editor — und damit auch in der Vorschau — nur seinen rohen Text in einem Eingabefeld; die Vorlagen sahen dort aus wie Formulare. `inc/patterns.php` nutzt jetzt durchgängig die nativen `idt/*`-Blöcke — beim Eyebrow einen Absatz mit dem Markup des vorhandenen Inline-Formats, das rendert die Vorschau ohne Server-Anfrage und lässt den Text direkt im Editor bearbeiten. Dafür neu in `inc/blocks.php`: Block **„Social-Leiste"** (Icon-Reihe, Zeilen „Plattform | Link", Shortcode-Pendant `[socialrow]` in `inc/shortcodes.php`, CSS `.idt-socialrow`); der **Dunkle Einschub** hat zusätzlich ein Feld **Kennzahlen** („Zahl | Label" je Zeile), damit die Vorlage ihre beiden Kennzahlen behält, ohne dass Redaktion `[stat]` tippen muss.
  - **Karten-Raster galt als ungültiger Block.** In den drei Raster-Vorlagen (Konzept-, Themen-, News-Karten) und in den Demo-Inhalten fehlte im gespeicherten Rahmen die von WordPress generierte Blockklasse `wp-block-idt-kartenraster`, die `useBlockProps.save()` beim Speichern setzt. Der Editor hielt den Block deshalb für ungültig und zeigte statt der Karten die Meldung „Block-Wiederherstellung versuchen" — genau das war in der Vorschau zu sehen. Der Rahmen kommt jetzt aus `idt_kartenraster_open()` / `idt_kartenraster_close()` (inc/blocks.php), sodass Vorlagen, Demo-Inhalte und `save()` nicht mehr auseinanderlaufen können.
  - **Vorschaubreite.** Ohne Angabe rendert WordPress eine Vorlagen-Vorschau in 700px Breite. Ein dreispaltiges Karten-Raster greift aber erst ab 960px, die Splash-Bühne ist auf 1280px gebaut — die Vorschau zeigte also Layouts, die im Frontend nie vorkommen. Jede Vorlage bekommt jetzt ein passendes `viewportWidth` (700–1280).
  - **Splash-Bühne ohne Skalierungs-Script.** `assets/scale.js` läuft nur im Frontend, und Vorschau-Rahmen haben keine brauchbare Viewport-Höhe (`height: 86vh`). Neu: `assets/editor.css` (nur im Editor geladen, `add_editor_style()`) ersetzt beides per CSS — feste Bühnenhöhe und eine Skalierungs-Treppe, die die Canvas die Bühne immer voll decken lässt. Dieselbe Datei gleicht im Karten-Raster den zusätzlichen Block-Rahmen des Editors aus, damit die Karten dort gleich hoch sind wie im Frontend.

- **Themenblock mit Linkliste (v2.0.19).** Neues Designelement für den Einstieg in einen Themenbereich: farbige Fläche mit optionalem Eyebrow, Überschrift, Einleitung, Fließtext und einer **beliebig langen Linkliste** — jede Zeile eine ganzflächig klickbare Reihe aus Titel, Kurzbeschreibung und Pfeil, getrennt durch Haarlinien, links begleitet von einer senkrechten Akzentleiste. Die Liste läuft randlos bis an die Kanten der Fläche (negative Ränder heben das Innenmaß auf, die Zeilen bringen es als eigenes Innenmaß zurück).
  - **Hintergrundfarbe frei einstellbar.** Block „Themenblock mit Linkliste" (`idt/themenblock`) mit **Farbwähler** in der Seitenleiste — dafür kennt `assets/blocks.js` jetzt den Feldtyp `color` (`ColorPalette` mit den Markenfarben als Vorschläge, freie Farbwahl bleibt möglich). Als Shortcode `[themenblock bg="ink" eyebrow="…" title="…" lead="…"]Fließtext --- Beschriftung | Link | Beschreibung[/themenblock]`; `bg` nimmt einen Markennamen (ink, paper, paper-2, violet, cyan, yellow, gray, …) **oder** einen freien Hex-Wert.
  - **Schriftfarbe folgt dem Kontrast.** `idt_surface_is_dark()` vergleicht serverseitig den WCAG-Kontrast der Fläche zu Papier und zu Tinte und wählt das besser lesbare Schema (`.idt-themenblock--dark` / `--light`); Nebentexte, Linien und Akzentleiste sind durchscheinende Abstufungen der Schriftfarbe, damit auch frei gewählte Farben tragen. Neue Helfer in `inc/shortcodes.php`: `idt_color_hex()`, `idt_color_luminance()`, `idt_parse_link_lines()` (drittes Feld = Kurzbeschreibung).
  - Die Editor-Markenpalette liegt jetzt in `idt_brand_palette()` (functions.php) — eine Quelle für `editor-color-palette` und die Farbwähler der eigenen Blöcke. Dazu Block-Pattern „Themenblock mit Linkliste" und ein Abschnitt auf der Seite **Stilelemente** (dunkle und helle Variante).

- **Trenner im Markenlook: Verlauf sichtbar, neuer kurzer Zwischenstrich (v2.0.19).** Ein im Editor gewählter Verlauf blieb am WordPress-Trenner (`core/separator`) wirkungslos — der Block zeichnet den Strich als Rahmenlinie (`border-top: 2px solid`, Farbe = Textfarbe), Farbe und Verlauf legt WordPress dagegen als *Hintergrund* ab (`.has-background`, `.has-…-gradient-background`). Auf einem Element ohne Höhe ist dieser Hintergrund unsichtbar, sichtbar blieb die graue Rahmenlinie. Die Core-Regeln, die das auffangen, stehen in `wp-block-library-theme` — das lädt WordPress nur für Themes mit `wp-block-styles`, was den WP-Standardlook mitbrächte. Neu deshalb Abschnitt 7d in `style.css`: Rahmen weg, echte Höhe, Hintergrund sichtbar (ohne Farbwahl feine Linie im Rahmenton, mit Farbe oder Verlauf 4 px mit runden Enden). Dazu zwei Blockstile unter „Trenner → Stile" (`idt_register_core_block_styles()`, `inc/blocks.php`): **Verlauf (Violett → Cyan)** über die volle Textbreite und **Kurzer Strich** — die kurze Akzentmarke (56 × 4 px) am linken Textrand als Zwischenstrich zwischen zwei Abschnitten; eine im Farbbereich getroffene eigene Wahl sticht den Stil-Verlauf weiterhin (`:not(.has-background)`). Demo-Seite „Stilelemente" zeigt beide Stile.

- **Suche als Lupe + seitenfüllendes Overlay (v2.0.18).** Im Menüband stand bisher ein kleines Suchfeld (`searchform.php` via `get_search_form()`), das auf Mobil im aufgeklappten Menü verschwand. Jetzt zeigt die Kopfleiste rechts nur noch eine **Lupe** (`.idt-searchtoggle`, neben dem Hamburger in der neuen `.site-header__actions`-Leiste); ein Klick öffnet die Suche als **Ebene über der ganzen Seite** (`idt_render_search_overlay()` in der neuen `inc/search.php`, Verhalten in `assets/search.js`): großes Suchfeld im Markenstil, Verlaufskante Violett→Cyan, Schließen per ×, Esc oder Klick daneben, Fokus bleibt im Overlay, Seite dahinter scrollt nicht.
  - **Treffer schon beim Tippen.** Der neue REST-Endpunkt `idt/v1/suche` (öffentlich lesbar, nur veröffentlichte Beiträge/Seiten) liefert die Treffer als **fertig gerendertes Markup** — dieselben Karten (`.idt-result`: Inhaltstyp, Datum, Titel, Anriss, Schlagwörter, hervorgehobener Suchbegriff), die auch die Ergebnisseite nutzt. Vorschau und Ergebnisseite können damit nicht auseinanderlaufen. Anfragen sind entprellt, veraltete Antworten werden verworfen; fällt die Vorschau aus, führt die Eingabetaste weiterhin auf die Ergebnisseite.
  - **Eigene Ergebnisseite `search.php`.** Suchergebnisse liefen bisher über den Blog-Index (`index.php`) mit der normalen Beitragsliste. Jetzt eigene Vorlage mit Trefferzahl, großem Suchfeld zum Nachschärfen, den Treffer-Karten und einem freundlichen Leerzustand; die tote Suchverzweigung in `index.php` ist entfallen.
  - **Ohne JavaScript nutzbar.** Die Lupe ist ein Link auf `#idt-searchbox`; eine `:target`-Regel klappt das Overlay auch ohne Skript auf, das Formular darin führt regulär zur Ergebnisseite. Das kompakte `searchform.php` bleibt für alles, was WordPress selbst ausgibt (Widgets, Suchblock).

- **Kopfmenü-Logo im Backend einstellbar (v2.0.17).** Das Bild im Menüband war schon über „Design → Website-Identität → Logo" austauschbar (`add_theme_support('custom-logo')`, Fallback bleibt das mitgelieferte Marken-PNG), seine Höhe steckte aber fest im Stylesheet (`.site-header__brand img { height: 38px }`) — ein Logo mit anderem Seitenverhältnis saß dadurch zu groß oder zu klein. Neu: Einstellung `idt_header_logo_height` (Zahlenfeld 20–120 px, Vorgabe 38) direkt unter dem Logo-Feld im WordPress-Standardbereich „Website-Identität". Sie überschreibt per Inline-CSS das neue Token `--header-logo-h` (style.css), das die Header-Regel jetzt nutzt; ausgegeben wird nur, was von der Vorgabe abweicht (`idt_header_logo_css()`). Live-Vorschau ohne Neuladen über `assets/customize-preview.js` (`transport => postMessage`). Die Bildregel bekam zusätzlich `max-width: 100%` und `object-fit: contain`, damit auch ein sehr breites Logo das Menüband nicht sprengt.

- **Karten-Raster: Karten gleich groß nebeneinander (v2.0.17).** Karten (`[concept]`, `[newscard]`, `[card]`) waren bisher nur einzeln einsetzbar; für Reihen mussten sie in den WordPress-**Spalten**-Block gelegt werden — jede Karte behielt dabei ihre eigene Höhe, und in Shortcode-Blöcken abgelegte Karten standen jeweils allein über die volle Breite. Neu: Container-Block **„Karten-Raster“** (`idt/kartenraster`, `inc/blocks.php` + `assets/blocks.js`) — im Unterschied zu den übrigen Elementen ein **statischer Block mit InnerBlocks**: gespeichert wird nur der Rahmen `<div class="idt-cards …">`, die Karten darin bleiben eigenständige, einzeln über die Seitenleiste bearbeitbare Blöcke. Spaltenzahl (2/3/4/automatisch) in der Seitenleiste. Als Shortcode-Pendant `[cards cols="3"] … [/cards]` (`idt_sc_cards()`, mit `idt_strip_autop()` gegen wpautop-Reste, die sonst leere Rasterzellen erzeugen). CSS: `.idt-cards` als Grid (Handy 1 Spalte, ab 720px zwei, ab 960px die gewählte Zahl; `--auto` = auto-fit ab 280px Kartenbreite), Karten füllen die Zelle auf volle Höhe (`.idt-concept` ist dafür jetzt selbst Flex-Spalte). Patterns **Konzept-Karten** und **News-Karten** nutzen statt Spalten das Raster; neues Pattern **Themen-Karten (Einstiegs-Raster)** — vier gleich große Einstiegskarten ohne Icon. Demo-Inhalte entsprechend umgestellt.

- **Footer-Slogan über den Customizer pflegbar (v2.0.16).** Der Slogan neben dem Footer-Logo („Mehr Verkehr auf die Schiene…") stand bisher fest in `footer.php`. Neuer Customizer-Abschnitt „Footer" (`idt_customize_register()`, functions.php) mit Textarea-Feld `idt_footer_slogan` — Redaktion kann den Text jetzt unter Design → Customizer → Footer ändern, ohne Code anzufassen. Vorgabetext bleibt der bisherige Slogan (`idt_footer_slogan_default()`), `footer.php` liest ihn per `get_theme_mod()` mit diesem Fallback.

- **Beitrags-Filterung jetzt auch nach Kategorien (v2.0.15).** Analog zur Schlagwort-Filterung (s. u.) filtert die Beitragsansicht jetzt auch nach der WordPress-Taxonomie `category`. Neu: `idt_render_taxonomy_filter()` (generische Filterleiste, ersetzt intern die bisherige `idt_render_tag_filter()`-Logik) plus `idt_render_category_filter()` in `inc/shortcodes.php`. `index.php` zeigt die Filterleiste auf Kategorie-Archiven (`is_category()`, eigener Eyebrow „Kategorie"); die Standardkategorie „Uncategorized/Allgemein" wird dabei ausgeblendet. `[neuigkeiten]`/Block „Beiträge-Übersicht" um `category=""` (Slug-Filter, kommagetrennt, UND-verknüpft mit `tag=""`) erweitert.

- **Neue Seitenvorlage „Menü ohne Logo" (v2.0.14).** Für die Startseite, deren Splash-Bühne das Logo bereits selbst zeigt und es im Menüband daher nicht doppelt braucht: neue Vorlage `page-no-logo.php` (analog zu `page-no-nav.php`) zeigt weiterhin Menü + Suche im Header, blendet aber nur den Marken-Block (`.site-header__brand`) links oben aus. Geprüft wird wie beim „Ohne Menüband"-Pendant der Template-Slug (`get_page_template_slug()` in `header.php`), damit es auch für die statische Startseite greift, die von `front-page.php` gerendert wird. CSS: `.site-header--no-logo .site-header__inner` schaltet `justify-content` auf `flex-end`, damit das Menü ohne Logo-Gegengewicht rechtsbündig bleibt statt an den linken Rand zu rutschen.

- **Seiten-Hero: Website-Name mit Pfeil entfernt (v2.0.12).** Über jeder Seitenüberschrift stand als Eyebrow der Website-Name (`bloginfo('name')`), dem die `.idt-eyebrow`-Regel per `::before` einen Pfeil `→` voranstellte — auf jeder Seite also „→ Website-Name" über dem eigentlichen Titel. Diese Zeile ist jetzt aus `page.php` entfernt, sodass nur noch der Seitentitel (`<h1>`) im `.page-hero` steht. Die `.idt-eyebrow`-CSS-Regel selbst bleibt unangetastet, da sie an anderen Stellen (Shortcodes, Übersichtsseiten, 404) bewusst genutzt wird.

- **Fehlende Standard-Theme-Funktionen ergänzt (v2.0.12).** Vier Lücken geschlossen, die das Theme trotz sonst hohem Reifegrad noch hatte:
  - **Beitragsbilder werden jetzt angezeigt.** `add_theme_support('post-thumbnails')` und der Theme-Tag `featured-images` waren gesetzt, aber kein Template rief je `the_post_thumbnail()` auf — gesetzte Beitragsbilder blieben unsichtbar. Jetzt gerendert im Einzelbeitrag (`single.php`, im Textmaß über dem Fließtext, `.post-hero-img`), in der Beitragsliste (`index.php`, `.post-list__thumb`) und in den dynamischen News-Karten des `[neuigkeiten]`-Loops (`inc/shortcodes.php`, randlos an der Kartenoberkante, `.idt-newscard__img`). Alle konditional (`has_post_thumbnail()`), also kein Layoutbruch für Beiträge ohne Bild.
  - **Beitrags-Navigation (vorheriger/nächster Beitrag)** am Ende von `single.php` (`previous_post_link`/`next_post_link`, `.post-nav`) — Leser landen nicht mehr in der Sackgasse.
  - **Suche auffindbar gemacht.** Neues markenkonformes `searchform.php` (Feld + Icon-Button, neues `search`-Icon in `idt_icon()`) via `get_search_form()` im Hauptmenü — auf Desktop inline im Menü, mobil im aufklappbaren Menüband. `index.php` verarbeitete Suchergebnisse bereits, es fehlte nur der Einstieg.
  - **Custom-Logo + Footer-Widget-Bereich.** `add_theme_support('custom-logo')` — das Kopf-Logo ist über „Design → Website-Identität" pflegbar (Fallback bleibt das mitgelieferte Marken-PNG; der dunkle Footer nutzt weiter die Inverse-Variante). Neuer registrierter Widget-Bereich `footer`: zugewiesene Widgets erscheinen als zusätzliche Footer-Spalten (`.fcol`, Widget-Titel als `<h4>`) neben „Themen"/„Mitmachen". Zusätzlich `.screen-reader-text`-Utility ergänzt (von `searchform.php` genutzt).

- **Beitrags-Filterung nach Schlagwörtern (v2.0.12).** Die Beitragsansicht filtert jetzt nach echten WordPress-Schlagwörtern (`post_tag`) statt nach dekorativen Chips. Neu: Helfer `idt_post_tags_html()` (rendert die tatsächlichen Schlagwörter eines Beitrags als farbige, deterministisch eingefärbte Chips, optional verlinkt) und `idt_render_tag_filter()` (Filterleiste aus allen vergebenen Schlagwörtern mit „Alle"-Reset und Hervorhebung des aktiven Schlagworts) in `inc/shortcodes.php`. `index.php` zeigt die Filterleiste auf der Beitragsübersicht (`is_home()`) und den Schlagwort-Archiven (`is_tag()`, eigener Eyebrow „Schlagwort") sowie die Schlagwörter je Listeneintrag; `single.php` zeigt die Schlagwörter unter dem Beitrag. `[neuigkeiten]`/Block „Beiträge-Übersicht" um `tag=""` (Slug-Filter, kommagetrennt) erweitert und zeigt statt der alten rotierenden Fake-Beschriftung (`$tagmap`) die echten Schlagwörter des jeweiligen Beitrags. CSS (`.idt-tag--link`, `.idt-tag.is-active`, `.idt-tagfilter`, `.post-list__tags`, `.post-tags`). Demo-Beiträge bekommen echte Schlagwörter (`inc/demo-content.php`: Gesetzgebung/Infrastruktur/Fahrplan/Klimaschutz), sodass das Filtern out of the box sichtbar ist. Der dekorative `[tag]`-Shortcode bleibt unverändert (reines Stilelement in Fließtext).

- **Nummerierte Aufzählungen unterstützt und gestaltet (v2.0.12).** Bisher setzte `style.css` (Sektion 3, Basis-Styles) für `ul, ol` nur Rand und Einzug, ohne `list-style` — die Markerdarstellung hing allein am Browser-Default, und nummerierte Listen (`ol` / WordPress-Listenblock „geordnet") hatten keine markenkonforme, verlässliche Darstellung. Jetzt sind die Marker explizit gesetzt: `ul` → Punkte, `ol` → Ziffern (greift auch für `ul/ol.wp-block-list`). Die Marker erscheinen im Markenton (`li::marker` in Violett/`--accent-primary`, kräftig); die Ziffern nummerierter Listen zusätzlich in der Mono-Schrift mit Tabellenziffern, passend zum Kennzahlen-/Label-Look. Verschachtelte `ol` zählen sauber weiter (Ebene 2 `a`, Ebene 3 `i`). Die klassenspezifischen `list-style:none`-Listen (Navigation, Footer, Beitragsliste) bleiben durch höhere Spezifität unberührt.

- **„Alle Beiträge"-Link folgt der Beitragsseite + Auto-Übernahme (v2.0.12/2.0.13).** Der „← Alle Beiträge"-Link in `single.php` nutzte `idt_page_url('aktuelles', home_url('/'))` und fiel auf die **Startseite** zurück, sobald keine Seite mit exaktem Slug `aktuelles` existierte (z. B. abweichender Slug auf der Live-Seite). Neuer Helfer `idt_blog_url()` nutzt jetzt bevorzugt die in WordPress konfigurierte **Beitragsseite** (`page_for_posts`, Einstellungen → Lesen) — unabhängig vom Slug; Slug-Suche + Startseite bleiben nur als Fallback. Zusätzlich `idt_adopt_posts_page()` (Hook `after_setup_theme`): übernimmt **einmalig** die Seite „Aktuelles" als Beitragsseite, falls dort keine gesetzt ist (Guard `idt_posts_page_adopted`) — so funktioniert der Link auch auf Bestandsinstallationen ohne manuelles Nachpflegen; eine bewusste spätere Änderung der Redaktion wird nicht überschrieben.

- **Splash v2-Link-Stack robuster gegen überschriebenes `display` (v2.0.11).** `.stage2__menu` (Theme) bzw. `.page-id-992 .lp-root .stage2__menu` (`landing-2.css`, Root-Fragment) ist jetzt selbst `display:flex; flex-direction:column` statt sich allein auf `display:block` der einzelnen `.stage2__row`-Links zu verlassen — Flex-Items werden laut Spezifikation immer „blockified" und per Default gestreckt, dieselbe Technik wie bei `.splash-links` (Horizont-Splash v1), die dort bereits nachweislich funktioniert. Zusätzlich im Root-Fragment `landing-2.html`/`landing-2.css`: die generischen Klassennamen `.menu`/`.row`/`.center` (Kollisionsrisiko mit gleichnamigen Klassen des fremden Host-Themes auf der Zielseite page-id-992) auf `.stage2__menu`/`.stage2__row`/`.stage2__center` umbenannt — konsistent mit dem Theme-Baustein.

- **Horizont-Splash v2 „Zentriert" als Theme-Baustein (v2.0.10).** Die fluide Splash-Alternative aus `landing-2.*` (Repo-Root) jetzt auch als vollwertiger Theme-Baustein: `idt_render_splash2()` (functions.php), Shortcode `[splash2]` (inc/shortcodes.php, gleiche Syntax wie `[splash]`) und Block „Horizont-Splash v2 (Zentriert)" im Inserter (inc/blocks.php, wiederverwendet die Sidebar-Felder von `[splash]`). CSS-Sektion 6b in style.css: `.stage2` füllt randlos 100svh und zentriert Logo + Link-Stack per Flexbox — kein 1280×800-Fixmaß, kein Scaling-Script. `main#content`-Padding-Ausnahme (kein Fußabstand nach Splash) gilt jetzt für `.stage` und `.stage2`. Nebenbei behoben: `IDT_VERSION`-Konstante in functions.php war beim letzten Versionsstand (2.0.9) nicht mitgezogen worden (Cache-Busting der Assets hing dadurch am alten Stand) — jetzt mit dem style.css-Header synchron.

- **Versions-Checkpoint (v2.0.9).** Reiner Versionsstand-Release ohne Theme-Code-Änderungen. Die tatsächlichen Änderungen betrafen die eigenständigen Splash-Fragmente `landing.html`/`landing-2.*` im Repo-Root (Einbettung in Seite page-id-992, nicht Teil dieses Themes): Skalierung nutzt jetzt `stage.clientWidth` statt `window.innerWidth` inkl. ResizeObserver (behebt Abschneiden auf breiten Displays), sowie eine neue fluide Splash-Variante „Zentriert" (`landing-2.html`/`landing-2.css`) ohne Fixmaße/Skalierungs-Script als Alternative zum Horizont-Splash.

- **Mobiler Splash: weiße Diagonalstreifen im hellen Bereich (v2.0.7).** Der Creme-Bereich hinter dem Logo bekommt mobil zwei durchgehende weiße 131°-Streifen als .canvas-Hintergrund (gleicher Winkel wie das dunkle Band; .splash-bottom deckt den unteren Teil ab) — das Horizont-Motiv ist damit auch im Handy-Layout vollständig.

- **Splash-Links konfigurierbar + kein Fußabstand nach Splash (v2.0.6).** idt_render_splash() nimmt Pills ([Label, URL]-Paare) und Caption als Parameter; [splash] parst „Beschriftung | Link"-Zeilen aus dem Shortcode-Inhalt (leer = Standard-Links), caption=""blendet die Schlagzeile aus. Neuer Block „Horizont-Splash" mit Seitenleisten-Feldern (Textarea + Caption). Gemeinsamer Parser idt_parse_button_lines() (auch vom Pill-Stack genutzt). CSS: main#content:has(.entry > .stage:last-child) → padding-bottom 0, Splash schließt direkt an den Footer an, wenn er das letzte Element ist.

- **Mobiler Splash + Marken-Farbpalette im Editor (v2.0.5).** Splash ≤ 820px: statt der skalierten Bühne (Pills wurden ~13px klein) ein gestapeltes Layout in Originalgröße — Logo zentriert auf Papier, dunkles Diagonal-Band (Gradient) mit voll antippbaren Pills; Wrapper .splash-bottom ist auf Desktop display:contents, scale.js räumt Inline-Maße ≤ 820px. Fix: .canvas .splash-logo neutralisiert die .entry-img-Regel (margin/border-radius), die die Zentrierung aushebelte. Editor: WP-Standard-Farb-/Verlaufsvorschläge durch IDT-Palette ersetzt (editor-color-palette/-gradient-presets, 10 Markenfarben + 2 Verläufe, .has-…-Klassen in style.css 7c).

- **Splash randlos + Vorlage „Ohne Menüband" (v2.0.4).** Splash-Bühne dehnt sich jetzt über die volle Viewportbreite: Balken mittig positioniert (calc(50% ± Offset)), scale.js verbreitert die Canvas auf max(1280, vw/s) — kein „Karten"-Rand mehr auf breiten Screens, Mobil unverändert. Neue Seitenvorlage page-no-nav.php („Ohne Menüband"): blendet den Site-Header aus (Prüfung via get_page_template_slug() in header.php, greift dadurch auch für die statische Startseite); ohne page-hero, Inhalt bestimmt die Bühne selbst.

- **Pill-Button-Stack (v2.0.3).** Neuer Block „Pill-Button-Stack" + Shortcode `[pillstack]`: beliebig viele Pill-Buttons vertikal gestapelt, alle automatisch gleich breit (inline-flex + align-items:stretch — die breiteste Beschriftung bestimmt die Breite). Konfiguration über Seitenleiste: Buttons als „Beschriftung | Link"-Zeilen, Stil (Outline/Solid/hell für dunklen Grund), Ausrichtung, optionale Mindestbreite. `[pill]` um `style="on-ink"` erweitert. Außerdem: `main#content` bekommt padding-bottom (Footer klebt nie mehr am Inhalt).

- **Stilelemente als native, interaktive Blöcke (statt Shortcode-Text).** Alle Block-Elemente (Lead, Callout, Diagonal, Karte, Kennzahl, Takt, Button, Pill, Konzept-Karte, Einschub, News-Karte, Beiträge-Übersicht) sind jetzt echte dynamische Gutenberg-Blöcke (`inc/blocks.php` + `assets/blocks.js`). Bearbeitung über Formularfelder in der Seitenleiste (Text/Auswahl/Schalter/Schieberegler) mit **Live-Vorschau** (ServerSideRender) — kein HTML/Shortcode-Tippen mehr. Server-Render nutzt die bestehenden Shortcode-Funktionen weiter. Eigene Inserter-Kategorie „Deutschlandtakt". Damit erscheinen die Elemente auch zuverlässig im **„/"-Befehlsmenü** — inkl. **Pill-Button**. Die nun redundanten Einzel-Element-Patterns wurden entfernt (Kompositions-Patterns bleiben).

- **Buttons kuratiert + Gradient-Rahmen-Button.** Auswahl im Editor = nur die Marken-Buttons (Primary/Violett, Sekundär/Cyan, Outline, Gradient-Rahmen Violett→Cyan) + Pill. Neuer Gradient-Rahmen-Button (`[btn variant="gradient"]`, `.idt-btn--gradient`, Masken-Technik). WordPress-Standard-Button-Block im Editor ausgeblendet (`editor-formats.js`, reversibel). Ghost/Inverse bleiben per CSS für Sonderfälle, aber nicht als Auswahl.
- **Diagonal-Aussageblock: durchgehende Streifen.** `.idt-diagonal` nutzt jetzt durchgehende, parallele Diagonalstreifen (Violett + Cyan auf Ink) per `repeating-linear-gradient` statt der zwei gezackt wirkenden Blob-Pseudoelemente.
- **Startseite ist im Editor bearbeitbar.** Ursache: `front-page.php` war eine feste PHP-Vorlage, die den Seiteninhalt ignorierte. Jetzt rendert sie `the_content()`; die Startseite ist mit dem Design als Blöcke/Shortcodes befüllt (Hero, Kennzahlen, Konzept-Karten mit Links, Einschub, Beiträge-Übersicht) → vollständig editierbar. `[concept]` um optionales `href` erweitert (klickbare Karten).
- **Beitragsansicht als Designelement.** Neuer Shortcode `[neuigkeiten count="3"]` rendert die neuesten Beiträge automatisch als News-Karten-Reihe (wie auf der Startseite) — als wiederverwendbares Element auf jeder Seite, auch im Editor unter „Beiträge-Übersicht (dynamisch)" auswählbar.
