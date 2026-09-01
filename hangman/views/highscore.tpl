<table class="table table-striped hangman-highscore">
    <thead>
        <tr>
            <th>#</th>
            <th>{lang('username', 'hangman')}</th>
            <th>{lang('score', 'hangman')}</th>
            <th>{lang('games', 'hangman')}</th>
        </tr>
    </thead>
    <tbody>
        {foreach $entries as $entry}
            <tr>
                <td>{$entry@iteration}</td>
                <td>{$entry.username|escape}</td>
                <td>{$entry.score}</td>
                <td>{$entry.games}</td>
            </tr>
        {foreachelse}
            <tr>
                <td colspan="4">{lang('no_highscores', 'hangman')}</td>
            </tr>
        {/foreach}
    </tbody>
</table>
<p class="hangman-links">
    <a href="{$url}hangman">{lang('hangman', 'hangman')}</a>
</p>
