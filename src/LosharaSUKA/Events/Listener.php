<?php

namespace LosharaSUKA\Events;

use LosharaSUKA\Main;
use LosharaSUKA\Tasks\TopsTask;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerJoinEvent;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use pocketmine\Server;

class EventListener implements Listener
{

    public function banCommand(PlayerCommandPreprocessEvent $e)
    {
        $p = $e->getPlayer();
        $command = $e->getMessage();
        $banCommand = explode(" ", $e->getMessage());
        if (
            strtolower($banCommand[0] == "/msg" || $banCommand[0] == "/w"
            || $banCommand[0] == "/tell" || $banCommand[0] == "/me"
            || $banCommand[0] == "/ver" || $banCommand[0] == "/version"
            || $banCommand[0] == "/mixer" || $banCommand[0] == "/about"
            || $banCommand[0] == "suicide" || $banCommand[0] == "/kill"
            || $banCommand[0] == "/help" || $banCommand[0] == "/info"
            || $banCommand[0] == "/автор" || $banCommand[0] == "/server")
        ) {
            if (!$p->isOp()) {
                $content = match ($this->getSettings($p, "Lang")) {
                    'Russ' => 'Привет друг! Раз ты написал эту комнду, значит ты захотел что-то узнать про сервер.',
                    'Eng' => 'Hello Friend! Since you wrote this command, 
                    then you wanted to know something about the server.',
                    'DW' => 'Hallo Freund! Da Sie dieses Team geschrieben haben, 
                    wollten Sie etwas über den Server wissen',
                };
                $p->sendMessage($content);
                $e->setCancelled();
            }
        }
    }
    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $event->setJoinMessage(null);
        $player->setImmobile(false);
        $this->updateTag($player);
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
        $this->Main($player);
        $name = $player->getName();
        switch ($this->getCountGroup($name)) {
            case 1:
                $this->getServer()->broadcastMessage("§7[§a+§7] §aVIP §7$name");
                $this->setParticleAvailability($player, "Flames", "Available");
                break;
            case 2:
                $this->getServer()->broadcastMessage("§7[§a+§7] §3Premium §7$name");
                $this->setParticleAvailability($player, "Flames", "Available");
                $this->setParticleAvailability($player, "HappyVillager", "Available");
                break;
            case 3:
                $this->getServer()->broadcastMessage("§7[§a+§7] §6Holy §7$name");
                $this->setParticleAvailability($player, "Flames", "Available");
                $this->setParticleAvailability($player, "HappyVillager", "Available");
                $this->setParticleAvailability($player, "LavaDrip", "Available");
                break;
            case 4:
                $this->getServer()->broadcastMessage("§7[§a+§7] §dImmortal §7$name");
                $this->setParticleAvailability($player, "Hearts", "Available");
                $this->setParticleAvailability($player, "Flames", "Available");
                $this->setParticleAvailability($player, "HappyVillager", "Available");
                $this->setParticleAvailability($player, "LavaDrip", "Available");
                break;
            case 5:
                $this->getServer()->broadcastMessage("§7[§a+§7] §cYou§fTube §c$name");
                $this->setParticleAvailability($player, "Hearts", "Available");
                $this->setParticleAvailability($player, "Flames", "Available");
                $this->setParticleAvailability($player, "HappyVillager", "Available");
                $this->setParticleAvailability($player, "LavaDrip", "Available");
                break;
            case 7:
                $this->getServer()->broadcastMessage("§7[§a+§7] §bCreator §7$name");
                $this->setParticleAvailability($player, "Hearts", "Available");
                $this->setParticleAvailability($player, "Flames", "Available");
                $this->setParticleAvailability($player, "HappyVillager", "Available");
                $this->setParticleAvailability($player, "LavaDrip", "Available");
                break;
            case 8:
                $this->getServer()->broadcastMessage("§7[§a+§7] §g§lAdmin §7$name");
                $this->setParticleAvailability($player, "Hearts", "Available");
                $this->setParticleAvailability($player, "Flames", "Available");
                $this->setParticleAvailability($player, "HappyVillager", "Available");
                $this->setParticleAvailability($player, "LavaDrip", "Available");
                break;
        }
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

                $event->getEntity()->sendTitle("§l§cDEATH!§r");
                $this->addDeath($event->getEntity());

                if ($event instanceof EntityDamageByEntityEvent) {
                    $d = $event->getDamager();

                    $event->getDamager()->addTitle("§l§aKILL!§r", $event->getEntity()->getDisplayName());
                    $this->addKill($d);
                    $rand = mt_rand(3, 10);
                    $this->addKarma($d, $rand);
                    $d->addTitle("§c§lKill", "§f+ §e{$rand} §fKarma!");
                    $d->sendPopup("§l§cKILL!§r", $event->getEntity()->getDisplayName());
                    $message = "§e{$event->getEntity()->getDisplayName()} §fkilled by
                    §e{$event->getDamager()->getDisplayName()}!";

                    foreach ($event->getDamager()->getLevel()->getPlayers() as $pl) {
                        $pl->sendMessage($message);
                    }
                }

                Server::getInstance()->dispatchCommand($event->getEntity(), "lobby");
            }
        }
    }
}
