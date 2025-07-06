<?php

/**
 * A PocketMine-MP plugin that makes only shows players that are in the same world in the player list menu.
 * Copyright (C) 2020 aminozomty
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace aminozomty\PerWorldPlayersList;

use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;

use aminozomty\PerWorldPlayersList\Main;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\player\Player;

class EventListener implements Listener
{

    public function onJoin(PlayerJoinEvent $event): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player->getWorld()->getFolderName() == $event->getPlayer()->getWorld()->getFolderName()) return;
            $entry = PlayerListEntry::createRemovalEntry($event->getPlayer()->getUniqueId());
            $player->getNetworkSession()->sendDataPacket(PlayerListPacket::remove([$entry]));
            $entry = PlayerListEntry::createRemovalEntry($player->getUniqueId());
            $event->getPlayer()->getNetworkSession()->sendDataPacket(PlayerListPacket::remove([$entry]));
        }
    }

    public function onEntityTeleport(EntityTeleportEvent $event)
    {
        $player = $event->getEntity();
        if (!$player instanceof Player) return;
        foreach (Main::getInstance()->getServer()->getOnlinePlayers() as $p) {
            if ($p->getWorld()->getFolderName() == $player->getWorld()->getFolderName()) {
                $entry = PlayerListEntry::createAdditionEntry($player->getUniqueId(), $player->getId(), $player->getDisplayName(), $player->getNetworkSession()->getTypeConverter()->getSkinAdapter()->toSkinData($player->getSkin()));
                $p->getNetworkSession()->sendDataPacket(PlayerListPacket::add([$entry]));
                $entry = PlayerListEntry::createAdditionEntry($p->getUniqueId(), $p->getId(), $p->getDisplayName(), $p->getNetworkSession()->getTypeConverter()->getSkinAdapter()->toSkinData($p->getSkin()));
                $p->getNetworkSession()->sendDataPacket(PlayerListPacket::add([$entry]));
                continue;
            }
            $entry = PlayerListEntry::createRemovalEntry($player->getUniqueId());
            $p->getNetworkSession()->sendDataPacket(PlayerListPacket::remove([$entry]));
            $entry = PlayerListEntry::createRemovalEntry($p->getUniqueId());
            $player->getNetworkSession()->sendDataPacket(PlayerListPacket::remove([$entry]));
        }
    }
}
