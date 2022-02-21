<?php

use pocketmine\Player;

class Party{

    /*
    /**
     * @var string $id
    public string $id;
    */


    /**
     * @var Player|array
     * Players in the party.
     */
    public $players = [];

    /**
     * @var Player
     * Party's owner.
     */
    public Player $owner;

    public function __construct($owner){
    $this->owner = $owner;
    }

    /**
     * @param bool $includeOwner
     * @return array|Player
     * Returns players in the party.
     */
    public function getPlayers($includeOwner = true): array
    {
        if ($includeOwner) {
            return $this->players;
        } else {
            $players = $this->players;
            if (($key = array_search($this->owner, $players, true)) !== FALSE)
                unset($players[$key]);
                return $players;
        }
    }

    /**
     * @param Player $player
     * Adds player to the party.
     */
    public function addPlayer(Player $player)
    {

    $this->players[] = $player;

    }

    /**
     * @param Player $player
     * Removes player from the party.
     */

    public function removePlayer(Player $player)
    {
        if (is_array($player)) {
            foreach ($player as $players)
                if (($key = array_search($players, $this->players, true)) !== FALSE) {
                    unset($this->players[$key]);
                }
        } else {
            if (($key = array_search($player, $this->players, true)) !== FALSE) {
                unset($this->players[$key]);
            }
        }
    }

    /**
     * @param Player $player
     * Sets the party owner.
     */
    public function setOwner(Player $player)
    {
        $this->owner = $player;
    }

    /**
     * Deletes the party.
     */
    public function delete()
    {
    \Core\Main::getInstance()->deleteParty($this);
    }



}