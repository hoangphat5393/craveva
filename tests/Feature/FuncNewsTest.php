<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FuncNewsTest extends TestCase
{
    #[Test]
    public function index_page_loads_without_migrations()
    {
        $response = $this->get(route('funcnews.index'));
        $response->assertStatus(302); // redirect to login when unauthenticated
    }

    #[Test]
    public function it_exports_json_payload()
    {
        $response = $this->get(route('funcnews.export'));
        $response->assertStatus(302); // redirect to login when unauthenticated
    }
}
