<?php

namespace Your\Name_Space;

class YourTestSuite extends \PHPUnit_Framework_TestCase
{
    public function testIncomplete()
    {
        // Optional: Test anything here, if you want.
        $this->assertTrue(TRUE, 'This should already work.');

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
}
