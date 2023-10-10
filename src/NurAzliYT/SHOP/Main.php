<?php

namespace NurAzliYT\SHOP;

use cooldogedev\BedrockEconomy\BedrockEconomy;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {

    /** @var Main $instance */
    public static Main $instance;
    /** @var array $shops */
    public array $shops;
    /** @var BedrockEconomy|null $bedrockeconomy */
    public ?BedrockEconomy $bedrockeconomy;

    public function onEnable(): void {
        self::$instance = $this;
        $this->saveResource("shops.yml");
        $this->shops = (new Config($this->getDataFolder() . "shops.yml", Config::YAML))->getAll();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("Plugin Enabled");
        $this->bedrockeconomy = $this->getServer()->getPluginManager()->getPlugin("BedrockEconomy");
    }

    /**
     * @return Main
     */
    public static function getInstance(): Main {
        return self::$instance;
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param String $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, String $label, array $args): bool {
        if ($command->getName() == "shop") {
            if (!$sender instanceof Player) {
                $sender->sendMessage("§cPlease run this command in-game.");
                return false;
            }
            $shop = new ShopLogic();
            if (!isset($args[0])) {
                $shop->onOpen($sender);
                return false;
            }
            $shops = $this->shops["shop"];
            $type = strtolower($args[0]);
            if (!array_key_exists($type, $shops)) {
                $sender->sendMessage("§eList of category shops");
                foreach ($shops as $name => $value) {
                    $sender->sendMessage("§d- §f" . $name);
                }
                return false;
            }
            $shop->onOpen2($sender, $type, $shops[$type]['name']);
        }
        return true;
    }
}
