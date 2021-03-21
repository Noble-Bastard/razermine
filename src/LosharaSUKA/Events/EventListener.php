<?php

namespace LosharaSUKA\Events;

use LosharaSUKA\Main;
use LosharaSUKA\Tasks\TopsTask;
use LosharaSUKA\OtherMethods\OtherMethods;

use jojoe77777\FormAPI\SimpleForm;

use pocketmine\{Player, Server};

use pocketmine\event\Listener;

use pocketmine\event\player\{
    PlayerQuitEvent,
    PlayerJoinEvent, 
    PlayerDropItemEvent, 
    PlayerPreLoginEvent,
    PlayerExhaustEvent, 
    PlayerCommandPreprocessEvent, 
    PlayerChatEvent, 
    PlayerInteractEvent, 
    PlayerMoveEvent
};

use pocketmine\event\block\{BlockBreakEvent, BlockPlaceEvent};
use pocketmine\event\entity\{EntityDamageEvent, EntityDamageByEntityEvent};

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;

class EventListener implements Listener
{
    public $banCommand = [
        '/msg', 
        '/w', 
        '/tell', 
        '/me', 
        '/ver', 
        '/version', 
        '/mixer', 
        '/about', 
        '/suicide', 
        '/kill', 
        '/help', 
        '/info', 
        '/автор', 
        'server'
    ];

    public $selectGame = [
        '§fИграть',
        '§fPlay',
        '§fDas Spiel'
    ];

    public $selectMain = [
        '§fОсновное',
        '§fMain',
        '§fHauptsächlich'
    ];

    public $selectBoxMenu = [
        '§fКейсы',
        '§fBox',
        '§fKiste'
    ];

    public $selectStorage = [
        '§fХранилище',
        '§fStorage',
        '§fAufbewahrungsort'
    ];


    public function handleDrop(PlayerDropItemEvent $event): void
    {
        $event->setCancelled();
    }

    public function handlePlace(BlockPlaceEvent $event): void
    {
        $event->setCancelled();
    }

    public function handleBreak(BlockBreakEvent $event): void
    {
        $event->setCancelled();
    }

    public function handleHunger(PlayerExhaustEvent $event): void
    {
        $event->setCancelled();
    }

    public function MoveEvent(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player->getY() <= 10) 
            $player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
    }

    public function DamageEvent(EntityDamageEvent $event): void
    {
        if (($event->getEntity() instanceof Player) and $event->getEntity()->getLevel()->getFolderName() == "world") 
            $event->setCancelled();
        
        if ($event instanceof EntityDamageByEntityEvent) {
            if (($event->getDamager() instanceof Player) and $event->getDamager()->getLevel()->getFolderName() == "world") 
                $event->setCancelled();
        }
    }

    public function onLogin(DataPacketReceiveEvent $event): void
    {
        if ($event->getPacket() instanceof LoginPacket) {
            $nick = $event->getPacket()->username;
            // $this->clientData[$event->getPacket()->username] = $event->getPacket()->clientData;
        }
    }

    public function banCommand(PlayerCommandPreprocessEvent $event)
    {
        $player = $event->getPlayer();
        $bancommand = explode(" ", $event->getMessage());

        if (in_array(strtolower($bancommand[0], $this->banCommand))) {
            if (!$player->isOp()) {
                $content = match ($this->getSettings($player, "Lang")) {
                    'Russ' => 'Привет друг! Раз ты написал эту комнду, значит ты захотел что-то узнать про сервер.',
                    'Eng' => 'Hello Friend! Since you wrote this command, 
                    then you wanted to know something about the server.',
                    'DW' => 'Hallo Freund! Da Sie dieses Team geschrieben haben, 
                    wollten Sie etwas über den Server wissen',
                };
                $player->sendMessage($content);
                $event->setCancelled();
            }
        }
    }

    public function onChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
        $msg = $event->getMessage();
        $name = $player->getName();
        if ($this->getGroup($name) == "Player") {
            return $event->setFormat("§7{$name}: §f{$msg}");
        }
        if ($this->getGroup($name) == "VIP") {
            return $event->setFormat("§a[V] {$name}: §f{$msg}");
        }
        if ($this->getGroup($name) == "Premium") {
            return $event->setFormat("§3[P] {$name}: §f{$msg}");
        }
        if ($this->getGroup($name) == "Holy") {
            return $event->setFormat("§6[H] {$name}: §f{$msg}");
        }
        if ($this->getGroup($name) == "Immortal") {
            return $event->setFormat("§d[I] {$name}: §f{$msg}");
        }
        if ($this->getGroup($name) == "YouTube") {
            return $event->setFormat("§cYou§fTube§r§c {$name}: §f{$msg}");
        }
        if ($this->getGroup($name) == "Moderator") {
            return $event->setFormat("§1Moderator {$name}: §f{$msg}");
        }
        if ($this->getGroup($name) == "Creator") {
            return $event->setFormat("§bCreator {$name}: §f{$msg}");
        }
        if ($this->getGroup($name) == "Admin") {
            return $event->setFormat("§g§lAdmin {$name}: §f{$msg}");
        }
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer()->getName();

        if (isset($this->scoreboards[$player])) 
            unset($this->scoreboards[$player]);
        
        if (isset($this->gaming[$player])) 
            unset($this->gaming[$player]);
        
        $this->online = $this->online - 1;
        $event->setQuitMessage(null);
    }

    public function playerJoin(PlayerJoinEvent $event): void
    {
        // $this->initPlayerClickData($event->getPlayer());
    }

    public function playerQuit(PlayerQuitEvent $event): void
    {
        // $this->removePlayerClickData($event->getPlayer());
    }

    public function packetReceive(DataPacketReceiveEvent $event): void
    {
        // if (
        //     isset($this->clicksData[$event->getPlayer()->getLowerCaseName()]) &&
        //     (
        //         ($event->getPacket()::NETWORK_ID === InventoryTransactionPacket::NETWORK_ID &&
        //             $event->getPacket()->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY) ||
        //         ($event->getPacket()::NETWORK_ID === LevelSoundEventPacket::NETWORK_ID &&
        //             $event->getPacket()->sound === LevelSoundEventPacket::SOUND_ATTACK_NODAMAGE) ||
        //         ($this->countLeftClickBlock &&
        //             $event->getPacket()::NETWORK_ID === PlayerActionPacket::NETWORK_ID &&
        //             $event->getPacket()->action === PlayerActionPacket::ACTION_START_BREAK)
        //     )
        // ) {
        //     $this->addClick($event->getPlayer());
        // }
    }

    public function CreateStats(PlayerPreLoginEvent $event)
    {
        $name = strtolower($event->getPlayer()->getName());
        $cfg = new Config($this->getDataFolder() . "players/{$name}.yml", Config::YAML, array(
            "Group" => "Player",
            "Karma" => 0,
            "Cps" => "on",
            "Board" => "on",
            "Static" => "on",
            "Lang" => "Eng",
            "BoxD" => 0,
            "BoxC" => 0,
            "BoxB" => 0,
            "BoxA" => 0,
            "BoxS" => 0,
            "Tops" => "on",
            "Flames" => "No",
            "HappyVillager" => "No",
            "LavaDrip" => "No",
            "Hearts" => "No",
            "Dus2" => "No",
            "Dus23" => "No",
            "Dus4" => "No",
            "Particle" => "none"
        ));
        if (!$this->db->query("SELECT * FROM stats WHERE name = '$name'")->fetchArray(SQLITE3_ASSOC)) {
            $this->db->query("INSERT INTO stats (name, death, kills) VALUES ('$name', 0, 0);");
        }
    }

    public function onInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        if ($player->getLevel()->getFolderName() == "world") 
            $event->setCancelled();
        
        $item = $player->getInventory()->getItemInHand();

        $block = $event->getBlock();
        $frame = $block->getLevel()->getTile($block);
        if ($frame instanceof ItemFrame && $frame->getItem() instanceof FilledMap && !$event->getPlayer()->hasPermission('mapimageengine.bypassprotect')) 
            $event->setCancelled(true);
        
        if (in_array($item->getCustomName(), $this->selectGame))
        // if ($item->getCustomName() == "§fИграть" or $item->getCustomName() == "§fPlay" or $item->getCustomName() == "§fDas Spiel") 
            $this->SelectGame($player);
        

        if (in_array($item->getCustomName(), $this->selectMain))
        if ($item->getCustomName() == "§fОсновное" or $item->getCustomName() == "§fMain" or $item->getCustomName() == "§fHauptsächlich") 
            $this->SelectMain($player);
        
        if (in_array($item->getCustomName(), $this->selectBoxMenu))
        if ($item->getCustomName() == "§fКейсы" or $item->getCustomName() == "§fBox" or $item->getCustomName() == "§fKiste") 
            $this->BoxMenu($player);
        

        if (in_array($item->getCustomName(), $this->selectStorage))
            $this->Storage($player);
        
    }

    public function onDamage(EntityDamageEvent $event): void
    {
        if ($event->getEntity() instanceof Player) {
            if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
                $event->setCancelled();

                return;
            }

            if (($event->getEntity()->getHealth() - $event->getFinalDamage()) < 1) {
                $event->setCancelled();

                //$event->getEntity()->getLevel()->addParticle(new DestroyBlockParticle($event->getEntity()->getPosition(), Block::get(152, 0)));
                $event->getEntity()->addTitle("§l§cDEATH!§r");
                $this->addDeath($event->getEntity());

                if ($event instanceof EntityDamageByEntityEvent) {
                    $d = $event->getDamager();

                    $event->getDamager()->addTitle("§l§aKILL!§r", $event->getEntity()->getDisplayName());
                    $this->addKill($d);
                    $rand = mt_rand(3, 10);
                    $this->addKarma($d, $rand);
                    $d->addTitle("§c§lKill", "§f+ §e{$rand} §fKarma!");
                    $d->sendPopup("§l§cKILL!§r", $event->getEntity()->getDisplayName());
                    $message = "§e{$event->getEntity()->getDisplayName()} §fkilled by §e{$event->getDamager()->getDisplayName()}!";

                    foreach ($event->getDamager()->getLevel()->getPlayers() as $pl) {
                        $pl->sendMessage($message);
                    }
                }

                $this->getServer()->dispatchCommand($event->getEntity(), "lobby");
            }
        }
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $otherMethodsInstance = new OtherMethods();
        $player = $event->getPlayer();
        $event->setJoinMessage(null);
        $player->setImmobile(false);
        $player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
        Main::getInstance()->getScheduler()->scheduleDelayedTask(new TopsTask($this, $player), 15);
        if (!$player->hasPlayedBefore()) {
            $form = new SimpleForm(function (Player $sender, int $data = null) {
                $result = $data;
                if ($result === null) {
                    return true;
                }
                if ($result === 0) {
                    $this->SelectLang($sender);
                }
            });
            $form->setTitle("§r§lWelcome to §fServerName");
            $form->setContent("§7Choose the language for the game\n
            \n§7Выберите язык для игры\n\n§7Wähle die Sprache für das Spiel\n
            \n§7§lIf you do not select the main language, English will be selected as the main language.\n");
            $form->addButton("§b» §f§lSelect Main Language.§r §b«", 0);
            $form->sendToPlayer($player);
        }
        $otherMethodsInstance->rebirthPlayer($player);
    }

    public function osnDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity  instanceof Player) {
            if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
                $event->setCancelled();

                return;
            }

            if (($entity ->getHealth() - $event->getFinalDamage()) < 1) {
                $event->setCancelled();

                $entity ->sendTitle("§l§cDEATH!§r");
                $this->addDeath($entity);

                if ($event instanceof EntityDamageByEntityEvent) {
                    $damager = $event->getDamager();
                    $entity = $event->getEntity();

                    $event->getDamager()->addTitle("§l§aKILL!§r", $entity ->getDisplayName());
                    $this->addKill($damager);
                    $rand = mt_rand(3, 10);
                    $this->addKarma($damager, $rand);
                    $damager->sendTitle("§c§lKill", "§f+ §e{$rand} §fKarma!");
                    $damager->sendPopup("§l§cKILL!§r", $entity ->getDisplayName());
                    $message = "§e{$entity ->getDisplayName()} §fkilled by
                    §e{$event->getDamager()->getDisplayName()}!";

                    foreach ($event->getDamager()->getLevel()->getPlayers() as $pl) {
                        $pl->sendMessage($message);
                    }
                    if ($event->getDamager()->getLevel()->getFolderName() == "world") {
                        $event->setCancelled();
                    }
                }

                Server::getInstance()->dispatchCommand($entity, "lobby");
            }
            if ($event instanceof EntityDamageByEntityEvent) {
                if ($event->getDamager()->getLevel()->getFolderName() == "world") {
                    $event->setCancelled();
                }
            }

            if ($entity ->getLevel()->getFolderName() == "world") {
                $event->setCancelled(true);
            }
        }
    }
}
