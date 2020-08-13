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
            $sender->sendMessage("§9Usage: §7/addcoins <player> <amount>");
            return;
        }

        $player = Server::getInstance()->getPlayer($args[0]);
        if($player === null) {
            $sender->sendMessage("§9Account> §7Invalid player given");
            return;
        }

        if(!is_numeric($args[1])) {
            $sender->sendMessage("§9Account> §cInvalid amount given");
            return;
        }

        Economy::addCoins($player, (int)$args[1]);
        $player->sendMessage("§9Account> §7You have received §a{$args[1]} §7coins from §a{$sender->getName()}!");
        $sender->sendMessage("§9Account> §7You gave §a{$args[1]} §7coins to §a{$sender->getName()}!");
    }
}