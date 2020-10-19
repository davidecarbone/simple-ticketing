<?php

namespace SimpleTicketing\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ContainerAwareTest extends WebTestCase
{
    protected function setUp()
    {
        parent::setUp();
	    self::bootKernel();
    }
}
