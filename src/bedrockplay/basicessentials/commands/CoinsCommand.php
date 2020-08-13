<?php

declare(strict_types=1);

namespace bedrockplay\basicessentials\commands;

use bedrockplay\openapi\economy\Economy;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

/**
 * Class CoinsCommand
 * @package bedrockplay\basicessentials\commands
 */
class CoinsCommand extends Command {

    /**
     * CoinsCommand constructor.
     */
    public function __construct() {
        parent::__construct("coins", "Displays coins status", null, ["money", "status", "cash"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender instanceof Player) {
            $sender->sendMessage("§9Game>§7 Command only usable in-game");
            return;
        }

        Economy::getCoins($sender, function (int $amount) use ($sender) {
            $sender->sendMessage("§9Account> §7You currently have§a $amount §7coins!");
        });
    }
}