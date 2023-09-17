<?php

/**
 * Useful Links
 * Why Pest: https://pestphp.com/docs/why-pest
 * Writing Tests: https://pestphp.com/docs/writing-tests
 * 3 Compelling Reasons For Developers To Write Tests: https://christoph-rumpel.com/2023/6/three-compelling-reasons-for-developers-to-write-tests
 * Everything You Can Test In Your Laravel Application: https://christoph-rumpel.com/2023/3/everything-you-can-test-in-your-laravel-application
 */
it('can add two numbers', function () {
    function sum(int $a, int $b)
    {
        return $a + $b;
    }

    $sum = sum(2, 3);

    expect($sum)->toBe(5);
});
