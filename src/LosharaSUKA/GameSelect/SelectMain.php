<?php

declare(strict_types=1);

namespace LosharaSUKA\GameSelect;

use Exception;
use InvalidArgumentException;
use LosharaSUKA\GiveItems\GivePlayer;
use pocketmine\Player;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Server;
use LosharaSUKA\Main;

final class SelectMain
{
    /*
     * Возможно кто-то назовёт меня ебнутым и почему я создаю такой дебилский массив, но я скажу что я ебнутый)))
     * Индексы в 2 измерении идут поэтапно следуя устройству
     * Например: 0(Neon) - ALL, 1(FightNight) - PE, 2(Backwood) - PC
     * */
    private const GAMES = [
        'Gapple' => [
            'Low' => [
                'Fractal', 'FightNight', 'Backwood'
            ],
            'High' => [
                'Neon', 'Museum', 'Good'
            ]
        ],
        'NoDebuff' => [

            'Low' => [
                'Copy2', 'Copy3', 'Copy4'
            ],

            'High' => [
                'Highset', 'Reef', 'Copy1'
            ]
        ],
        'Fist' => [
            'Copy2', 'Copy3', 'Copy4'
        ]

    ];
    private const PCDEVICES = [
        "Unknown",
        "macOS",
        "FireOS",
        "GearVR",
        "Windows 10",
        "EducalVersion",
        "Dedicated",
        "PlayStation4",
        "Switch",
        "XboxOne"
    ];
    private const TELEPHONEDEVICES = [
        'Android',
        'iOS',
        'Windows',
        'HoloLens'
    ];
    private const LOBBYUSAGE = '§fLobby§g ';

    private function getOnlineInAreas(
        array $array,
        int $index,
        int $arrayIndex = 0
    ): int {

        if (0 === $arrayIndex) {
            $online = 0;
            for ($i = 0; $i < $index; $i++) {
                $online += count(Server::getInstance()->getLevelByName($array[$i])->getPlayers());
            }
            return $online;
        }
        $online = 0;
        for ($i = 0; $i < $index; $i++) {
            $online += count(Server::getInstance()->getLevelByName($array[$i - $arrayIndex])->getPlayers());
        }
        return $online;
    }

    public function mainSelectMenu(
        Player $player
    ) {

        $mapName = [
            'Fractal', 'FightNight', 'Backwood',
            'Neon', 'Museum', 'Good',
            'Copy2', 'Copy3', 'Copy4',
            'Highset', 'Reef', 'Copy1',
            'Copy2', 'Copy3', 'Copy4'
        ];

        $form = new SimpleForm(function (Player $sender, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            $game = match ($result) {
                0 => 'Gapple',
                1 => 'NoDebuff',
                2 => 'Fist',
                default => throw new InvalidArgumentException('АЛО БЛЯТЬ КТО ЕЩЕ ОДНУ ФОРМУ СОЗДАЛ ИЛИ МНЕ
                ПИСАТЬ ДЖОДЖО И ЕМУ ДАВАТЬ ПИЗДЫ ЗА ЕГО АПИ????')
            };
            $this->selectGame($sender, $game);
        });
        // $serverInstance = new Server();
        foreach ($mapName as $level) {
            Server::getInstance()->loadLevel($level);
        }

        $online = $this->getOnlineInAreas($mapName, count($mapName));
        $onlineGapple = $this->getOnlineInAreas($mapName, 6);
        $onlineNodebuff = $this->getOnlineInAreas($mapName, 6, 7);
        $onlineFist = $this->getOnlineInAreas($mapName, 3, 14);

        $content = match (Main::getInstance()->getSettings($player, 'Lang')) {
            'Russ' => '§fОбщий онлайн на аренах: §b ' . $online . '\n',
            'Eng' => '§fGeneral online in arenas: §b ' . $online . '\n',
            'DW' => '§fAllgemein online in Arenen: §b ' . $online . '\n',
            default => throw new InvalidArgumentException('Произошла Ошибка! Неверный результат!')
        };

        $form->setTitle('§l§6-§g- §fPlay §g-§6-');
        $form->setContent($content);
        $form->addButton('§8Gapple FFA\n§e' . $onlineGapple . ' Рlayers', 0, 'textures/items/apple_golden');
        $form->addButton(
            '§8NoDebuff FFA\n§e' . $onlineNodebuff . 'Рlayers',
            0,
            'textures/items/potion_bottle_splash_heal'
        );
        $form->addButton('§8Fist FFA\n§e ' . $onlineFist . ' Рlayers', 0, 'textures/items/mutton_cooked');
        $form->sendToPlayer($player);
    }

    private function selectGame(
        Player $player,
        string $game
    ) {
        $form = new SimpleForm(function (Player $sender, int $data = null) use ($game) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    $this->selectGameType($sender, $game, 'High');
                    break;

                case 1:
                    $this->selectGameType($sender, $game, 'Low');
                    break;

                case 2:
                    $this->mainSelectMenu($sender);
                    break;
                default:
                    throw new InvalidArgumentException('Мда опять экзепшн кидать кто блять
                    еще одну кнопку добавил мать его');
            }
        });
        $form->setTitle('§l§r' . $game . ' FFA');
        $form->addButton('§lHigh KB\n§e0 Рlayers', 0, 'textures/items/potion_bottle_splash_heal');
        $form->addButton('§lLOW KB\n§e0 Рlayers', 0, 'textures/items/potion_bottle_splash_heal');
        $form->addButton('§lBack', 0, 'textures/blocks/barrier');
        $form->sendToPlayer($player);
    }

    public function selectGameType(
        Player $player,
        string $game,
        string $gameType
    ) {

        $itemClass = new GivePlayer();

        $form = new SimpleForm(function ($sender, $data = null) use ($gameType, $game, $itemClass) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    $this->selectGame($sender, $game);
                    break;
                case 1:
                    $sender->sendMessage(self::LOBBYUSAGE . $game . ' FFA§f->' . $gameType . ' KB§f->§gALL');
                    $server = Server::getInstance();
                    $server->loadLevel((string)self::GAMES[$game][$gameType][0]);
                    $server->loadLevel('Nether');
                    $sender->teleport($sender->getServer()->getLevelByName('Nether')->getSpawnLocation());

                    $sender->teleport($sender->getServer()->getLevelByName((string)self::GAMES[$game]
                    [$gameType][0])->getSpawnLocation());

                    $itemClass->giveItems($sender, $game);
                    break;
                case 2:
                    $os = Main::getInstance()->getDeviceOS($sender->getName());
                    if (in_array($os, self::TELEPHONEDEVICES)) {
                        $content = match (Main::getInstance()->getSettings($sender, 'Lang')) {
                            'Russ' => '§l§cТы не можешь войти на PC арены!',
                            'Eng' => '§l§cYou cannot enter the PC arenas!',
                            'DW' => '§l§cSie können die PC-Arenen nicht betreten!',
                            default => throw new InvalidArgumentException('Неверный результат! ДАЛБАЕБ ТЫ
                            ЧТО БЛЯТЬ БД РЕДАКТИРОВАЛ МАТЬ ТВОЮ ИДИ ИСПРАВЛЯЙ ДАЛАБЕБ')
                        };
                        $sender->sendMessage($content);
                    } else {
                        $sender->sendMessage(self::LOBBYUSAGE . $game . ' FFA§f-> ' . $gameType . 'KB§f->§gPC');
                        $server = Server::getInstance();
                        $server->loadLevel((string)self::GAMES[$game][$gameType][1]);
                        $server->loadLevel('Nether');
                        $sender->teleport($sender->getServer()->getLevelByName('Nether')->getSpawnLocation());

                        $sender->teleport($sender->getServer()->getLevelByName((string)self::GAMES[$game]
                        [$gameType][1])->getSpawnLocation());

                        $itemClass->giveItems($sender, $game);
                    }
                    break;
                case 3:
                    $name = $sender->getName();
                    $os = Main::getInstance()->getDeviceOS($name);

                    if (in_array($os, self::PCDEVICES)) {
                        $content = match (Main::getInstance()->getSettings($sender, 'Lang')) {
                            'Russ' => '§l§cТы не можешь войти на PE арены!',
                            'Eng' => '§l§cYou cannot enter the PE arenas!',
                            'DW' => '§l§cSie können die PE-Arenen nicht betreten!',
                            default => throw new InvalidArgumentException('Неверный результат! ДАЛБАЕБ
                            ТЫ ЧТО БЛЯТЬ БД РЕДАКТИРОВАЛ МАТЬ ТВОЮ ИДИ ИСПРАВЛЯЙ ДАЛАБЕБ')
                        };
                        $sender->sendMessage($content);
                    } else {
                        $sender->sendMessage(self::LOBBYUSAGE . $game . ' FFA§f->' . $gameType . ' KB§f->§gPE');
                        $server = Server::getInstance();
                        $server->loadLevel((string)self::GAMES[$game][$gameType][2]);
                        $server->loadLevel('Nether');
                        $sender->teleport($sender->getServer()->getLevelByName('Nether')->getSpawnLocation());

                        $sender->teleport($sender->getServer()->getLevelByName((string)self::GAMES[$game]
                        [$gameType][2])->getSpawnLocation());

                        $itemClass->giveItems($sender, $game);
                        $sender->addTitle('§l§g' . $game . ' FFA PHONE', '§r§fУдачи');
                    }
                    break;
                default:
                    throw new InvalidArgumentException('Мда опять экзепшн кидать кто блять еще
                    одну кнопку добавил мать его');
            }
        });
        $form->setTitle('§l§r' . $game . ' FFA ' . $gameType);
        $form->addButton('§lBack', 0, 'textures/blocks/barrier');
        $form->addButton('§lAll', 0, 'textures/items/apple_golden');
        $form->addButton('§lPC', 0, 'textures/items/apple_golden');
        $form->addButton('§lPhone Players', 0, 'textures/items/apple_golden');
        $form->sendToPlayer($player);
    }
}
