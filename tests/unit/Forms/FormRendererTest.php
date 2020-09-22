<?php
/*
Pupilsight, Flexible & Open School System


For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

namespace Pupilsight\Forms;

use PHPUnit\Framework\TestCase;
use Pupilsight\Forms\FormRendererInterface;

/**
 * @covers FormRenderer
 */
class FormRendererTest extends TestCase
{
    public function testCanBeCreatedStatically()
    {
        $this->assertInstanceOf(
            FormRenderer::class,
            FormRenderer::create()
        );
    }
}
