<?php

namespace Core\Functions;

use Core\Utils;
use Core\Main;
use pocketmine\level\Level;
use pocketmine\scheduler\AsyncTask;

class AsyncCreateMap extends AsyncTask{

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
     * @var Level $level
     */
    public Level $level;

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

    public function onRun(){

        $folderName = $this->folderName;
        $player = $this->player;
        $plugin = $this->plugin;


        $mapname = Utils::getMapNameFormat($player);

        $zipPath = $plugin->getServer()->getDataPath() . "plugin_data/Core/arenas" . $folderName . ".zip";

        if (file_exists($plugin->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $mapname)) {
            $plugin->deleteMap($player, $folderName);
        }

        $zipArchive = new \ZipArchive();
        if ($zipArchive->open($zipPath) == true) {
            $zipArchive->extractTo($plugin->getServer()->getDataPath() . "worlds");
            $zipArchive->close();
            $plugin->getLogger()->notice("Zip Object created!");
        } else {
            $plugin->getLogger()->notice("Couldn't create Zip Object!");
        }

        rename($plugin->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $folderName, $plugin->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $mapname);
         $plugin->getServer()->loadLevel($mapname);

        $this->level = Main::getInstance()->getLevelByName($mapname);
    }

    public function getLevel(){
        return $this->level;
    }
}