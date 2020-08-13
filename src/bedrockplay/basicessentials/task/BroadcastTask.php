<?php

namespace bedrockplay\basicessentials\task;


use bedrockplay\basicessentials\BasicEssentials;
use pocketmine\scheduler\Task;

/**
 * Class BroadcastTask
 * @package bedrockplay\basicessentials\task
 */
class BroadcastTask extends Task {

    public const BROADCASTER_MESSAGES = [
        "Vote for our server at https://bit.do/bedrockplay and receive some epic rewards!",
        "Make sure to visit our online store at https://bedrockplay.tebex.io!",
        "Need information? Visit our official website https://bedrockplay.eu!",
        "Join our discord server at https://discord.io/bedrockplay",
        "Not your language? Do /lang",
        "Our servers are hosted by https://tradehosting.it!"
    ];

    /** @var BasicEssentials $plugin */
    public $plugin;

    /**
     * BroadcastTask constructor.
     * @param BasicEssentials $plugin
     */
    public function __construct(BasicEssentials $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        $message = self::BROADCASTER_MESSAGES[array_rand(self::BROADCASTER_MESSAGES, 1)];
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $player->sendMessage("§9§l>§r §7{$message}");
        }
    }
}