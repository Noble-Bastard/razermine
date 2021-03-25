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

    public function getCountGroup(string $name): int
    {
        return match ($this->getGroup($name)) {
            'Player' => 0,
            'VIP' => 1,
            'Premium' => 2,
            'Holy' => 3,
            'Immortal' => 4,
            'YouTube' => 5,
            'Moderator' => 6,
            'Creator' => 7,
            'Admin' => 8,
            default => throw new InvalidArgumentException('ДАЛБАЕБ ТЫ ЧТО БЛЯТЬ БД РЕДАКТИРОВАЛ ИЛИ В КОДЕ Я НАКОСЯЧИЛ')
        };
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
