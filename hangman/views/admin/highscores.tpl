<div class="box">
    <div class="box-header">
        <h3 class="box-title">{lang('highscore', 'hangman')}</h3>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-striped">
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
    </div>
    <div class="box-footer">
        <a href="{$url}admin/hangman/resetHighscores" class="btn btn-danger btn-sm"
           onclick="return confirm('{lang('really_reset', 'hangman')}');">
            {lang('highscore_reset', 'hangman')}
        </a>
        <a href="{$url}admin/hangman/settings" class="btn btn-default btn-sm">{lang('settings', 'hangman')}</a>
    </div>
</div>