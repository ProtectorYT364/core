<?php

namespace Core;

use Core\Errors;
use Core\Events\TurtleAddPlayerToQueueEvent;
use Core\Games\Duels;
use Core\Main as Core;
use Core\BossBar\BossBar;
use Core\Events\TurtleGameEnterEvent;
use Core\Functions\{AsyncDeleteDir, AsyncDeleteMap, Countdown, CustomTask, AsyncCreateMap};
use Core\Games\FFA;
use Core\Entities\Bot;
use libReplay\ReplayServer;
use Party\PartyHandler;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\BookEditPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\GameMode;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerJoinEvent, PlayerChatEvent, PlayerCreationEvent, PlayerMoveEvent, PlayerQuitEvent};
use pocketmine\event\block\{BlockBreakEvent, BlockPlaceEvent};
use pocketmine\event\entity\EntityDamageByEntityEvent;
use Core\Game\{DuelQueues, Game, Modes, ModesManager, GamesManager};
use Core\Game\GamesManager as Games;
use Core\Events\TurtleGameEndEvent;
use Core\Functions\DeleteBlock;
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
     * @var PartyHandler
     */
    public PartyHandler $partyHandler;

    /**
     * @var Game|array
     */
    public $runningGames = [];

    /**
     * @var Config
     */
    private Config $arenas;

    /**
     * @var DuelQueues
     */
    public DuelQueues $DuelQueues;


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

        $fist = new Game(null, GamesManager::FFA, ModesManager::FIST, 'fist');
        $sumo = new Game(null, GamesManager::FFA, ModesManager::SUMO, 'sumo');
        $this->addRunningGame($fist, 'fist-ffa');
        $this->addRunningGame($sumo, 'sumo-ffa');

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $eventHandler = new Events($this);
        $eventHandler->registerEvents();

        Entity::registerEntity(Bot::class, true);

        $e =  new DuelQueues();
        $e->setup();
        $this->DuelQueues = $e;

    }


    /**
     * @param PlayerCreationEvent $e
     */
    public function playerClass(PlayerCreationEvent $e)
    {
        $e->setPlayerClass(TurtlePlayer::class);
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

        if($game->getType() == Games::BOT){

            foreach ($players as $player) {
                if ($player instanceof TurtlePlayer) {
                    $yes = $player;
                 }
                }

                $this->createMap($yes, Utils::getRandomMap());

        }

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
        $e->getPlayer()->teleport(new Vector3(0, 20, 0, 0, 0, $this->getServer()->getLevelByName("lobby")));

        try {
            $bossbar = new BossBar();
            $bossbar->setTitle("Turtle PvP " . $e->getPlayer()->getGame());
            $bossbar->addPlayer($e->getPlayer());
        } catch (\Exception $exception) {
            $bossbar = new BossBar();
            $bossbar->setTitle("Playing on turtle pvp");
            $bossbar->addPlayer($e->getPlayer());
        }

        $e->getPlayer()->initializeLobby();


        if(!is_file($this->getDataFolder() . 'plugin_data/' . 'Core/' . $e->getPlayer()->getName() . '.json')){

            $e->getPlayer()->buildConfigClass($type = false);
            $file = fopen($this->getDataFolder() . 'plugin_data/' . 'Core/' . $e->getPlayer()->getName() . '.json', "w+");
            fwrite($file, json_encode($e->getPlayer()->getConfig()));
            fclose($file);


        } else {
            $e->getPlayer()->buildConfigClass();
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

            $ffa = GamesManager::FFA;
            $kbffa = GamesManager::KBFFA;
            $bot_ = GamesManager::BOT;
            $duel = GamesManager::DUEL;

            switch($minigame) {

                case $ffa:

                    Core::getInstance()->getGamesManager()->getFFAManager()->initializeGame($this, $game);
                    $game->addPlayer($e->getPlayer());
                    break;

                case $kbffa:


                    Core::getInstance()->getGamesManager()->getKBFFAManager()->initializeGame($this, $game);
                    $game->addPlayer($e->getPlayer());
                    break;

                case $bot_:

                    foreach ($e->getGame()->getPlayers() as $players) {
                        if ($players instanceof Bot) {
                            $bot = $players;
                        }
                    }

                    Duels::initializeBotGame($e->getPlayer(), $bot, $e->getGame());
                    break;

                case $duel:

                    foreach($e->getGame()->getPlayers() as $players)
                    {
                        $players->setGamemode(GameMode::SURVIVAL_VIEWER);
                    }
                    break;

                default:
                    $e->getPlayer()->sendMessage("Error encountered. ERROR CODE 3: " . Errors::CODE_3);
                    break;
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

    public function queued(TurtleAddPlayerToQueueEvent $e){

        $duelQueue = $e->getQueue();

      if(array_count_values($duelQueue->getQueue()) < 0){

          $p = $e->getPlayer();
          foreach($duelQueue->getQueue() as $players){
              if($players !== $p){
                  $o = $players;
              }
          }
          $game = $this->createGame($duelQueue->getQueue(), GamesManager::DUEL, ModesManager::NODEBUFF, Utils::buildID($p, $o), Utils::buildID($p, $o));


              $p->setGame($game);

              $map = $this->createMap($p, Utils::getRandomMap());

              foreach($duelQueue->getQueue() as $players){
                  $players->teleport($map->getSafeSpawn());
                  $duelQueue->removePlayerFromQueue($players);
                  $event = new TurtleGameEnterEvent($players, $game);
                  $event->call();
              }


          $this->getScheduler()->scheduleDelayedTask(new Countdown(3, "Spawning in...", "3 seconds", $game, $p, true), 20 * 1);
          $this->getScheduler()->scheduleDelayedTask(new Countdown(2, "Spawning in...", "2 seconds", $game, $p, true), 20 * 2);
          $this->getScheduler()->scheduleDelayedTask(new Countdown(1, "Spawning in...", "1 seconds", $game, $p, true), 20 * 3);
          $this->getScheduler()->scheduleDelayedTask(new Countdown(0, "Spawning in...", "0 seconds", $game, $p, true), 20 * 4);




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
        if($d instanceof TurtlePlayer) {
            if($d->getConfig()->deviceQueuing == "true" or $p->getConfig()->deviceQueuing == "true") {
                $p->setTagged($d);
                $p->sendMessage("You're now combat logged.");
                $task = $p->setTagged(null);
                $this->getScheduler()->scheduleDelayedTask(new CustomTask($task), 20 * 10);
            }
        }

        if($d instanceof TurtlePlayer && $p instanceof TurtlePlayer){

            if($d->getConfig()->deviceQueuing == "true" or $p->getConfig()->deviceQueuing == "true") {
                if ($d->getDeviceOS() !== $p->getDeviceOS()) {
                    $d->sendMessage("You cannot hit a player that you're not on the same OS with! (They have Device Queuing on!)");
                    $e->setCancelled();
                }
            }
         }
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
     * @param TurtlePlayer $player
     * @param $folderName
     * @return Level
     */
    public function createMap(TurtlePlayer $player, $folderName): Level
    {
       $create = new AsyncCreateMap($player, $folderName, $this);
       $create->run();

       return $create->getLevel();
    }

    /**
     * @param TurtlePlayer $player
     * @param $folderName
     * @return void
     */
    public function deleteMap(TurtlePlayer $player, $folderName): void
    {

        $delete = new AsyncDeleteMap($player, $folderName, $this);
        $delete->run();

    }

    /**
     * @param $path
     * @return void
     */
    public function removeDirectory($path): void
    {

        $delete = new AsyncDeleteDir($path, $this);
        $delete->run();

    }

    /**
     * @return DuelQueues
     */
    public function getDuelQueues(): DuelQueues
    {

        return $this->DuelQueues;

    }

    /**
     * @return PartyHandler
     */
    public function getPartyHandler(): PartyHandler
    {
        return $this->partyHandler;
    }


}