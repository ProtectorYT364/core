<?php

namespace Core\Game;

use Core\Games\FFA;
use Core\Games\KnockbackFFA;

class GamesManager{

    const ACCEPTED_MODES = ["FFA", "KBFFA", "BOT"];
    const SINGLE_MODES = ["lobby", "KBFFA"];

    const FFA = "FFA";
    const KBFFA = "KBFFA";
    const BOT = "BOT";

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

class Games{

    const ACCEPTED_MODES = ["FFA", "KBFFA"];
    const SINGLE_MODES = ["lobby", "KBFFA"];

    const FFA = "FFA";
    const KBFFA = "KBFFA";

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



