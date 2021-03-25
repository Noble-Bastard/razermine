<?php

namespace LosharaSUKA\Setters;

use LosharaSUKA\Main;
use pocketmine\Player;
use pocketmine\utils\Config;

final class Setters
{
    public function __construct(
        private Main $mainInstance,
    ) {
    }

    public function addClick(Player $player): void
    {
        array_unshift($this->clicksData[$player->getLowerCaseName()], microtime(true));
        if (count($this->clicksData[$player->getLowerCaseName()]) >= self::ARRAY_MAX_SIZE) {
            array_pop($this->clicksData[$player->getLowerCaseName()]);
        }
    }

    public function removePlayerClickData(Player $player): void
    {
        unset($this->clicksData[$player->getLowerCaseName()]);
    }

    public function setParticle(Player $player, $particle)
    {
        $name = strtolower($player->getName());
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        $cfg->set("Particle", $particle);
        $cfg->save();
    }

    public function setParticleAvailability($player, $particle, $availability)
    {
        $name = strtolower($player->getName());
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        $cfg->set($particle, $availability);
        $cfg->save();
    }

    public function addBox($player, int $count, string $type)
    {
        $name = strtolower($player->getName());
        $this->mainInstance->db->query("UPDATE `stats` SET `" . $type . "` = `" . $type . "` + $count WHERE `name` = '$name'");
    }

    public function remBox($player, $count, $type)
    {
        $name = strtolower($player->getName());
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        $cfg->set($type, $cfg->get($type) - $count);
        $cfg->save();
    }

    public function setGroup($player, $group)
    {
        $name = strtolower($player);
        $this->mainInstance->db->query("UPDATE `stats` SET `Groups` = '" . $group . "' WHERE `name` = '$name'");
    }

    public function addKill($player)
    {
        $name = strtolower($player->getName());
        $this->mainInstance->db->query("UPDATE `stats` SET `kills` = `kills` +1 WHERE `name` = '$name'");
    }

    public function addKarma($player, int $count)
    {
        $name = strtolower($player);
        $this->mainInstance->db->query("UPDATE `stats` SET `Karma` = `Karma` + $count WHERE `name` = '$name'");
    }

    public function remKarma($player, $count)
    {
        $name = strtolower($player->getName());
        $this->mainInstance->db->query("UPDATE `stats` SET `Karma` = `Karma` - $count WHERE `name` = '$name'");
    }

    public function setSettings($player, $settings, $none)
    {
        $name = strtolower($player->getName());
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        $cfg->set($settings, $none);
        $cfg->save();
    }

    public function addDeath($player)
    {
        $name = strtolower($player->getName());
        $this->mainInstance->db->query("UPDATE `stats` SET `death` = `death` +1 WHERE `name` = '$name'");
    }
}
