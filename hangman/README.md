# Hangman for FusionCMS 9.5.0 – WoW Rewards Edition

This package updates the supplied Hangman module with World of Warcraft words and a server-side reward system.

## Features
- Vote Points as rewards.
- Optional Donation Points as rewards.
- Separate reward values for Easy / Medium / Hard.
- Rewards only after a server-side verified win.
- Duplicate payout protection using unique `hangman_rewards.game_id`.
- Reward history in the Hangman admin settings page.
- SQL schema.
- Guest play with reward preview only.
- World of Warcraft themed English and German word pools.

## Default rewards

| Difficulty | Vote Points | Donation Points |
|---|---:|---:|
| Easy | 1 | 0 |
| Medium | 2 | 0 |
| Hard | 3 | 1 |
| Mythic | 6 | 2 |
| MythicPlus | 9 | 3 |

Change these values in `application/modules/hangman/config/hangman.php`.

## Guest behaviour

If **Allow guests to play** is enabled, guests can play normally.

Guests never receive VP or DP and are not added to the persistent highscore. The reward shown on the page is a preview of what a logged-in account would receive.

## Account currency

The reward code uses:
- `account_data.vp` = Vote Points
- `account_data.dp` = Donation Points

The module does not create these core columns. Your FusionCMS database must already provide them.

## New installation

1. Copy `hangman/` to `application/modules/`.
2. Run `sql/hangman.sql`.
3. Activate/install the module and assign its permissions.
4. Configure rewards in `config/hangman.php`.
5. Visit `/hangman`.

## Existing installation

Do not rerun the fresh-install SQL over a live database.

1. Copy the updated `hangman/` module over the existing module.
2. Run `sql/migration_rewards.sql` once.
3. Run `sql/replace_words_wow.sql` if you want to replace the old word pool with the supplied WoW words.
4. Configure rewards in `config/hangman.php`.

## Security

The browser never sends the reward amount. PHP calculates it from the stored difficulty and server-side settings.

A win is determined from the word stored in the database and the letters stored for the current game.

A unique `game_id` in `hangman_rewards` prevents the same game from being paid twice.

## Supplied archive note

The uploaded archive contained truncated controller/JavaScript files. This release supplies complete versions so the game, guest preview, reward logic and image endpoint work together.
