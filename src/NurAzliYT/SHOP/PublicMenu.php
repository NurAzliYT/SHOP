<?php

namespace NurAzliYT\SHOP;

class PublicMenu {

    /**
     * @return Main
     */
    public function getPlugin(): Main {
        return Main::getInstance();
    }

    /**
     * @param $sender
     * @param $message
     * @return void
     */
    public function sendMessageForm($sender, $message): void {
        $sender->sendMessage("§a[SHOP] §f" . $message);
    }
}
