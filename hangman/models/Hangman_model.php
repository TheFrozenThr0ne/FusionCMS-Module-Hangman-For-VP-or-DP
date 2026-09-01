<?php

class Hangman_model extends CI_Model
{
    private ?array $settings = null;

    public function getSettings(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $defaults = [
            'guest_allow' => (string)$this->config->item('guest_allow'),
            'days_old_del' => (string)$this->config->item('days_old_del'),
            'letter_buttons' => (string)$this->config->item('letter_buttons'),
            'reveal_initial_letter' => (string)$this->config->item('reveal_initial_letter'),
            'color' => (string)$this->config->item('color'),
            'guesses' => (string)$this->config->item('guesses'),
            'reward_enabled' => (string)$this->config->item('reward_enabled'),
            'reward_easy_vp' => (string)$this->config->item('reward_easy_vp'),
            'reward_medium_vp' => (string)$this->config->item('reward_medium_vp'),
            'reward_hard_vp' => (string)$this->config->item('reward_hard_vp'),
            'reward_mythic_vp' => (string)$this->config->item('reward_mythic_vp'),
            'reward_mythicplus_vp' => (string)$this->config->item('reward_mythicplus_vp'),
            'reward_easy_dp' => (string)$this->config->item('reward_easy_dp'),
            'reward_medium_dp' => (string)$this->config->item('reward_medium_dp'),
            'reward_hard_dp' => (string)$this->config->item('reward_hard_dp'),
			'reward_mythic_dp' => (string)$this->config->item('reward_mythic_dp'),
			'reward_mythicplus_dp' => (string)$this->config->item('reward_mythicplus_dp'),
        ];

        // Game and reward settings deliberately live in config/hangman.php.
        // Do not merge hangman_settings here: old database entries must never
        // silently override the values deployed with the module.
        return $this->settings = $defaults;
    }

    public function getSetting(string $name, $default = null)
    {
        $settings = $this->getSettings();
        return $settings[$name] ?? $default;
    }

    public function getWords(?int $difficulty = null, ?string $locale = null): array
    {
        $q = $this->db->table('hangman_words');
        if ($difficulty) $q->where('difficulty', $difficulty);
        if ($locale) $q->where('locale', $locale);
        return $q->orderBy('locale')->orderBy('difficulty')->orderBy('text')->get()->getResultArray();
    }

    public function getWord(int $id): ?array
    {
        $row = $this->db->table('hangman_words')->where('id', $id)->get()->getRowArray();
        return $row ?: null;
    }

    public function getRandomWord(int $difficulty, string $locale): ?array
    {
        $q = $this->db->table('hangman_words')
            ->where('difficulty', $difficulty)
            ->where('locale', $locale)
            ->orderBy('id', 'RANDOM')->limit(1)->get()->getRowArray();

        if (!$q && $locale !== 'english') {
            $q = $this->db->table('hangman_words')
                ->where('difficulty', $difficulty)
                ->where('locale', 'english')
                ->orderBy('id', 'RANDOM')->limit(1)->get()->getRowArray();
        }
        return $q ?: null;
    }

    public function addWord(string $text, int $difficulty, string $locale): int
    {
        $this->db->table('hangman_words')->insert([
            'text'=>$text, 'difficulty'=>$difficulty, 'locale'=>$locale
        ]);
        return (int)$this->db->insertID();
    }

    public function updateWord(int $id, string $text, int $difficulty, string $locale): void
    {
        $this->db->table('hangman_words')->where('id',$id)->update([
            'text'=>$text, 'difficulty'=>$difficulty, 'locale'=>$locale
        ]);
    }

    public function deleteWord(int $id): void
    {
        $this->db->table('hangman_words')->where('id',$id)->delete();
    }

    public function getGame(int $userId, string $sessionId): ?array
    {
        $q = $this->db->table('hangman_games');
        if ($userId > 0) $q->where('user_id',$userId);
        else $q->where('user_id',0)->where('session_id',$sessionId);
        $row = $q->orderBy('id','DESC')->limit(1)->get()->getRowArray();
        return $row ?: null;
    }

    public function createGame(array $data): int
    {
        $this->db->table('hangman_games')->insert($data);
        return (int)$this->db->insertID();
    }

    public function updateGame(int $id, array $data): void
    {
        $this->db->table('hangman_games')->where('id',$id)->update($data);
    }

    public function deleteOldGames(int $days): void
    {
        if ($days < 1) return;
        $this->db->table('hangman_games')
            ->where('last_activity <', date('Y-m-d H:i:s', time()-($days*86400)))
            ->delete();
    }

    public function getHighscores(int $limit=25): array
    {
        return $this->db->table('hangman_highscore')
            ->orderBy('score','DESC')->orderBy('games','ASC')->limit($limit)
            ->get()->getResultArray();
    }

    public function addHighscore(int $userId, int $score): void
    {
        if ($userId < 1) return;
        $row=$this->db->table('hangman_highscore')->where('user_id',$userId)->get()->getRowArray();
        if ($row) {
            $this->db->table('hangman_highscore')->where('user_id',$userId)->update([
                'score'=>(int)$row['score']+$score,
                'games'=>(int)$row['games']+1
            ]);
        } else {
            $this->db->table('hangman_highscore')->insert([
                'user_id'=>$userId,'score'=>$score,'games'=>1
            ]);
        }
    }

    public function resetHighscores(): void
    {
        $this->db->table('hangman_highscore')->emptyTable();
    }

    public function countGames(): int { return $this->db->table('hangman_games')->countAllResults(); }
    public function countWords(): int { return $this->db->table('hangman_words')->countAllResults(); }

    public function getRewardHistory(int $limit=100): array
    {
        return $this->db->table('hangman_rewards')
            ->orderBy('id','DESC')->limit($limit)->get()->getResultArray();
    }

    public function rewardGame(int $gameId,int $userId,int $vp,int $dp,int $difficulty): bool
    {
        if ($gameId<1 || $userId<1 || ($vp<1 && $dp<1)) return false;

        $this->db->transBegin();

        $existing=$this->db->table('hangman_rewards')->where('game_id',$gameId)->get()->getRowArray();
        if ($existing) {
            $this->db->transRollback();
            return false;
        }

        $account=$this->db->table('account_data')->where('id',$userId)->get()->getRowArray();
        if (!$account) {
            $this->db->transRollback();
            return false;
        }

        $this->db->table('hangman_rewards')->insert([
            'game_id'=>$gameId,'user_id'=>$userId,'vote_points'=>$vp,
            'donate_points'=>$dp,'difficulty'=>$difficulty,'created_at'=>date('Y-m-d H:i:s')
        ]);

        // FusionCMS uses account_data.vp for Vote Points and account_data.dp for Donation Points.
        $this->db->query(
            'UPDATE `account_data` SET `vp` = `vp` + ?, `dp` = `dp` + ? WHERE `id` = ?',
            [$vp,$dp,$userId]
        );

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return false;
        }

        $this->db->table('hangman_games')->where('id',$gameId)->where('rewarded',0)->update([
            'rewarded'=>1,'reward_vp'=>$vp,'reward_dp'=>$dp
        ]);

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transCommit();
        return true;
    }
}
