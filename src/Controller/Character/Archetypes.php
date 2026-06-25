<?php

declare(strict_types=1);

namespace App\Controller\Character;

use App\Service\Archetype\Applier;
use App\Service\Data\Archetypes as ArchetypesData;
use App\Service\Data\Manager;
use Flight;

class Archetypes
{
    public function __construct(
        private Manager $manager,
        private Applier $applier,
    ) {
    }

    public function index(): void
    {
        /** @var ArchetypesData $catalog */
        $catalog = $this->manager->getType(ArchetypesData::class);

        Flight::render('character/archetypes.twig', [
            'page_title' => 'Choose an Archetype',
            'archetypes' => $catalog->all(),
        ]);
    }

    public function create(): void
    {
        /** @var ArchetypesData $catalog */
        $catalog = $this->manager->getType(ArchetypesData::class);

        $id = (string) ($_POST['archetype'] ?? '');
        $archetype = $catalog->forId($id);

        if (!is_array($archetype)) {
            Flight::session()->error('Unknown archetype selected');
            Flight::redirect(Flight::getUrl('characters_new'));
            return;
        }

        $entity = $this->applier->applyToNewCharacter($archetype);

        Flight::redirect(Flight::getUrl('characters_settings', ['hash' => $entity->hash]));
    }
}
