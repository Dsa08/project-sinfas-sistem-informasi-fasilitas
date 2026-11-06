<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Class ExampleTest
 *
 * Menguji kesiapan lingkungan pengujian unit (*PHPUnit test runner*).
 */
class ExampleTest extends TestCase
{
    /**
     * Memastikan fungsi assertion dasar pada PHPUnit berjalan dengan semestinya.
     *
     * @return void
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}
