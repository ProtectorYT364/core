<?php

namespace Core\Game;

use pocketmine\Player;

class Queue{

    /**
     * @var array|Player
     */
    public array|Player $queue;

    /**
     * @var string
     */
    public string $queueType;


    /**
     * Queue constructor.
     * @param string $queueType
     */
    public function __construct(string $queueType){

        $this->queueType = $queueType;

    }

    /**
     * @return array|Player
     */
    public function getQueue(): array|Player
    {
        return $this->queue;
    }

    /**
     * @param Player $player
     */
    public function addPlayerToQueue(Player $player)
    {
        $this->queue[] = $player;
    }

    /**
     * @param Player $player
     */
    public function removePlayerFromQueue(Player $player)
    {
        if(($key = array_search($player, $this->queue, true)) !== false) {
            unset($this->queue[$key]);
        }

    }


}