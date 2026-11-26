<?php

namespace Tests\Feature;

use Tests\LegacyDatabaseTestCase;

class CookieNoticeTest extends LegacyDatabaseTestCase
{
    public function test_every_public_page_shows_the_cookie_notice(): void
    {
        $pages = [route('home'), route('privacy'), route('login'), ...array_map(fn (string $module) => route('solicitudes.create', $module), array_keys(config('samuel.modules')))];

        foreach ($pages as $page) {
            $this->get($page)->assertOk()->assertSee('id="cookieNotice"', false)->assertSee('Este sitio usa cookies')->assertSee('Uso de cookies')->assertSee(route('privacy').'#cookies', false);
        }
    }

    public function test_privacy_notice_documents_every_cookie_the_site_sets(): void
    {
        $response = $this->get(route('home'))->assertOk();
        $cookieNames = collect($response->headers->getCookies())->map->getName();

        $this->assertNotEmpty($cookieNames);
        $privacy = $this->get(route('privacy'))->assertOk()->assertSee('id="cookies"', false);
        foreach ($cookieNames as $cookieName) {
            $privacy->assertSee($cookieName);
        }
    }
}
