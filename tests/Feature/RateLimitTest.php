<?php

use Illuminate\Support\Facades\RateLimiter;
use function Pest\Laravel\get;

test('web routes are rate limited to 300 requests per minute', function () {
    // We will simulate actual requests to ensure the middleware logic is triggered correctly.
    // This is safer than guessing the internal cache key.
    
    // 1. Make 300 successful requests
    for ($i = 0; $i < 300; $i++) {
        // We use a lightweight route if possible, or just login page
        get('/login')->assertStatus(200);
    }
    
    // 2. The 301st request should be blocked
    get('/login')->assertStatus(429);
});
