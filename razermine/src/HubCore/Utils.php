<?php

declare(strict_types=1);


namespace HubCore;


    class Utils {

        public static function getKarma(string $username): int {
            $prepare = Main::getDatabase()->prepare("SELECT * FROM `data` WHERE username = :username");
            $prepare->bindValue('username', $username);
    
            $resource = $prepare->execute()->fetchArray(1);
    
            if (!is_bool($resource)) 
                return (int) $resource['karma'];
    
            return 0;
        }

        public static function setKarma(string $username, int $amount): void {
            $prepare = Main::getDatabase()->prepare("SELECT * FROM `data` WHERE username = :username");
            $prepare->bindValue('username', $username);
    
            $resource = $prepare->execute()->fetchArray(1);
    
            if (is_bool($resource)) {
                $prepare = Main::getDatabase()->prepare("INSERT INTO `data` (username, karma) VALUES (:username, :karma)");
            } else {
                $prepare = Main::getDatabase()->prepare("UPDATE `data` SET karma = :karma WHERE username = :username");
            }
    
            $prepare->bindValue("username", $username);
            $prepare->bindValue("karma", $amount);
            $prepare->execute();
        }
    }

?>