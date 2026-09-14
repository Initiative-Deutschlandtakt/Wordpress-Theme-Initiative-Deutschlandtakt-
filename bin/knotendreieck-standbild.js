#!/usr/bin/env node
/**
 * Baut das Standbild des Knotendreiecks neu.
 *
 *   ./bin/knotendreieck-standbild.js
 *
 * Ergebnis: theme/idt-deutschlandtakt/blocks/knotendreieck/standbild.svg —
 * die Grafik zur Minute :30, ausgeliefert als <noscript>-Ersatz (und damit
 * auch das, was in einer PDF-Druckansicht ohne JavaScript erscheint).
 *
 * Gezeichnet wird von denselben Funktionen wie im Browser: view.js gibt
 * fullSvg() unter Node frei. Zwei Zeichenwege fuer dasselbe Bild liefen
 * unweigerlich auseinander — deshalb wird das Standbild gebaut, nicht
 * gepflegt. Nach jeder Aenderung an Geometrie, Farben oder Vorgabetexten in
 * view.js dieses Skript laufen lassen und das Ergebnis mitcommitten.
 *
 * Node ist nur hier noetig, nicht im Theme: das Ergebnis liegt im Repository.
 */
'use strict';

const fs   = require('fs');
const path = require('path');

const block = path.join(__dirname, '..', 'theme', 'idt-deutschlandtakt', 'blocks', 'knotendreieck');
const view  = require(path.join(block, 'view.js'));
const out   = path.join(block, 'standbild.svg');

/* Minute :30 — beide Knotenfenster liegen symmetrisch dazu, die Fahrzeuge
   stehen also nirgends im Halt und das Bild zeigt den Takt in Bewegung. */
const svg = '<?xml version="1.0" encoding="UTF-8"?>\n' + view.fullSvg(30, false) + '\n';

fs.writeFileSync(out, svg, 'utf8');
console.log('Fertig: ' + path.relative(path.join(__dirname, '..'), out) + ' (' + svg.length + ' Bytes)');
