<?php

namespace Core\Functions;


use Core\Main;
use Core\TurtlePlayer;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\Enchantment;
use Core\Game\{Games, Modes};
use pocketmine\utils\TextFormat;

class GiveItems{
    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public static function giveKit(string $kit, TurtlePlayer $p){
        if($p->getGame() == Games::FFA) {
            if ($kit == Modes::FIST) {
                $p->getInventory()->setItem(7, Item::get(364, 0, 64));
            } elseif ($kit == Modes::SUMO) {
                $p->getInventory()->setItem(7, Item::get(364, 0, 64));
            }
        }elseif($p->getGame() == Games::KBFFA){

            $sword = Item::get(268, 0, 1);
            $stick = Item::get(280, 0, 1);
            $bow = Item::get(261, 0, 1);
            $stone = Item::get(24, 0, 64);
            $enderpearl = Item::get(368, 0, 1);
            $arrow = Item::get(262, 0, 1);
            $cob = Item::get(332, 0, 10);
            $helm = Item::get(298);
            $chest = Item::get(299);
            $pant = Item::get(300);
            $boot = Item::get(301);
            $pick = Item::get(270);

            $allitems = array($sword, $pick, $stick, $bow, $stone, $enderpearl, $arrow, $cob, $helm, $chest, $pant, $boot);

            foreach ($allitems as $itemz) {
                if ($itemz instanceof Durable) {
                    $itemz->setUnbreakable();
                }
            }

            $sharpness = Enchantment::getEnchantment(9);
            $prot = Enchantment::getEnchantment(0);
            $kb = Enchantment::getEnchantment(12);
            $eff = Enchantment::getEnchantment(15);
            $punch = Enchantment::getEnchantment(20);

            //enchant items
            $stick->addEnchantment(new EnchantmentInstance($kb, 1));
            $bow->addEnchantment(new EnchantmentInstance($punch, 2));
            $helm->addEnchantment(new EnchantmentInstance($prot, 4));
            $chest->addEnchantment(new EnchantmentInstance($prot, 4));
            $boot->addEnchantment(new EnchantmentInstance($prot, 4));
            $pant->addEnchantment(new EnchantmentInstance($prot, 4));
            $pick->addEnchantment(new EnchantmentInstance($eff, 5));
            $pick->addEnchantment(new EnchantmentInstance($sharpness, 6));

            //set sum shit
            $p->extinguish();
            $p->setScale(1);
            $p->setGamemode(0);
            $p->setMaxHealth(20);
            $p->setHealth(20);
            $p->getInventory()->clearAll();
            $p->getArmorInventory()->clearAll();

            //set items
            $p->getInventory()->setItem(0, $stick);
            $p->getInventory()->setItem(8, $bow);
            $p->getInventory()->setItem(2, $enderpearl);
            $p->getInventory()->setItem(5, $stone);
            $p->getInventory()->setItem(34, $arrow);
            $p->getInventory()->setItem(4, $cob);
            $p->getInventory()->setItem(1, $pick);


            //set armor inv
            $p->getArmorInventory()->setHelmet($helm);
            $p->getArmorInventory()->setChestplate($chest);
            $p->getArmorInventory()->setLeggings($pant);
            $p->getArmorInventory()->setBoots($boot);

        }elseif($p->getGame() == "lobby") {
            //create items
            $playMenu = Item::get(Item::COMPASS, 0, 1);

            //give name
            $playMenu->setCustomName(TextFormat::BOLD . TextFormat::BLUE . "Navigator");

            //create enchant glint
            $playMenu->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SLOT_NONE), 0));

            //give item
            $p->getInventory()->addItem($playMenu);
        }
    }
}