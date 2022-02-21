<?php


namespace Core;

use Core\TurtlePlayer;


class Utils{


    /**
     * @param $player
     * @param string $type
     * @return string
     * get map name format for AsyncCreateMap
     */
    public static function getMapNameFormat($player, $type = 'game'){

        if($type == 'game') {
            $name = $player->getName() . "-vs-" . $player->getGame()->getType();
            return $name;
            } elseif ($type == 'opponentName') {

            $players = $player->getGame()->getPlayers();
            foreach($players as $player1) {

                if(!$player1 instanceof $player){
                    $opponent = $player1->getName();
                 }
                }

            return $player->getName()."-vs-".$opponent;

        }

        return 'ok';
    }

    /**
     * @return mixed
     * @throws \Exception
     * Gets random map name format
     */
    public static function getRandomMap(){

        $folders = Main::getInstance()->getConfig();
        $count = count($folders->getAll());
        $int = random_int(0, $count);

        $level = $folders->get($int);

        return $level;

    }

    public static function buildID($player, $player2){

        return $player->getName()."-vs-".$player2->getName();

    }

}
