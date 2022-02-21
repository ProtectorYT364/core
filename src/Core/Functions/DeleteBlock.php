<?php

namespace Core\Functions;

use pocketmine\Player;
use pocketmine\level\level;
use pocketmine\math\Vector3;
use Core\Main;
use pocketmine\scheduler\Task;
use Core\Game\Modes;
use Core\Game\Games;
use pocketmine\block\Air;

class DeleteBlock extends Task{

    public $block;
    public $level;

    public function __construct($block, $level){
    $this->block = $block;
    $this->level = $level;
    }


    public function onRun(int $currentTick)
    {
        $this->level->setBlock(new Vector3($this->block->getX(), $this->block->getY(), $this->block->getZ()), new Air(), false);
    }


}
