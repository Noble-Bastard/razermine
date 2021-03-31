<?php

namespace HubCore\commands;

use HubCore\Main;
use HubCore\Utils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class SetGroup extends Command
{
    private const AVAILABLE_GROUPS = [
        'Player',
        'VIP',
        'Premium',
        'Holy',
        'Creator',
        'Immortal',
        'YouTube',
        'Moderator',
        'Admin'
    ];

    public function __construct(
        private Main $main,
        private Utils $utilInstance,
        string $name = 'setgroup',
        string $description = 'setgroup',
        string $usageMessage = null,
        array $aliases = []
    ) {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if ($sender->isOp()) {
            if (!isset($args[1])) {
                $sender->sendMessage('§7› §fИспользование: §b/setgroup <игрок> <статус>');
                return false;
            }
            if (in_array($args[1], self::AVAILABLE_GROUPS)) {
                echo $args[0] . ' and ' . $args[1];
                $this->utilInstance->setGroup($args[0], $args[1]);
                $sender->sendMessage('§7> §fИгроку §e' . $args[0] . ' была выдана привилегия §e' . $args[1]);
                return true;
            } else {
                $sender->sendMessage('§7> §cТЫ ВВЕЛ НЕПРАВИЛЬЫНЙ ДАННЫЕ, ЧУВАК!');
            }
        } else {
            $sender->sendMessage('§7gg');
        }
        return true;
    }
}
