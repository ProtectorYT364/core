<?php

namespace Core\Functions;

use Core\Utils;
use pocketmine\level\Level;
use pocketmine\scheduler\AsyncTask;

class AsyncDeleteMap extends AsyncTask{

    /**
     * @var \Core\TurtlePlayer
     */
    public \Core\TurtlePlayer $player;

    /**
     * @var string $folderName
     */
    public string $folderName;

    /**
     * @var \Core\Main $plugin
     */
    public \Core\Main $plugin;

    /**
     * AsyncDeleteMap constructor.
     * @param \Core\TurtlePlayer $player
     * @param $folderName
     * @param \Core\Main $plugin
     */
    public function __construct(\Core\TurtlePlayer $player, $folderName, \Core\Main $plugin){
        $this->player = $player;
        $this->folderName = $folderName;
        $this->plugin = $plugin;
    }

    public function onRun(): void{

        $folderName = $this->folderName;
        $player = $this->player;
        $plugin = $this->plugin;

        $mapName = $mapname = Utils::getMapNameFormat($player);

        if (!$plugin->getServer()->isLevelGenerated($mapName)) {

            return;
        }

        if (!$plugin->getServer()->isLevelLoaded($mapName)) {

            return;
        }

        $plugin->getServer()->unloadLevel($plugin->getServer()->getLevelByName($mapName));
        $folderName = $plugin->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $mapName;
        $plugin->removeDirectory($folderName);

        $plugin->getLogger()->notice("World has been deleted for player called " . $player->getName());
    }
}