<?php
/*
Pupilsight, Flexible & Open School System


For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

namespace Pupilsight\Domain\System;

use PHPUnit\Framework\TestCase;
use Pupilsight\Domain\System\Theme;

/**
 * @covers Theme
 */
class ThemeTest extends TestCase
{
    protected $theme;

    public function setUp()
    {
        $this->theme = $this->getMockBuilder(Theme::class)
            ->setConstructorArgs([])
            ->getMockForAbstractClass();
    }

    public function testCanAddStylesheets()
    {
        $this->theme->stylesheets->add('foo', 'bar/baz');

        $this->assertArrayHasKey('foo',  $this->theme->stylesheets->getAssets());
    }

    public function testCanAddScripts()
    {
        $this->theme->scripts->add('fiz', 'bar/baz');

        $this->assertArrayHasKey('fiz',  $this->theme->scripts->getAssets());
    }
}
