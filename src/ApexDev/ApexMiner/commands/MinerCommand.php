<?php

namespace ApexDev\ApexMiner\commands;

use ApexDev\ApexMiner\Main;
use ApexDev\ApexMiner\utils\ConfigManager;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;

class MinerCommand extends PluginCommand
{

    /** @var Main */
    private $plugin;

    /**
     * MinerCommand constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        parent::__construct("miner", $plugin);
        $this->setUsage("/miner [int:count] [int:level] [string:player]");
        $this->setAliases(["giveminer"]);
        $this->setDescription("ApexMiner Base Command");
        $this->setPermission("apexminer.command.miner");
        $this->plugin = $plugin;
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool|mixed
     * @throws ReflectionException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!isset($args[2]) && $sender instanceof ConsoleCommandSender) return;
        if (!$sender->hasPermission("apexminer.command.miner")) {
            $sender->sendMessage(Main::PREFIX . C::DARK_RED . "Insufficient Permission.");
            return false;
        }

        if (empty($args)) {
            $sender->sendMessage(Main::PREFIX . C::RED . $this->getUsage());
            return false;
        }

        if (count($args)< 2 ) {
            $sender->sendMessage(Main::PREFIX . C::RED . $this->getUsage());
            return false;
        }

        $count = 1;
        if (isset($args[0]) && (int)$args[0] >= 1) {
            $count = (int)$args[0];
        }

        $level = 1;
        if (isset($args[1]) && (int)$args[1] >= 1) {
            $level = (int)$args[1];
        }

        $player = $sender;
        if (isset($args[2])) {
            $player = $this->plugin->getServer()->getPlayer($args[2]);
            if ($player === null) {
                $sender->sendMessage(Main::PREFIX . C::RED . "Player " . C::DARK_AQUA . $args[2] . C::RED . " not found!");
                return false;
            }
        }

        $miner = Main::$instance->getMiner($level, $count);
        $minerLevel = $miner->getNamedTag()->getInt("ApexMiner_Level");

        if ($player instanceof Player) {
            $message = ConfigManager::getMessage("player-given-miner");
            $message = str_replace("{player}", $player->getName(), $message);
            $message = str_replace("{count}", $miner->getCount(), $message);
            $message = str_replace("{miner}", "Miner Level ". $minerLevel, $message);

            $sender->sendMessage(Main::PREFIX . $message);
            $player->getInventory()->addItem($miner);
            return true;
        } else {
            $sender->sendMessage(Main::PREFIX . C::RED . "Player not found!");
        }

        return false;
    }
}