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

namespace ILIAS\ProofOfWork;

use ilSetting;

/**
 * HMAC secret in the existing settings table, module proofofwork.
 * No challenge rows are written.
 */
final class SettingsSecret
{
    private const string KEY = 'hmac_secret';

    public function __construct(private readonly ilSetting $settings)
    {
    }

    public function get(): string
    {
        $secret = $this->settings->get(self::KEY, '');
        if ($secret === null || $secret === '') {
            $secret = bin2hex(random_bytes(32));
            $this->settings->set(self::KEY, $secret);
        }

        return $secret;
    }
}
