<?php

test('application uses the maritest logo for browser icons', function () {
    $response = $this->get(route('home'));

    $response->assertSuccessful();
    $response->assertSee(
        '<link rel="icon" href="/maritest_logo.png" type="image/png">',
        escape: false,
    );
    $response->assertSee(
        '<link rel="apple-touch-icon" href="/maritest_logo.png">',
        escape: false,
    );
    $response->assertDontSee('/favicon.ico');
    $response->assertDontSee('/favicon.svg');
    $response->assertDontSee('/apple-touch-icon.png');
});
