<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\External\PokeService;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home", methods={"GET"})
     */
    public function index(PokeService $pokeService)
    {
        $types = $pokeService->getAllTypes();
        $pokemonsWithType = [];

        foreach ($types as $type) {
            $pokemons = $pokeService->getPokemonsOfType($type['name']);

            foreach ($pokemons as $pokemon) {
                $path = parse_url($pokemon['pokemon']['url'], PHP_URL_PATH);
                $id = (int) explode("/", $path)[4];
                // if it belongs to more than ONE type:
                if ( isset($pokemonsWithType[$id]) && ($pokemonsWithType[$id]['id'] == $id) ) {
                    $pokemonsWithType[$id]['type'] = $pokemonsWithType[$id]['type'] . ' / ' . $type['name'];
                } else {
                    // it has only one type
                    $pokemonsWithType[$id] = [
                        'id' => $id,
                        'name' => $pokemon['pokemon']['name'],
                        'type' => $type['name'],
                        'url' => $pokemon['pokemon']['url'],
                    ];
                }
            }
        }

        return $this->render('default/index.html.twig', [
            'pokemonsWithType' => $pokemonsWithType,
        ]);
    }

    /**
     * @Route("/{id}", name="pokemon", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function show(int $id, PokeService $pokeService)
    {
        $pokemon = $pokeService->getFullPokemonData($id);

        $pokemonsWithBiggestStats = [];
        foreach ($pokemon['types'] as $type) {
            $pokemonsWithBiggestStats[] = $pokeService->getStrongestPokemonOfType($type['type']['name']);
        }

        return $this->render('default/show.html.twig', [
            'pokemon' => $pokemon,
            'pokemonsWithBiggestStats' => $pokemonsWithBiggestStats,
        ]);
    }
}