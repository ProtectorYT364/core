<?php

namespace Core\Entities;

use Core\Events\TurtleGameEndEvent;
use Core\Game\GamesManager;
use pocketmine\block\Liquid;
use pocketmine\entity\Attribute;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\Sword;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class Bot extends Human{

    /** @var string */
    private $target = "";

    /** @var int */
    private $hitTick = 0;

    /** @var int */
    private $neededPots = 0;

    /** @var int */
    private $potTick = 0;

    /** @var float */
    private $speed = 0.4;

    /** @var int */
    private $potionsRemaining = 33;

    /** @var int */
    private $pearlsRemaining = 16;

    /** @var int */
    private $agroCooldown = 0;

    /**
     * @var int
     */
    private $mode = null;

    /**
     * @var string
     */
    private string $difficulty;

    /**
     * @var int
     */
    private int $accuracy;

    /**
     * @var bool
     */
    private bool $disabled = true;



    /**
     * Bot constructor.
     * @param Level $level
     * @param CompoundTag $nbt
     * @param string $target
     * @param Skin $skin
     * @param string $mode
     * @param string $difficulty
     */
    public function __construct(Level $level, CompoundTag $nbt, string $target, string $mode, string $difficulty){
        parent::__construct($level, $nbt);

        $this->target = $target;
        $this->mode = $mode;
        $this->difficulty = $difficulty;

        if($this->difficulty = GamesManager::DIFFICULTY_EASY){
            $this->accuracy = 50;
        }elseif($this->difficulty = GamesManager::DIFFICULTY_MEDIUM){
            $this->accuracy = 69;
        }elseif($this->difficulty = GamesManager::DIFFICULTY_HARD){
            $this->accuracy = 80;
        }

    }

    /**
     * @return Player|null
     */
    public function getTargetPlayer() : ?Player{
        return Server::getInstance()->getPlayer($this->target);
    }


    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool{
        $hasUpdate = parent::entityBaseTick($tickDiff);
        if(!$this->isAlive()){
            if(!$this->closed) $this->flagForDespawn();

            $event = new TurtleGameEndEvent([$this, $this->getTargetPlayer()], $this->getTargetPlayer(), $this, $this->getTargetPlayer()->getGame());
            $event->call();

            return false;
        }
        $roundedHealth = round($this->getHealth(), 0);
        $this->setNameTag(TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Bot\n" . TextFormat::WHITE . "||" . TextFormat::RED . "  $roundedHealth  " . TextFormat::WHITE . "||");
        $position = $this->getTargetPlayer()->asVector3();
        $x = $position->x - $this->getX();
        $z = $position->z - $this->getZ();
        if($x != 0 || $z != 0){
            $this->motion->x = $this->getSpeed() * 0.35 * ($x / (abs($x) + abs($z)));
            $this->motion->z = $this->getSpeed() * 0.35 * ($z / (abs($x) + abs($z)));
        }
        $this->setSprinting(true);
        if($this->getHealth() < 5){
            if($this->potionsRemaining > 0){
                $this->pot();
            } else {
                if(!$this->recentlyHit()){
                    $this->move($this->motion->x, $this->motion->y, $this->motion->z);
                }
                $this->attackTargetPlayer();
            }
        } else {
            if(!$this->recentlyHit()){
                $this->move($this->motion->x, $this->motion->y, $this->motion->z);
            }
            if($this->getTargetPlayer() === null){
                $this->flagForDespawn();
                return false;
            } elseif($this->neededPots === 1){
                if($this->potionsRemaining > 0){
                    $this->pot();
                } else {
                    $this->attackTargetPlayer();
                }
            } else {
                $this->attackTargetPlayer();
            }
        }
        if(Server::getInstance()->getTick() - $this->hitTick >= 60){
            if($this->getHealth() <= 10){
                $this->pot();
            }
        }
        if($this->distance($this->getTargetPlayer()) > 20){
            $this->pearl();
        }
        if($this->distance($this->getTargetPlayer()) > 0.25 && $this->distance($this->getTargetPlayer()) < 4 && $this->getTargetPlayer()->getHealth() <= 15 && $this->canAgroPearl()){
            $this->pearl(true);
        }
        return $this->isAlive();
    }

    public function attackTargetPlayer() : void{

        if(mt_rand(0, 100) % 4 === 0){
            $this->lookAt($this->getTargetPlayer()->asVector3());
        }

        if($this->isLookingAt($this->getTargetPlayer()->asVector3())){
            if($this->distance($this->getTargetPlayer()) <= 3){
                $this->getInventory()->setHeldItemIndex(0);

                if ($this->canAttack()) {
                    if (Server::getInstance()->getTick() - $this->potTick >= 5) {
                        $event = new EntityDamageByEntityEvent($this, $this->getTargetPlayer(), EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getInventory()->getItemInHand() instanceof Sword ? $this->getInventory()->getItemInHand()->getAttackPoints() : 0.5);
                        $this->broadcastEntityEvent(4);
                        $this->getTargetPlayer()->attack($event);
                    }
                }
            }
        }

    }

    public function canAttack(): bool{

        if(mt_rand(0, 100) <= $this->accuracy){
            return true;
        } else {
            return false;
        }

    }

    public function pearl($agro = false) : void{
        if($this->mode = 'nodebuff') {
            if ($this->pearlsRemaining > 0) {
                if (!$agro) {
                    $max = 5;
                } else {
                    $max = 1.5;
                    $this->agroCooldown = Server::getInstance()->getTick();
                }
                --$this->pearlsRemaining;
                $this->teleport($this->getTargetPlayer()->asVector3()->subtract(mt_rand(0, $max), 0, mt_rand(0, $max)));
            }
        }
    }

    public function pot() : void{
        if($this->mode = 'nodebuff') {

            if ($this->yaw < 0) {
                $this->yaw = abs($this->yaw);
            } elseif ($this->yaw == 0) {
                $this->yaw = -180;
            } else {
                $this->yaw = -$this->yaw;
            }
            $this->pitch = 85;
            $this->getInventory()->setHeldItemIndex(2);
            ++$this->neededPots;
            $player = $this->getTargetPlayer();
            $soundPacket = new LevelSoundEventPacket();
            $soundPacket->sound = LevelSoundEventPacket::SOUND_GLASS;
            $soundPacket->position = $this->asVector3();
            $player->dataPacket($soundPacket);
            $effect = new EffectInstance(Effect::getEffect(Effect::INSTANT_HEALTH), 0, 1);
            $this->addEffect($effect);
            if ($this->distance($player) <= 2) {
                $player->addEffect($effect);
            }
            --$this->potionsRemaining;
            $this->potTick = Server::getInstance()->getTick();

        }
    }

    /**
     * @param Vector3 $target
     * @return bool
     */
    public function isLookingAt(Vector3 $target) : bool{
        $horizontal = sqrt(($target->x - $this->x) ** 2 + ($target->z - $this->z) ** 2);
        $vertical = $target->y - $this->y;
        $expectedPitch = -atan2($vertical, $horizontal) / M_PI * 180; //negative is up, positive is down

        $xDist = $target->x - $this->x;
        $zDist = $target->z - $this->z;
        $expectedYaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
        if($expectedYaw < 0){
            $expectedYaw += 360.0;
        }

        return abs($expectedPitch - $this->getPitch()) <= 5 && abs($expectedYaw - $this->getYaw()) <= 10;
    }

    /**
     * @return bool
     */
    public function canAgroPearl() : bool{
        return $this->agroCooldown === null ? true : Server::getInstance()->getTick() - $this->agroCooldown >= 175;
    }

    /**
     * @param Entity $attacker
     * @param float $damage
     * @param float $x
     * @param float $z
     * @param float $base
     */
    public function knockBack(Entity $attacker, float $damage, float $x, float $z, float $base = 0.4) : void{
        parent::knockBack($attacker, $damage, $x, $z, 0.45);
        $this->hitTick = Server::getInstance()->getTick();
    }

    /**
     * @return float
     */
    public function getSpeed() : float{
        return $this->speed;
    }

    public function attack(EntityDamageEvent $source): void{
        parent::attack($source);
        $this->hitTick = Server::getInstance()->getTick();
    }

    public function recentlyHit() : bool{
        return $this->hitTick !== null ? Server::getInstance()->getTick() - $this->hitTick <= 4 : false;
    }

    public function getRemainingPots() : int{
        return $this->potionsRemaining;
    }

    public function setDisabled(bool $bool){
        $this->disabled = $bool;
    }

}