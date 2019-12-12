<?php

/**
 * @file
 * Tests event dispatching.
 */
namespace MyParcelModule\cweagans\Composer\Tests;

use MyParcelModule\cweagans\Composer\PatchEvent;
use MyParcelModule\cweagans\Composer\PatchEvents;
use Composer\Package\PackageInterface;
class PatchEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests all the getters.
     *
     * @dataProvider patchEventDataProvider
     */
    public function testGetters($event_name, \MyParcelModule\Composer\Package\PackageInterface $package, $url, $description)
    {
        $patch_event = new \MyParcelModule\cweagans\Composer\PatchEvent($event_name, $package, $url, $description);
        $this->assertEquals($event_name, $patch_event->getName());
        $this->assertEquals($package, $patch_event->getPackage());
        $this->assertEquals($url, $patch_event->getUrl());
        $this->assertEquals($description, $patch_event->getDescription());
    }
    public function patchEventDataProvider()
    {
        $prophecy = $this->prophesize('Composer\\Package\\PackageInterface');
        $package = $prophecy->reveal();
        return array(array(\MyParcelModule\cweagans\Composer\PatchEvents::PRE_PATCH_APPLY, $package, 'https://www.drupal.org', 'A test patch'), array(\MyParcelModule\cweagans\Composer\PatchEvents::POST_PATCH_APPLY, $package, 'https://www.drupal.org', 'A test patch'));
    }
}
