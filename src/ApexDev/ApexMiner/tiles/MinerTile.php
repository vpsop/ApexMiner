<?php

namespace ApexDev\ApexMiner\tiles;

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\tile\Tile;

class MinerTile extends Tile
{
    /** @var CompoundTag */
    private $nbt;

    /** @var Position */
    private $position;

    /** @var int */
    private $minerLevel;

    /** @var string */
    private $minerOwner;


    public function __construct(Level $level, Position $position, int $minerLevel, string $minerOwner)
    {
        $this->position = $position;
        $this->minerLevel = $minerLevel;
        $this->minerOwner = $minerOwner;


        $nbt = new CompoundTag();
        $nbt->setInt(self::TAG_X, $position->x);
        $nbt->setInt(self::TAG_Y, $position->y);
        $nbt->setInt(self::TAG_Z, $position->z);
        $nbt->setString("id", "MinerTile");


        $nbt->setInt("minerLevel", $minerLevel);
        $nbt->setString("minerOwner", $minerOwner);

        parent::__construct($level, $nbt);



    }

    public function getPosition(): Position
    {
        return $this->position;
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