<?php

namespace Tests\Feature;

use Tests\TestCase;

class RouteIntegrityTest extends TestCase
{
    public function test_city_destinations_have_distinct_named_routes(): void
    {
        $this->assertSame(url('/city/lifemarket'), route('city.lifemarket'));
        $this->assertSame(url('/city/entertainment'), route('city.entertainment'));
    }
}
