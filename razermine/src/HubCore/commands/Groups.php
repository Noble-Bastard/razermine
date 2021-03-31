<?php

namespace HubCore\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Groups extends Command
{
    public function __construct(
        string $name = 'groups',
        string $description = 'groups',
        string $usageMessage = null,
        array $aliases = []
    ) {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if ($sender->isOp()) {
            $sender->sendMessage("Список привилегии: Player, VIP, Premium, Holy, Creator, Immortal, YouTube, Moderator, Admin");
            $sender->sendMessage("ВЫДАВАТЬ С ТАКИМ ЖЕ РЕГИСТРОМ КАК И Я НАПИСАЛ ВЫШЕ!!!");
            $sender->sendMessage("ВЫДАЕШЬ ЧЕРЕЗ ВОТ ТАКУЮ КОМАНДУ: /setgroup (NICKNAME) (GROUP)");
            $sender->sendMessage("Например: /setgroup noblessediamand VIP");
        } else {
            $sender->sendMessage("§7> §cgg");
        }
        return true;
    }
}
