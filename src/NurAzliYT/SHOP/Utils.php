<?php

namespace SafiraaCute\Shop;

use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;

class Utils {

    /**
     * @return Main
     */
    public function getPlugin(): Main {
        return Main::getInstance();
    }

    /**
     * @param Player $player
     * @param int $volume
     * @param int $pitch
     * @param string $sound_name
     * @return void
     */
    public static function addSound(Player $player, int $volume = 2, int $pitch = 1, string $sound_name = "note.bell"): void{
        $sound = new PlaySoundPacket();
        $sound->soundName = $sound_name;
        $sound->x = $player->getPosition()->getX();
        $sound->y = $player->getPosition()->getY();
        $sound->z = $player->getPosition()->getZ();
        $sound->volume = $volume;
        $sound->pitch = $pitch;
        $player->getNetworkSession()->sendDataPacket($sound);
    }
}