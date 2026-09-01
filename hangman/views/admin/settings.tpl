<div class="row">
    <div class="col-md-6">
        <div class="box">
            <div class="box-header"><h3 class="box-title">{lang('settings','hangman')}</h3></div>
            <div class="box-body">
                <div class="alert alert-info">
                    These values are read only. Change <code>application/modules/hangman/config/hangman.php</code> and clear the FusionCMS cache to update them.
                </div>
                <dl class="dl-horizontal">
                    <dt>{lang('guesses','hangman')}</dt><dd>{$settings.guesses}</dd>
                    <dt>{lang('days_old_del','hangman')}</dt><dd>{$settings.days_old_del}</dd>
                    <dt>{lang('guest_allow','hangman')}</dt><dd>{if $settings.guest_allow}Enabled{else}Disabled{/if}</dd>
                    <dt>{lang('letter_buttons','hangman')}</dt><dd>{if $settings.letter_buttons}Enabled{else}Disabled{/if}</dd>
                    <dt>{lang('reveal_initial_letter','hangman')}</dt><dd>{if $settings.reveal_initial_letter}Enabled{else}Disabled{/if}</dd>
                    <dt>{lang('reward_enabled','hangman')}</dt><dd>{if $settings.reward_enabled}Enabled{else}Disabled{/if}</dd>
                </dl>
                <h4>{lang('rewards','hangman')}</h4>
                <table class="table table-striped table-condensed">
                    <thead><tr><th>{lang('difficulty','hangman')}</th><th>VP</th><th>DP</th></tr></thead>
                    <tbody>
                        <tr><td>{lang('easy','hangman')}</td><td>{$settings.reward_easy_vp}</td><td>{$settings.reward_easy_dp}</td></tr>
                        <tr><td>{lang('medium','hangman')}</td><td>{$settings.reward_medium_vp}</td><td>{$settings.reward_medium_dp}</td></tr>
                        <tr><td>{lang('hard','hangman')}</td><td>{$settings.reward_hard_vp}</td><td>{$settings.reward_hard_dp}</td></tr>
                        <tr><td>{lang('mythic','hangman')}</td><td>{$settings.reward_mythic_vp}</td><td>{$settings.reward_mythic_dp}</td></tr>
                        <tr><td>{lang('mythicplus','hangman')}</td><td>{$settings.reward_mythicplus_vp}</td><td>{$settings.reward_mythicplus_dp}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="box">
            <div class="box-header"><h3 class="box-title">{lang('statistics','hangman')}</h3></div>
            <div class="box-body">
                <p>{lang('words','hangman')}: <strong>{$wordCount}</strong></p>
                <p>{lang('running_games','hangman')}: <strong>{$gameCount}</strong></p>
                <p><a href="{$url}admin/hangman/highscores" class="btn btn-default btn-sm">{lang('highscore','hangman')}</a></p>
            </div>
        </div>

        <div class="box">
            <div class="box-header"><h3 class="box-title">{lang('rewardHistory','hangman')}</h3></div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-condensed">
                    <thead><tr>
                        <th>{lang('gameId','hangman')}</th>
                        <th>User ID</th>
                        <th>VP</th>
                        <th>DP</th>
                        <th>{lang('difficulty','hangman')}</th>
                        <th>{lang('date','hangman')}</th>
                    </tr></thead>
                    <tbody>
                    {foreach from=$rewardHistory item=reward}
                        <tr>
                            <td>{$reward.game_id}</td>
                            <td>{$reward.user_id}</td>
                            <td>{$reward.vote_points}</td>
                            <td>{$reward.donate_points}</td>
                            <td>{$reward.difficulty}</td>
                            <td>{$reward.created_at}</td>
                        </tr>
                    {foreachelse}
                        <tr><td colspan="6">{lang('rewardHistoryEmpty','hangman')}</td></tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
