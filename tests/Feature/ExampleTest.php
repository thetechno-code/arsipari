<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test that root route redirects unauthenticated users to login.
     */
    public function test_the_application_redirects_guest_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
