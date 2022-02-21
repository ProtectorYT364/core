<?php

namespace Core\Games;

use Core\Game\Game;
use pocketmine\Player;
use pocketmine\level\level;
use pocketmine\math\Vector3;
use Core\Main;
use Core\Functions\Countdown;
use Core\Functions\GiveItems;
use Core\Errors;
use Core\Game\Modes;
use Core\Game\Games;

class FFA{

    public Game $sumo_game;
    public Game $fist_game;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
        $this->sumo_game = Main::getInstance()->getGame('sumo-ffa');
        $this->fist_game = Main::getInstance()->getGame('fist-ffa');
    }

    public function initializeGame(Player $p, $game){
    if($game->getMode() == Games::ACCEPTED_MODES) {
        if ($game->getMode() == Modes::SUMO) {
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(0, "Spawning in...", "0 seconds", $game, $p), 20 * 1);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(5, "Spawning in...", "1 seconds", $game, $p),20 * 2);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(4, "Spawning in...", "2 seconds", $game, $p),20*3);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(3, "Spawning in...", "3 seconds", $game, $p),20*4);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(2, "Spawning in...", "4 seconds", $game, $p),20*5);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(1, "Spawning in...", "5 seconds", $game, $p),20*6);
            GiveItems::giveKit(Modes::SUMO, $p);
        }elseif($game->getMode() == Modes::FIST){
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(0, "Spawning in...", "0 seconds", $game, $p), 20 * 1);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(5, "Spawning in...", "1 seconds", $game, $p),20 * 2);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(4, "Spawning in...", "2 seconds", $game, $p),20*3);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(3, "Spawning in...", "3 seconds", $game, $p),20*4);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(2, "Spawning in...", "4 seconds", $game, $p),20*5);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(1, "Spawning in...", "5 seconds", $game, $p),20*6);
            GiveItems::giveKit(Modes::FIST, $p);
        }
      }
    }

    public function getGame(string $name){
        if ($name == 'sumo' or $name == 'fist') {
            if ($name == 'sumo') {
                return $this->sumo_game;
            } elseif ($name == 'fist') {
                return $this->fist_game;
            }
        }
    }

}