<?php

namespace Core\Functions;

use pocketmine\scheduler\Task;


class customTask extends Task
{

    public $task;

    public function __construct($task){
     $this->task = $task;
    }

    public function onRun(int $currentTick){
    $this->task;
    }


}