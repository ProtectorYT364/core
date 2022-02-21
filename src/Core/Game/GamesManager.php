<?php

namespace Core\Game;

use Core\Games\FFA;
use Core\Games\KnockbackFFA;

class GamesManager{

    const ACCEPTED_MODES = ["FFA", "KBFFA", "BOT"];
    const SINGLE_MODES = ["lobby", "KBFFA"];
    const DIFFICULTIES = ['Easy', 'Medium', 'Hard'];

    const FFA = "FFA";
    const KBFFA = "KBFFA";
    const BOT = "BOT";
    const DUEL = "DUEL";
    const NODEBUFF = "NODEBUFF";

    const DIFFICULTY_EASY = 'Easy';
    const DIFFICULTY_MEDIUM = 'Medium';
    const DIFFICULTY_HARD = 'Hard';

    public function validate(Game $game){
        if($game->getMode() == $this::ACCEPTED_MODES) {
            return true;
        } else {
            return false;
        }
    }

    public function getFFAManager(){
        return FFA::class;
    }

    public function getKBFFAManager(){
        return KnockbackFFA::class;
    }




}


