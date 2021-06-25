<?php


namespace Core\Game\Managers\Lobby\Listeners;


use Core\Game\GamesManager;
use Core\Games\FFA;
use Core\Main;
use ethaniccc\NoDebuffBot\Bot;
use pocketmine\entity\Entity;
use Core\Forms\{SimpleForm, ModalForm, CustomForm};
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ItemListener implements Listener
{

    public function onInteract(PlayerInteractEvent $event) {

        $item = $event->getItem();
        if(!$event->getPlayer()->isOp()) {
            $event->setCancelled();
        }
        if(!$event->getPlayer()->hasPermission("events.turtleclient.bypassinteract")) {
            $event->setCancelled();
        }
        $player = $event->getPlayer();

        if($item->getCustomName() === TextFormat::BOLD . TextFormat::BLUE . "Navigator") {

            $form = new CustomForm(function (Player $player, ?int $data, Player $user, string $rank) {
                if (!is_null($data)) {
                    switch ($data[0]) {
                        case 0:
                            $player->initializeGame(Main::getInstance()->getGame('sumo-ffa'));
                            break;
                        case 1:
                            $player->initializeGame(Main::getInstance()->getGame('fist-ffa'));
                            break;
                        default:
                            return;
                    }

                    switch ($data[1]){

                        case 0:

                            $level = Main::getInstance()->createMap($player, Main::getInstance()->getRandomMap());

                            $nbt = Entity::createBaseNBT($player->asVector3()->subtract(10, 0, 10));
                            $nbt->setTag($player->namedtag->getTag("Skin"));

                            $bot = new Bot($level, $nbt, $player->getName(), 'nodebuff', GamesManager::DIFFICULTY_EASY);
                            $bot->setNameTagAlwaysVisible(true);
                            $bot->spawnToAll();
                            $bot->setCanSaveWithChunk(false);
                            $bot->teleport($level->getSafeSpawn());
                            $bot->setImmobile(true);

                            $game = Main::getInstance()->createGame([$player, $bot], GamesManager::BOT, GamesManager::DIFFICULTY_EASY);

                    }
                }
            });

            $form->setTitle("§e§lNavigator");
            $form->addDropdown("FFA", ["Sumo", "Fist"], 0);
            $form->addDropdown("Bots", ["Easy", "Medium", "Hard"], 1);

            $form->sendToPlayer($player);
        }

    }

}