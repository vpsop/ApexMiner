<?php

declare(strict_types=1);

namespace ApexDev\ApexMiner;

use ApexDev\ApexMiner\task\MinerTask;
use ApexDev\ApexMiner\tiles\MinerTile;
use ApexDev\ApexMiner\utils\ConfigManager;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\Listener;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\tile\Tile;

class EventListener implements Listener
{
    /**
     * On BLockPlaceEvent check if placed block is a Miner. If so, do the neccessary things.
     *
     * @param BlockPlaceEvent $event
     * @return void
     */
    public function onBlockPlace(BlockPlaceEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $nbt = $event->getItem()->getNamedTag();
        $position = $block->asPosition();

        if ($nbt->hasTag("ApexMiner")) {
            $level = $nbt->getInt("ApexMiner_Level", 0);
            if($level === 0) return;
            
            $tile = $player->getLevel()->getTile($block->asVector3());
            if (!$tile instanceof MinerTile) {

                $nbt = new CompoundTag();

                $nbt->setInt(Tile::TAG_X, $position->x);
                $nbt->setInt(Tile::TAG_Y, $position->y);
                $nbt->setInt(Tile::TAG_Z, $position->z);
                $nbt->setString("id", "MinerTile");
                $nbt->setInt("minerLevel", $level);
                $nbt->setString("minerOwner", $player->getName());

                new MinerTile($player->getLevel(), $nbt);
            }
            $minerDelay = (int)(((int)ConfigManager::getValue("miner-delay") / $level) * 20);
            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new MinerTask($block), $minerDelay);
        }
    }


    /**
     * On Block break, check if the block broken was a miner if so
     * drop the miner or place it in player inventory depend on configuration
     *
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBlockBreak(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if ($event->isCancelled()) return;


        $tile = $event->getBlock()->getLevel()->getTile($block);
        if (!$tile instanceof MinerTile) return;

        $level = $tile instanceof MinerTile ? $tile->getMinerLevel() : 0;
        if ($level === 0) return $event->setCancelled(true);

        $miner = Main::getInstance()->getMiner($level, 1);

        $dropOnBreak = ConfigManager::getToggle("drop-on-break");
        if ($dropOnBreak) {
            $drops = array();
            $drops[] = $miner;
            $event->setDrops($drops);
        } else {

            if ($player->getInventory()->canAddItem($miner)) {
                $event->setDrops(array());
                $player->getInventory()->addItem($miner);
            } else {
                $drops = array();
                $drops[] = $miner;
                $event->setDrops($drops);
            }
        }
    }


    /**
     * For server restart
     *
     * @param ChunkLoadEvent $event
     * @return void
     */
    public function onChunkLoad(ChunkLoadEvent $event)
    {
        $chunkTiles = $event->getChunk()->getTiles();
        foreach($chunkTiles as $tile){
            if($tile instanceof MinerTile){
                $block = $tile->getBlock();
                $level = $tile->getMinerLevel();
                if($level < 1) return;
                $belowBlock = $event->getLevel()->getBlock($block->asVector3()->floor()->down(1), false, false);
                if($belowBlock->getId() === Block::AIR){
                    $event->getLevel()->setBlock($belowBlock->asVector3(), Block::get(Block::COBBLESTONE));
                }
                $minerDelay = (int)(((int)ConfigManager::getValue("miner-delay") / $level) * 20);
                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new MinerTask($block), $minerDelay);
            }
        }
    }
  
}