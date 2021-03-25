<?php

namespace LosharaSUKA\SelectMenu;

use Exception;
use InvalidArgumentException;
use LosharaSUKA\GiveItems\GivePlayer;
use pocketmine\Player;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Server;
use LosharaSUKA\Main;

class AllSelects
{

    public function __construct(
        private \Getters $getterInstance,
        private string $serverName
    ) {
    }

    public function selectStats($player)
    {
        $form = new SimpleForm(function (Player $sender, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            if ($result == 0) {
                $this->selectMain($sender);
            } else {
                throw new InvalidArgumentException('Получен неверный индекс, кто блять что сделал??');
            }
        });
        $form->setTitle("§l§rStatistics");
        $kills = $this->getterInstance->getKills($player);
        $death = $this->getterInstance->getDeath($player);
        if ($death == 0 or $kills == 0) {
            $vin = 0 . "%";
        } else {
            $vin = round($kills / $death * 100) . "%";
        }
        $name = $player->getName();
        $content = match ($this->getterInstance->getSettings($player, 'Lang')) {
            'Russ' => "\n§r§7Ник: §b$name \n§r§7Привилегия: §e\n\n§r§7Смертей: §e $death\n
            §r§7Убийств: §e $kills \n§r§7Винрейт: §r§b $vin% \n\n",
            'Eng' => "\n§r§7Nаme: §b$name\n§r§7Grоup: §e\n\n§r§7Dеath: §e$death\n
            §r§7Кills: §e$kills\n§r§7Vin: §r§b$vin% \n\n",
            'DW' => "\n§r§7Nоmen: §b$name\n§r§7Gruрpe: §e\n\n§r§7Stеrben: §e$death\n
            §r§7Mоrde: §e$kills\n§r§7Vin: §r§b$vin% \n\n",
            default => throw new InvalidArgumentException('Произошла Ошибка! Неверный результат!')
        };
        $form->setContent($content);

        $form->addButton("§lBack", 0, "textures/blocks/barrier");
        $form->sendToPlayer($player);
    }
    public function selectMain($player)
    {
        $form = new SimpleForm(function (Player $sender, $data) {
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
                    $this->selectStats($sender);
                    break;
                case 3:
                    break;
            }
        });
        $form->setTitle("§l§4-§c- §fSETTINGS §c-§4-");

        $content = match ($this->getterInstance->getSettings($player, 'Lang')) {
            'Russ' => [
                '§8Выбор языка',
                '§8Мастер-Настройки',
                '§8Статистика',
                '§8Бейджики'
            ],
            'Eng' => [
                '§8Lang-Settings',
                '§8PvP-Settings',
                '§8Statistics',
                '§8Marks'
            ],
            'DW' => [
                '§8Spracheinstellungen',
                '§8PvP-Einstellungen',
                '§8Statistiken',
                '§8Markierungen'
            ],
            default => throw new InvalidArgumentException('Произошла Ошибка! Неверный результат!')
        };
        $form->addButton($content[0], 0, "textures/gui/newgui/Language16");
        $form->addButton($content[1], 0, "textures/blocks/chain_command_block_conditional_mipmap");
        $form->addButton($content[2], 0, "textures/items/map_filled");
        $form->addButton($content[3], 0, "textures/map/map_background");

        $form->sendToPlayer($player);
    }
}
