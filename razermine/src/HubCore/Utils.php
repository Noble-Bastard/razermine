<?php

declare(strict_types=1);

namespace HubCore;

class Utils
{
    public function __construct(private Main $mainInstance)
    {
    }

    public function getKarma(string $username): int
    {
        $prepare = $this->mainInstance->database->prepare("SELECT * FROM `data` WHERE username = :username");
        $prepare->bindValue('username', $username);

        $resource = $prepare->execute()->fetchArray(1);

        if (!is_bool($resource)) {
            return (int) $resource['karma'];
        }

        return 0;
    }

    public function setKarma(string $username, int $amount): void
    {
        $prepare = $this->mainInstance->database->prepare("SELECT * FROM `data` WHERE username = :username");
        $prepare->bindValue('username', $username);

        $resource = $prepare->execute()->fetchArray(1);

        if (is_bool($resource)) {
            $prepare = $this->mainInstance->database->prepare("INSERT INTO `data` (username, karma) VALUES (:username, :karma)");
        } else {
            $prepare = $this->mainInstance->database->prepare("UPDATE `data` SET karma = :karma WHERE username = :username");
        }

        $prepare->bindValue("username", $username);
        $prepare->bindValue("karma", $amount);
        $prepare->execute();
    }

    public function setGroup(string $username, string $group): void
    {
        $prepare = $this->mainInstance->database->prepare("SELECT * FROM `data` WHERE username = :username");
        $prepare->bindValue('username', $username);

        $resource = $prepare->execute()->fetchArray(1);

        if (is_bool($resource)) {
            $prepare = $this->mainInstance->database->prepare("INSERT INTO `data` (username, group) VALUES (:username, :group)");
        } else {
            $prepare = $this->mainInstance->database->prepare("UPDATE `data` SET group = :group WHERE username = :username");
        }

        $prepare->bindValue("username", $username);
        $prepare->bindValue("group", $group);
        $prepare->execute();
    }
}
