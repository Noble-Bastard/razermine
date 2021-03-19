<?php

namespace LosharaSUKA\GiveItems;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

class GivePlayer
{
    private string $gappleItems = '310:0:1, 311:0:1, 312:0:1, 313:0:1, 276:0:1, 322:0:12';

    public function giveItems(Player $player, string $gameType)
    {
        if (!in_array($gameType, ['Gapple', 'NoDebuff', 'Fist'])) {
            throw new \Exception('далбаеб передавай правильный параметр');
        }

        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->setGameMode(0);
        $player->setAllowFlight(false);
        $player->removeAllEffects();
        $player->setFood(20);
        $inventory = $player->getArmorInventory();

        if ($gameType === 'Gapple') {
            $items = [];
            $itemsAsArrayString = explode(', ', $this->gappleItems);
            foreach ($itemsAsArrayString as $itemString) {
                list($id, $meta) = explode(':', $this->gappleItems);
                $items[] = Item::get($id, $meta);
            }
            $inventory->addItem($items);
        } elseif ($gameType === 'NoDebuff') {
            $item1 = Item::get(310, 0, 1);
            $enchantment1 = Enchantment::getEnchantment(0);
            $item1->addEnchantment(new EnchantmentInstance($enchantment1, 2));
            $inventory->setHelmet($item1);
            $item2 = Item::get(311, 0, 1);
            $enchantment2 = Enchantment::getEnchantment(0);
            $item2->addEnchantment(new EnchantmentInstance($enchantment2, 2));
            $inventory->setChestplate($item2);
            $item3 = Item::get(312, 0, 1);
            $enchantment3 = Enchantment::getEnchantment(0);
            $item3->addEnchantment(new EnchantmentInstance($enchantment3, 2));
            $inventory->setLeggings($item3);
            $item4 = Item::get(313, 0, 1);
            $enchantment4 = Enchantment::getEnchantment(0);
            $item4->addEnchantment(new EnchantmentInstance($enchantment4, 2));
            $inventory->setBoots($item4);
            $item5 = Item::get(276, 0, 1);
            $enchantment = Enchantment::getEnchantment(9);
            $item5->addEnchantment(new EnchantmentInstance($enchantment, 3));
            $player->getInventory()->addItem($item5);
            $player->getInventory()->addItem(Item::get(438, 16, 3));
            $player->getInventory()->addItem(Item::get(438, 22, 32));
        } elseif ($gameType === 'Fist') {
            $player->getInventory()->addItem(Item::get(364, 0, 64));
        }
    }
}