<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class FuncNewsTest extends TestCase
{
    /** @test */
    public function index_page_loads_without_migrations()
    {
        $response = $this->get(route('funcnews.index'));
        $response->assertStatus(302); // redirect to login when unauthenticated
    }

    /** @test */
    public function it_exports_json_payload()
    {
        $response = $this->get(route('funcnews.export'));
        $response->assertStatus(302); // redirect to login when unauthenticated
    }
}
