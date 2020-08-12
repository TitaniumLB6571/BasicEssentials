<?php

declare(strict_types=1);

namespace bedrockplay\basicessentials\commands;

use bedrockplay\openapi\economy\Economy;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;

/**
 * Class AddCoinsCommand
 * @package bedrockplay\basicessentials\commands
 */
class AddCoinsCommand extends Command {

    /**
     * AddCoinsCommand constructor.
     */
    public function __construct() {
        parent::__construct("addcoins", "Adds coins to player");
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
            $sender->sendMessage("§cUsage: §7/addcoins <player> <amount>");
            return;
        }

        $player = Server::getInstance()->getPlayer($args[0]);
        if($player === null) {
            $sender->sendMessage("§9Account> §cInvalid player");
            return;
        }

        if(!is_numeric($args[1])) {
            $sender->sendMessage("§9Account> §cInvalid amount");
            return;
        }

        Economy::addCoins($player, (int)$args[1]);
        $player->sendMessage("§9Account> §aYou have received {$args[1]} coins from {$sender->getName()}!");
        $sender->sendMessage("§9Account> §aYou gave {$args[1]} coins to {$sender->getName()}!");
    }
}