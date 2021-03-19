<?php

declare(strict_types=1);

namespace LosharaSUKA\Tasks;

use Exception;
use pocketmine\scheduler\Task;
use LosharaSUKA\Main;

class ScoreBoard extends Task
{

    private Main $plugin;

    /*
     *  в случае чего можно изменить значения поэтмоу я скостыльнул
     * и юзанул длинный match для каждого Можно было Просто сделать default для Eng и DW и заменить только Morde
     * Но я человек ебанутый и решил поступить вот так вот чтобы в будущем было быстрее
     * Нумерация в ассоциативном массиве идет по индексу в скорборде
    */
    private const LANG = [
        'Eng' => [
            1 => '§fOnline:  ' ,
            4 => '§fKills:  ',
            7 => '§fKarma:  ',
            10 => '§fPing:  '
        ],
        'Russ' => [
            1 => '§fОнлайн:  ' ,
            4 => '§fУбийств:  ',
            7 => '§fКарма:  ',
            10 => '§fПинг:  '
        ],
        'DW' => [
            1 => '§fOnline:  ' ,
            4 => '§fMorde:  ',
            7 => '§fKarma:  ',
            10 => '§fPing:  '
        ]
    ];

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick): void
    {
        $api = Main::getInstance();
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $kills = $this->plugin->getKills($player);
            $karma = $this->plugin->getKarma($player);
            $ping = $player->getPing();
            $CPS = $this->plugin->getCPS($player);
            $online = count($this->plugin->getServer()->getOnlinePlayers());
            $name = $player->getName();
            if ($this->plugin->getSettings($player, "Board") == "on") {
                if ($this->plugin->getSettings($player, "Lang") == "Eng") {
                    $api->new($player, $player->getName(), "§dRаzerMine");
                    $onlineValue  = match ($this->plugin->getSettings($player, "Lang")) {
                        'Eng' => self::LANG['Eng'][1],
                        'Russ' => self::LANG['Russ'][1],
                        'DW' => self::LANG['DW'][1],
                        default => throw new \Exception('Блять ты что опять нахуй бд редачил далбаеб')
                    };
                    $killsValue = match ($this->plugin->getSettings($player, "Lang")) {
                        'Eng' => self::LANG['Eng'][4],
                        'Russ' => self::LANG['Russ'][4],
                        'DW' => self::LANG['DW'][4],
                        default => throw new \Exception('Блять ты что опять нахуй бд редачил далбаеб')
                    };
                    $karmaValue = match ($this->plugin->getSettings($player, "Lang")) {
                        'Eng' => self::LANG['Eng'][7],
                        'Russ' => self::LANG['Russ'][7],
                        'DW' => self::LANG['DW'][7],
                        default => throw new \Exception('Блять ты что опять нахуй бд редачил далбаеб')
                    };
                    $pingValue = match ($this->plugin->getSettings($player, "Lang")) {
                        'Eng' => self::LANG['Eng'][10],
                        'Russ' => self::LANG['Russ'][10],
                        'DW' => self::LANG['DW'][10 ],
                        default => throw new \Exception('Блять ты что опять нахуй бд редачил далбаеб')
                    };
                    $api->new($player, $player->getName(), "§dRаzerMine");
                    $api->setLine($player, 1, "§f$onlineValue:  ");
                    $api->setLine($player, 2, "§b$online §r ");
                    $api->setLine($player, 3, " ");
                    $api->setLine($player, 4, "§f$killsValue:  ");
                    $api->setLine($player, 5, " §b$kills ");
                    $api->setLine($player, 6, "   ");
                    $api->setLine($player, 7, "§f$karmaValue:  ");
                    $api->setLine($player, 8, "§b$karma  ");
                    $api->setLine($player, 9, "     ");
                    $api->setLine($player, 10, "§f$pingValue:  ");
                    $api->setLine($player, 11, "§b$ping ms  ");
                }
            }
            if ($CPS >= 14) {
                if ($player->getLevel()->getName() == "world") {
                    $player->sendPopup("Ты автокликер? :/");
                } else {
                    $this->plugin->getServer()->dispatchCommand($player, "lobby");
                    $player->sendTitle("§c§lОтключи авто-кликер");
                    $player->sendPopup("§c§lDisable autoclicker");
                    $player->sendTip("§c§lDisable Autoclicker");

                    $content = match (Main::getInstance()->getSettings($player, 'Lang')) {
                        'Russ' => '§fОтключи автокликер. Твой CPS превысил 14CPS!',
                        'Eng' => '§fDisable autoclicker. Your CPS has exceeded 14CPS!',
                        'DW' => '§fDeaktivieren Sie den Autoklicker. Ihr CPS hat 14CPS überschritten!',
                        default => throw new \Exception('Произошла Ошибка! Неверный результат!')
                    };
                    $player->sendMessage($content);
                }
            } elseif ($CPS >= 30) {
                $content = match (Main::getInstance()->getSettings($player, 'Lang')) {
                    'Russ' => '§e§lОФФАЙ АНТИКЛИКЕР!!!!!',
                    'Eng' => '§e§lTurn off the Autoclicker!!!!!',
                    'DW' => '§e§lSchalten Sie den Autoklicker aus!!!!!',
                    default => throw new \Exception('Произошла Ошибка! Неверный результат!')
                };
                $player->close($content);
            }
        }
    }
}
