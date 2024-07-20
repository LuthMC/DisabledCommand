<?php

declare(strict_types=1);

namespace Luthfi\DisabledCommand;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\utils\Config;
use pocketmine\player\Player;
use _64FF00\PurePerms\PurePerms;

class Main extends PluginBase implements Listener {

    private $config;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * @param PlayerCommandPreprocessEvent $event
     */
    public function onPlayerCommand(PlayerCommandPreprocessEvent $event): void {
        $message = $event->getMessage();
        if (substr($message, 0, 1) === "/") {
            $command = explode(" ", $message)[0];
            $player = $event->getPlayer();

            foreach ($this->config->get("commands", []) as $value) {
                if ($command === $value['command'] && !$player->hasPermission($value['permission'])) {
                    if (in_array($this->getPlayerGroup($player), explode(",", $value['groups']))) {
                        $player->sendMessage("You do not have permission to use this command.");
                        $event->cancel();
                        return;
                    }
                }
            }
        }
    }

    private function getPlayerGroup(Player $player): string {
        $purePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
        if ($purePerms === null || !$purePerms instanceof PurePerms) {
            return "default";
        }
        $group = $purePerms->getUserDataMgr()->getGroup($player);
        return $group !== null ? $group->getName() : "default";
    }
}
