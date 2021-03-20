<?php

namespace LosharaSUKA\Events;

use LosharaSUKA\Main;
use LosharaSUKA\OtherMethods\OtherMethods;
use LosharaSUKA\Tasks\TopsTask;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\Player;
use pocketmine\Server;

class EventListener implements Listener
{

    private const PARTICLES = [
        "Flames",
        "HappyVillager",
        "LavaDrip",
        "Hearts"
    ];

    public function banCommand(PlayerCommandPreprocessEvent $event)
    {
        $player = $event->getPlayer();
        $command = $event->getMessage();
        $banCommand = explode(" ", $event->getMessage());
        if (
            strtolower($banCommand[0] == "/msg" || $banCommand[0] == "/w"
            || $banCommand[0] == "/tell" || $banCommand[0] == "/me"
            || $banCommand[0] == "/ver" || $banCommand[0] == "/version"
            || $banCommand[0] == "/mixer" || $banCommand[0] == "/about"
            || $banCommand[0] == "suicide" || $banCommand[0] == "/kill"
            || $banCommand[0] == "/help" || $banCommand[0] == "/info"
            || $banCommand[0] == "/автор" || $banCommand[0] == "/server")
        ) {
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

    public function onDamage(EntityDamageEvent $event): void
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

    public function noPlace(BlockPlaceEvent $event)
    {
        $event->setCancelled();
    }

    public function noBreak(BlockBreakEvent $event)
    {
        $event->setCancelled();
    }

    public function noHunger(PlayerExhaustEvent $event)
    {
        $event->setCancelled(true);
    }

    public function noDrop(PlayerDropItemEvent $event)
    {
        $event->setCancelled();
    }
}
