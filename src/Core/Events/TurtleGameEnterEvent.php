<?php

namespace Core\Events;

use Core\Game\Game;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\player;

class TurtleGameEnterEvent extends PluginEvent{

 public Player $player;
 public Game $game;

 public function __construct($player, $game){
  parent::__construct(self::getPlugin());
  $this->player = $player;
  $this->game = $game;
 }

 public function getPlayer(): Player{
     return $this->player;
 }

 public function getGame(): Game{
     return $this->game;
 }

}