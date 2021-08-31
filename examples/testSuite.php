<?php

namespace Your\Name_Space;

use PHPUnit\Framework\TestCase;

class YourTestSuite extends TestCase
{
    public function testIncomplete()
    {
        // Optional: Test anything here, if you want.
        $this->assertTrue(true, 'This should already work.');

        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testRisky()
    {
    }

    public function testSkipped()
    {
        $this->markTestSkipped('This test was skipped for any reason.');
    }

    public function testFailure()
    {
        $this->assertEmpty(array('foo'));
    }

    public function testPass()
    {
        $this->assertTrue(true, 'This should always work.');
    }

    /**
     * @dataProvider additionProvider
     */
    public function testDataProvider($a, $b, $expected)
    {
        $this->assertEquals($expected, $a + $b);
    }

    public function additionProvider()
    {
        return [
            [0, 0, 0],
            [0, 1, 1],
            [1, 0, 1],
            [1, 1, 3]
        ];
    }
}
