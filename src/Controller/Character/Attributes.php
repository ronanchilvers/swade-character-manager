<?php

declare(strict_types=1);

namespace App\Controller\Character;

use App\Dice;
use App\Entity;
use App\Entity\Factory\Character as FactoryCharacter;
use App\Filter;
use App\Service\GameData;
use Flight;

class Attributes
{
    public function __construct(
        private FactoryCharacter $factory,
        private GameData $gameData,
    ) {
    }

    public function index(string $hash): void
    {
        $entity = $this->factory->forHash($hash);
        if (!$entity instanceof Entity) {
            Flight::session()->flash('Unable to find character', 'error');
            Flight::redirect(Flight::getUrl('home_page'));
            return;
        }

        $sizes = Dice::validSizes();
        $errors = [];
        if ('POST' === Flight::request()->getMethod()) {
            $attributes = Filter::numberArray($_POST['attributes'], $sizes);
            foreach ($attributes as $key => $value) {
                $entity->set($key, $value);
            }
            $result = $this->factory->update($entity);
            if ($result->isSuccess()) {
                Flight::redirect(Flight::getUrl('characters_skills', ['hash' => $entity->hash]));
                return;
            }
            $errors = $result->errors();
        }

        Flight::render('character/attributes.twig', [
            'page_title' => 'Choose Attributes',
            'entity' => $entity,
            'errors' => $errors,
            'attribute_options' => $sizes,
            'attribute_fields' => $this->factory->attributeFields(),
        ]);
    }
}
