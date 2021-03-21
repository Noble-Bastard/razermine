<?php

declare(strict_types=1);

namespace LosharaSUKA\Commands;

use LosharaSUKA\Main;
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
                $sender->sendMessage("§7› §fИспользование: §b/setgroup <игрок> <статус>");
                return false;
            }
            if (in_array($args[1], self::AVAILABLE_GROUPS)) {
                $player = $args[0];
                // $this->setGroup($player, $args[1]);
                $sender->sendMessage("§7> §fИгроку §e{$player} была выдана привилегия §e{$args[1]}§f!");
            } else {
                $sender->sendMessage("§7> §cТЫ ВВЕЛ НЕПРАВИЛЬЫНЙ ДАННЫЕ, ЧУВАК!");
            }
        } else {
            $sender->sendMessage("§7> §cgg");
        }
        return true;
    }
}
