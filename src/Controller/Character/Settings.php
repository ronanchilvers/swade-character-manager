<?php

declare(strict_types=1);

namespace App\Controller\Character;

use App\Entity;
use App\Entity\Factory\Character as FactoryCharacter;
use App\Filter;
use App\Service\Sources;
use Flight;

class Settings
{
    public function __construct(
        private FactoryCharacter $factory,
        private Sources $sources,
    ) {
    }

    public function create(): void
    {
        $this->edit(new Entity());
    }

    public function index(string $hash): void
    {
        $entity = $this->factory->forHash($hash);
        if (!$entity instanceof Entity) {
            Flight::session()->error(
                'Unable to find character'
            );
            Flight::redirect(Flight::getUrl('home_page'));
        }

        $this->edit($entity);
    }

    private function edit(Entity $entity): void
    {
        $errors = [];
        if ("POST" == Flight::request()->getMethod()) {
            $isNew = !isset($entity->id);
            $sourcesPost = $this->sources->filter(isset($_POST['sources']) ? array_keys($_POST['sources']) : []);
            $sharingPost = isset($_POST['sharing']) && $_POST['sharing'] === 'on';
            $entity->name = Filter::noTags($_POST['name'] ?? '');
            $entity->sharing = true == $sharingPost ? 1 : 0;
            $entity->sources = implode(',', $sourcesPost);
            $errors = $this->factory->validate($entity);
            if (!$errors) {
                $result = $this->factory->upsert($entity);
                $errors = $result->errors();
            }
            if (!$errors) {
                Flight::session()->success(
                    sprintf('Saved character %s successfully', $entity->name)
                );
                if ($isNew) {
                    Flight::redirect(Flight::getUrl('characters_settings', ['hash' => $entity->hash]));
                    return;
                }

                Flight::reload();
                return;
            }
            Flight::session()->error(
                sprintf('Sorry! There was a problem!')
            );
        }

        Flight::render('character/settings.twig', [
            'page_title' => 'Settings',
            'entity' => $entity,
            'errors' => $errors,
            'sources' => $this->sources->options(),
            'selected_sources' => $this->sources->selectedFromString($entity->sources ?? null),
        ]);
    }
}
