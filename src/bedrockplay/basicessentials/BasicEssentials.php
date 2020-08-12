<?php

declare(strict_types=1);

namespace bedrockplay\basicessentials;

use bedrockplay\basicessentials\broadcaster\BroadcasterManager;
use bedrockplay\basicessentials\broadcaster\task\BroadcastTask;
use bedrockplay\basicessentials\commands\AddCoinsCommand;
use bedrockplay\basicessentials\commands\BanCommand;
use bedrockplay\basicessentials\commands\BroadcastCommand;
use bedrockplay\basicessentials\commands\CoinsCommand;
use bedrockplay\basicessentials\commands\SetRankCommand;
use bedrockplay\openapi\ranks\RankDatabase;
use bedrockplay\openapi\servers\ServerManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\plugin\PluginBase;
use vixikhd\bpcore\api\language\T;

/**
 * Class BasicEssentials
 * @package bedrockplay\basicessentials
 */


class BasicEssentials extends PluginBase implements Listener {

    /** @var float[] $chatDelays */
    public $chatDelays = [];

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleRepeatingTask(new BroadcastTask($this), 20 * 60 * 5); // Every 5 minutes
        $this->getServer()->getCommandMap()->register("BasicEssentials", new AddCoinsCommand());
        $this->getServer()->getCommandMap()->register("BasicEssentials", new BanCommand());
        $this->getServer()->getCommandMap()->register("BasicEssentials", new CoinsCommand());
        $this->getServer()->getCommandMap()->register("BasicEssentials", new SetRankCommand());

    }

    public function onDisable() {
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            ServerManager::getServer("Lobby-1")->transferPlayerHere($player);
        }

        sleep(2);
    }

    /**
     * @param PlayerChatEvent $event
     *
     * @priority LOW
     */
    public function onChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();

        // Chat delay
        $delay = 2;
        if($player->hasPermission("bedrockplay.vip")) {
            $delay = 0.5;
        }
        if($player->hasPermission("bedrockplay.mvp")) {
            $delay = 0;
        }

        if($delay > 0) {
            // TODO - Move Language API from BPCore to OpenAPI
            if(isset($this->chatDelays[$player->getName()]) && microtime(true) - $this->chatDelays[$player->getName()] <= $delay) {
                $player->sendMessage(T::trp($player, "chat-limit", [(string)round($delay - abs( $this->chatDelays[$player->getName()] - microtime(true)), 2)], T::PREFIX_CHAT));
                $event->setCancelled(true);
            }
            else {
                $this->chatDelays[$player->getName()] = microtime(true);
            }
        }

        // CAPS detector
        if(!$player->hasPermission("bedrockplay.vip")) {
            $upperLetters = 0;
            foreach (str_split($event->getMessage()) as $letter) {
                if(ctype_upper($letter)) {
                    $upperLetters++;
                }
            }

            if($upperLetters > 5) {
                $player->sendMessage(T::trp($player,"chat-caps", [], T::PREFIX_CHAT));
                $event->setMessage(ucfirst(strtolower($event->getMessage())));
            }
        }

        // Anti advertisement
        if(!$player->hasPermission("bedrockplay.mvp")) {
            $wrong = [".cz", ".one", ".pe", "hicoria.", ".net", "mc-play", "play.", "leet.", ".cc", ".eu", ".com", ":19132", "aternos.", ".aternos", "muj server", "můj server", "nbb.one", "nbbone", "nbb.wtf", "nbbwtf"];
            $problemFound = false;

            $fixedMessage = str_replace([",", "-"], [".", "."], strtolower($event->getMessage()));
            foreach($wrong as $word) {
                if(strpos($fixedMessage, $word) !== false) {
                    $problemFound = true;
                    break;
                }
            }

            if($problemFound) {
                $player->sendMessage(T::trp($player,"chat-advertisement", [], T::PREFIX_CHAT));
                $event->setCancelled(true);
            }
        }

        // Format
        $rank = RankDatabase::getPlayerRank($player);
        $chatColor = $player->hasPermission("bedrockplay.vip") ? "§f" : "§7";
        $fontHeightParameter = $rank->getName() === "Guest" ? "՗" : " ";
        $event->setFormat("{$rank->getFormatForChat()}§r§7{$player->getName()}§8:{$chatColor}{$fontHeightParameter}{$event->getMessage()}");
    }
}