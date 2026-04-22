<?php

declare(strict_types=1);

namespace App\Controller\Character;

use App\Entity;
use App\Entity\Factory\Character as FactoryCharacter;
use App\Entity\Factory\Hindrance;
use App\Filter;
use App\Service\Data\Manager;
use App\Service\Data\Hindrances as HindrancesData;
use Flight;

class Hindrances
{
    public function __construct(
        private FactoryCharacter $factory,
        private Hindrance $hindranceFactory,
        private Manager $manager,
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
        $characterHindrances = $errors = [];
        if ('POST' === Flight::request()->getMethod()) {
            $selected = Filter::alphaArray($_POST['hindrances'] ?? []);
            $result = $this->hindranceFactory->syncForCharacter(
                $entity,
                $selected
            );
            if ($result->isSuccess()) {
                Flight::session()->success(
                    sprintf('Saved character %s successfully', $entity->name)
                );
                Flight::redirect(Flight::getUrl('characters_attributes', ['hash' => $entity->hash]));
                return;
            }
            $errors = $result->errors();
            Flight::session()->error(
                'Sorry! There was a problem!'
            );
        } else {
            $characterHindrances = $this->hindranceFactory->forCharacter($entity);
            $selected = [];
            foreach ($characterHindrances as $hindrance) {
                $selected[$hindrance->key] = $hindrance->level;
            }
        }

        /** @var HindrancesData $hindranceService */
        $hindranceService = $this->manager->getType(HindrancesData::class);
        Flight::render('character/hindrances.twig', [
            'page_title' => 'Hindrances',
            'entity'     => $entity,
            'hindrances' => $hindranceService->forBuilder(),
            'selected'   => $selected,
            'errors'     => $errors,
        ]);
    }
}
