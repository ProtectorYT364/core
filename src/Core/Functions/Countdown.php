<?php

namespace Core\Functions;

use Core\Game\Game;
use Core\TurtlePlayer;
use ethaniccc\NoDebuffBot\Bot;
use pocketmine\network\mcpe\protocol\types\GameMode;
use pocketmine\Player;
use pocketmine\level\level;
use pocketmine\math\Vector3;
use Core\Main;
use pocketmine\scheduler\Task;
use Core\Game\Modes;
use Core\Game\GamesManager as Games;

class Countdown extends Task
{

    public Player $player;
    public $num;
    public Game $game;
    public $text;
    public $text2;
    public $mode;

    public function __construct($num, $string, $string2, $game, $player, $mode)
    {
        $this->num = $num;
        $this->player = $player;
        $this->text = $string;
        $this->text2 = $string2;
        $this->game = $game;
        $this->mode = $mode;
    }

    public function onRun(int $tick){

        if(!$this->mode) {

            if (!$this->num == 0) {
                if (!$this->player == null) {
                    if ($this->player->isOnline()) {
                        if ($this->game->getType() != null) {
                            $this->player->addTitle($this->text, $this->text2, 20, 60, 40);
                            $this->player->getInventory()->clearAll();
                            $this->player->setGamemode(GameMode::SURVIVAL_VIEWER);
                        }
                    }
                }
            } else {
                if (!$this->player == null) {
                    if ($this->player->isOnline()) {
                        $this->player->setGamemode(0);
                        if ($this->game->getType() == Games::FFA) {
                            if ($this->game->getMode() == Modes::SUMO) {
                                $this->player->teleport($this->player->getLevel()->getSafeSpawn());
                                $this->player->setGamemode(0);
                                $this->player->setIsRespawning(false);
                            } elseif ($this->game == Modes::FIST) {
                                $this->player->teleport($this->player->getLevel()->getSafeSpawn());
                                $this->player->setGamemode(0);
                                $this->player->setIsRespawning(false);
                            }
                        } elseif ($this->game->getType() == Games::KBFFA or $this->game->getType() == Games::KBFFA) {
                            $this->player->teleport($this->player->getLevel()->getSafeSpawn());
                            $this->player->setGamemode(0);
                            $this->player->setIsRespawning(false);
                            GiveItems::giveKit(Games::KBFFA, $this->player);
                        } else {

                            $this->player->teleport($this->player->getLevel()->getSafeSpawn());
                            $this->player->setGamemode(0);

                        }
                    }
                }
            }
        } else {
            if ($this->num !== 0) {
                if (!is_null($this->player)) {
                    if ($this->player->isOnline()) {
                        $this->player->setImmobile();
                        $this->player->addTitle($this->text, $this->text2, 20, 60, 40);
                        $this->player->getInventory()->clearAll();
                        $this->player->setGamemode(GameMode::SURVIVAL_VIEWER);
                    }
                }
            } else {

                GiveItems::giveKit('nodebuff', $this->player);
                foreach($this->game->getPlayers() as $player){

                    if($player instanceof Bot){
                        $player->setImmobile(false);
                    }

                    $this->player->setGamemode(GameMode::SURVIVAL);
                    $this->player->teleport($this->player->getLevel()->getSafeSpawn());
                }

            }
        }
    }
}