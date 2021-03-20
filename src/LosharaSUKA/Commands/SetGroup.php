<?php

declare(strict_types=1);

namespace LosharaSUKA\Commands;

use LosharaSUKA\Main;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class SetGroup extends Command 
{

	public function __construct(Main $main, string $name, string $description, string $permission) 
	{
		$this->main = $main;
		parent::__construct($name, $description);
		$this->setPermission($permission);	
	}

	public function execute(CommandSender $sender, string $label, array $args): bool 
	{
        if ($sender->isOp()) { // or $this->getCountGroup($sendergetName()) >= 7
            if (!isset($args[1])) {
                $sender->sendMessage("§7› §fИспользование: §b/setgroup <игрок> <статус>");
                return true;
            }
            if ($args[1] == "Player" or $args[1] == "VIP" or $args[1] == "Premium" or $args[1] == "Holy" or $args[1] == "Creator" or $args[1] == "Immortal" or $args[1] == "YouTube" or $args[1] == "Moderator" or $args[1] == "Admin") {
                $player = $args[0];
                // $this->setGroup($player, $args[1]);
                $sender->sendMessage("§7> §fИгроку §e{$player} была выдана привилегия §e{$args[1]}§f!");
                return true;
            } else {
                $sender->sendMessage("§7> §cТЫ ВВЕЛ НЕПРАВИЛЬЫНЙ ДАННЫЕ, ЧУВАК!");
                return true;
            }
        } else {
            $sender->sendMessage("§7> §cgg");
            return true;
        }
	}
}

?>