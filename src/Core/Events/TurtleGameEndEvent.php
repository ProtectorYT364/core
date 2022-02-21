<?php

namespace Core\Events;

use Core\Game\Game;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\player;

class TurtleGameEndEvent extends PluginEvent{

    public $players;
    public $winner;
    public $loser;
    public $game;

    public function __construct(array $players, $winner, $loser, Game $game){
     parent::__construct(self::getPlugin());
     $this->players = $players;
     $this->winner = $winner;
     $this->loser = $loser;
     $this->game = $game;
    }

    public function getGamePlayers(){
    return $this->players;
    }

    public function getWinner(){
    return $this->winner;
    }

    public function getLoser(){
    return $this->loser;
    }

    public function getGame(){
        return $this->game;
    }

}