<?php

declare(strict_types=1);

namespace LosharaSUKA\Commands;

use LosharaSUKA\Main;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Prefix extends Command 
{

	public function __construct(Main $main, string $name, string $description, string $permission) 
	{
		$this->main = $main;
		parent::__construct($name, $description);
		$this->setPermission($permission);	
	}

	public function execute(CommandSender $sender, string $label, array $args): bool 
	{
		if ($sender->isOp()) { // or $this->getCountGroup($sender->getName()) >= 4
            if (!isset($args[0])) {
                $sender->sendMessage("§7› §fИспользование: §b/prefix <prefix>");
                return true;
            }
            $prefix = $args[0];
            $sender->setNameTag("{$prefix} {$sender->getName()}");
            $sender->setDisplayName("{$prefix} " . $sender->getName());
            return true;
        } else {
            $sender->sendMessage("§7> §cgg");
            return true;
        }
	}
}

?>