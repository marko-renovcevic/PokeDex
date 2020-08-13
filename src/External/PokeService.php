<?php

namespace App\External;

use App\Client\PokeClient;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Log\LoggerInterface;

class PokeService
{
    private $pokeClient;
    private $logger;
    private $cache;

    public function __construct(PokeClient $pokeClient, LoggerInterface $logger)
    {
        $this->pokeClient = $pokeClient;
        $this->logger = $logger;
        $this->cache = new FilesystemAdapter();
    }

    public function getAllTypes(): array
    {
        // The callable will only be executed on a cache miss.
        $value = $this->cache->get('pokemon_types.all', function (ItemInterface $item) {
            $item->expiresAfter(300);

            // ... do some HTTP request or heavy computations
            $response = $this->pokeClient->getClient()->request('GET', 'type');
            $contents = $response->getBody()->getContents();
            $types = json_decode($contents, true);
            $this->logger->info('pokemon_types.all cached!');
    
            return $types['results'];
        });

        return $value;
    }

    public function getPokemonsOfType(string $type): array
    {
        // The callable will only be executed on a cache miss.
        $value = $this->cache->get('pokemon_type.' . $type, function (ItemInterface $item) use ($type) {
            $item->expiresAfter(300);

            // ... do some HTTP request or heavy computations
            $response = $this->pokeClient->getClient()->request('GET', 'type/' . $type);
            $contents = $response->getBody()->getContents();
            $pokemons = json_decode($contents, true);
            $this->logger->info('pokemon_type.' . $type . ' cached!');
    
            return $pokemons['pokemon'];
        });

        return $value;
    }

    public function getFullPokemonData(int $id): array
    {
        // The callable will only be executed on a cache miss.
        $value = $this->cache->get('pokemon.' . $id, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(300);

            // ... do some HTTP request or heavy computations
            $response = $this->pokeClient->getClient()->request('GET', 'pokemon/' . $id);
            $contents = $response->getBody()->getContents();
            $pokemon = json_decode($contents, true);
            $this->logger->info('pokemon.' . $id . ' cached!');
    
            return $pokemon;
        });

        return $value;
    }

    public function getStrongestPokemonOfType(string $type): array
    {
        // The callable will only be executed on a cache miss.
        $value = $this->cache->get('pokemon_type_strongest.' . $type, function (ItemInterface $item) use ($type) {
            $item->expiresAfter(300);

            // ... do some HTTP request or heavy computations
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
                        'id' => (int) $pokemon['id'],
                        'name' => $pokemon['name'],
                        'type' => $type,
                        'stats' => $stats,
                    ];
                }
            }
            $this->logger->info('pokemon_type_strongest.' . $type . ' cached!');
    
            return $pokemonWithBiggestStats;
        });

        return $value;
    }
}
