<?php

declare(strict_types=1);

namespace LosharaSUKA\Commands;

use LosharaSUKA\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;

class Lobby extends Command
{

    /**
     * @var Main
     */
    private Main $main;

    public function __construct(
        Main $main,
        string $name,
        string $description,
        string $permission,
        array $aliases
    ) {
        $this->main = $main;
        parent::__construct($name, $description);
        $this->setAliases($aliases);
        $this->setPermission($permission);
    }

    public function execute(
        CommandSender $sender,
        string $label,
        array $args
    ): bool {
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
