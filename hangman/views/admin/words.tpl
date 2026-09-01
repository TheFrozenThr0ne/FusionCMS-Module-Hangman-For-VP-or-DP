<div class="row">
    <div class="col-md-5">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title" id="hangman-form-title">{lang('add_word', 'hangman')}</h3>
            </div>
            <form action="{$url}admin/hangman/save" method="post" id="hangman-word-form">
                <div class="box-body">
                    <input type="hidden" name="id" id="hangman-word-id" value="">
                    <div class="form-group">
                        <label for="hangman-word-text">{lang('word', 'hangman')}</label>
                        <input type="text" class="form-control" name="text" id="hangman-word-text" required>
                    </div>
                    <div class="form-group">
                        <label for="hangman-word-difficulty">{lang('difficulty', 'hangman')}</label>
                        <select class="form-control" name="difficulty" id="hangman-word-difficulty">
                            {foreach $difficulties as $difficulty}
                                <option value="{$difficulty.id}">{$difficulty.name|escape}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="hangman-word-locale">{lang('locale', 'hangman')}</label>
                        <input type="text" class="form-control" name="locale" id="hangman-word-locale" list="hangman-locales" value="english" required>
                        <datalist id="hangman-locales">
                            {foreach $locales as $locale}
                                <option value="{$locale|escape}"></option>
                            {/foreach}
                        </datalist>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">{lang('save', 'hangman')}</button>
                    <button type="button" class="btn btn-default" id="hangman-word-reset">{lang('cancel', 'hangman')}</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-7">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">{lang('words', 'hangman')}</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{lang('word', 'hangman')}</th>
                            <th>{lang('difficulty', 'hangman')}</th>
                            <th>{lang('locale', 'hangman')}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $words as $word}
                            <tr>
                                <td>{$word.text|escape}</td>
                                <td>{$word.difficulty}</td>
                                <td>{$word.locale|escape}</td>
                                <td class="text-right">
                                    <button type="button"
                                            class="btn btn-xs btn-default hangman-edit"
                                            data-id="{$word.id}"
                                            data-text="{$word.text|escape}"
                                            data-difficulty="{$word.difficulty}"
                                            data-locale="{$word.locale|escape}">
                                        {lang('edit', 'hangman')}
                                    </button>
                                    <a href="{$url}admin/hangman/delete/{$word.id}" class="btn btn-xs btn-danger hangman-delete">
                                        {lang('delete', 'hangman')}
                                    </a>
                                </td>
                            </tr>
                        {foreachelse}
                            <tr>
                                <td colspan="4">{lang('no_words', 'hangman')}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>