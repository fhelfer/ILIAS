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
use ILIAS\Cache\Container\BaseRequest;

final class ServiceFactory
{
    public static function fromGlobals(): Service
    {
        global $DIC;

        // cli/build_bootstrap.php executes provide-closures while resolving
        // components. There is no DIC yet, and ilSetting would call database() on null.
        if (!$DIC instanceof \ILIAS\DI\Container) {
            return new Service(
                new class () implements ChallengeIssuer {
                    public function create(): array
                    {
                        return [];
                    }

                    public function verify(string $payload): bool
                    {
                        return false;
                    }
                },
                new class () implements ConsumedProofStore {
                    public function claim(string $proof_id, int $ttl_seconds): bool
                    {
                        return false;
                    }
                },
            );
        }

        return new Service(
            new AltchaChallengeIssuer(new SettingsSecret(new ilSetting('proofofwork'))),
            new CacheConsumedProofStore(
                $DIC->globalCache()->get(new BaseRequest('proofofwork', true))
            ),
        );
    }
}
