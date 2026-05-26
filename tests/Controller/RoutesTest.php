<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Controller\Admin\Campaigns as AdminCampaigns;
use App\Controller\Admin\Characters as AdminCharacters;
use App\Controller\Admin\Users as AdminUsers;
use App\Controller\Auth;
use App\Controller\Campaigns;
use App\Controller\Character\Attributes;
use App\Controller\Character\Base;
use App\Controller\Character\Edges;
use App\Controller\Character\Hindrances;
use App\Controller\Character\Sheet;
use App\Controller\Character\Skills;
use App\Controller\Character\Settings;
use App\Controller\Home;
use App\Middleware\Auth as AuthMiddleware;
use App\Middleware\Superuser;
use Flight;
use Tests\Support\ControllerTestCase;

class RoutesTest extends ControllerTestCase
{
    public function testRoutesRegisterExpectedControllerAliasesAndMiddleware(): void
    {
        $routes = $this->loadRoutesByAlias();

        $expected = [
            'home_page' => ['/', [Home::class, 'index'], [AuthMiddleware::class]],
            'characters_public_sheet' => ['/characters/shared/@token:[a-f0-9]{64}', [Sheet::class, 'shared'], []],
            'auth_login' => ['/auth/', [Auth::class, 'index'], []],
            'auth_logout' => ['/auth/logout', [Auth::class, 'logout'], []],
            'admin_campaigns_index' => ['/admin/campaigns/', [AdminCampaigns::class, 'index'], [AuthMiddleware::class, Superuser::class]],
            'admin_campaigns_view' => ['/admin/campaigns/@hash:[a-z0-9]{32}', [AdminCampaigns::class, 'view'], [AuthMiddleware::class, Superuser::class]],
            'admin_characters_index' => ['/admin/characters/@id:[0-9]+', [AdminCharacters::class, 'index'], [AuthMiddleware::class, Superuser::class]],
            'admin_users_index' => ['/admin/users/', [AdminUsers::class, 'index'], [AuthMiddleware::class, Superuser::class]],
            'admin_users_edit' => ['/admin/users/@id:[0-9]+', [AdminUsers::class, 'edit'], [AuthMiddleware::class, Superuser::class]],
            'admin_users_disable' => ['/admin/users/@id:[0-9]+/disable', [AdminUsers::class, 'disable'], [AuthMiddleware::class, Superuser::class]],
            'admin_users_enable' => ['/admin/users/@id:[0-9]+/enable', [AdminUsers::class, 'enable'], [AuthMiddleware::class, Superuser::class]],
            'campaigns_index' => ['/campaigns/', [Campaigns::class, 'index'], [AuthMiddleware::class]],
            'campaigns_create' => ['/campaigns/create', [Campaigns::class, 'create'], [AuthMiddleware::class]],
            'campaigns_view' => ['/campaigns/@hash:[a-z0-9]{32}', [Campaigns::class, 'view'], [AuthMiddleware::class]],
            'campaigns_edit' => ['/campaigns/@hash:[a-z0-9]{32}/edit', [Campaigns::class, 'edit'], [AuthMiddleware::class]],
            'campaigns_join' => ['/campaigns/join/@hash:[a-z0-9]{32}', [Campaigns::class, 'join'], [AuthMiddleware::class]],
            'campaigns_add_character' => ['/campaigns/@hash:[a-z0-9]{32}/characters', [Campaigns::class, 'addCharacter'], [AuthMiddleware::class]],
            'campaigns_leave_character' => ['/campaigns/@hash:[a-z0-9]{32}/characters/@character_hash:[a-z0-9]{32}/leave', [Campaigns::class, 'leaveCharacter'], [AuthMiddleware::class]],
            'campaigns_leave' => ['/campaigns/@hash:[a-z0-9]{32}/leave', [Campaigns::class, 'leave'], [AuthMiddleware::class]],
            'campaigns_reset' => ['/campaigns/@hash:[a-z0-9]{32}/reset', [Campaigns::class, 'reset'], [AuthMiddleware::class]],
            'characters_create' => ['/characters/create', [Settings::class, 'create'], [AuthMiddleware::class]],
            'characters_delete' => ['/characters/delete/@hash:[a-z0-9]{32}', [Base::class, 'delete'], [AuthMiddleware::class]],
            'characters_concept' => ['/characters/concept/@hash:[a-z0-9]{32}', [Base::class, 'index'], [AuthMiddleware::class]],
            'characters_hindrances' => ['/characters/hindrances/@hash:[a-z0-9]{32}', [Hindrances::class, 'index'], [AuthMiddleware::class]],
            'characters_attributes' => ['/characters/attributes/@hash:[a-z0-9]{32}', [Attributes::class, 'index'], [AuthMiddleware::class]],
            'characters_skills' => ['/characters/skills/@hash:[a-z0-9]{32}', [Skills::class, 'index'], [AuthMiddleware::class]],
            'characters_edges' => ['/characters/edges/@hash:[a-z0-9]{32}', [Edges::class, 'index'], [AuthMiddleware::class]],
            'characters_sheet' => ['/characters/sheet/@hash:[a-z0-9]{32}', [Sheet::class, 'index'], [AuthMiddleware::class]],
            'characters_sheet_state' => ['/characters/sheet/@hash:[a-z0-9]{32}/state', [Sheet::class, 'updateState'], [AuthMiddleware::class]],
            'characters_sheet_notes' => ['/characters/sheet/@hash:[a-z0-9]{32}/notes', [Sheet::class, 'updateNotes'], [AuthMiddleware::class]],
            'characters_sheet_gear' => ['/characters/sheet/@hash:[a-z0-9]{32}/gear', [Sheet::class, 'updateGear'], [AuthMiddleware::class]],
            'characters_sheet_weapons' => ['/characters/sheet/@hash:[a-z0-9]{32}/weapons', [Sheet::class, 'updateWeapons'], [AuthMiddleware::class]],
        ];

        foreach ($expected as $alias => [$pattern, $callback, $middleware]) {
            self::assertArrayHasKey($alias, $routes, sprintf('Missing route alias %s', $alias));
            self::assertSame($pattern, $routes[$alias]->pattern);
            self::assertSame($callback, $routes[$alias]->callback);
            self::assertSame($middleware, $routes[$alias]->middleware);
        }
    }

    public function testAuthReturnRouteIsRegisteredWithoutAlias(): void
    {
        $this->loadRoutesByAlias();

        $matches = array_values(array_filter(
            Flight::router()->getRoutes(),
            fn (object $route): bool => '/auth/return' === $route->pattern
        ));

        self::assertCount(1, $matches);
        self::assertSame([Auth::class, 'return'], $matches[0]->callback);
        self::assertContains('GET', $matches[0]->methods);
        self::assertSame([], $matches[0]->middleware);
    }

    private function loadRoutesByAlias(): array
    {
        require __DIR__ . '/../../config/routes.php';

        $routes = [];
        foreach (Flight::router()->getRoutes() as $route) {
            if ('' === $route->alias) {
                continue;
            }
            $routes[$route->alias] = $route;
        }

        return $routes;
    }
}
