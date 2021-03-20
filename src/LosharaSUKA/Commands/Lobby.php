<?php

declare(strict_types=1);

namespace LosharaSUKA\Commands;

use LosharaSUKA\Main;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Lobby extends Command 
{

	public function __construct(Main $main, string $name, string $description, string $permission, array $aliases) 
	{
		$this->main = $main;
		parent::__construct($name, $description);
        $this->setAliases($aliases);
		$this->setPermission($permission);	
	}

	public function execute(CommandSender $sender, string $label, array $args): bool 
	{
		if ($sender->getLevel()->getName() == "world") {
            $sender->sendMessage("§7› §fТы уже в лобби!");
        } else {
            $server = Main::getServer();
            $server->loadLevel("Nether");
            $sender->teleport($sender->getServer()->getLevelByName("Nether")->getSpawnLocation());
            $sender->teleport(Main::getInstance()->getDefaultLevel()->getSafeSpawn());
            // $this->Main($p);
        }
	}
}

?>