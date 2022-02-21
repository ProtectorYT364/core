<?php

namespace Core\Game;

class ModesManager{

    const ACCEPTED_MODES = ["FFA_FIST", "FIST_SUMO"];

    const SUMO = "FFA_SUMO";
    const FIST = "FFA_FIST";
    const NODEBUFF = "NODEBUFF";

    public function validate($game){
        if($game->getMode() == $this::ACCEPTED_MODES) {
            return true;
        }else{
            return false;
        }
    }
}

class Modes{

    const ACCEPTED_MODES = ["FFA_FIST", "FIST_SUMO"];

    const SUMO = "FFA_SUMO";
    const FIST = "FFA_FIST";

    public function validate($game){
        if($game->getMode() == $this::ACCEPTED_MODES) {
           return true;
        }else{
           return false;
        }
    }
}

