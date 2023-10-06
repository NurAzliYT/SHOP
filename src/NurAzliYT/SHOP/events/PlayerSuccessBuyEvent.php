<?php

namespace SafiraaCute\Shop\events;

use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

class PlayerSuccessBuyEvent extends PlayerEvent {

    private array $data;

    public function __construct(array $data, Player $player) {
        $this->data = $data;
        $this->player = $player;
    }
    
    /**
     * @return array
     */
    public function getItem(): array {
        return $this->data;
    }
}