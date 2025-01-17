<?php

declare(strict_types=1);

namespace libReplay;

use libReplay\data\entry\DataEntry;
use libReplay\event\ReplayListener;
use libReplay\task\CaptureTask;
use libReplay\task\ReplayCompressionTask;
use NetherGames\NGEssentials\thread\NGThreadPool;
use pocketmine\event\HandlerList;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginManager;
use RuntimeException;

/**
 * Class ReplayServer
 * @package libReplay
 */
class ReplayServer
{

    /** @var float The API version. */
    public const API = 1.0;

    private const PHP_EXT_ZSTD = 'zstd';
    private const ENABLED = true;
    private const DISABLED = false;

    /** @var PluginBase */
    private static $plugin;
    /** @var PluginManager */
    private static $pluginManager;
    /** @var NGThreadPool */
    private static $threadPool;
    /** @var ReplayServer[] */
    private static array $serverList = [];

    /** @var bool */
    private static $status = self::DISABLED;
    /** @var ReplayClient[] */
    private array $connectedClientList = [];
    /** @var Level */
    private Level $level;
    /** @var DataEntry[] */
    private array $currentTickDataEntryMemory = [];
    /** @var ReplayListener */
    private ReplayListener $replayListener;
    /** @var CaptureTask */
    private CaptureTask $captureTask;
    /** @var bool */
    private bool $recording = false;
    /** @var ReplayCompressionTask */
    private ReplayCompressionTask $compressionTask;

    /**
     * ReplayServer constructor.
     * @param Player[] $playerList
     * @param Level $level
     */
    public function __construct(array $playerList, Level $level)
    {
        if (!self::$status) {
            throw new RuntimeException('The replay system has not been setup properly');
        }
        foreach ($playerList as $player) {
            $this->addClientFromPlayer($player);
        }
        $this->level = $level;
        self::$serverList[] = $this;
    }

    /**
     * Add client from a Player object.
     *
     * @param Player $player
     * @return void
     */
    public function addClientFromPlayer(Player $player): void
    {
        $client = ReplayClient::readFromPlayer($player);
        if ($client instanceof ReplayClient) {
            $this->connectedClientList[$client->getClientId()] = $client;
        }
    }

    /**
     * Setup the ReplayServer.
     *
     * @param PluginBase $plugin
     * @return void
     */
    public static function setup(PluginBase $plugin): void
    {
        if (!extension_loaded(self::PHP_EXT_ZSTD)) {
            throw new RuntimeException('The Zstandard extension cannot be found.');
        }
        self::$plugin = $plugin;
        self::$pluginManager = $plugin->getServer()->getPluginManager();
        self::$threadPool = NGThreadPool::getInstance();
        ReplayViewer::setup();
        self::$status = self::ENABLED;
    }

    /**
     * Get the plugin running this framework.
     *
     * @return PluginBase
     */
    public static function getPlugin(): PluginBase
    {
        return self::$plugin;
    }

    /**
     * Get all the replay servers currently
     * instantiated.
     *
     * @return ReplayServer[]
     */
    public static function getServerList(): array
    {
        return self::$serverList;
    }

    /**
     * Get all the replay servers currently
     * instantiated & recording.
     *
     * @return ReplayServer[]
     */
    public static function getRecordingServerList(): array
    {
        $recordingServerList = [];
        foreach (self::$serverList as $server) {
            $isRecording = $server->isRecording();
            if ($isRecording) {
                $recordingServerList[] = $server;
            }
        }
        return $recordingServerList;
    }

    /**
     * Check if the server is recording.
     *
     * @return bool
     */
    public function isRecording(): bool
    {
        return $this->recording;
    }

    /**
     * Get all the replay servers currently
     * instantiated & recording on a specific
     * level.
     *
     * @param Level $level
     * @return ReplayServer[]
     */
    public static function getRecordingServerListByLevel(Level $level): array
    {
        $recordingServerList = [];
        foreach (self::$serverList as $server) {
            $isRecording = $server->isRecording();
            $serverLevel = $server->getLevel();
            if ($isRecording && $serverLevel === $level) {
                $recordingServerList[] = $server;
            }
        }
        return $recordingServerList;
    }

    /**
     * Get the Level for which the server is currently
     * running on.
     *
     * @return Level
     */
    public function getLevel(): Level
    {
        return $this->level;
    }

    /**
     * Get the entire connected client list.
     *
     * @return ReplayClient[]
     */
    public function getConnectedClientList(): array
    {
        return $this->connectedClientList;
    }

    /**
     * Get the connected client by referencing a Player.
     * Note: This is a wrapper over getConnectedClientByClientId().
     *
     * @param Player $player
     * @return ReplayClient|null
     */
    public function getConnectedClientByPlayer(Player $player): ?ReplayClient
    {
        $playerId = $player->getName();
        return $this->getConnectedClientByClientId($playerId);
    }

    /**
     * Get the connected client by client id.
     *
     * @param string $clientId
     * @return ReplayClient|null
     */
    public function getConnectedClientByClientId(string $clientId): ?ReplayClient
    {
        $isConnected = isset($this->connectedClientList[$clientId]);
        if ($isConnected) {
            return $this->connectedClientList[$clientId];
        }
        return null;
    }

    /**
     * Remove a client from the recording.
     *
     * @param ReplayClient $connectedClient
     * @return void
     */
    public function removeClient(ReplayClient $connectedClient): void
    {
        $isConnected = in_array($connectedClient, $this->connectedClientList, true);
        if ($isConnected) {
            $clientId = $connectedClient->getClientId();
            unset($this->connectedClientList[$clientId]);
        }
    }

    /**
     * Adds the data entry to the current tick memory.
     * These data entries are captured into long term
     * memory every tick.
     *
     * @param DataEntry $entry
     * @return void
     */
    public function addEntryToTickMemory(DataEntry $entry): void
    {
        $this->currentTickDataEntryMemory[] = $entry;
    }

    /**
     * Capture the tick's entries into a safe long-term
     * memory storage.
     *
     * @param int $currentTick
     * @return void
     */
    public function capture(int $currentTick): void
    {
        /**
         * $this->dataEntryMemory[$currentTick] = $this->currentTickDataEntryMemory;
         * $this->currentTickDataEntryMemory = [];
         */
        $threadSafeTickMemory = [];
        foreach ($this->currentTickDataEntryMemory as $stepStamp => $dataEntry) {
            $threadSafeTickMemory[$stepStamp] = $dataEntry->convertToNonVolatile();
        }
        $this->currentTickDataEntryMemory = [];
        $this->compressionTask->addCurrentTickToMemory($currentTick, $threadSafeTickMemory);
    }

    /**
     * Toggle recording.
     *
     * @param bool $saveRecording
     * @param array $extraSaveData
     * @return void
     */
    public function toggleRecord(bool $saveRecording = false, array $extraSaveData = []): void
    {
        $this->recording = !$this->recording;
        foreach ($this->connectedClientList as $connectedClient) {
            $connectedClient->toggleRecord();
        }
        if ($this->recording) {
            // start recording & return
            $this->currentTickDataEntryMemory = [];
            $this->compressionTask = new ReplayCompressionTask();
            $this->replayListener = new ReplayListener($this);
            self::$pluginManager->registerEvents($this->replayListener, self::$plugin);
            $this->captureTask = new CaptureTask($this);
            self::$plugin->getScheduler()->scheduleRepeatingTask($this->captureTask, 1);
            return;
        }

        // stop recording and possibly save
        if ($this->replayListener instanceof ReplayListener) {
            HandlerList::unregisterAll($this->replayListener);
            unset($this->replayListener);
        }
        if ($this->captureTask instanceof CaptureTask) {
            $taskId = $this->captureTask->getTaskId();
            self::$plugin->getScheduler()->cancelTask($taskId);
            unset($this->captureTask);
        }

        if ($saveRecording) {
            /**
             * $replay = new Replay($this->dataEntryMemory, $this->connectedClientList, $this->referenceLevelName);
             * $this->savedRecording[$recordingName] = $replay;
             * $this->currentTickDataEntryMemory = [];
             * $this->dataEntryMemory = [];
             */
            foreach ($this->connectedClientList as $connectedClient) {
                $this->compressionTask->addClientData($connectedClient->convertToNonVolatile());
            }
            $this->compressionTask->storeExtraData($extraSaveData);
            self::$threadPool->submitTask($this->compressionTask);
            unset(self::$serverList[array_search($this, self::$serverList)]);
        }
    }

}