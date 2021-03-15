<?php

namespace ApexDev\ApexMiner\tiles;

use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\tile\Tile;

class MinerTile extends Tile
{
    /** @var CompoundTag */
    private $nbt;

    /** @var int */
    private $minerLevel;

    /** @var string */
    private $minerOwner;


    public function __construct(Level $level, CompoundTag $nbt)
    {
        $this->minerLevel = $nbt->getInt("minerLevel");
        $this->minerOwner = $nbt->getString("minerOwner");

        parent::__construct($level, $nbt);



    }

    public function getMinerLevel() : int
    {
        return $this->minerLevel;
    }

    public function getMinerOwner() : string
    {
        return $this->minerOwner;
    }

    
    
    public function writeSaveData(CompoundTag $nbt): void
    {
        foreach ($this->nbt->getValue() as $tag) {
            $nbt->setTag($tag);
        }
    }

    
    public function readSaveData(CompoundTag $nbt): void
    {
        $this->nbt = $nbt;
    }

}