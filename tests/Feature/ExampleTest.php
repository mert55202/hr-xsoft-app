<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_login_route_is_registered(): void
    {
        $this->assertTrue(route('login') === url('/giris'));
    }
}
