<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

require_once(__DIR__ . "/../../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");
require_once(__DIR__ . "/CommonFieldRendering.php");

use ILIAS\UI\Component\Input\Field;

class ProofOfWorkInputTest extends ILIAS_UI_TestBase
{
    use CommonFieldRendering;

    public function testDefaultRendersCheckbox(): void
    {
        $field = $this->getFieldFactory()->proofOfWork('#', 'label', 'byline')
            ->withNameFrom(new DefNamesource());

        $html = $this->renderInsideContainer($field);

        $this->assertInstanceOf(Field\ProofOfWork::class, $field);
        $this->assertFalse($field->isAutomaticVerification());
        $this->assertStringContainsString('<altcha-widget', $html);
        $this->assertStringNotContainsString('auto="onload"', $html);
    }

    public function testAutomaticVerificationStartsOnLoad(): void
    {
        $field = $this->getFieldFactory()->proofOfWork('#', 'label')
            ->withAutomaticVerification()
            ->withNameFrom(new DefNamesource());

        $html = $this->renderInsideContainer($field);

        $this->assertTrue($field->isAutomaticVerification());
        $this->assertStringContainsString('auto="onload"', $html);
    }
}
