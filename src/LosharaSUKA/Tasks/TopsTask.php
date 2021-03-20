<?php
declare(strict_types=1);

namespace LosharaSUKA\Tasks;

use pocketmine\scheduler\Task;
use LosharaSUKA\Main;

class TopsTask extends Task
{

    private Main $plugin;

    public function __construct(
        Main $plugin
    ) {
        $this->plugin = $plugin;
    }

    public function onRun(
        int $currentTick
    ): void {

        foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
            if ($this->plugin->getSettings($p, "Tops") === "on") {
                $this->plugin->topKills();
            }

        }
    }

}
