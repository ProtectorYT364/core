<?php

namespace Core\Events;

use Core\Game\Game;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\level\Level;
use pocketmine\player;

class TurtleGameEnterEvent extends PluginEvent{

 public Player $player;
 public Game $game;
 public Level $level;

 public function __construct($player, $game, $level = null){
  parent::__construct(self::getPlugin());
  $this->player = $player;
  $this->game = $game;

  if(!is_null(($level)) && $level instanceof Level) {
      $this->level = $level;
  }

 }

 public function getPlayer(): Player{
     return $this->player;
 }

 public function getGame(): Game{
     return $this->game;
 }

 public function getLevel(): Level{
     return $this->level;
 }



}