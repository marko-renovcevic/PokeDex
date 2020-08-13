<?php

namespace App\Client;

use GuzzleHttp\Client;

class PokeClient
{
    private $pokeAPIbaseURI;

    public function __construct($pokeAPIbaseURI)
    {
        $this->pokeAPIbaseURI = $pokeAPIbaseURI;
    }

    public function getClient()
    {
        return $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->pokeAPIbaseURI,
            // You can set any number of default request options.
            // 'timeout'  => 2.0,
        ]);
    }
}
