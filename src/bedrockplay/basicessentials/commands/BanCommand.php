<?php

declare(strict_types=1);

namespace bedrockplay\basicessentials\commands;

use bedrockplay\openapi\math\TimeFormatter;
use bedrockplay\openapi\mysql\query\BanQuery;
use bedrockplay\openapi\mysql\query\FindPlayerQuery;
use bedrockplay\openapi\mysql\QueryQueue;
use bedrockplay\openapi\servers\ServerManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;

/**
 * Class BanCommand
 * @package bedrockplay\basicessentials\commands
 */
class BanCommand extends Command {
    use TimeFormatter;

    public const MINUTE = 60;
    public const HOUR = 60 * 60;
    public const DAY = 60 * 60 * 24;
    public const WEEK = 60 * 60 * 24 * 7;
    public const MONTH = 60 * 60 * 24 * 30;
    public const YEAR = 60 * 60 * 24 * 30 * 12;

    public const BAN_DATA = [
        "fly" => ["You were banned for 1 week (Fly)", self::WEEK, "Fly"],
        "reach" => ["You were banned for 1 week (Reach)", self::WEEK, "Reach"],
        "hitbox" => ["You were banned for 1 week (HitBox)", self::WEEK, "HitBox"],
        "airjump" => ["You were banned for 1 week (AirJump)", self::WEEK, "AirJump"],
        "killaura" => ["You were banned for 1 week (KillAura)", self::WEEK, "KillAura"],
        "speed" => ["You were banned for 1 day (Speed)", self::DAY, "Speed"],
        "glide" => ["You were banned for 1 day (Glide)", self::DAY, "Glide"],
        "multiple" => ["You were banned for 30 days (Multiple Hacks)", self::MONTH, "MultipleHacks"]
    ];

    /**
     * BanCommand constructor.
     */
    public function __construct() {
        parent::__construct("ban", "Ban commands", null, []);
        $this->setPermission("bp.staff");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$this->testPermission($sender)) {
            return;
        }
        if(count($args) < 2) {
            $sender->sendMessage("§9Usage: §7/ban <player> <time|[fly, reach, hitbox, airjump, killaura, speed, glide, multiple]> [reason]");
            return;
        }

        $player = Server::getInstance()->getPlayer($playerName = $args[0]);

        if($this->canFormatTime($args[1])) {
            if(!isset($args[2])) {
                $sender->sendMessage("§9Usage: §7/ban <player> <time> <reason>");
                return;
            }

            $time = time() + $this->getTimeFromString($args[1]);

            array_shift($args);
            array_shift($args);
            $reason = implode(" ", $args);

            if($player === null) {
                $sender->sendMessage("§9Bans> §6Looking for the player in our database...");

                QueryQueue::submitQuery(new FindPlayerQuery($playerName), function (FindPlayerQuery $query) use ($sender, $reason, $time, $playerName) {
                    if((!$sender instanceof Player) || !$sender->isOnline()) {
                        return;
                    }

                    if(!$query->exists) {
                        $sender->sendMessage("§9Bans> §cPlayer $playerName wasn't found in our database.");
                        return;
                    }

                    $this->banPlayer($playerName, $sender->getName(), $reason, $time);
                });
                return;
            }

            $this->banPlayer($player->getName(), $sender->getName(), $reason, $time);
            return;
        }

        $cheat = strtolower($args[1]);
        if(isset(self::BAN_DATA[$cheat])) {
            if($player === null) {
                $sender->sendMessage("§9Bans> §6Looking for the player in our database...");

                $reason = self::BAN_DATA[$cheat][2];
                $time = time() + self::BAN_DATA[$cheat][1];
                QueryQueue::submitQuery(new FindPlayerQuery($playerName), function (FindPlayerQuery $query) use ($sender, $reason, $time, $playerName) {
                    if((!$sender instanceof Player) || !$sender->isOnline()) {
                        return;
                    }

                    if(!$query->exists) {
                        $sender->sendMessage("§9Bans> §cPlayer $playerName wasn't found in our database.");
                        return;
                    }

                    $this->banPlayer($playerName, $sender->getName(), $reason, $time);
                });
                return;
            }

            $this->banPlayer($player->getName(), $sender->getName(), self::BAN_DATA[$cheat][2], time() + self::BAN_DATA[$cheat][1]);
            return;
        }

        $sender->sendMessage("§9§l> §7Normal bans have not been implemented. Contact VixikHD or TitaniumLB to implement them.");
    }

    /**
     * @param string $player
     * @param string $admin
     * @param string $reason
     * @param int $timeUntil
     */
    public function banPlayer(string $player, string $admin, string $reason, int $timeUntil) {
        $until = $this->getTimeName($timeUntil);

        Server::getInstance()->broadcastMessage("§9Bans> §7Player $player was banned by $admin because of $reason until $until.");
        QueryQueue::submitQuery(new BanQuery($player, $admin, $timeUntil, $reason));

        $targetPlayer = Server::getInstance()->getPlayer($player);
        if($targetPlayer !== null && !ServerManager::getCurrentServer()->isLobby()) {
            ServerManager::getServer("Lobby-1")->transferPlayerHere($targetPlayer);
        }
    }
}