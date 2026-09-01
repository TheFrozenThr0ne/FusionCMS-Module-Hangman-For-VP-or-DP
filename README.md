# WoW Hangman für FusionCMS 9.5.0

Ein World-of-Warcraft-Hangman-Modul für **FusionCMS 9.5.0** mit Vote-Point- (VP) und Donation-Point-Belohnungen (DP).

Ein portierte version von hhunderter/Hangman. Original project: https://github.com/hhunderter/Hangman

<img width="863" height="541" alt="image" src="https://github.com/user-attachments/assets/2f74540c-2ff5-471c-8d57-e730cc0add1d" />

## Funktionen

- WoW-Wortliste in Deutsch und Englisch
- Fünf Schwierigkeitsstufen: Leicht, Mittel, Schwer, Mythic und MythicPlus
- Serverseitig berechnete VP-/DP-Belohnungen
- Schutz vor doppelten Auszahlungen pro Spiel
- Highscores und Belohnungsverlauf
- Optionales Spielen als Gast; Gäste erhalten keine VP oder DP
- Einstellungen ausschließlich über `config/hangman.php`
- 150 zusätzliche WoW-Wörter: 15 pro Stufe und Sprache

## Voraussetzungen

- FusionCMS 9.5.0
- PHP-Erweiterung `gd` für die Hangman-Grafik
- Die Tabellenfelder `account_data.vp` und `account_data.dp`
- Schreibrechte für FusionCMS-Module und `manifest.json`

## Installation

1. Lade das Modul in den Ordner `application/modules/hangman/` hoch.
2. Importiere `sql/hangman.sql` in die Datenbank.
3. Leere den FusionCMS-Cache.
4. Öffne im Adminbereich **Modules** und aktiviere **Hangman**.

## VP und DP konfigurieren

Alle Spiel- und Reward-Einstellungen stehen hier:

```php
application/modules/hangman/config/hangman.php
```

Beispiel:

```php
$config['reward_easy_vp'] = 1;
$config['reward_easy_dp'] = 0;

$config['reward_medium_vp'] = 2;
$config['reward_medium_dp'] = 0;

$config['reward_hard_vp'] = 3;
$config['reward_hard_dp'] = 1;

$config['reward_mythic_vp'] = 6;
$config['reward_mythic_dp'] = 2;

$config['reward_mythicplus_vp'] = 9;
$config['reward_mythicplus_dp'] = 3;
```

Weitere wichtige Werte:

```php
$config['guest_allow'] = 1;      // Gäste dürfen spielen, erhalten aber keine Punkte
$config['reward_enabled'] = 1;   // VP-/DP-Belohnungen aktivieren
$config['guesses'] = 6;          // erlaubte Fehlversuche
$config['letter_buttons'] = 1;   // Buchstaben-Schaltflächen anzeigen
```

## Sicherheit

- Der Browser sendet keine Reward-Beträge.
- Das Backend ermittelt Schwierigkeitsgrad, Sieg und Belohnung selbst.
- Eine eindeutige `game_id` in `hangman_rewards` verhindert doppelte Auszahlungen.
- Gäste werden weder in den persistenten Highscore aufgenommen noch belohnt.

## Lizenz und Markenhinweis

World of Warcraft und alle zugehörigen Namen sind Marken von Blizzard Entertainment. Dieses Projekt steht in keiner Verbindung zu Blizzard Entertainment und wird von Blizzard weder unterstützt noch genehmigt.
