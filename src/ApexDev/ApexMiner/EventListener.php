<?php

declare(strict_types=1);

namespace ApexDev\ApexMiner;

use ApexDev\ApexMiner\task\MinerTask;
use ApexDev\ApexMiner\utils\ConfigManager;
use DenielWorld\EzTiles\data\TileInfo;
use DenielWorld\EzTiles\tile\SimpleTile;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\level\ChunkPopulateEvent;
use pocketmine\event\Listener;
use pocketmine\tile\Chest as TileChest;

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

        if ($nbt->hasTag("ApexMiner")) {
            $level = $nbt->getInt("ApexMiner_Level", 0);
            if($level === 0) return;
            
            $tile = $player->getLevel()->getTile($block->asVector3());
            if (!$tile instanceof SimpleTile) {
                $tileinfo = new TileInfo($event->getBlock(), ["id" => "simpleTile", "level" => $level, 'owner' => $player->getName()]);
                new SimpleTile($player->getLevel(), $tileinfo); 
            }
            // $uptile = $player->getLevel()->getTile($block->asVector3()->floor()->up(1));
            // if(!$uptile instanceof TileChest) return;
            $minerDelay = (int)(((int)ConfigManager::getValue("miner-delay") / $level) * 20);
            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new MinerTask($block), $minerDelay);
        }
    }


    public function onBlockBreak(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        // $bbelow = $block->getLevel()->getBlock($event->getBlock()->floor()->down(1));

        if ($event->isCancelled()) return;


        $tile = $event->getBlock()->getLevel()->getTile($block);
        if (!$tile instanceof SimpleTile) return;

        $level = $tile instanceof SimpleTile ? $tile->getData("level")->getValue() : 0;
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


    public function onChunkPopulate(ChunkLoadEvent $event)
    {
        $chunkTiles = $event->getChunk()->getTiles();
        foreach($chunkTiles as $tile){
            if($tile instanceof SimpleTile){
                $event->getLevel()->getServer()->broadcastMessage("Found a autominer Tile");
                $block = $tile->getBlock();
                $level = $tile->getData("level")->getValue();
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