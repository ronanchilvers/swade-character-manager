<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity;
use App\Entity\Factory\Character;
use App\Entity\Factory\User;
use App\Filter;
use Flight;

class Characters
{
    public function __construct(
        private User $userFactory,
        private Character $characterFactory,
    ) {
    }

    public function index(string $id): void
    {
        $user = $this->userForId($id);
        if (!$user instanceof Entity) {
            Flight::session()->error('Unable to find user');
            Flight::redirect('/admin/users');
            return;
        }

        Flight::render('admin/characters/index.twig', [
            'page_title' => 'User Characters',
            'user' => $user,
            'characters' => $this->characterFactory->forUser((int) $user->id),
            'primary_stats' => [
                'agility' => 'Agility',
                'smarts' => 'Smarts',
                'spirit' => 'Spirit',
                'strength' => 'Strength',
                'vigor' => 'Vigor',
                'pace' => 'Pace',
                'parry' => 'Parry',
                'toughness' => 'Toughness',
            ],
        ]);
    }

    private function userForId(string $id): ?Entity
    {
        $userId = Filter::number($id);
        if ($userId <= 0) {
            return null;
        }

        return $this->userFactory->byId($userId);
    }
}
