<?php

use MX\MX_Controller;

class Hangman extends MX_Controller
{
    private array $alphabet = [
        'a','b','c','d','e','f','g','h','i','j','k','l','m',
        'n','o','p','q','r','s','t','u','v','w','x','y','z'
    ];

    private array $difficulties = [1=>'easy',2=>'medium',3=>'hard',4=>'mythic',5=>'mythicplus'];

    public function __construct()
    {
        parent::__construct();
        $this->load->config('hangman');
        $this->load->model('hangman_model');
        requirePermission('view');

        $this->difficulties = (array)($this->config->item('difficulties') ?: $this->difficulties);
        $this->hangman_model->deleteOldGames((int)$this->hangman_model->getSetting('days_old_del',30));
    }

    public function index(): void
    {
        $this->template->setTitle(lang('hangman','hangman'));
        foreach (['newGame','guess','letter','difficulty','gameWinMsg','gameLosMsg','loginfirst',
            'score','easy','medium','hard','mythic','mythicplus','rewardPreview','rewardReceived','guestRewardNotice',
            'votePoints','donatePoints','wrongGuesses','playAgain'] as $key) {
            clientLang($key,'hangman');
        }

        $data=[
            'url'=>$this->template->page_url,
            'state'=>json_encode($this->buildState($this->getGame()),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            'highscoreUrl'=>$this->template->page_url.'hangman/highscore'
        ];

        $content=$this->template->loadPage('hangman.tpl',$data);
        $page=$this->template->loadPage('page.tpl',[
            'module'=>'default','headline'=>lang('hangman','hangman'),'content'=>$content
        ]);
        $this->template->view($page,'modules/hangman/css/hangman.css','modules/hangman/js/hangman.js');
    }

    public function highscore(): void
    {
        $this->template->setTitle(lang('highscore','hangman'));
        $entries=$this->hangman_model->getHighscores(25);
        foreach($entries as $k=>$entry) {
            $entries[$k]['username']=$this->user->getUsername((int)$entry['user_id']) ?: '-';
        }
        $content=$this->template->loadPage('highscore.tpl',['url'=>$this->template->page_url,'entries'=>$entries]);
        $page=$this->template->loadPage('page.tpl',[
            'module'=>'default',
            'headline'=>breadcrumb(['hangman'=>lang('hangman','hangman'),'hangman/highscore'=>lang('highscore','hangman')]),
            'content'=>$content
        ]);
        $this->template->view($page,'modules/hangman/css/hangman.css');
    }

    public function play(): void
    {
        $game=$this->getGame();

        if (!$this->canPlay()) {
            $this->output($this->buildState($game));
            return;
        }

        $action=(string)$this->input->post('action');

        switch($action) {
            case 'new':
                $difficulty=(int)$this->input->post('difficulty');
                if (!isset($this->difficulties[$difficulty])) {
                    $difficulty=$game ? (int)$game['difficulty'] : 1;
                }
                $game=$this->newGame($difficulty);
                break;

            case 'difficulty':
                $difficulty=(int)$this->input->post('difficulty');
                if (!isset($this->difficulties[$difficulty])) $difficulty=1;
                $game=$this->newGame($difficulty);
                break;

            case 'guess':
                $game=$this->guessLetter($game,(string)$this->input->post('letter'));
                break;
        }

        $this->output($this->buildState($game));
    }

    public function img($wrong=0): void
    {
        $wrong=max(0,min(6,(int)$wrong));
        $color=(string)$this->hangman_model->getSetting('color','#000000');

        if(!function_exists('imagecreatetruecolor')) {
            show_404();
            return;
        }

        $im=imagecreatetruecolor(220,220);
        imagealphablending($im,false);
        imagesavealpha($im,true);
        $transparent=imagecolorallocatealpha($im,255,255,255,127);
        imagefill($im,0,0,$transparent);

        $rgb=sscanf($color,'#%02x%02x%02x') ?: [0,0,0];
        $line=imagecolorallocate($im,(int)$rgb[0],(int)$rgb[1],(int)$rgb[2]);

        imagesetthickness($im,4);
        imageline($im,20,200,200,200,$line);
        imageline($im,50,200,50,20,$line);
        imageline($im,50,20,155,20,$line);
        imageline($im,155,20,155,45,$line);

        if($wrong>=1) imageellipse($im,155,68,45,45,$line);
        if($wrong>=2) imageline($im,155,91,155,145,$line);
        if($wrong>=3) imageline($im,155,105,125,125,$line);
        if($wrong>=4) imageline($im,155,105,185,125,$line);
        if($wrong>=5) imageline($im,155,145,130,180,$line);
        if($wrong>=6) imageline($im,155,145,180,180,$line);

        header('Content-Type: image/png');
        imagepng($im);
        imagedestroy($im);
    }

    private function canPlay(): bool
    {
        return $this->currentUserId()>0 || (int)$this->hangman_model->getSetting('guest_allow',0)===1;
    }

    private function currentUserId(): int
    {
        return ($this->user && $this->user->isOnline()) ? (int)$this->user->getId() : 0;
    }

    private function getGame(): ?array
    {
        return $this->hangman_model->getGame($this->currentUserId(),session_id());
    }

    private function newGame(int $difficulty): ?array
    {
        $word=$this->hangman_model->getRandomWord($difficulty,$this->detectLocale());
        if(!$word) return $this->getGame();

        $data=[
            'user_id'=>$this->currentUserId(),
            'session_id'=>session_id(),
            'last_activity'=>date('Y-m-d H:i:s'),
            'score'=>0,
            'health'=>max(1,min(6,(int)$this->hangman_model->getSetting('guesses',6))),
            'word_id'=>(int)$word['id'],
            'difficulty'=>$difficulty,
            // A hint is stored as an already-known letter. It is neither a
            // guess nor a score event, but stays stable for this game.
            'letters'=>$this->initialHintLetter((string)$word['text']),
            'rewarded'=>0,
            'reward_vp'=>0,
            'reward_dp'=>0
        ];

        $this->hangman_model->createGame($data);
        return $this->hangman_model->getGame($data['user_id'],$data['session_id']);
    }

    private function guessLetter(?array $game,string $letter): ?array
    {
        if(!$game) return $this->newGame(1);
        if((int)$game['health']<=0 || $this->isWon($game)) return $game;

        $letter=strtolower(trim($letter));
        if(!in_array($letter,$this->alphabet,true)) return $game;

        $used=$this->lettersFromGame($game);
        if(in_array($letter,$used,true)) return $game;
        $used[]=$letter;

        $word=$this->hangman_model->getWord((int)$game['word_id']);
        if(!$word) return $game;

        $answer=$this->normalizeWord((string)$word['text']);
        $correct=strpos($answer,$letter)!==false;
        $health=(int)$game['health'];
        $difficulty=(int)$game['difficulty'];
        $score=(int)$game['score'];

        if($correct) {
            $score += 10*$difficulty;
        } else {
            $health--;
            $score=max(0,$score-2);
        }

        $this->hangman_model->updateGame((int)$game['id'],[
            'last_activity'=>date('Y-m-d H:i:s'),
            'score'=>$score,'health'=>$health,'letters'=>implode(',',$used)
        ]);

        $game=$this->hangman_model->getGame((int)$game['user_id'],(string)$game['session_id']);

        if($game && $this->isWon($game)) $this->finishWin($game);

        return $this->hangman_model->getGame((int)$game['user_id'],(string)$game['session_id']);
    }

    private function finishWin(array $game): void
    {
        $userId=$this->currentUserId();

        // Guests never receive currency and never enter the persistent highscore.
        if($userId<1) return;

        $this->hangman_model->addHighscore($userId,(int)$game['score']);

        if((int)$game['rewarded']===1 || (int)$this->hangman_model->getSetting('reward_enabled',1)!==1) return;

        [$vp,$dp]=$this->rewardForDifficulty((int)$game['difficulty']);
        if($vp>0 || $dp>0) {
            $this->hangman_model->rewardGame((int)$game['id'],$userId,$vp,$dp,(int)$game['difficulty']);
        }
    }

    private function rewardForDifficulty(int $difficulty): array
    {
        $map=[
            1=>['reward_easy_vp','reward_easy_dp'],
            2=>['reward_medium_vp','reward_medium_dp'],
            3=>['reward_hard_vp','reward_hard_dp'],
            4=>['reward_mythic_vp','reward_mythic_dp'],
            5=>['reward_mythicplus_vp','reward_mythicplus_dp']
        ];
        $keys=$map[$difficulty]??$map[1];
        return [
            max(0,(int)$this->hangman_model->getSetting($keys[0],0)),
            max(0,(int)$this->hangman_model->getSetting($keys[1],0))
        ];
    }

    private function isWon(array $game): bool
    {
        $word=$this->hangman_model->getWord((int)$game['word_id']);
        if(!$word) return false;

        $answer=$this->normalizeWord((string)$word['text']);
        $letters=$this->lettersFromGame($game);

        foreach(preg_split('//u',$answer,-1,PREG_SPLIT_NO_EMPTY) as $char) {
            if(preg_match('/[a-z]/',$char) && !in_array($char,$letters,true)) return false;
        }
        return true;
    }

    private function normalizeWord(string $word): string
    {
        $word=strtolower($word);
        $trans=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$word);
        $word=$trans!==false?$trans:$word;
        return preg_replace('/[^a-z]/','',$word)??'';
    }

    private function initialHintLetter(string $word): string
    {
        if ((int)$this->hangman_model->getSetting('reveal_initial_letter', 1) !== 1) {
            return '';
        }

        $letters=array_values(array_unique(str_split($this->normalizeWord($word))));
        if (!$letters) {
            return '';
        }

        return $letters[random_int(0,count($letters)-1)];
    }

    private function lettersFromGame(array $game): array
    {
        $value=trim((string)$game['letters']);
        return $value===''?[]:array_values(array_filter(array_map('strtolower',explode(',',$value))));
    }

    private function detectLocale(): string
    {
        $lang='';
        if(method_exists($this->user,'getLanguage')) $lang=(string)$this->user->getLanguage();
        if($lang==='') $lang=(string)$this->config->item('language');
        $lang=strtolower($lang);
        return (strpos($lang,'de')===0 || strpos($lang,'german')!==false)?'german':'english';
    }

    private function buildState(?array $game): array
    {
        $difficulty=$game?(int)$game['difficulty']:1;
        [$previewVp,$previewDp]=$this->rewardForDifficulty($difficulty);

        $word=$game?$this->hangman_model->getWord((int)$game['word_id']):null;
        $letters=$game?$this->lettersFromGame($game):[];
        $won=$game?$this->isWon($game):false;
        $lost=$game && (int)$game['health']<=0 && !$won;

        $mask=[];
        $wrong=0;
        if($game && $word) {
            $normalized=$this->normalizeWord((string)$word['text']);
            $answerChars=preg_split('//u',(string)$word['text'],-1,PREG_SPLIT_NO_EMPTY);
            $normChars=preg_split('//u',$normalized,-1,PREG_SPLIT_NO_EMPTY);
            $normIndex=0;
            foreach($answerChars as $char) {
                $n=$normChars[$normIndex]??'';
                $isLetter=preg_match('/[a-z]/',$n)===1;
                if($isLetter) $normIndex++;
                $mask[]=[
                    'char'=>$char,
                    'isLetter'=>$isLetter,
                    'revealed'=>!$isLetter || in_array($n,$letters,true) || $won || $lost
                ];
            }
            foreach($letters as $l) if(strpos($normalized,$l)===false) $wrong++;
        }

        $difficulties=[];
        foreach($this->difficulties as $id=>$name) {
            $difficulties[]=['id'=>(int)$id,'name'=>lang($name,'hangman')];
        }

        $isGuest=$this->currentUserId()<1;
        return [
            'hasGame'=>$game!==null,
            'id'=>$game?(int)$game['id']:0,
            'difficulty'=>$difficulty,
            'difficultyName'=>lang($this->difficulties[$difficulty]??'easy','hangman'),
            'difficulties'=>$difficulties,
            'alphabet'=>$this->alphabet,
            'letters'=>$letters,
            'mask'=>$mask,
            'health'=>$game?(int)$game['health']:0,
            'maxHealth'=>max(1,(int)$this->hangman_model->getSetting('guesses',6)),
            'wrong'=>$wrong,
            'score'=>$game?(int)$game['score']:0,
            'won'=>$won,
            'over'=>$lost,
            'rewarded'=>$game?(int)$game['rewarded']===1:false,
            'rewardVp'=>$game?(int)$game['reward_vp']:0,
            'rewardDp'=>$game?(int)$game['reward_dp']:0,
            'rewardPreviewVp'=>$previewVp,
            'rewardPreviewDp'=>$previewDp,
            'rewardEnabled'=>(int)$this->hangman_model->getSetting('reward_enabled',1)===1,
            'isGuest'=>$isGuest,
            'guestAllowed'=>(int)$this->hangman_model->getSetting('guest_allow',0)===1,
            'loginRequired'=>!$this->canPlay(),
            'letterButtons'=>(int)$this->hangman_model->getSetting('letter_buttons',1)===1
        ];
    }

    private function output(array $state): void
    {
        $this->output->set_content_type('application/json')
            ->set_output(json_encode($state,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }
}
