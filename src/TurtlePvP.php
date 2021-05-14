<?php

namespace Core;

use Core\Games\FFA;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class Core extends PluginBase implements Listener{

    private static $instance;
    public $ffa;

    public function onEnable():void{
        self::$instance = $this;
        $this->ffa = FFA::class;
    }

    public function getFFA(){
    return $this->ffa;
    }

    public static function getInstance(){
        return self::$instance;
    }

    public function onJoin(PlayerJoinEvent $e){
    $e->getPlayer()->teleport(new Vector3(0, 0, 0, 0, 0, $this->getServer()->getLevelByName("lobby")));
    }

        public function onDeath(EntityDamageByEntityEvent $e)
        {
            $victim = $e->getEntity();
            if ($victim instanceof Player) {
                if ($victim->isOnline()) {
                    if ($e->getFinalDamage() >= $victim->getHealth()) {
                        if($victim->getCurrentMinigame() != "lobby" or $victim->getCurrentGamemode() != "lobby")
                        $victim->intializeRespawn($victim->getCurrentGamemode());
                    }
                }
            }
        }

        public function onChat(PlayerChatEvent $e){
        if($e->getPlayer()->getIsRespawning() == true) {
            if($e->getMessage() == "lobby"){
                $e->getPlayer()->initializeLobby();
            }
         }
        }
}