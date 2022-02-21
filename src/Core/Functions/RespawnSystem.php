<?php

namespace Core\Functions;

use pocketmine\Player;
use Core\Functions\Countdown;
use Core\Main;
use pocketmine\utils\TextFormat;

class RespawnSystem{




    public static function initializeSystem(Player $p, $game){

     foreach(Main::getInstance()->getServer()->getPlayers() as $all){
        $all->sendMessage("");
     }

     $p->setGamemode(3);
     Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(3, "Respawning in...", "3 seconds", $game, $p, false), 20 * 1);
     Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(2, "Respawning in...", "2 seconds", $game, $p, false), 20 * 2);
     Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(1, "Respawning in...", "1 seconds", $game, $p, false), 20 * 3);
     Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(0, "Respawning in...", "0 seconds", $game, $p, false), 20 * 4);

     $p->sendActionBarMessage(TextFormat::RED . "You have died!");
     //we're gonna pretend the stats api is here :thumbsup:

    }
}