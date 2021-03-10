<?php

namespace ApexDev\ApexMiner\task;

use ApexDev\ApexMiner\Main;
use pocketmine\block\Block;
use pocketmine\tile\Chest as TIleChest;
use pocketmine\item\Item;
use pocketmine\level\sound\FizzSound;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;

class MinerTask extends Task
{
    private $minerBlock;

    public function __construct(Block $minerBlock)
    {
        $this->minerBlock = $minerBlock;
    }

    public function onRun(int $currentTick)
    {
        
        $scheduler = Main::getInstance()->getScheduler();
        $level = $this->minerBlock->getLevel();
        $upBlock = $this->minerBlock->getSide(Vector3::SIDE_UP);
        $downBlock = $this->minerBlock->getSide(Vector3::SIDE_DOWN);
        $upTile = $level->getTile($upBlock->asVector3());
        if ($upTile instanceof TileChest){
            $minerPos = $this->minerBlock->asVector3();

            if ($level->getBlock($minerPos, false, false)->getId() !== $this->minerBlock->getId()) {
                $scheduler->cancelTask($this->getTaskId());
            }

            $drops = $downBlock->getDrops(Item::get(Item::DIAMOND_PICKAXE));
            $level->setBlock($downBlock->asVector3(), Block::get(Block::AIR));
            $level->addSound(new FizzSound($this->minerBlock->asVector3()));
            foreach ($drops as $drop) {
                $upTile->getInventory()->addItem($drop);
            }
        } 
    }

    

}