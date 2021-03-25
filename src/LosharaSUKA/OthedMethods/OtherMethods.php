<?php

namespace LosharaSUKA\OtherMethods;

use Exception;
use InvalidArgumentException;
use pocketmine\item\Item;
use pocketmine\Player;

class OtherMethods
{

    public function rebirthPlayer(Player $player): void
    {
        $this->updateTag($player);
        $player->setGameMode(2);
        $player->removeAllEffects();
        $player->setHealth(20);

        if ($player->isOp() ||  $this->getCountGroup($player->getName()) >= 1) {
            $player->setAllowFlight(true);
        } else {
            $player->setAllowFlight(false);
        }

        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $content = match ($this->getSettings($player, 'Lang')) {
            'Russ' => [
                '§fИграть',
                '§fКейсы',
                '§fОсновное',
                '§fХранилище'
            ],
            'Eng'  => [
                '§fPlay',
                '§fBox',
                '§fMain',
                '§fStorage'
            ],
            'DW'  => [
                '§fDas Spiel',
                '§fKiste',
                '§fHauptsächlich',
                '§fAufbewahrungsort'
            ],
            default => throw new InvalidArgumentException('ДАЛБАЕБ ТЫ ЧТО БЛЯТЬ БД РЕДАКТИРОВАЛ ИЛИ В КОДЕ Я НАКОСЯЧИЛ')
        };
        $player->getInventory()->setItem(0, Item::get(258)->setCustomName($content[0]));
        $player->getInventory()->setItem(2, Item::get(342)->setCustomName($content[1]));
        $player->getInventory()->setItem(8, Item::get(188)->setCustomName($content[2]));
        $player->getInventory()->setItem(4, Item::get(242)->setCustomName($content[3]));

        $player->setFood(20);
    }

    public function updateTag(Player $player): void
    {
        $name = $player->getName();
        $content = match ($this->getGroup($name)) {
            'Player' => '§7Player §f',
            'VIP' => '§a[V] ',
            'Premium' => '§3Premium ',
            'Holy' => '§6[H]§r',
            'Immortal' => '§d[I] ',
            'YouTube' => '§cYou§fTube ',
            'Moderator' => '§1Moderator ',
            'Creator' => '§bCreator§r ',
            'Admin' => '§g§lAdmin§r ',
            default => throw new InvalidArgumentException('ДАЛБАЕБ ТЫ ЧТО БЛЯТЬ БД РЕДАКТИРОВАЛ ИЛИ В КОДЕ Я НАКОСЯЧИЛ')
        };
        $player->setNameTag($content);
        $player->setDisplayName($content);
    }

    public function enableParticle(Player $player, string $particle)
    {
        $aliveParticles = [
            'Dus4',
            'Dus23',
            'Dus2',
            'Hearts',
            'LavaDrip'
        ];
        if (in_array($particle, $aliveParticles)) {
            if ($this->getParticleAvailability($player, $particle) === 'No') {
                $this->setParticleAvailability($player, $particle, 'Available');
                $player->sendMessage('§7> §aТы выбил партикл ' . $particle);
            } elseif ($this->getParticleAvailability($player, $particle) == 'Available') {
                $this->setParticle($player, $particle);
                $player->sendMessage('§7> §aТы включил партикл §6' . $particle . '§a!');
            }
        } else {
            throw new Exception('Че за далбаеб код редактнул, ищи по enableParticle странные параметры и фикси');
        }
    }
}
