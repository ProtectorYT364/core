<?php

namespace Core;

use Core\Events\TurtleGameEnterEvent;
use Core\Game\Game;
use pocketmine\Player;
use pocketmine\level\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\math\Vector3;
use pocketmine\network\SourceInterface;
use Core\Core;
use Core\Functions\RespawnSystem;
use Core\Games\FFA;
use Core\Errors;
use Core\Game\Modes;
use Core\Game\Games;
use Core\Games\KnockbackFFA;


class TurtlePlayer extends Player{

    /**
     * @var bool $respawning
     * Variable to see if player is in respawn status.
     */
    public $respawning = false;

    /**
     * @var null|Player $tag
     */
    public $tag = null;

    /**
     * @var null|string $kb
     */
    public $kb = null;

    /**
     * @var null|Game $game
     */
    public $game = null;

    /**
     * @var string $actualNameTag
     */
    public string $actualNameTag;

    public function __construct(SourceInterface $interface, $ip, $port)
    {
        parent::__construct($interface, $ip, $port);
        $plugin = $this->getServer()->getPluginManager()->getPlugin("Core");
        if ($plugin instanceof Core) {
            $this->setPlugin($plugin);
        }
    }

    /**
     * @return Game
     * Returns the current game object of the player.
     */
    public function getGame(): Game{
    return $this->game;
    }

    /**
     * @param Game $game
     * Sets the current game object of the player.
     */
    public function setGame(Game $game){
    $this->game = $game;
    }

    /**
     * Deletes the current game object permanently.
     */
    public function unsetGame(){
    unset($this->game);
    }

    /**
     * @param Game $game
     * Calls an event and sets the game object of the player.
     */
    public function initializeGame(Game $game){
    $this->game = $game;
    $event = new TurtleGameEnterEvent($this, $game);
    $event->call();
    }


    public function initializeRespawn($game){
     RespawnSystem::initializeSystem($this, $game);

    }

    /**
     * @param bool $bool
     * Sets respawn status of the player.
     */
    public function setIsRespawning(bool $bool){
        $this->respawning = $bool;
    }

    /**
     * @return bool
     * Gets respawn status of the player.
     */
    public function getIsRespawning(){
        return $this->respawning;
    }

    /**
     * Initializes the lobby for the player.
     */
    public function initializeLobby(){
        $this->setIsRespawning(false);
        if($this->game != null) {
            unset($this->game);
        }else{
            $this->sendMessage("Error Encountered. ERROR CODE 10: ".Errors::CODE_10);
        }
        $this->teleport(new Vector3(0, 0, 0, 0, 0, $this->getServer()->getLevelByName("lobby")));
        \Core\Functions\GiveItems::giveKit("lobby", $this);
    }

    public function getKB(){
    return $this->kb;
    }

    public function setKB($kb){
    $this->kb = $kb;
    }

    public function setPlugin($plugin){
        $this->plugin=$plugin;
    }

    /**
     * @param $tag
     * Sets the person who tagged the player.
     */
    public function setTagged($tag){
    if($tag !== null) {
        if (is_string($tag)) {
            if (Player::isValidUserName($tag)) {
                $this->tag = $tag;
            } else {
                $this->sendMessage("Error Encountered. ERROR CODE 8: " . Errors::CODE_8);
            }
        } elseif (is_object($tag)) {
            if ($tag instanceof Player) {
                $this->tag = $tag->getName();
            } else {
                $this->sendMessage("Error Encountered. ERROR CODE 6: " . Errors::CODE_6);
            }
        } elseif (!is_object($tag) && !is_string($tag)) {
            $this->sendMessage("Error Encountered. ERROR CODE 7: " . Errors::CODE_7);
        }
    }else{
        $this->tag = null;
    }


       if($tag !== null) {
           if (is_object($tag) && $tag instanceof Player) {
               $players = $this->getServer()->getOnlinePlayers();
               foreach ($players as $player)
                   if ($player->getName() !== $tag->getName()) {
                       $this->hidePlayer($player);
                   }
           } elseif (is_string($tag)) {
               $players = $this->getServer()->getOnlinePlayers();
               foreach ($players as $player)
                   if (Player::isValidUserName($tag)) {
                       if ($player->getName() !== $tag) {
                           $this->hidePlayer($player);
                       }
                   } else {
                       $this->sendMessage("Error Encountered. ERROR CODE 9: " . Errors::CODE_9);
                   }
           }
       }else{
           $players = $this->getServer()->getOnlinePlayers();
           foreach ($players as $player)
           $this->showPlayer($player);
       }
    }

    /**
     * @return Player|null
     * Returns who tagged the player
     */
    public function getTagged(){
    return $this->tag;
    }

    /**
     * @return bool
     * Returns a bool to see if the player has someone tagged.
     */
    public function hasTagged(){
     if($this->tag !== null) {
       return false;
     } else {
    return true;
    }
   }

    /**
     * @param string $name
     * Change the player's nickname.
     */
   public function nick(string $name){
   $this->actualNameTag = $this->getName();
   $this->setNameTag($name);
   }

}