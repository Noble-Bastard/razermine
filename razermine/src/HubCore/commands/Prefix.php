<?php

namespace HubCore\commands;

use pocketmine\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Prefix extends Command
{
    public function __construct(
        string $name = 'prefix',
        string $description = 'prefix',
        string $usageMessage = null,
        array $aliases = []
    ) {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if ($sender instanceof Player) {
            if ($sender->isOp()) { // or $this->getCountGroup($sender->getName()) >= 4
                if (!isset($args[0])) {
                    $sender->sendMessage("§7› §fИспользование: §b/prefix <prefix>");
                    return false;
                }
                $sender->setNameTag("{$args[0]} {$sender->getName()}");
                $sender->setDisplayName("{$args[0]} " . $sender->getName());
            } else {
                $sender->sendMessage("§7> §cgg");
            }
        } else {
            echo 'Ауууу пиши в игре, префикс только себе можешь выдать';
        }
        return true;
    }
}
