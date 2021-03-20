<?php

declare(strict_types=1);

namespace LosharaSUKA\Commands;

use LosharaSUKA\Main;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Hub extends Command 
{

	public function __construct(Main $main, string $name, string $description, string $permission) 
	{
		$this->main = $main;
		parent::__construct($name, $description);
		$this->setPermission($permission);	
	}

	public function execute(CommandSender $sender, string $label, array $args): bool 
	{
		$sender->transfer("95.181.153.160", 19132);
        return true;
	}
}

?>