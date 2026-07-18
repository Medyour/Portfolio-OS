<?php

use Inertia\Testing\AssertableInertia as Assert;

test('the application renders the minimal Inertia page', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Setup')
        );
});
