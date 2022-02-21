<?php

namespace Core\Functions;

use Core\Game\Game;
use Core\TurtlePlayer;
use pocketmine\Player;
use pocketmine\level\level;
use pocketmine\math\Vector3;
use Core\Main;
use pocketmine\scheduler\Task;
use Core\Game\Modes;
use Core\Game\Games;

class Countdown extends Task
{

    public Player $player;
    public $num;
    public Game $game;
    public $text;
    public $text2;

    public function __construct($num, $string, $string2, $game, $player)
    {
        $this->num = $num;
        $this->player = $player;
        $this->text = $string;
        $this->text2 = $string2;
        $this->game = $game;
    }

    public function onRun(int $tick){
        if (!$this->num == 0){
            if(!$this->player == null) {
                if($this->player->isOnline()) {
                    if($this->game->getType() != null) {
                        $this->player->addTitle($this->text, $this->text2, 20, 60, 40);
                        $this->player->getInventory()->clearAll();
                    }
                }
            }
        } else {
            if(!$this->player == null) {
                if($this->player->isOnline()) {
                    $this->player->setGamemode(0);
                    if($this->game->getType() == Games::FFA) {
                        if ($this->game->getMode() == Modes::SUMO) {
                            $this->player->teleport(new Vector3(1, 1, 1, 0, 0, Main::getInstance()->getServer()->getLevelByName("sumoFFA")));
                            $this->player->setGamemode(0);
                            $this->player->setIsRespawning(false);
                        } elseif ($this->game == Modes::FIST) {
                            $this->player->teleport(new Vector3(1, 1, 1, 0, 0, Main::getInstance()->getServer()->getLevelByName("fistFFA")));
                            $this->player->setGamemode(0);
                            $this->player->setIsRespawning(false);
                        }
                    }elseif($this->game->getType() == Games::KBFFA or $this->game->getType() == Games::KBFFA){
                        $this->player->teleport(new Vector3(1, 1, 1, 0, 0, Main::getInstance()->getServer()->getLevelByName("kbFFA")));
                        $this->player->setGamemode(0);
                        $this->player->setIsRespawning(false);
                        GiveItems::giveKit(Games::KBFFA, $this->player);
                    }
                }
            }
        }
    }
}