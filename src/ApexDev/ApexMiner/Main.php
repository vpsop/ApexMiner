<?php

namespace ApexDev\ApexMiner;

use DenielWorld\EzTiles\EzTiles;

use ApexDev\ApexMiner\commands\MinerCommand;
use ApexDev\ApexMiner\utils\ConfigManager;

use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as C;


class Main extends PluginBase{
    
    /**
     * @var string
     */
    public const PREFIX = C::BOLD . C::RED . "» A" . C::LIGHT_PURPLE . "M « " . C::RESET;
    
    /**
     * @var Main
     */
    public static $instance;

    public function onEnable()
    {
        self::$instance = $this;
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->register("ApexMiner", new MinerCommand($this));
        
        EzTiles::register($this);
        EzTiles::init();
        
    }

    /**
     * Get instance of Main Class
     * @return Main
     */
    public static function getInstance() : Main
    {
        return self::$instance;
    }


    /**
     * Makes a Miner Item and returns it
     *
     * @param integer $level The level of the miner
     * @param integer $count Number of miners to make
     * @return Item Returns the miner 
     */
    public function getMiner(int $level = 1, int $count = 1) : Item
    {
        $nbt = new CompoundTag("", [
            new IntTag("ApexMiner_Level", (int)$level),
            new IntTag("ApexMiner", 1),
            new ListTag("ench"),
            
        ]);

        $minerId = (int)ConfigManager::getValue("miner-block-id");
        $miner = Item::get($minerId, 0, $count, $nbt);

        $levelstr = "I";
        switch ($level) {
            case 1:
                $levelstr = "I";
                break;
            
            case 2:
                $levelstr = "II";
                break;
            
            case 3:
                $levelstr = "III";
                break;
            
            case 4:
                $levelstr = "IV";
                break;
            
            case 5:
                $levelstr = "V";
                break;
            
            default:
                $levelstr = "I";
                break;
        }

        $loreLines = [
          C::RESET . C::LIGHT_PURPLE . "Place this on a OreGenerator and",
          C::RESET . C::LIGHT_PURPLE . "Place a chest over it to collect the mined items" 
        ];
        
        $miner->setCustomName(C::RESET . C::BOLD . C::GOLD . "Autominer ". $levelstr);
        $miner->setLore($loreLines);
        return $miner;
    }
    
    
}