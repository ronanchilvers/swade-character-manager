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
            Flight::session()->error('Unable to find character');
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
                Flight::session()->success(
                    sprintf('Saved character %s successfully', $entity->name)
                );
                Flight::redirect(Flight::getUrl('characters_skills', ['hash' => $entity->hash]));
                return;
            }
            $errors = $result->errors();
            Flight::session()->error(
                'Sorry! There was a problem!',
            );
        }

        Flight::render('character/attributes.twig', [
            'page_title' => 'Attributes',
            'entity' => $entity,
            'errors' => $errors,
            'attribute_options' => $sizes,
            'attribute_fields' => $this->factory->attributeFields(),
        ]);
    }
}
