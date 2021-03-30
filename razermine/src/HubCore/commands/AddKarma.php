<?php

namespace HubCore\commands;

use HubCore\Main;
use HubCore\Utils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class AddKarma extends Command
{
    public function __construct(
        private Main $main,
        private Utils $utilInstance,
        string $name = 'addkarma',
        string $description = 'addkarma',
        string $usageMessage = null,
        array $aliases = []
    ) {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if ($sender->isOp()) {
            if (!isset($args[1])) {
                $sender->sendMessage('§7› §fИспользование: §b/addkarma <игрок> <кол-во>');
                return false;
            }
            if (!is_numeric($args[1])) {
                $sender->sendMessage('§7› §cКоличество коинов должно быть только в цифрах!');
                return false;
            }
            $this->utilInstance->setKarma($args[0], $args[1]);
            $sender->sendMessage('§7› §aВы выдали игроку ' . $args[0] . ' §b' . $args[1] . ' §aкармы.');
        } else {
            $sender->sendMessage('§7gg');
        }
        return true;
    }
}
