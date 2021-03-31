<?php

namespace HubCore\commands;

use HubCore\Main;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Lobby extends Command
{
    public function __construct(
        string $name = 'lobby',
        string $description = 'lobby',
        string $usageMessage = null,
        array $aliases = []
    ) {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if ($sender instanceof Player) {
            if ($sender->getLevel()->getName() == "world") {
                $sender->sendMessage("§7› §fТы уже в лобби!");
            } else {
                $server = Server::getInstance();
                $server->loadLevel("Nether");
                $sender->teleport($sender->getServer()->getLevelByName("Nether")->getSpawnLocation());
                $sender->teleport(Main::getInstance()->getDefaultLevel()->getSafeSpawn());
                // $this->Main($p);
            }
        } 
        return true;
    }
}
