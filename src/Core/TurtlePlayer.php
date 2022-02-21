<?php

namespace Core;

use Core\Main as Core;
use Core\Errors;
use Core\Events\TurtleGameEnterEvent;
use Core\Game\Game;
use Party\Party;
use pocketmine\Player;
use pocketmine\level\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\math\Vector3;
use pocketmine\network\SourceInterface;
use Core\Functions\RespawnSystem;
use Core\Games\FFA;
use Core\Game\Modes;
use Core\Game\GamesManager as Games;
use Core\Games\KnockbackFFA;


class TurtlePlayer extends Player{

    /**
     * @var bool $respawning
     * Variable to see if player is in respawn status.
     */
    public bool $respawning = false;

    /**
     * @var null|Player $tag
     */
    public null|Player $tag = null;

    /**
     * @var null|string $kb
     */
    public null|string $kb = null;

    /**
     * @var null|Game $game
     */
    public null|Game $game = null;

    /**
     * @var PlayerConfig
     */
    public PlayerConfig $config;

    /**
     * @var string $actualNameTag
     */
    public string $actualNameTag;

    /**
     * @var Party
     */
    public Party $party;

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
        } else {

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

    public function getX()
    {
        return $this->getX();
    }

    public function getZ()
    {
        return $this->getZ();
    }

    /**
     * @return PlayerConfig
     * get config
     */
    public function getConfig(): PlayerConfig{
        return $this->config;
    }

    /**
     * save config
     */
    public function saveConfig(){

        $config = json_encode($this->getConfig());
        $thefile = fopen(Main::getInstance()->getDataFolder(). 'plugin_data/' . 'Core/' . $this->getName() . '.json', "w+");
        fwrite($thefile, $config);
        fclose($thefile);

    }

    /**
     * @param string $type
     */
    public function setConfigByType(string $type){

        foreach($this->getConfig()->configs as $configs){
            if($configs == $type){
                $configs = $type;
            }
        }
    }

    /**
     * build the config class ($this->config) from .json
     * @param bool $type
     */
    public function buildConfigClass(bool $type = true){

        $playerConfigClass = new PlayerConfig();

        if ($type) {

            $thefile = fopen(Main::getInstance()->getDataFolder() . 'plugin_data/' . 'Core/' . $this->getName() . '.json', "w+");

            $jsonData = file_get_contents($thefile);
            $phpClass = json_decode($jsonData);

            $playerConfigClass->deviceQueuing = $phpClass->deviceQueuing;
            $playerConfigClass->javaInventory = $phpClass->javaInventory;

            $this->config = $playerConfigClass;

        } else {

            $playerConfigClass->deviceQueuing = "false";
            $playerConfigClass->javaInventory = "true";

            $this->config = $playerConfigClass;

        }

    }

    /**
     * @param Party $party
     */
    public function setParty(Party $party){
        $this->party = $party;
    }

    /**
     * remov
     */
    public function removeParty(){
        unset($this->party);
    }

    /**
     * @return Party|null
     */
    public function getParty(): Party|null{
        if($this->partyExists()){
            return $this->party;
        } else {
            return null;
        }
    }

    /**
     * @return bool
     */
    public function partyExists(): bool{
        if(isset($this->party)){
            return true;
        } else {
            return false;
        }
    }

}