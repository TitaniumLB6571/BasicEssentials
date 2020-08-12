<?php

declare(strict_types=1);

namespace bedrockplay\basicessentials\commands;

use bedrockplay\openapi\ranks\RankDatabase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;

/**
 * Class SetRankCommand
 * @package bedrockplay\basicessentials\commands
 */
class SetRankCommand extends Command {

    /**
     * SetRankCommand constructor.
     */
    public function __construct() {
        parent::__construct("setrank", "Updates player's rank");
        $this->setPermission("bedrockplay.operator");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$this->testPermission($sender)) {
            return;
        }
        if(count($args) < 2) {
            $sender->sendMessage("§cUsage: §7/setrank <player> <rank>");
            return;
        }

        $player = Server::getInstance()->getPlayer($args[0]);
        if($player === null) {
            $sender->sendMessage("§9Ranks> §cInvalid player");
            return;
        }

        $rank = RankDatabase::getRankByName($args[1]);
        if($rank === null || in_array(strtolower($args[1]), ["vip", "mvp", "bedrock"])) {
            $sender->sendMessage("§9Ranks> §cInvalid rank");
            return;
        }

        RankDatabase::savePlayerRank($player, $rank->getName(), true);
        $player->sendMessage("§9Ranks> §aYour rank was updated by {$sender->getName()} to {$rank->getName()}!");
        $sender->sendMessage("§9Ranks> §a{$player->getName()}'s rank successfully updated to {$rank->getName()}!");
    }
}