<?php

namespace Core;

use Core\Game\Managers\Lobby\Listeners\ItemListener;

class Events
{
    private Main $core;
    public function __construct(Main $core) {
        $this->core = $core;
    }
    public function registerEvents() {
        $this->core->getServer()->getPluginManager()->registerEvents(new ItemListener(), $this->core);
    }
}