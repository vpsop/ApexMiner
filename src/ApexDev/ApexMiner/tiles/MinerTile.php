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


    /**
     * MinerTile constructor
     *
     * @param Level $level
     * @param CompoundTag $nbt
     */
    public function __construct(Level $level, CompoundTag $nbt)
    {
        $this->minerLevel = $nbt->getInt("minerLevel");
        $this->minerOwner = $nbt->getString("minerOwner");

        parent::__construct($level, $nbt);



    }

    /**
     * Gives Miner Level
     *
     * @return integer
     */
    public function getMinerLevel() : int
    {
        return $this->minerLevel;
    }

    /**
     * Gives the GamerTag of the player who placed the miner
     * No current use, Maybe used in future versions
     *
     * @return string
     */
    public function getMinerOwner() : string
    {
        return $this->minerOwner;
    }

    
    /**
     * @param CompoundTag $nbt
     * @return void
     */
    public function writeSaveData(CompoundTag $nbt): void
    {
        foreach ($this->nbt->getValue() as $tag) {
            $nbt->setTag($tag);
        }
    }

    /**
     * @param CompoundTag $nbt
     * @return void
     */
    public function readSaveData(CompoundTag $nbt): void
    {
        $this->nbt = $nbt;
    }

}