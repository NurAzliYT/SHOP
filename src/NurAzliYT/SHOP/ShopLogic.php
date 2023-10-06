<?php

namespace SafiraaCute\Shop;

use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use SafiraaCute\Shop\events\PlayerSuccessBuyEvent;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\player\Player;

class ShopLogic extends PublicMenu {

    /**
     * @return array
     */
    public function getConfig(): array {
        return $this->getPlugin()->shops;
    }

    /**
     * @param Player $player
     * @return void
     */
    public function onOpen(Player $player): void {
        $form = new SimpleForm(function (Player $player, $data) {
            if (is_null($data)) return;
            if ($data == 0) {
                $this->sendMessageForm($player, $this->getConfig()['exitmessage']);
            } else {
                $button = $data - 1;
                $list = array_keys($this->getConfig()['shop']);
                $shop = $list[$button];
                $this->onOpen2($player, $shop, $this->getConfig()['shop'][$shop]['name']);
            }
        });

        $name = $player->getName();
        $dataConfig = $this->getConfig();
        $form->setTitle($dataConfig['uititle']);
        $form->setContent(str_replace('{name}', $name, $dataConfig['uicontent']));
        $form->addButton($dataConfig['exitname'], $dataConfig['exittype'], $dataConfig['exitimg']);
        foreach (array_keys($dataConfig['shop']) as $shop) {
            $form->addButton("§b»§3 ".$dataConfig['shop'][$shop]['name']."\n§7Click to select!", $dataConfig['shop'][$shop]['type'], $dataConfig['shop'][$shop]['img']);
        }
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param string $sub
     * @param string $title
     * @return void
     */
    public function onOpen2(Player $player, string $sub, string $title): void {
        $form = new SimpleForm(function (Player $player, $data) use ($sub) {
            if (is_null($data)) return;
            if ($data == 0) {
                $player->sendMessage($this->getConfig()['exitmessage']);
            } else {
                $button = $data - 1;
                $list = array_keys($this->getConfig()['shop'][$sub]['item']);
                $shop = $list[$button];
                $data = $this->getConfig()['shop'][$sub]['item'][$shop];
                $this->openCount($player, $data['idMeta'], $data['price'], $data['name'], $data['img']);
            }
        });
        $dataShop = $this->getConfig();
        $form->setTitle(str_replace('{title}', $title, $dataShop['uisubtitle']));
        $form->setContent(str_replace('{name}', $player->getName(), $dataShop['uicontent']));
        $form->addButton($dataShop['backname'], $dataShop['backtype'], $dataShop['backimg']);
        foreach (array_keys($dataShop['shop'][$sub]['item']) as $item) {
            $form->addButton("§b»§3 ".$dataShop['shop'][$sub]['item'][$item]['name'] . "\n§cPrice: §7" . number_format($dataShop['shop'][$sub]['item'][$item]['price']),
                $dataShop['shop'][$sub]['item'][$item]['type'],
                $dataShop['shop'][$sub]['item'][$item]['img']
            );
        }
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param string $idMeta
     * @param int $price
     * @param string $namee
     * @param string $img
     * @return void
     */
    public function openCount(Player $player, string $idMeta, int $price, string $namee, string $img): void {
        $form = new CustomForm(function (Player $player, $data) use ($idMeta, $price, $namee, $img) {
            if ($data !== null) {
                if (!is_numeric($data[1]) || (int)$data[1] < 0) {
                    $player->sendMessage('§cPlease enter an amount');
                    return;
                }
                $count = $data[1];
                $total = $price * (int)$count;
                $this->openBuyItemConfirmationMenu($player, $idMeta, $total, $count, $namee);
            }
        });
        $economy = $this->getPlugin()->economy;
        $form->setTitle($namee);
        $form->addLabel('§bYour Money: §e' . self::onFormatMoney($economy->myMoney($player)));
        $form->addSlider('Please enter an amount: ', 1, 64, 1);
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param string $idMeta
     * @param int $cost
     * @param int $count
     * @param string $namee
     * @return void
     */
    public function openBuyItemConfirmationMenu(Player $player, string $idMeta, int $cost, int $count, string $namee): void {
        $economy = $this->getPlugin()->economy;
        $dataShop = $this->getConfig();
        $money = $economy->myMoney($player);
        if ($money < $cost) {
            $missing = (int)$money - $cost;
            $str = str_replace('-', '', $missing);
            Utils::addSound($player, 400, (int) 0.4);
            $this->sendMessageForm($player, str_replace(["{count}", "{item_name}", "{price}", "{missing}"], [$this->onFormatMoney($count), $namee, $this->onFormatMoney($cost), $this->onFormatMoney($str)], $dataShop['erorBuy']));
            return;
        }
        $economy->reduceMoney($player, $cost);
        Utils::addSound($player, 400);
        $this->sendMessageForm($player, str_replace(["{count}", "{item_name}"], [$this->onFormatMoney($count), $namee], $dataShop['successBuy']));
        try {
            $itemData = explode(":", $idMeta);
//            $item = StringToItemParser::getInstance()->parse($itemData[0]);
//            $item?->setCount($count);
//            var_dump($item);
//            $player->getInventory()->addItem($item);
            if(isset($itemData[1])){
                $item = StringToItemParser::getInstance()->parse($itemData[0] . ":" . $itemData[1]) ?? LegacyStringToItemParser::getInstance()->parse($itemData[0] . ":" . $itemData[1]);
            }else{
                $item = StringToItemParser::getInstance()->parse($itemData[0]) ?? LegacyStringToItemParser::getInstance()->parse($itemData[0]);
            }
            $item?->setCount($count);
            $player->getInventory()->addItem($item);
        }catch(LegacyStringToItemParserException $e){
            $this->getPlugin()->getLogger()->error($e->getMessage());
        }
        (new PlayerSuccessBuyEvent(["idMeta" => $idMeta, "count" => $count], $player))->call();
    }

    /**
     * @param $number
     * @return string
     */
    public function onFormatMoney($number): string {
        $result = is_numeric($number) ? $number : 0;
        $integer = (int)$result;

        $key = [
            12 => 'T',
            9 => 'B',
            6 => 'M',
            3 => 'K',
            0 => ''
        ];
        foreach ($key as $exponent => $abbrev){
            if(abs($integer) >= pow(10, $exponent)){
                $display = $integer / pow(10, $exponent);
                $decimals = ($exponent >= 3 && round($display) < 100) ? 1 : 0;
                $number = number_format($display, $decimals).$abbrev;
                break;
            }
        }
        return $number;
    }
}