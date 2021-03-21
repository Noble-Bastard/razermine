<?php

declare(strict_types=1);

namespace LosharaSUKA\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class Hub extends Command
{

    public function __construct(
        string $name,
        string $description,
        string $permission
    ) {
        parent::__construct($name, $description);
        $this->setPermission($permission);
    }

    public function execute(CommandSender $sender, string $label, array $args): bool
    {
        if ($sender instanceof Player) {
            $sender->transfer("95.181.153.160", 19132);
        }
        return true;
    }
}
