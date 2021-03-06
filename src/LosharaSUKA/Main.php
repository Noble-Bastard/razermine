<?php

namespace LosharaSUKA;

use LosharaSUKA\Events\EventListener;
use LosharaSUKA\Getters\Getters;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\{
    RemoveObjectivePacket,
    SetDisplayObjectivePacket,
    SetScorePacket,
    types\ScorePacketEntry
};
use pocketmine\level\particle\{GenericParticle, FloatingTextParticle};
use pocketmine\level\particle\Particle;
use LosharaSUKA\Tasks\{
    ScoreBoard,
    CpsTask,
    TopsTask
};
use LosharaSUKA\Commands\{
    AddKarma,
    Groups,
    SetGroup,
    Prefix,
    Lobby,
    Hub
};

use function round;

class Main extends PluginBase implements Listener
{
    private $scoreboards = [];
    public \SQLite3 $db;
    /**
     * @var FloatingTextParticle
     */
    private FloatingTextParticle $topkills;

    public function onLoad(): void
    {
        self::$instance = $this;

        $commands = [
            new AddKarma($this, "addkarma", "Тебе не доступна данная команда", "operator"),
            new SetGroup($this, "setgroup", "Тебе не доступна данная команда", "operator"),
            new Groups("groups", "Тебе не доступна данная команда", "operator"),
            new Lobby($this, "lobby", "Back To Lobby", "operator", ['quit', 'leave', 'spawn']),
            new Prefix("prefix", "loа", "operator"),
            new Hub("hub", "Back To Lobby", "operator")
        ];

        foreach ($commands as $command) {
            $this->getServer()->getCommandMap()->register($this->getName(), $command);
        }
    }

    public $cfg; public $online;

    public $gaming = array();
    public static $instance;

    /** @var array[] */
    private $clicksData = [];

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->load();
        $this->getLogger()->info("§aВСЕ ОКЕЙ, БОСС!");
        if (!is_dir($this->getDataFolder())) {
            @mkdir($this->getDataFolder());
        }
        $this->cfg = new Config($this->getDataFolder() . "players.yml");
        $this->saveDefaultConfig();
        $this->countLeftClickBlock = $this->getConfig()->get('count-left-click-on-block');
        $this->online = 0;
        $this->getScheduler()->scheduleRepeatingTask(new ScoreBoard($this), 100);
        $this->getScheduler()->scheduleRepeatingTask(new CpsTask($this), 2);
        $this->getScheduler()->scheduleRepeatingTask(new TopsTask($this), 600);
        //$this->getScheduler()->scheduleRepeatingTask(new TagTask($this), 2);
    }

    public function load()
    {
        foreach ($this->getServer()->getLevels() as $l) {
            $l->setTime(3500);
            $l->stopTime();
        }
        $this->topkills = new FloatingTextParticle(new Vector3(-34, 40, -29), "", "");
            $this->db = new \SQLite3($this->getDataFolder() . "stats.db");
            $this->db->query("CREATE TABLE IF NOT EXISTS stats(
                                                         name TEXT NOT NULL,
                                                         death INTEGER NOT NULL,
                                                         kills INTEGER NOT NULL,
                                                         Groups TEXT NOT NULL,
                                                         Karma INTEGER NOT NULL,
                                                         Cps TEXT NOT NULL,
                                                         Board TEXT NOT NULL,
                                                         Static TEXT NOT NULL,
                                                         Lang TEXT NOT NULL,
                                                         BoxD INTEGER NOT NULL,
                                                         BoxC INTEGER NOT NULL,
                                                         BoxB INTEGER NOT NULL,
                                                         BoxA INTEGER NOT NULL,
                                                         BoxS INTEGER NOT NULL,
                                                         Tops TEXT NOT NULL,
                                                         Flames TEXT NOT NULL,
                                                         HappyVillager TEXT NOT NULL,
                                                         LavaDrip TEXT NOT NULL,
                                                         Hearts TEXT NOT NULL,
                                                         Dus2 TEXT NOT NULL,
                                                         Dus23 TEXT NOT NULL,
                                                         Dus4 TEXT NOT NULL,
                                                         Particle TEXT NOT NULL);");
            echo "DataBase Load!! \n";
    }

    public static function getDataPath(): string
    {
        return self::getInstance()->getDataFolder();
    }

    //lang
    public function SelectLang(Player $player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    $this->setSettings($sender, "Lang", "Russ");
                    $sender->sendMessage("§fТы выбрал русский язык!");
                    $this->Main($sender);
                    break;

                case 1:
                    $this->setSettings($sender, "Lang", "Eng");
                    $sender->sendMessage("§fYou have chosen English!");
                    $this->Main($sender);
                    break;

                case 2:
                    $this->setSettings($sender, "Lang", "DW");
                    $sender->sendMessage("§fSie haben Deutsch gewählt!");
                    $this->Main($sender);
                    break;
            }
        });
        $form->setTitle("§l§rChoose language");
        $form->addButton("§lРусский", 0);
        $form->addButton("§lEnglish", 1);
        $form->addButton("§lDeutsche", 2);
        $form->sendToPlayer($player);
    }

    public function SelectParticles(Player $player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    $this->Storage($sender);
                    break;
                case 1:
                    $this->enableParticle($sender, "Flames");
                    break;
                case 2:
                    $this->enableParticle($sender, "HappyVillager");
                    break;
                case 3:
                    $this->enableParticle($sender, "LavaDrip");
                    break;
                case 4:
                    $this->enableParticle($sender, "Hearts");
                    break;
                case 5:
                    $this->enableParticle($sender, "Dus2");
                    break;
                case 6:
                    $this->enableParticle($sender, "Dus23");
                    break;
                case 7:
                    $this->enableParticle($sender, "Dus4");
                    break;
            }
        });
        $form->setTitle("§l§rChoose language");
        $form->addButton("§lBack", 0, "textures/blocks/barrier");
        if ($this->getParticleAvailability($player, "Flames") == "Available") {
            $form->addButton("§lFlames", 1);
        }
        if ($this->getParticleAvailability($player, "HappyVillager") == "Available") {
            $form->addButton("§lHappyVillager", 2);
        }
        if ($this->getParticleAvailability($player, "LavaDrip") == "Available") {
            $form->addButton("§lLavaDrip", 3);
        }
        if ($this->getParticleAvailability($player, "Hearts") == "Available") {
            $form->addButton("§lHearts", 4);
        }
        if ($this->getParticleAvailability($player, "Dus2") == "Available") {
            $form->addButton("§lEXCLUSIVE 1", 5);
        }
        if ($this->getParticleAvailability($player, "Dus23") == "Available") {
            $form->addButton("§lEXCLUSIVE 2", 6);
        }
        if ($this->getParticleAvailability($player, "Dus4") == "Available") {
            $form->addButton("§lEXCLUSIVE 3", 7);
        }
        $form->sendToPlayer($player);
    }

    public function SelectPvPSettings(Player $p)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createCustomForm(function (Player $sender, $data) {
            $result = $data;
            if ($result == null) {
                return true;
            }
            if ($data[1] == false) {
                if ($this->getSettings($sender, "Cps") == "on") {
                    $this->setSettings($sender, "Cps", "off");
                }
            } else {
                $this->setSettings($sender, "Cps", "on");
            }
            if ($data[2] == false) {
                if ($this->getSettings($sender, "Board") == "on") {
                    $this->setSettings($sender, "Board", "off");
                }
            } else {
                $this->setSettings($sender, "Board", "on");
            }
            if ($data[3] == false) {
                if ($this->getSettings($sender, "Static") == "on") {
                    $this->setSettings($sender, "Static", "off");
                }
            } else {
                $this->setSettings($sender, "Static", "on");
            }
            if ($data[4] == false) {
                if ($this->getSettings($sender, "Tops") == "on") {
                    $this->setSettings($sender, "Tops", "off");
                }
            } else {
                $this->setSettings($sender, "Tops", "on");
            }
        });
        $form->setTitle("§l§bPvP-Setiins§r");
        $form->addLabel("§7Select");
        if ($this->getSettings($p, "Cps") == "on") {
            $form->addToggle("§7СPS", true);
        } else {
            $form->addToggle("§7СPS", false);
        }
        if ($this->getSettings($p, "Board") == "on") {
            $form->addToggle("§7ScоreBоard", true);
        } else {
            $form->addToggle("§7ScоreBоard", false);
        }
        if ($this->getSettings($p, "Static") == "on") {
            $form->addToggle("§7Static", true);
        } else {
            $form->addToggle("§7Static", false);
        }
        if ($this->getSettings($p, "Tops") == "on") {
            $form->addToggle("§7Tops", true);
        } else {
            $form->addToggle("§7Tops", false);
        }
        $form->sendToPlayer($p);
    }

    public function SelectStats($player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    $this->SelectMain($sender);
                    break;
            }
        });
        $form->setTitle("§l§rStatistics");
        $kills = $this->getKills($player);
        $death = $this->getDeath($player);
        if ($death == 0 or $kills == 0) {
            $vin = 0 . "%";
        } else {
            $vin = round($kills / $death * 100) . "%";
        }
        $name = $player->getName();
        if ($this->getSettings($player, "Lang") == "Russ") {
            $form->setContent("\n§r§7Ник: §b{$name}\n§r§7Привилегия: §e\n\n§r§7Смертей: §e{$death}\n§r§7Убийств: §e{$kills}\n§r§7Винрейт: §r§b{$vin}% \n\n");
        } elseif ($this->getSettings($player, "Lang") == "Eng") {
            $form->setContent("\n§r§7Nаme: §b{$name}\n§r§7Grоup: §e\n\n§r§7Dеath: §e{$death}\n§r§7Кills: §e{$kills}\n§r§7Vin: §r§b{$vin}% \n\n");
        } elseif ($this->getSettings($player, "Lang") == "DW") {
            $form->setContent("\n§r§7Nоmen: §b{$name}\n§r§7Gruрpe: §e\n\n§r§7Stеrben: §e{$death}\n§r§7Mоrde: §e{$kills}\n§r§7Vin: §r§b{$vin}% \n\n");
        }

        $form->addButton("§lBack", 0, "textures/blocks/barrier");
        $form->sendToPlayer($player);
    }

    public function SelectMain($player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    $this->SelectLang($sender);
                    break;
                case 1:
                    $this->SelectPvPSettings($sender);
                    break;
                case 2:
                    $this->SelectStats($sender);
                    break;
                case 3:
                    break;
            }
        });
        $form->setTitle("§l§4-§c- §fSETTINGS §c-§4-");
        if ($this->getSettings($player, "Lang") == "Eng") {
            $form->addButton("§8Lang-Settings", 0, "textures/gui/newgui/Language16");
            $form->addButton("§8PvP-Settings", 0, "textures/blocks/chain_command_block_conditional_mipmap");
            $form->addButton("§8Statistics", 0, "textures/items/map_filled");
            $form->addButton("§8Marks", 0, "textures/map/map_background");
        } elseif ($this->getSettings($player, "Lang") == "Russ") {
            $form->addButton("§8Выбор языка", 0, "textures/gui/newgui/Language16");
            $form->addButton("§8Мастер-Настройки", 0, "textures/blocks/chain_command_block_conditional_mipmap");
            $form->addButton("§8Статистика", 0, "textures/items/map_filled");
            $form->addButton("§8Бейджики", 0, "textures/map/map_background");
        } elseif ($this->getSettings($player, "Lang") == "DW") {
            $form->addButton("§8Spracheinstellungen", 0, "textures/gui/newgui/Language16");
            $form->addButton("§8PvP-Einstellungen", 0, "textures/blocks/chain_command_block_conditional_mipmap");
            $form->addButton("§8Statistiken", 0, "textures/items/map_filled");
            $form->addButton("§8Markierungen", 0, "textures/map/map_background");
        }
        $form->sendToPlayer($player);
    }

    public function BoxD($player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    if ($this->getkarma($sender) >= 2000) {
                        $this->addBox($sender, 1, "BoxD");
                        $this->remKarma($sender, 2000);
                    } else {
                        $sender->sendMessage("§7You don't have 2000 Karma.");
                    }
                    break;
                case 1:
                    $this->BoxMenu($sender);
                    break;
            }
        });
        $form->setTitle("§l§9-§b- §fD RANK - §e2000 §fKarma §b-§9-");
        if ($this->getSettings($player, "Lang") == "Eng") {
            $form->setContent("§FChance to drop a privilege - §e10% \n§fChance to drop 1000 karma - §e10% \n§fChance to drop a particle - §e10% \n§fChance to drop a nothing - §e30% \n§fChance to drop 200 karma - §e30% \n\n");
            $form->addButton("§aBuy", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cBack", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "Russ") {
            $form->setContent("§fШанс выпадения привилегии - §e10% \n§fШанс выпадения 1000 кармы - §e10% \n§fШанс выпадения партикла - §e10% \n§fШанс выпадения ничего - §e30% \n§fШанс выпадения 200 кармы - §e30% \n\n");
            $form->addButton("§aКупить", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cНазад", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "DW") {
            $form->setContent("§Fähigkeit, ein Privileg fallen zu lassen - §e10% \n§fWahrscheinlichkeit, 1000 Karma fallen zu lassen - §e10% \n§fWahrscheinlichkeit, ein Teilchen fallen zu lassen - §e10% \n§fWahrscheinlichkeit, einen NOTHING fallen zu lassen - §e30% \n§fWahrscheinlichkeit, 200 Karma fallen zu lassen - §e30% \n\n");
            $form->addButton("§aKaufen", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cZurück", 0, "textures/blocks/barrier");
        }
        $form->sendToPlayer($player);
    }

    public function BoxC($player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    if ($this->getkarma($sender) >= 4000) {
                        $this->addBox($sender, 1, "BoxC");
                        $this->remKarma($sender, 4000);
                    } else {
                        $sender->sendMessage("§7You don't have 4000 Karma.");
                    }
                    break;
                case 1:
                    $this->BoxMenu($sender);
                    break;
            }
        });
        $form->setTitle("§l§9-§b- §fC RANK - §e4000 §fKarma §b-§9-");
        if ($this->getSettings($player, "Lang") == "Eng") {
            $form->setContent("§FChance to drop a privilege(VIP) - §e10% \n§fChance to drop 2000 karma - §e20% \n§fChance to drop a particle - §e10% \n§fChance to drop a nothing - §e20% \n§fChance to drop 500 karma - §e30% \n\n");
            $form->addButton("§aBuy", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cBack", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "Russ") {
            $form->setContent("§fШанс выпадения привилегии(VIP) - §e10% \n§fШанс выпадения 2000 кармы - §e20% \n§fШанс выпадения партикла - §e10% \n§fШанс выпадения ничего - §e20% \n§fШанс выпадения 500 кармы - §e30% \n\n");
            $form->addButton("§aКупить", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cНазад", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "DW") {
            $form->setContent("§Fähigkeit, ein Privileg fallen zu lassen(VIP) - §e10% \n§fWahrscheinlichkeit, 2000 Karma fallen zu lassen - §e20% \n§fWahrscheinlichkeit, ein Teilchen fallen zu lassen - §e10% \n§fWahrscheinlichkeit, einen NULL fallen zu lassen - §e20% \n§fWahrscheinlichkeit, 500 Karma fallen zu lassen - §e30% \n\n");
            $form->addButton("§aKaufen", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cZurück", 0, "textures/blocks/barrier");
        }
        $form->sendToPlayer($player);
    }

    public function BoxB($player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    if ($this->getkarma($sender) >= 8000) {
                        $this->addBox($sender, 1, "BoxB");
                        $this->remKarma($sender, 8000);
                    } else {
                        $sender->sendMessage("§7You don't have 8000 Karma.");
                    }
                    break;
                case 1:
                    $this->BoxMenu($sender);
                    break;
            }
        });
        $form->setTitle("§l§9-§b- §fB RANK - §e8000 §fKarma §b-§9-");
        if ($this->getSettings($player, "Lang") == "Eng") {
            $form->setContent("§FChance to drop a privilege(Premium) - §e10% \n§fChance to drop 6000 karma - §e20% \n§fChance to drop a particle - §e10% \n§fChance to drop a nothing - §e30% \n§fChance to drop 1000 karma - §e20% \n\n");
            $form->addButton("§aBuy", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cBack", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "Russ") {
            $form->setContent("§fШанс выпадения привилегии(Premium) - §e10% \n§fШанс выпадения 6000 кармы - §e20% \n§fШанс выпадения партикла - §e10% \n§fШанс выпадения ничего - §e30% \n§fШанс выпадения 1000 кармы - §e20% \n\n");
            $form->addButton("§aКупить", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cНазад", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "DW") {
            $form->setContent("§Fähigkeit, ein Privileg fallen zu lassen(Premium) - §e10% \n§fWahrscheinlichkeit, 6000 Karma fallen zu lassen - §e10% \n§fWahrscheinlichkeit, ein Teilchen fallen zu lassen - §e10% \n§fWahrscheinlichkeit, einen Nothing fallen zu lassen - §e30% \n§fWahrscheinlichkeit, 1000 Karma fallen zu lassen - §e20% \n\n");
            $form->addButton("§aKaufen", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cZurück", 0, "textures/blocks/barrier");
        }
        $form->sendToPlayer($player);
    }

    public function BoxA($player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    if ($this->getkarma($sender) >= 16000) {
                        $this->addBox($sender, 1, "BoxA");
                        $this->remKarma($sender, 16000);
                    } else {
                        $sender->sendMessage("§7You don't have 16000 Karma.");
                    }
                    break;
                case 1:
                    $this->BoxMenu($sender);
                    break;
            }
        });
        $form->setTitle("§l§9-§b- §fA RANK - §e16000 §fKarma §b-§9-");
        if ($this->getSettings($player, "Lang") == "Eng") {
            $form->setContent("§FChance to drop a privilege(Premium) - §e10% \n§fChance to drop 10000 karma - §e20% \n§fChance to drop a particle(EXCLUSIVE 2) - §e10% \n§fChance to drop a nothing - §e30% \n§fChance to drop 2000 karma - §e20% \n\n");
            $form->addButton("§aBuy", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cBack", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "Russ") {
            $form->setContent("§fШанс выпадения привилегии(Premium) - §e10% \n§fШанс выпадения 10000 кармы - §e20% \n§fШанс выпадения партикла(EXCLUSIVE 2) - §e10% \n§fШанс выпадения ничего - §e30% \n§fШанс выпадения 2000 кармы - §e20% \n\n");
            $form->addButton("§aКупить", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cНазад", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "DW") {
            $form->setContent("§Fähigkeit, ein Privileg fallen zu lassen(Premium) - §e10% \n§fWahrscheinlichkeit, 10000 Karma fallen zu lassen - §e20% \n§fWahrscheinlichkeit, ein Teilchen fallen zu lassen(EXCLUSIVE 2) - §e10% \n§fWahrscheinlichkeit, einen NOTHING fallen zu lassen - §e30% \n§fWahrscheinlichkeit, 2000 Karma fallen zu lassen - §e20% \n\n");
            $form->addButton("§aKaufen", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cZurück", 0, "textures/blocks/barrier");
        }
        $form->sendToPlayer($player);
    }

    public function BoxS($player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    if ($this->getkarma($sender) >= 20000) {
                        $this->addBox($sender, 1, "BoxS");
                        $this->remKarma($sender, 20000);
                    } else {
                        $sender->sendMessage("§7You don't have 20000 Karma.");
                    }
                    break;
                case 1:
                    $this->BoxMenu($sender);
                    break;
            }
        });
        $form->setTitle("§l§9-§b- §cS §fRANK - §e20000 §fKarma §b-§9-");
        if ($this->getSettings($player, "Lang") == "Eng") {
            $form->setContent("§FChance to drop a privilege(Holy) - §e20% \n§fChance to drop 10000 karma - §e20% \n§fChance to drop a particle(EXCLUSIVE 3) - §e20% \n§fChance to drop a ничего - §e20% \n§fChance to drop 5000 karma - §e20% \n\n");
            $form->addButton("§aBuy", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cBack", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "Russ") {
            $form->setContent("§fШанс выпадения привилегии(Holy) - §e20% \n§fШанс выпадения 10000 кармы - §e20% \n§fШанс выпадения партикла(EXCLUSIVE 3) - §e20% \n§fШанс выпадения ничего - §e20% \n§fШанс выпадения 5000 кармы - §e20% \n\n");
            $form->addButton("§aКупить", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cНазад", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "DW") {
            $form->setContent("§Fähigkeit, ein Privileg fallen zu lassen(Holy) - §e20% \n§fWahrscheinlichkeit, 10000 Karma fallen zu lassen - §e20% \n§fWahrscheinlichkeit, ein Teilchen fallen zu lassen(EXCLUSIVE 3) - §e20% \n§fWahrscheinlichkeit, einen NOTHING fallen zu lassen - §e20% \n§fWahrscheinlichkeit, 5000 Karma fallen zu lassen - §e20% \n\n");
            $form->addButton("§aKaufen", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cZurück", 0, "textures/blocks/barrier");
        }
        $form->sendToPlayer($player);
    }

    public function BoxMenu($player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    $this->BoxD($sender);
                    break;

                case 1:
                    $this->BoxC($sender);
                    break;

                case 2:
                    $this->BoxB($sender);
                    break;

                case 3:
                    $this->BoxA($sender);
                    break;

                case 4:
                    $this->BoxS($sender);
                    break;
            }
        });
        $form->setTitle("§l§9-§b- §fBOXES §b-§9-");
        if ($this->getSettings($player, "Lang") == "Eng") {
            $form->setContent("§fYour Karma Points: §e{$this->getKarma($player)}\n");
            $form->addButton("§8Rank §gD §8Box", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§8Rank §dC §8Box", 0, "textures/blocks/smithing_table_side");
            $form->addButton("§8Rank §bB §8Box", 0, "textures/blocks/ender_chest_front");
            $form->addButton("§8Rank §aA §8Box", 0, "textures/blocks/end_portal");
            $form->addButton("§8Rank §cS §8Box", 0, "textures/blocks/structure_block_data");
        } elseif ($this->getSettings($player, "Lang") == "Russ") {
            $form->setContent("§fТвои очки кармы: §e{$this->getKarma($player)}\n");
            $form->addButton("§8Кейс §gD §8Ранга", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§8Кейс §dC §8Ранга", 0, "textures/blocks/smithing_table_side");
            $form->addButton("§8Кейс §bB §8Ранга", 0, "textures/blocks/ender_chest_front");
            $form->addButton("§8Кейс §aA §8Ранга", 0, "textures/blocks/end_portal");
            $form->addButton("§8Кейс §cS §8Ранга", 0, "textures/blocks/structure_block_data");
        } elseif ($this->getSettings($player, "Lang") == "DW") {
            $form->setContent("§fIhre Karma-Punkte: §e{$this->getKarma($player)}\n");
            $form->addButton("§8Rank §gD §8Box", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§8Rank §dC §8Box", 0, "textures/blocks/smithing_table_side");
            $form->addButton("§8Rank §bB §8Box", 0, "textures/blocks/ender_chest_front");
            $form->addButton("§8Rank §aA §8Box", 0, "textures/blocks/end_portal");
            $form->addButton("§8Rank §cS §8Box", 0, "textures/blocks/structure_block_data");
        }
        $form->sendToPlayer($player);
    }

    public function RandomD(Player $player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    if ($this->getBox($sender, "BoxD") >= 1) {
                        $rand = mt_rand(1, 9);
                        if ($rand == 1) {
                            $name = $sender->getName();
                            $this->setGroup($name, "VIP");
                            $sender->sendMessage("You won VIP status!!!!");
                        } elseif ($rand == 2) {
                            $this->addKarma($sender, 1000);
                            $sender->sendMessage("You win a 1000 karma!");
                        } elseif ($rand == 3) {
                            $this->setParticleAvailability($sender, "Dus2", "Available");
                            $sender->sendMessage("You win a EXCLUSIVE Particle!");
                        } elseif ($rand == 4 or $rand == 5 or $rand == 6) {
                            $this->addKarma($sender, 200);
                            $sender->sendMessage("You win 200 karma!");
                        } elseif ($rand == 7 or $rand == 8 or $rand == 9) {
                            $sender->sendMessage("You won nothing :(");
                        }
                        $this->remBox($sender, 1, "BoxD");
                    } else {
                        if ($this->getSettings($sender, "Lang") == "Eng") {
                            $sender->sendMessage("You don't have a boxes");
                        }
                        if ($this->getSettings($sender, "Lang") == "Russ") {
                            $sender->sendMessage("Ты не имеешь кейсов");
                        }
                        if ($this->getSettings($sender, "Lang") == "DW") {
                            $sender->sendMessage("Du hast keinen Box");
                        }
                    }
                    break;
                case 1:
                    $this->StoreBox($sender);
                    break;
            }
        });
        $form->setTitle("§l§9-§b- §gD §fRANK §fBOX§b-§9-");
        if ($this->getSettings($player, "Lang") == "Eng") {
            $form->setContent("§8You have §e{$this->getBox($player, "BoxD")} §8Boxes\n\n");
            $form->addButton("§aOpen", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cBack", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "Russ") {
            $form->setContent("§8У тебя §e{$this->getBox($player, "BoxD")} §8Кейсов\n\n");
            $form->addButton("§aОткрыть", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cНазад", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "DW") {
            $form->setContent("§8Sie haben §e{$this->getBox($player, "BoxD")} §8Boxen\n\n");
            $form->addButton("§aÖffnen", 0, "textures/blocks/smoker_front_off");
            $form->addButton("§cZurück", 0, "textures/blocks/barrier");
        }
        $form->sendToPlayer($player);
    }

    public function RandomC(Player $player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    if ($this->getBox($sender, "BoxC") >= 1) {
                        $rand = mt_rand(1, 9);
                        if ($rand == 1) {
                            $name = $sender->getName();
                            $this->setGroup($name, "VIP");
                            $sender->sendMessage("You won VIP status!!!!");
                        } elseif ($rand == 2 or $rand == 9) {
                            $this->addKarma($sender, 2000);
                            $sender->sendMessage("You win a 2000 karma!");
                        } elseif ($rand == 3) {
                            $this->setParticleAvailability($sender, "Dus2", "Available");
                            $sender->sendMessage("You win a EXCLUSIVE Particle!");
                        } elseif ($rand == 4 or $rand == 5 or $rand == 6) {
                            $this->addKarma($sender, 500);
                            $sender->sendMessage("You win 500 karma!");
                        } elseif ($rand == 7 or $rand == 8) {
                            $sender->sendMessage("You won nothing :(");
                        }
                        $this->remBox($sender, 1, "BoxC");
                    } else {
                        if ($this->getSettings($sender, "Lang") == "Eng") {
                            $sender->sendMessage("You don't have a boxes");
                        }
                        if ($this->getSettings($sender, "Lang") == "Russ") {
                            $sender->sendMessage("Ты не имеешь кейсов");
                        }
                        if ($this->getSettings($sender, "Lang") == "DW") {
                            $sender->sendMessage("Du hast keinen Box");
                        }
                    }
                    break;
                case 1:
                    $this->StoreBox($sender);
                    break;
            }
        });
        $form->setTitle("§l§9-§b- §gC §fRANK §fBOX§b-§9-");
        if ($this->getSettings($player, "Lang") == "Eng") {
            $form->setContent("§8You have §e{$this->getBox($player, "BoxC")} §8Boxes\n\n");
            $form->addButton("§aOpen", 0, "textures/blocks/smithing_table_side");
            $form->addButton("§cBack", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "Russ") {
            $form->setContent("§8У тебя §e{$this->getBox($player, "BoxC")} §8Кейсов\n\n");
            $form->addButton("§aОткрыть", 0, "textures/blocks/smithing_table_side");
            $form->addButton("§cНазад", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "DW") {
            $form->setContent("§8Sie haben §e{$this->getBox($player, "BoxC")} §8Boxen\n\n");
            $form->addButton("§aÖffnen", 0, "textures/blocks/smithing_table_side");
            $form->addButton("§cZurück", 0, "textures/blocks/barrier");
        }
        $form->sendToPlayer($player);
    }

    public function RandomB(Player $player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    if ($this->getBox($sender, "BoxB") >= 1) {
                        $rand = mt_rand(1, 9);
                        if ($rand == 1) {
                            $name = $sender->getName();
                            $this->setGroup($name, "Premium");
                            $sender->sendMessage("You won Premium status!!!!");
                        } elseif ($rand == 2 or $rand == 9) {
                            $this->addKarma($sender, 6000);
                            $sender->sendMessage("You win a 6000 karma!");
                        } elseif ($rand == 3) {
                            $this->setParticleAvailability($sender, "Dus2", "Available");
                            $sender->sendMessage("You win a EXCLUSIVE Particle!");
                        } elseif ($rand == 4 or $rand == 5 or $rand == 6) {
                            $this->addKarma($sender, 1000);
                            $sender->sendMessage("You win 1000 karma!");
                        } elseif ($rand == 7 or $rand == 8) {
                            $sender->sendMessage("You won nothing :(");
                        }
                        $this->remBox($sender, 1, "BoxB");
                    } else {
                        if ($this->getSettings($sender, "Lang") == "Eng") {
                            $sender->sendMessage("You don't have a boxes");
                        }
                        if ($this->getSettings($sender, "Lang") == "Russ") {
                            $sender->sendMessage("Ты не имеешь кейсов");
                        }
                        if ($this->getSettings($sender, "Lang") == "DW") {
                            $sender->sendMessage("Du hast keinen Box");
                        }
                    }
                    break;
                case 1:
                    $this->StoreBox($sender);
                    break;
            }
        });
        $form->setTitle("§l§9-§b- §gB §fRANK §fBOX§b-§9-");
        if ($this->getSettings($player, "Lang") == "Eng") {
            $form->setContent("§8You have §e{$this->getBox($player, "BoxB")} §8Boxes\n\n");
            $form->addButton("§aOpen", 0, "textures/blocks/ender_chest_front");
            $form->addButton("§cBack", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "Russ") {
            $form->setContent("§8У тебя §e{$this->getBox($player, "BoxB")} §8Кейсов\n\n");
            $form->addButton("§aОткрыть", 0, "textures/blocks/ender_chest_front");
            $form->addButton("§cНазад", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "DW") {
            $form->setContent("§8Sie haben §e{$this->getBox($player, "BoxB")} §8Boxen\n\n");
            $form->addButton("§aÖffnen", 0, "textures/blocks/ender_chest_front");
            $form->addButton("§cZurück", 0, "textures/blocks/barrier");
        }
        $form->sendToPlayer($player);
    }

    public function RandomA(Player $player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    if ($this->getBox($sender, "BoxA") >= 1) {
                        $rand = mt_rand(1, 9);
                        if ($rand == 1) {
                            $name = $sender->getName();
                            $this->setGroup($name, "Premium");
                            $sender->sendMessage("You won Premium status!!!!");
                        } elseif ($rand == 2 or $rand == 9) {
                            $this->addKarma($sender, 10000);
                            $sender->sendMessage("You win a 10000 karma!");
                        } elseif ($rand == 3) {
                            $this->setParticleAvailability($sender, "Dus23", "Available");
                            $sender->sendMessage("You win a EXCLUSIVE2 Particle!");
                        } elseif ($rand == 4 or $rand == 5) {
                            $this->addKarma($sender, 2000);
                            $sender->sendMessage("You win 2000 karma!");
                        } elseif ($rand == 7 or $rand == 8 or $rand == 6) {
                            $sender->sendMessage("You won nothing :(");
                        }
                        $this->remBox($sender, 1, "BoxA");
                    } else {
                        if ($this->getSettings($sender, "Lang") == "Eng") {
                            $sender->sendMessage("You don't have a boxes");
                        }
                        if ($this->getSettings($sender, "Lang") == "Russ") {
                            $sender->sendMessage("Ты не имеешь кейсов");
                        }
                        if ($this->getSettings($sender, "Lang") == "DW") {
                            $sender->sendMessage("Du hast keinen Box");
                        }
                    }
                    break;
                case 1:
                    $this->StoreBox($sender);
                    break;
            }
        });
        $form->setTitle("§l§9-§b- §gA §fRANK §fBOX§b-§9-");
        if ($this->getSettings($player, "Lang") == "Eng") {
            $form->setContent("§8You have §e{$this->getBox($player, "BoxA")} §8Boxes\n\n");
            $form->addButton("§aOpen", 0, "textures/blocks/end_portal");
            $form->addButton("§cBack", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "Russ") {
            $form->setContent("§8У тебя §e{$this->getBox($player, "BoxA")} §8Кейсов\n\n");
            $form->addButton("§aОткрыть", 0, "textures/blocks/end_portal");
            $form->addButton("§cНазад", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "DW") {
            $form->setContent("§8Sie haben §e{$this->getBox($player, "BoxA")} §8Boxen\n\n");
            $form->addButton("§aÖffnen", 0, "textures/blocks/end_portal");
            $form->addButton("§cZurück", 0, "textures/blocks/barrier");
        }
        $form->sendToPlayer($player);
    }

    public function RandomS(Player $player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    if ($this->getBox($sender, "BoxS") >= 1) {
                        $rand = mt_rand(1, 10);
                        if ($rand == 1 or $rand == 6) {
                            $name = $sender->getName();
                            $this->setGroup($name, "Holy");
                            $sender->sendMessage("You won Holy status!!!!");
                        } elseif ($rand == 2 or $rand == 9) {
                            $this->addKarma($sender, 10000);
                            $sender->sendMessage("You win a 10000 karma!");
                        } elseif ($rand == 3 or $rand == 5) {
                            $this->setParticleAvailability($sender, "Dus4", "Available");
                            $sender->sendMessage("You win a EXCLUSIVE3 Particle!");
                        } elseif ($rand == 4 or $rand == 10) {
                            $this->addKarma($sender, 5000);
                            $sender->sendMessage("You win 5000 karma!");
                        } elseif ($rand == 7 or $rand == 8) {
                            $sender->sendMessage("You won nothing :(");
                        }
                        $this->remBox($sender, 1, "BoxA");
                    } else {
                        if ($this->getSettings($sender, "Lang") == "Eng") {
                            $sender->sendMessage("You don't have a boxes");
                        }
                        if ($this->getSettings($sender, "Lang") == "Russ") {
                            $sender->sendMessage("Ты не имеешь кейсов");
                        }
                        if ($this->getSettings($sender, "Lang") == "DW") {
                            $sender->sendMessage("Du hast keinen Box");
                        }
                    }
                    break;
                case 1:
                    $this->StoreBox($sender);
                    break;
            }
        });
        $form->setTitle("§l§9-§b- §gA §fRANK §fBOX§b-§9-");
        if ($this->getSettings($player, "Lang") == "Eng") {
            $form->setContent("§8You have §e{$this->getBox($player, "BoxA")} §8Boxes\n\n");
            $form->addButton("§aOpen", 0, "textures/blocks/end_portal");
            $form->addButton("§cBack", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "Russ") {
            $form->setContent("§8У тебя §e{$this->getBox($player, "BoxA")} §8Кейсов\n\n");
            $form->addButton("§aОткрыть", 0, "textures/blocks/end_portal");
            $form->addButton("§cНазад", 0, "textures/blocks/barrier");
        } elseif ($this->getSettings($player, "Lang") == "DW") {
            $form->setContent("§8Sie haben §e{$this->getBox($player, "BoxA")} §8Boxen\n\n");
            $form->addButton("§aÖffnen", 0, "textures/blocks/end_portal");
            $form->addButton("§cZurück", 0, "textures/blocks/barrier");
        }
        $form->sendToPlayer($player);
    }

    public function StoreBox($player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    $this->RandomD($sender);
                    break;
                case 1:
                    $this->RandomC($sender);
                    break;
                case 2:
                    $this->RandomB($sender);
                    break;
                case 3:
                    $this->RandomA($sender);
                    break;
                case 4:
                    $this->RandomS($sender);
                    break;
                case 5:
                    $this->Storage($sender);
                    break;
            }
        });
        $form->setTitle("§l§6-§g- §fBoxes Storage §g-§6-");
        $form->addButton("§8Rank §gD §8Box\n§f{$this->getBox($player, "BoxD")} §8Boxes", 0, "textures/blocks/smoker_front_off");
        $form->addButton("§8Rank §dC §8Box\n§f{$this->getBox($player, "BoxC")} §8Boxes", 0, "textures/blocks/smithing_table_side");
        $form->addButton("§8Rank §bB §8Box\n§f{$this->getBox($player, "BoxB")} §8Boxes", 0, "textures/blocks/ender_chest_front");
        $form->addButton("§8Rank §aA §8Box\n§f{$this->getBox($player, "BoxA")} §8Boxes", 0, "textures/blocks/end_portal");
        $form->addButton("§8Rank §cS §8Box\n§f{$this->getBox($player, "BoxS")} §8Boxes", 0, "textures/blocks/structure_block_data");
        $form->addButton("§lBack", 0, "textures/blocks/barrier");
        $form->sendToPlayer($player);
    }

    public function Storage($player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    $this->StoreBox($sender);
                    break;
                case 1:
                    $this->SelectParticles($sender);
                    break;
                case 2:
                    $sender->sendMessage("Скоро...");
                    break;
            }
        });
        $form->setTitle("§l§e-§g- §fSTORAGE §g-§e-");
        if ($this->getSettings($player, "Lang") == "Eng") {
            $form->addButton("§8Boxes", 0, "textures/blocks/cartography_table_top");
            $form->addButton("§8Particles", 0, "textures/blocks/chorus_flower");
            $form->addButton("§8Ability", 0, "textures/environment/destroy_stage_9");
        } elseif ($this->getSettings($player, "Lang") == "Russ") {
            $form->addButton("§8Кейсы", 0, "textures/blocks/cartography_table_top");
            $form->addButton("§8Партиклы", 0, "textures/blocks/chorus_flower");
            $form->addButton("§8Способности", 0, "textures/environment/destroy_stage_9");
        } elseif ($this->getSettings($player, "Lang") == "DW") {
            $form->addButton("§8Boxes", 0, "textures/blocks/cartography_table_top");
            $form->addButton("§8Partikel", 0, "textures/blocks/chorus_flower");
            $form->addButton("§8Fähigkeit", 0, "textures/environment/destroy_stage_9");
        }
        $form->sendToPlayer($player);
    }


    //mainmenu


    public function randomFloat($min = -0.9, $max = 0.9)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    public function particles()
    {
        foreach ($this->getServer()->getOnlinePlayers() as $p) {
            $pos = new \pocketmine\math\Vector3(
                $p->getX() + $this->randomFloat(),
                $p->getY() + $this->randomFloat(0.5, 1.4),
                $p->getZ() + $this->randomFloat()
            );
            if ($this->getParticle($p) == "Flames") {
                $p->getLevel()->addParticle(new GenericParticle($pos, Particle::TYPE_FLAME));
            } elseif ($this->getParticle($p) == "HappyVillager") {
                $p->getLevel()->addParticle(new GenericParticle($pos, Particle::TYPE_VILLAGER_HAPPY));
            } elseif ($this->getParticle($p) == "Hearts") {
                $p->getLevel()->addParticle(new GenericParticle($pos, Particle::TYPE_HEART));
            } elseif ($this->getParticle($p) == "LavaDrip") {
                $p->getLevel()->addParticle(new GenericParticle($pos, Particle::TYPE_DRIP_LAVA));
            } elseif ($this->getParticle($p) == "Dus2") {
                $p->getLevel()->addParticle(new GenericParticle($pos, Particle::TYPE_DUST));
            } elseif ($this->getParticle($p) == "Dus23") {
                $p->getLevel()->addParticle(new GenericParticle($pos, Particle::TYPE_CONDUIT));
            } elseif ($this->getParticle($p) == "Dus4") {
                $p->getLevel()->addParticle(new GenericParticle($pos, Particle::TYPE_EVAPORATION));
            }
        }
    }

    public function enableParticle($p, $particle)
    {
        if ($particle == "Flames") {
            if ($this->getParticleAvailability($p, "Flames") == "No") {
                $this->setParticleAvailability($p, "Flames", "Available");
            } elseif ($this->getParticleAvailability($p, "Flames") == "Available") {
                $this->setParticle($p, "Flames");
                $p->sendMessage("§7> §aТы включил партикл §6Flames§a!");
            }
        }
        if ($particle == "HappyVillager") {
            if ($this->getParticleAvailability($p, "HappyVillager") == "No") {
                $this->setParticleAvailability($p, "HappyVillager", "Available");
            } elseif ($this->getParticleAvailability($p, "HappyVillager") == "Available") {
                $this->setParticle($p, "HappyVillager");
                $p->sendMessage("§7> §aТы включил партикл §6HappyVillager§a!");
            }
        }
        if ($particle == "LavaDrip") {
            if ($this->getParticleAvailability($p, "LavaDrip") == "No") {
                $this->setParticleAvailability($p, "LavaDrip", "Available");
            } elseif ($this->getParticleAvailability($p, "LavaDrip") == "Available") {
                $this->setParticle($p, "LavaDrip");
                $p->sendMessage("§7> §aТы включил партикл §6LavaDrip§a!");
            }
        }
        if ($particle == "Hearts") {
            if ($this->getParticleAvailability($p, "Hearts") == "No") {
                $this->setParticleAvailability($p, "Hearts", "Available");
            } elseif ($this->getParticleAvailability($p, "Hearts") == "Available") {
                $this->setParticle($p, "Hearts");
                $p->sendMessage("§7> §aТы включил партикл §6Hearts§a!");
            }
        }
        if ($particle == "Dus2") {
            if ($this->getParticleAvailability($p, "Dus2") == "No") {
                $this->setParticleAvailability($p, "Dus2", "Available");
                $p->sendMessage("§7> §aТы выбил партикл");
            } elseif ($this->getParticleAvailability($p, "Dus2") == "Available") {
                $this->setParticle($p, "Dus2");
                $p->sendMessage("§7> §aТы включил партикл §6Dus2§a!");
            }
        }
        if ($particle == "Dus23") {
            if ($this->getParticleAvailability($p, "Dus23") == "No") {
                $this->setParticleAvailability($p, "Dus23", "Available");
                $p->sendMessage("§7> §aТы выбил партикл!");
            } elseif ($this->getParticleAvailability($p, "Dus23") == "Available") {
                $this->setParticle($p, "Dus23");
                $p->sendMessage("§7> §aТы включил партикл §6Dus23§a!");
            }
        }

        if ($particle == "Dus4") {
            if ($this->getParticleAvailability($p, "Dus4") == "No") {
                $this->setParticleAvailability($p, "Dus4", "Available");
                $p->sendMessage("§7> §aТы выбил партикл СМОК");
            } elseif ($this->getParticleAvailability($p, "Dus4") == "Available") {
                $this->setParticle($p, "Dus4");
                $p->sendMessage("§7> §aТы включил партикл §6Dus4§a!");
            }
        }
    }

    // Скорборды
    public static function getInstance(): Main
    {
        return self::$instance;
    }

    public function new(Player $player, string $objectiveName, string $displayName): void
    {
        if (isset($this->scoreboards[$player->getName()])) {
            $this->remove($player);
        }
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = "sidebar";
        $pk->objectiveName = $objectiveName;
        $pk->displayName = $displayName;
        $pk->criteriaName = "dummy";
        $pk->sortOrder = 0;
        $player->sendDataPacket($pk);
        $this->scoreboards[$player->getName()] = $objectiveName;
    }

    public function remove(Player $player): void
    {
        $objectiveName = $this->getObjectiveName($player);
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = $objectiveName;
        $player->sendDataPacket($pk);
        unset($this->scoreboards[$player->getName()]);
    }

    public function setLine(Player $player, int $score, string $message): void
    {
        if (!isset($this->scoreboards[$player->getName()])) {
            $this->getLogger()->error("Cannot set a score to a player with no scoreboard");
            return;
        }
        if ($score > 15 || $score < 1) {
            $this->getLogger()->error("Score must be between the value of 1-15. $score out of range");
            return;
        }
        $objectiveName = $this->getObjectiveName($player);
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $objectiveName;
        $entry->type = $entry::TYPE_FAKE_PLAYER;
        $entry->customName = $message;
        $entry->score = $score;
        $entry->scoreboardId = $score;
        $pk = new SetScorePacket();
        $pk->type = $pk::TYPE_CHANGE;
        $pk->entries[] = $entry;
        $player->sendDataPacket($pk);
    }



    public function initPlayerClickData(Player $p): void
    {
        $this->clicksData[$p->getLowerCaseName()] = [];
    }


    public function topKills()
    {
        $top = $this->db->query("SELECT * FROM `stats` ORDER BY kills DESC LIMIT 10");
        $list = "";
        $count = 0;
        while ($element = $top->fetchArray(SQLITE3_ASSOC)) {
            $count++;
            if ($count == 1) {
                $list .= "\n§e1. §f{$element["name"]} §7- §e{$element["kills"]}\n\n";
            }
            if ($count == 2) {
                $list .= "§e2. §f{$element["name"]} §7- §e{$element["kills"]}\n\n";
            }
            if ($count == 3) {
                $list .= "§e3. §f{$element["name"]} §7- §e{$element["kills"]}\n\n";
            }
            if ($count == 4) {
                $list .= "§e4. §f{$element["name"]} §7- §e{$element["kills"]}\n\n";
            }
            if ($count == 5) {
                $list .= "§e5. §f{$element["name"]} §7- §e{$element["kills"]}\n\n";
            }
            if ($count == 6) {
                $list .= "§e6. §f{$element["name"]} §7- §e{$element["kills"]}\n\n";
            }
            if ($count == 7) {
                $list .= "§e7. §f{$element["name"]} §7- §e{$element["kills"]}\n\n";
            }
            if ($count == 8) {
                $list .= "§e8. §f{$element["name"]} §7- §e{$element["kills"]}\n\n";
            }
            if ($count == 9) {
                $list .= "§e9. §f{$element["name"]} §7- §e{$element["kills"]}\n\n";
            }
            if ($count == 10) {
                $list .= "§e10. §f{$element["name"]} §7- §e{$element["kills"]}";
            }
        }
        $this->topkills->setTitle("§l§cTOP KILLERS");
        $this->topkills->setText("\n" . $list);
        $this->getServer()->getDefaultLevel()->addParticle($this->topkills);
    }
}
