<?php

use LosharaSUKA\Main;
use pocketmine\utils\Config;


class Getters
{
    protected $clientData;
    
    public function getDeviceOS(string $username)
    {
        $devices =
            [
                "Unknown",
                "Android",
                "iOS",
                "macOS",
                "FireOS",
                "GearVR",
                "HoloLens",
                "Windows 10",
                "Windows",
                "EducalVersion",
                "Dedicated",
                "PlayStation4",
                "Switch",
                "XboxOne"
            ];
        return $devices[$this->clientData[$username]["DeviceOS"]];
    }

    public function getSettings($p, $settings)
    {
        $name = strtolower($p->getName());
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        return $cfg->get($settings);
    }

    public function getDeath($p)
    {
        $name = strtolower($p->getName());
        $result = $this->db->query("SELECT death FROM stats WHERE name = '$name'")->fetchArray(SQLITE3_ASSOC);
        return $result["death"];
    }

    public function getKills($p)
    {
        $name = strtolower($p->getName());
        $result = $this->db->query("SELECT kills FROM stats WHERE name = '$name'")->fetchArray(SQLITE3_ASSOC);
        return $result["kills"];
    }

    public function getKarma($p)
    {
        $name = strtolower($p->getName());
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        return $cfg->get("Karma");
    }

    public function getBox($p, $box)
    {
        $name = strtolower($p->getName());
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        return $cfg->get($box);
    }

    public function getGroup($p)
    {
        $name = strtolower($p);
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        return $cfg->get("Group");
    }

    public function getCountGroup($name)
    {
        if ($this->getGroup($name) == "Player") {
            return 0;
        } elseif ($this->getGroup($name) == "VIP") {
            return 1;
        } elseif ($this->getGroup($name) == "Premium") {
            return 2;
        } elseif ($this->getGroup($name) == "Holy") {
            return 3;
        } elseif ($this->getGroup($name) == "Immortal") {
            return 4;
        } elseif ($this->getGroup($name) == "YouTube") {
            return 5;
        } elseif ($this->getGroup($name) == "Moderator") {
            return 6;
        } elseif ($this->getGroup($name) == "Creator") {
            return 7;
        } elseif ($this->getGroup($name) == "Admin") {
            return 8;
        }
    }

    public function getParticle($p)
    {
        $name = strtolower($p->getName());
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        return $cfg->get("Particle");
    }

    public function getParticleAvailability($p, $particle)
    {
        $name = strtolower($p->getName());
        $cfg = new Config(Main::getDataPath() . "players/{$name}.yml", Config::YAML);
        return $cfg->get($particle);
    }

    public function getObjectiveName(Player $player): ?string
    {
        return isset($this->scoreboards[$player->getName()]) ? $this->scoreboards[$player->getName()] : null;
    }

    public function getCps(Player $player, float $deltaTime = 1.0, int $roundPrecision = 1): float
    {
        if (
            !isset($this->clicksData[$player->getLowerCaseName()]) ||
            empty($this->clicksData[$player->getLowerCaseName()])
        ) {
            return 0.0;
        }
        $ct = microtime(true);
        return round(count(array_filter(
            $this->clicksData[$player->getLowerCaseName()],
            static function (float $t) use ($deltaTime, $ct): bool {
                    return ($ct - $t) <= $deltaTime;
            }
        )) / $deltaTime, $roundPrecision);
    }

}
