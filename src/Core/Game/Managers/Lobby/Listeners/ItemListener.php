<?php


namespace Core\Game\Managers\Lobby\Listeners;


use Core\Forms\SimpleForm;
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
            $form = new SimpleForm(function (Player $player, ?int $data, Player $user, string $rank) {
                if (!is_null($data)) {
                    switch ($data) {
                        case 0:
                            $player->sendActionBarMessage(TextFormat::RED . "Coming soon");
                            break;
                        default:
                            return;
                    }
                }
            });
            $form->setTitle("§e§lNavigator");
            $form->addButton("kbffa");
            $form->sendToPlayer($player);
        }

    }

}