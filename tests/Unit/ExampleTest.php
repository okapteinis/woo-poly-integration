<?php
/**
 * Example Unit Test
 *
 * This is a sample test file demonstrating the test structure.
 * Replace this with actual tests for your plugin components.
 *
 * @package Hyyan\WPI\Tests\Unit
 */

namespace Hyyan\WPI\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Example Test Class
 */
class ExampleTest extends TestCase
{
    /**
     * Test that true is true
     *
     * This is a basic sanity check to ensure PHPUnit is working correctly.
     */
    public function testTrueIsTrue(): void
    {
        $this->assertTrue(true);
    }

    /**
     * Test that strings can be concatenated
     */
    public function testStringConcatenation(): void
    {
        $hello = 'Hello';
        $world = 'World';
        $result = $hello . ' ' . $world;

        $this->assertEquals('Hello World', $result);
    }

    /**
     * Test that arrays can be counted
     */
    public function testArrayCounting(): void
    {
        $array = [1, 2, 3, 4, 5];

        $this->assertCount(5, $array);
    }
}
