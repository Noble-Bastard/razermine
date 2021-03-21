<?php

declare(strict_types=1);

namespace LosharaSUKA\Commands;

use LosharaSUKA\Main;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Groups extends Command 
{

	public function __construct(Main $main, string $name, string $description, string $permission) 
	{
		$this->main = $main;
		parent::__construct($name, $description);
		$this->setPermission($permission);	
	}

	public function execute(CommandSender $sender, string $label, array $args): bool 
	{
        if ($sender->isOp()) {
            $sender->sendMessage("Список привилегии: Player, VIP, Premium, Holy, Creator, Immortal, YouTube, Moderator, Admin");
            $sender->sendMessage("ВЫДАВАТЬ С ТАКИМ ЖЕ РЕГИСТРОМ КАК И Я НАПИСАЛ ВЫШЕ!!!");
            $sender->sendMessage("ВЫДАЕШЬ ЧЕРЕЗ ВОТ ТАКУЮ КОМАНДУ: /setgroup (NICKNAME) (GROUP)");
            $sender->sendMessage("Например: /setgroup noblessediamand VIP");
            return true;
        } else {
            $sender->sendMessage("§7> §cgg");
            return true;
        }
	}
}

?>