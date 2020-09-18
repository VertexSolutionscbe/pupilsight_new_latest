<?php
/*
Pupilsight, Flexible & Open School System


For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

namespace Pupilsight\Forms;

use PHPUnit\Framework\TestCase;
use Pupilsight\Forms\FormFactoryInterface;

/**
 * @covers FormFactory
 */
class FormFactoryTest extends TestCase
{
    public function testCanBeCreatedStatically()
    {
        $this->assertInstanceOf(
            FormFactory::class,
            FormFactory::create()
        );
    }
}
