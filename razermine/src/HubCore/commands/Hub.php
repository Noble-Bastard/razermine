<?php

namespace HubCore\commands;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Hub extends Command
{
    public function __construct(
        string $name = 'hub',
        string $description = 'hub',
        string $usageMessage = null,
        array $aliases = []
    ) {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if ($sender instanceof Player) {
            $sender->transfer("95.181.153.160", 19132);
        }
        return true;
    }
}
