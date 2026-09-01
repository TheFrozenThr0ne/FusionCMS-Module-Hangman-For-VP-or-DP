<?php

use MX\MX_Controller;

class Admin extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->config('hangman');
        $this->load->model('hangman_model');
    }

    public function index(): void
    {
        requirePermission('canManageWords');
        $this->template->setTitle(lang('hangman','hangman'));
        $data=[
            'url'=>$this->template->page_url,
            'words'=>$this->hangman_model->getWords(),
            'difficulties'=>$this->difficultyList(),
            'locales'=>$this->localeList()
        ];
        $output=$this->template->loadPage('admin/words.tpl',$data);
        $this->administrator->view($output,false,'modules/hangman/js/admin.js');
    }

    public function save(): void
    {
        requirePermission('canManageWords');
        $id=(int)$this->input->post('id');
        $text=trim((string)$this->input->post('text'));
        $difficulty=(int)$this->input->post('difficulty');
        $locale=trim((string)$this->input->post('locale'));

        if($text===''||$locale===''){
            $this->session->set_flashdata('error',lang('word_missing','hangman'));
            redirect('admin/hangman'); return;
        }
        if($difficulty<1||$difficulty>5)$difficulty=1;

        if($id)$this->hangman_model->updateWord($id,$text,$difficulty,$locale);
        else $this->hangman_model->addWord($text,$difficulty,$locale);

        $this->session->set_flashdata('success',lang('word_saved','hangman'));
        redirect('admin/hangman');
    }

    public function delete($id=0): void
    {
        requirePermission('canManageWords');
        $this->hangman_model->deleteWord((int)$id);
        $this->session->set_flashdata('success',lang('word_deleted','hangman'));
        redirect('admin/hangman');
    }

    public function settings(): void
    {
        requirePermission('canManageHangmanSettings');
        $this->template->setTitle(lang('settings','hangman'));
        $data=[
            'url'=>$this->template->page_url,
            'settings'=>$this->hangman_model->getSettings(),
            'wordCount'=>$this->hangman_model->countWords(),
            'gameCount'=>$this->hangman_model->countGames(),
            'rewardHistory'=>$this->hangman_model->getRewardHistory(50)
        ];
        $output=$this->template->loadPage('admin/settings.tpl',$data);
        $this->administrator->view($output,false);
    }

    public function saveSettings(): void
    {
        requirePermission('canManageHangmanSettings');
        // Settings are intentionally configuration-only. Keeping this route
        // prevents bookmarks from failing while ensuring POST data is ignored.
        $this->session->set_flashdata('success','Hangman settings are read from config/hangman.php.');
        redirect('admin/hangman/settings');
    }

    public function highscores(): void
    {
        requirePermission('canManageHangmanSettings');
        $this->template->setTitle(lang('highscore','hangman'));
        $entries=$this->hangman_model->getHighscores(100);
        foreach($entries as $k=>$entry) $entries[$k]['username']=$this->user->getUsername((int)$entry['user_id'])?:'-';
        $output=$this->template->loadPage('admin/highscores.tpl',['entries'=>$entries,'url'=>$this->template->page_url]);
        $this->administrator->view($output,false);
    }

    public function resetHighscores(): void
    {
        requirePermission('canManageHangmanSettings');
        $this->hangman_model->resetHighscores();
        $this->session->set_flashdata('success',lang('highscore_reset','hangman'));
        redirect('admin/hangman/highscores');
    }

    private function difficultyList(): array
    {
        $difficulties=(array)$this->config->item('difficulties');
        $result=[];
        foreach($difficulties as $id=>$name) {
            $result[]=['id'=>(int)$id,'name'=>lang($name,'hangman')];
        }
        return $result;
    }

    private function localeList(): array
    {
        return ['english','german'];
    }
}
