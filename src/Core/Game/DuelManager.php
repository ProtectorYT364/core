<?php

namespace Core\Games;

use Core\Events\TurtleAddPlayerToQueueEvent;
use Core\Functions\Countdown;
use Core\Functions\GiveItems;
use Core\Game\DuelQueues;
use Core\Game\Game;
use Core\Game\GamesManager;
use Core\Main;
use Core\TurtlePlayer;
use ethaniccc\NoDebuffBot\Bot;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\network\mcpe\protocol\types\GameMode;

class Duels {

    public static function initializeBotGame($player, $bot, Game $game, $level = null){


        if(is_null($level)) {
            $level = Main::getInstance()->createMap($player, Main::getInstance()->getRandomMap());
        }

            GiveItems::giveKit('nodebuff', $bot);
            GiveItems::giveKit('nodebuff', $player);

            $p = $player;

            $p->setGamemode(GameMode::SURVIVAL_VIEWER);
            $p->teleport($level->getSafeSpawn());
            $bot->teleport($level->getSafeSpawn());

            Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(3, "Spawning in...", "3 seconds", $game, $p, true), 20 * 1);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(2, "Spawning in...", "2 seconds", $game, $p, true), 20 * 2);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(1, "Spawning in...", "1 seconds", $game, $p, true), 20 * 3);
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new Countdown(0, "Spawning in...", "0 seconds", $game, $p, true), 20 * 4);

    }

    public static function initializeGame($player, Game $game){


        if($game->getType() == GamesManager::NODEBUFF) {

            Main::getInstance()->getDuelQueues()->getQueue(DuelQueues::NODEBUFF_UNRANKED)->addPlayerToQueue($player);

            $event = new TurtleAddPlayerToQueueEvent($player, Main::getInstance()->getDuelQueues()->getQueue(DuelQueues::NODEBUFF_UNRANKED));
            $event->call();
        }

    }

}