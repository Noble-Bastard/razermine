<?php

use LosharaSUKA\Main;
use pocketmine\utils\Config;


class Setters
{
    public function addClick(Player $p): void
    {
        array_unshift($this->clicksData[$p->getLowerCaseName()], microtime(true));
        if (count($this->clicksData[$p->getLowerCaseName()]) >= self::ARRAY_MAX_SIZE) {
            array_pop($this->clicksData[$p->getLowerCaseName()]);
        }
    }

    public function removePlayerClickData(Player $p): void
    {
        unset($this->clicksData[$p->getLowerCaseName()]);
    }

    public function setParticle($p, $particle)
    {
        $name = strtolower($p->getName());
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        $cfg->set("Particle", $particle);
        $cfg->save();
    }

    public function setParticleAvailability($p, $particle, $availability)
    {
        $name = strtolower($p->getName());
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        $cfg->set($particle, $availability);
        $cfg->save();
    }

    public function addBox($p, int $count, $type)
    {
        $name = strtolower($p->getName());
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        $cfg->set($type, $cfg->get($type) + $count);
        $cfg->save();
    }

    public function remBox($p, $count, $type)
    {
        $name = strtolower($p->getName());
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        $cfg->set($type, $cfg->get($type) - $count);
        $cfg->save();
    }

    public function setGroup($p, $group)
    {
        $name = strtolower($p);
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        $cfg->set("Group", $group);
        $cfg->save();
    }

    public function addKill($p)
    {
        $name = strtolower($p->getName());
        $this->db->query("UPDATE `stats` SET `kills` = `kills` +1 WHERE `name` = '$name'");
    }

    public function addKarma($p, int $count)
    {
        $name = strtolower($p);
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        $cfg->set("Karma", $cfg->get("Karma") + $count);
        $cfg->save();
    }

    public function remKarma($p, $count)
    {
        $name = strtolower($p->getName());
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        $cfg->set("Karma", $cfg->get("Karma") - $count);
        $cfg->save();
    }

    public function setSettings($p, $settings, $none)
    {
        $name = strtolower($p->getName());
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        $cfg->set($settings, $none);
        $cfg->save();
    }

    public function addDeath($p)
    {
        $name = strtolower($p->getName());
        $this->db->query("UPDATE `stats` SET `death` = `death` +1 WHERE `name` = '$name'");
    }
}
