<?php

namespace App\External;

use App\Client\PokeClient;

class PokeService
{
    private $pokeClient;

    public function __construct(PokeClient $pokeClient)
    {
        $this->pokeClient = $pokeClient;
    }

    public function getAllTypes(): array
    {
        $response = $this->pokeClient->getClient()->request('GET', 'type');
        $contents = $response->getBody()->getContents();
        $types = json_decode($contents, true);

        return $types['results'];
    }

    public function getPokemonsOfType(string $type): array
    {
        $response = $this->pokeClient->getClient()->request('GET', 'type/' . $type);
        $contents = $response->getBody()->getContents();
        $pokemons = json_decode($contents, true);

        return $pokemons['pokemon'];
    }

    public function getFullPokemonData(int $id): array
    {
        $response = $this->pokeClient->getClient()->request('GET', 'pokemon/' . $id);
        $contents = $response->getBody()->getContents();
        $pokemon = json_decode($contents, true);

        return $pokemon;
    }

    public function getStrongestPokemonOfType(string $type): array
    {
        $pokemons = $this->getPokemonsOfType($type);

        $stats = 0;
        $pokemonWithBiggestStats = [];

        foreach ($pokemons as $pokemon) {
            $path = parse_url($pokemon['pokemon']['url'], PHP_URL_PATH);
            $id = (int) explode("/", $path)[4];
            $pokemon = $this->getFullPokemonData($id);
            // base stat for attack + base stat for defense
            if ( ($pokemon['stats'][1]['base_stat'] + $pokemon['stats'][2]['base_stat']) > $stats ) {
                $stats = $pokemon['stats'][1]['base_stat'] + $pokemon['stats'][2]['base_stat'];
                $pokemonWithBiggestStats = [
                    'id' => $pokemon['id'],
                    'name' => $pokemon['name'],
                    'type' => $type,
                    'stats' => $stats,
                ];
            }
        }

        return $pokemonWithBiggestStats;
    }
}
