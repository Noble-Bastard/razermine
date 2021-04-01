<?php

declare(strict_types=1);

namespace HubCore;

use HubCore\commands\
{
    AddKarma,
    Groups,
    SetGroup,
    Prefix,
    Lobby,
    Hub
};
use pocketmine\plugin\PluginBase;

class Main extends PluginBase
{
    public \SQLite3 $database;

    public function onLoad(): void
    {
        $this->database = new \SQLite3($this->getDataFolder() . 'users.db');
        $this->loadCommands();
    }

    public function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->config = $this->getConfig()->getAll();

        $this->saveResource("config.yml", true);
        $this->database->query("CREATE TABLE IF NOT EXISTS `data` (`username` text, `karma` int, `group` text, `kills` int, `death` int, `lang` text)");
    }


    private function loadCommands(): void
    {
        $commands = [
            new AddKarma($this, new Utils($this), "addkarma", "Выдача кармы", "operator"),
            new SetGroup($this, new Utils($this), "setgroup", "Тебе не доступна данная команда", "operator"),
            new Groups("groups", "Тебе не доступна данная команда", "operator"),
            new Lobby("lobby", "Back To Lobby", "operator", ['quit', 'leave', 'spawn']),
            new Prefix("prefix", "loа", "operator"),
            new Hub("hub", "Back To Lobby", "operator")
        ];

        foreach ($commands as $command) {
            $this->getServer()->getCommandMap()->register($this->getName(), $command);
        }
    }
}
