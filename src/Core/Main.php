<?php

namespace Core;

use Core\Main as Core;
use Core\BossBar\BossBar;
use Core\Errors;
use Core\Events\TurtleGameEnterEvent;
use Core\Functions\CustomTask;
use Core\Games\FFA;
use ethaniccc\NoDebuffBot\Bot;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\BookEditPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerJoinEvent, PlayerChatEvent, PlayerCreationEvent, PlayerMoveEvent, PlayerQuitEvent};
use pocketmine\event\block\{BlockBreakEvent, BlockPlaceEvent};
use pocketmine\event\entity\EntityDamageByEntityEvent;
use Core\Game\{Game, Modes, ModesManager, Games, GamesManager};
use Core\Events\TurtleGameEndEvent;
use Core\Functions\DeleteBlock;
use Party;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener
{

    private static $instance;

    /**
     * @var FFA
     */
    public FFA $ffa;

    /**
     * @var ModesManager
     */
    public ModesManager $mode;

    /**
     * @var GamesManager
     */
    public GamesManager $game;

    /**
     * @var array|Game
     */
    public $runningGames = [];

    /**
     * @var Party|array
     */
    public $parties = [];

    /**
     * @var Config
     */
    private Config $arenas;


    /**
     *
     */
    public function onEnable(): void
    {
        self::$instance = $this;

        if (!is_file($this->getDataFolder() . "arenas/config.yml")) {
            $this->saveDefaultConfig();
        }

        $this->arenas = new Config($this->getDataFolder() . "arenas/config.yml", Config::YAML, array());
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if (!is_dir($this->getDataFolder())) {
            @mkdir($this->getDataFolder());
        }
        $fist = new Game(null, Games::FFA, Modes::FIST, 'fist');
        $sumo = new Game(null, Games::FFA, Modes::SUMO, 'sumo');
        $this->addRunningGame($fist, 'fist-ffa');
        $this->addRunningGame($sumo, 'sumo-ffa');
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $eventHandler = new Events($this);
        $eventHandler->registerEvents();

    }


    /**
     * @param PlayerCreationEvent $e
     */
    public function playerClass(PlayerCreationEvent $e)
    {
        $e->setPlayerClass(\TurtlePlayer::class);
    }


    /**
     * @return mixed
     */
    public function getModesManager()
    {
        return $this->mode;
    }

    /**
     * @return mixed
     */
    public function getGamesManager()
    {
        return $this->game;
    }

    /**
     * @return array
     */
    public function getRunningGames()
    {
        return $this->runningGames;
    }

    /**
     * @param Game $game
     * @param string $name
     */
    public function addRunningGame(Game $game, string $name)
    {
        $this->runningGames[$name] = $game;
    }


    /**
     * @param string $name
     * @return mixed
     */
    public function getGame(string $name)
    {
        return $this->runningGames[$name];
    }

    /**
     * @return array
     */
    public function getConfig(): Config
    {
        return $this->arenas;
    }

    /**
     * make new game
     * @param array $players
     * @param string $type
     * @param string $mode
     * @param string $id
     * @param string $name
     * @return Game
     */

    public function createGame(array $players, string $type, string $mode, string $id, string $name): Game
    {

        $game = new Game($players, $type, $mode, $id);
        $this->addRunningGame($game, $name);

        return $game;
    }

    /**
     * @return mixed
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * @param PlayerJoinEvent $e
     */
    public function onJoin(PlayerJoinEvent $e)
    {
        $e->getPlayer()->teleport(new Vector3(0, 0, 0, 0, 0, $this->getServer()->getLevelByName("lobby")));
        try {
            $bossbar = new BossBar();
            $bossbar->setTitle("Turtle PvP " . $e->getPlayer()->getGame());
            $bossbar->addPlayer($e->getPlayer());
        } catch (\Exception $exception) {
            $bossbar = new BossBar();
            $bossbar->setTitle("Playing on turtle pvp");
            $bossbar->addPlayer($e->getPlayer());
        }
    }


    /**
     * @param EntityDamageByEntityEvent $e
     */
    public function onDeath(EntityDamageByEntityEvent $e)
    {
        $victim = $e->getEntity();
        if ($victim instanceof Player) {
            if ($victim->isOnline()) {
                if ($e->getFinalDamage() >= $victim->getHealth()) {
                    if ($victim->getGame() != null) {
                        if ($victim instanceof TurtlePlayer) {

                            if ($victim->getGame()->getType() !== GamesManager::BOT) {
                                $victim->initializeRespawn($victim->getGame());
                                $e->setCancelled();
                                $victim->setTagged(null);
                                $e->getEntity()->setTagged(null);

                            } else {
                                $victim->initializeLobby();
                            }

                        } elseif ($victim instanceof Bot) {
                            $victim->flagForDespawn();
                        }
                    } else {
                        $victim->sendMessage("Error encountered. ERROR CODE 4: " . Errors::CODE_4);
                    }
                }
            }
        }
    }

    /**
     * @param TurtleGameEnterEvent $e
     */
    public function onEnter(TurtleGameEnterEvent $e)
    {

        $game = $e->getGame();
        $minigame = $game->getType();
        $mode = $game->getMode();


        if (Core::getInstance()->getModesManager()->validate($mode) && Core::getInstance()->getGamesManager()->validate($minigame)) {
            if ($minigame == Core::getInstance()->getGamesManager()::FFA) {
                Core::getInstance()->getGamesManager()->getFFAManager()->initializeGame($this, $game);
                $game->addPlayer($e->getPlayer());
            } elseif ($minigame == Core::getInstance()->getGamesManager()::KBFFA) {
                Core::getInstance()->getGamesManager()->getKBFFAManager()->initializeGame($this, $game);
                $game->addPlayer($e->getPlayer());
            } elseif ($minigame == GamesManager::BOT) {
                echo 'coming soon';
            }
        } else {
            $e->getPlayer()->sendMessage("Error encountered. ERROR CODE 3: " . Errors::CODE_3);
        }

        try {
            $bossbar = new BossBar();
            $bossbar->setTitle("Turtle PvP " . $minigame);
            $bossbar->addPlayer($e->getPlayer());
        } catch (\Exception $exception) {
            $bossbar = new BossBar();
            $bossbar->setTitle("Playing on turtle pvp");
            $bossbar->addPlayer($e->getPlayer());
        }

    }

    /**
     * @param TurtleGameEndEvent $e
     */
    public function onLeave(TurtleGameEndEvent $e)
    {

        $e->getGame()->removePlayer($e->getGamePlayers());

        if ($e->getGame()->getType() == GamesManager::BOT) {
            if ($winner = $e->getWinner() instanceof TurtlePlayer) {
                $winner->initializeLobby();
            } elseif ($looser = $e->getLoser() instanceof TurtlePlayer) {
                $looser->initializeLobby();
            }
        }

        //gib winner kills, etc.

    }

    /**
     * @param PlayerChatEvent $e
     */
    public function onChat(PlayerChatEvent $e)
    {

        if ($e->getPlayer()->getIsRespawning()) {
            if ($e->getMessage() == "lobby") {

                $e->getPlayer()->initializeLobby();
                $players = [$e->getPlayer(), $e->getPlayer()->getTagged()];
                foreach ($players as $player)
                    $event = new TurtleGameEndEvent($player, $e->getPlayer()->getTagged(), $e->getPlayer(), $e->getPlayer()->getGame());
                $event->call();
                $e->setCancelled();

            }
        }
    }

    /**
     * @param BlockBreakEvent $e
     */
    public function onBreak(BlockBreakEvent $e)
    {

        if ($e->getPlayer()->getGame()->getType() != Games::KBFFA) {
            $e->setCancelled();
        }

    }

    /**
     * @param BlockPlaceEvent $e
     */
    public function onPlace(BlockPlaceEvent $e)
    {

        if ($e->getPlayer()->getGame()->getType() == Games::KBFFA) {
            $e->getPlayer()->getLevel()->broadcastLevelEvent($e->getBlock(), LevelEventPacket::EVENT_BLOCK_START_BREAK, (int)20 * 10);
            $this->getScheduler()->scheduleDelayedTask(new DeleteBlock($e->getBlock(), $e->getPlayer()->getLevel()), 20 * 10);

        } else {
            $e->setCancelled();
        }

    }

    /**
     * @param EntityDamageByEntityEvent $e
     */
    public function cancelHit(EntityDamageByEntityEvent $e)
    {

        if ($e->getDamager()->getGame() == null) {
            $e->setCancelled();

        }
    }

    /**
     * @param EntityDamageByEntityEvent $e
     */
    public function setKB(EntityDamageByEntityEvent $e)
    {


        if ($e->getDamager()->getGame()->getType() == Games::FFA) {
            if ($e->getDamager()->getGame()->getMode() == Modes::FIST) {
                $e->getDamager()->setMotion(new Vector3(0.405, 0.370, 0.405));
            } elseif ($e->getDamager()->getGame()->getMode() == Modes::SUMO) {
                $e->getDamager()->setMotion(new Vector3(0.385, 0.380, 0.385));
            }
        } elseif ($e->getDamager()->getGame()->getType() == GamesManager::BOT) {
            $e->getDamager()->setMotion(new Vector3(0.385, 0.380, 0.385));
        }


    }

    /**
     * @param EntityDamageEvent $e
     */
    public function setAttackTime(EntityDamageEvent $e)
    {


        if ($e->getDamager()->getGame()->getType() == Games::FFA) {
            if ($e->getDamager()->getGame()->getMode() == Modes::FIST) {
                $e->setAttackCooldown(8);
            } elseif ($e->getDamager()->getGame()->getMode() == Modes::SUMO) {
                $e->setAttackCooldown(10);
            }
        } elseif ($e->getDamager()->getGame()->getType() == GamesManager::BOT) {
            $e->setAttackCooldown(10);
        }
    }

    /**
     * @param EntityDamageByEntityEvent $e
     */
    public function onHit(EntityDamageByEntityEvent $e)
    {
        $d = $e->getDamager();
        $p = $e->getEntity();
        $p->setTagged($d);
        $p->sendMessage("You're now combat logged.");
        $task = $p->setTagged(null);
        $this->getScheduler()->scheduleDelayedTask(new CustomTask($task), 20 * 10);
    }

    public function onQuit(PlayerQuitEvent $e)
    {
        //TODO: Combat Logger, gib kills to who tagged
    }

    /**
     * @param PlayerMoveEvent $e
     */
    public function onMove(PlayerMoveEvent $e)
    {
        if (is_null($e->getPlayer()->getKB())) {
            $e->getPlayer()->sendMessage("Error encountered. ERROR CODE 5: " . Errors::CODE_5);
            $e->setCancelled();
        }
    }

    /**
     * @param Party $party
     * Unsets a party. Used by Party::delete()
     */
    public function deleteParty(Party $party)
    {
        unset($party);
    }


    /**
     * @param Player $owner
     * Creates a party.
     */
    public function createParty(Player $owner)
    {

        $party = new Party($owner);
        $this->parties[] = $party;

    }

    /**
     * @return Party
     * Returns all the current parties.
     */
    public function getParties(): Party
    {
        return $this->parties;
    }


    /**
     * @param TurtlePlayer $player
     * @param $folderName
     * @return \pocketmine\level\Level|null
     */
    public function createMap(TurtlePlayer $player, $folderName)
    {
        $mapname = $folderName . "-" . $player->getName();

        $zipPath = $this->getServer()->getDataPath() . "plugin_data/ClutchCore/" . $folderName . ".zip";

        if (file_exists($this->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $mapname)) {
            $this->deleteMap($player, $folderName);
        }

        $zipArchive = new \ZipArchive();
        if ($zipArchive->open($zipPath) == true) {
            $zipArchive->extractTo($this->getServer()->getDataPath() . "worlds");
            $zipArchive->close();
            $this->getLogger()->notice("Zip Object created!");
        } else {
            $this->getLogger()->notice("Couldn't create Zip Object!");
        }

        rename($this->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $folderName, $this->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $mapname);
        $this->getServer()->loadLevel($mapname);
        return $this->getServer()->getLevelByName($mapname);
    }

    /**
     * @param TurtlePlayer $player
     * @param $folderName
     * @return void
     */
    public function deleteMap(TurtlePlayer $player, $folderName): void
    {
        $mapName = $folderName . "-" . $player->getName();
        if (!$this->getServer()->isLevelGenerated($mapName)) {

            return;
        }

        if (!$this->getServer()->isLevelLoaded($mapName)) {

            return;
        }

        $this->getServer()->unloadLevel($this->getServer()->getLevelByName($mapName));
        $folderName = $this->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $mapName;
        $this->removeDirectory($folderName);

        $this->getLogger()->notice("World has been deleted for player called " . $player->getName());

    }

    /**
     * @param $path
     * @return void
     */
    public function removeDirectory($path): void
    {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            is_dir($file) ? $this->removeDirectory($file) : unlink($file);
        }
        rmdir($path);
        return;
    }
}