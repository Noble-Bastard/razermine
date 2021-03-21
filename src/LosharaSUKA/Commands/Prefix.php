<?php

declare(strict_types=1);

namespace LosharaSUKA\Commands;

use Exception;
use http\Exception\UnexpectedValueException;
use LosharaSUKA\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class Prefix extends Command
{

    public function __construct(
        string $name,
        string $description,
        string $permission
    ) {
        parent::__construct($name, $description);
        $this->setPermission($permission);
    }

    public function execute(
        CommandSender $sender,
        string $label,
        array $args
    ): bool {
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
