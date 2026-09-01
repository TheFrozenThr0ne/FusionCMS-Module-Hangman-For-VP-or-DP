# WoW Hangman for FusionCMS 9.5.0

A World of Warcraft Hangman module for **FusionCMS 9.5.0** with Vote Point (VP) and Donation Point (DP) rewards.

A ported version of hhunderter/Hangman. Original project: https://github.com/hhunderter/Hangman

<img width="863" height="541" alt="image" src="https://github.com/user-attachments/assets/2f74540c-2ff5-471c-8d57-e730cc0add1d" />


Letter buttons

<img width="587" height="150" alt="image" src="https://github.com/user-attachments/assets/1edbb161-63e2-4bbe-b98c-32104f041b4a" />

or

<img width="595" height="85" alt="image" src="https://github.com/user-attachments/assets/d885d4c1-df93-45f2-931e-d89e80d7d577" />

## Features

* WoW word list in German and English
* Five difficulty levels: Easy, Medium, Hard, Mythic, and MythicPlus
* VP/DP rewards calculated server-side
* Protection against duplicate payouts per game
* High scores and reward history
* Optional guest play; guests do not receive VP or DP
* Optional reveal of one or more random letter at the start of each game for long/very long words.
* Settings exclusively through `config/hangman.php`
* 150 additional WoW words: 15 per difficulty level and language

## Requirements

* FusionCMS 9.5.0
* PHP `gd` extension for the Hangman graphics
* The `account_data.vp` and `account_data.dp` table fields
* Write permissions for FusionCMS modules and `manifest.json`

## Installation

1. Upload the module to the `application/modules/hangman/` directory.
2. Import `sql/hangman.sql` into the database.
3. Clear the FusionCMS cache.
4. Open **Modules** in the admin panel and enable **Hangman**.

## Configuring VP and DP

All game and reward settings are located here:

```php
application/modules/hangman/config/hangman.php
```

Example:

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

Other important values:

```php
$config['guest_allow'] = 1;            // Guests may play, but do not receive points
$config['reward_enabled'] = 1;         // Enable VP/DP rewards
$config['guesses'] = 6;                // Allowed number of incorrect guesses
$config['letter_buttons'] = 1;         // Display letter buttons
$config['reveal_initial_letter'] = 1;  // Reveal of one or more random letter at the start of each game
```

## Security

* The browser does not send reward amounts.
* The backend determines the difficulty, win status, and reward itself.
* A unique `game_id` in `hangman_rewards` prevents duplicate payouts.
* Guests are neither added to the persistent high scores nor rewarded.

## License and Trademark Notice

World of Warcraft and all related names are trademarks of Blizzard Entertainment. This project is not affiliated with, endorsed, or approved by Blizzard Entertainment.
