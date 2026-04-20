<?php

namespace App\Service;

use Redis;

class RedisService
{
    private $redis; //je declare une propriété qui va stocker la co redis
    public function __construct()
    {
        $this->redis = new \Redis(); // ça crée un objet Redis
        $this->redis->connect("127.0.0.1" , 6379); // je me connecte au serv de redis
    }

    public function get(string $cle): ?string
    {
        $valeur = $this->redis->get($cle);
        if ($valeur === false) {
            return null;
        }
        return $valeur;
    }

    public function set(string $cle, string $valeur, int $duree = 300): void
    {
        $this -> redis -> setex($cle, $duree, $valeur);
    }

    public function delete(string $cle): void
    {
        $this->redis->del($cle);
    }

    public function exists(string $cle): bool
    {
        return $this->redis->exists($cle) > 0;
    }
}
