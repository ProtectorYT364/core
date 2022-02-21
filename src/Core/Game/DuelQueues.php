<?php

namespace Core\Game;

use pocketmine\Player;

class DuelQueues {

    const NODEBUFF_UNRANKED = "nodebuff_unranked_duel";

    /**
     * @var Queue
     */
    public Queue $nodebuff_unranked_duel;


    /**
     * Sets up all the queues
     */
    public function setup()
    {

        $nodebuff = new Queue("nodebuff");
        $this->nodebuff_unranked_duel = $nodebuff;

    }

    /**
     * @param string $queue
     * @return Queue
     */
    public function getQueue(string $queue): Queue
    {

        $queue = false;

        if($queue = self::NODEBUFF_UNRANKED){
            $queue = $this->nodebuff_unranked_duel;
        }

        return $queue;

    }



}