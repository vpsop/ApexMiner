<?php

namespace ApexDev\ApexMiner\task;

use ApexDev\ApexMiner\Main;
use ApexDev\ApexMiner\utils\ConfigManager;
use pocketmine\block\Block;
use pocketmine\tile\Chest as TIleChest;
use pocketmine\item\Item;
use pocketmine\level\sound\FizzSound;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;

class MinerTask extends Task
{
    /** @var Block */
    private $minerBlock;

    public function __construct(Block $minerBlock)
    {
        $this->minerBlock = $minerBlock;
    }

    /**
     * OnRun function for the task. 
     * Basically checks if the tile above miner is a chest tile, if true, 
     * break the block below the diamond pickaxe and place the drops in the chest inventory above
     *
     * @param int $currentTick
     * @return void
     */
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

            $fizzEnabled  = ConfigManager::getToggle("fizz-sound");
            if($fizzEnabled) {
                $level->addSound(new FizzSound($this->minerBlock->asVector3()));
            }
            
            foreach ($drops as $drop) {
                $upTile->getInventory()->addItem($drop);
            }
        } 
    }

    

}