<?php

namespace Core\Functions;

use pocketmine\level\Level;
use pocketmine\scheduler\AsyncTask;

class AsyncDeleteDir extends AsyncTask{

    /**
     * @var string $path
     */
    public string $path;

    /**
     * @var \Core\Main $plugin
     */
    public \Core\Main $plugin;

    /**
     * AsyncDeleteMap constructor.
     * @param string $path
     * @param \Core\Main $plugin
     */
    public function __construct(string $path, \Core\Main $plugin){
        $this->path = $path;
        $this->plugin = $plugin;
    }

    public function onRun(): void{

        $path = $this->path;

        $files = glob($path . '/*');
        foreach ($files as $file) {

            $delete = new AsyncDeleteDir($file, $this->plugin);
            is_dir($file) ? $delete->run() : unlink($file);

        }

        rmdir($path);
        return;
    }
}