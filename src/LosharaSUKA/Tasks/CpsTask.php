<?php

declare(strict_types=1);

namespace LosharaSUKA\Tasks;

use pocketmine\scheduler\Task;
use LosharaSUKA\Main;

class CpsTask extends Task
{

    private $plugin;
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick): void
    {

        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $name = $player->getName();
            $health2 = $player->getHealth();
            $this->plugin->particles($player);
            $health21 = round($health2);
            $os = $this->plugin->getDeviceOS($name);
            $CPS = $this->plugin->getCPS($player);
            $player->setScoreTag("§7{$os} §8| §c{$health21}❤");
            if ($this->plugin->getSettings($player, "Cps") == "on") {
                $right = str_repeat(" ", 81);
                $player->sendTip("\n\n\n\n\n\n\n\n\n\n§e§lCPS:§r §f{$CPS}{$right}");
            }
        }
    }
}
