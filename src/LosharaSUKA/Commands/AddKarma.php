<?php

declare(strict_types=1);

namespace LosharaSUKA\Commands;

use LosharaSUKA\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class AddKarma extends Command
{

    /**
     * @var Main
     */
    private Main $main;

    public function __construct(
        Main $main,
        string $name,
        string $description,
        string $permission
    ) {
        $this->main = $main;
        parent::__construct($name, $description);
        $this->setPermission($permission);
    }

    public function execute(
        CommandSender $sender,
        string $commandLabel,
        array $args
    ): bool {
        if ($sender->isOp()) {
            if (!isset($args[1])) {
                $sender->sendMessage('§7› §fИспользование: §b/addkarma <игрок> <кол-во>');
                return false;
            }
            if (!is_numeric($args[1])) {
                $sender->sendMessage("§7› §cКоличество коинов должно быть только в цифрах!");
                return false;
            }
            //$player = $args[0];
            //$this->addKarma($player, $args[1]);
            echo ' ok! ';
        } else {
            $sender->sendMessage("§7gg");
        }
        return true;
    }
}
