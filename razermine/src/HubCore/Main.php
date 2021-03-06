<?php

declare(strict_types=1);

namespace HubCore;

    use HubCore\commands\{
        AddKarma,
        Groups,
        SetGroup,
        Prefix,
        Lobby,
        Hub
    };

    use pocketmine\plugin\PluginBase;

    class Main extends PluginBase {

        private static $instance;
        private static $database;

        public function onLoad(): void {
            self::setInstance($this);
            self::defineDatabase();
            self::loadCommands();
        }

        public function onEnable(): void {

            $this->saveDefaultConfig();
            $this->config = $this->getConfig()->getAll();

            $this->saveResource("config.yml", true);

            $db = self::getDatabase();
            $db->query("CREATE TABLE IF NOT EXISTS `data` (`username` text, `karma` int, `kills` int, `death` int, `lang` text)");
        }

        private static function setInstance(Main $instance): void {
            self::$instance = $instance;
        }

        public static function getInstance(): Main {
            return self::$instance;
        }

        private static function defineDatabase(): void {
            self::$database = new \SQLite3(self::getInstance()->getDataFolder(). 'users.db');
        }

        public static function getDatabase(): \SQLite3 {
            return self::$database;
        }

        private static function loadCommands(): void {
            $commands = [
                new AddKarma(self::getInstance(), "addkarma", "Тебе не доступна данная команда", "operator"),
                // new SetGroup($this, "setgroup", "Тебе не доступна данная команда", "operator"),
                // new Groups("groups", "Тебе не доступна данная команда", "operator"),
                // new Lobby($this, "lobby", "Back To Lobby", "operator", ['quit', 'leave', 'spawn']),
                // new Prefix("prefix", "loа", "operator"),
                // new Hub("hub", "Back To Lobby", "operator")
            ];
    
            foreach ($commands as $command) {
                self::getInstance()->getServer()->getCommandMap()->register(self::getInstance()->getName(), $command);
            }
        }
    }

?>