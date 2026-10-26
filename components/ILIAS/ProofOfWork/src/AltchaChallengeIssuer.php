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

use AltchaOrg\Altcha\Altcha;
use InvalidArgumentException;
use AltchaOrg\Altcha\Algorithm\Pbkdf2;
use AltchaOrg\Altcha\VerifySolutionOptions;
use AltchaOrg\Altcha\CreateChallengeOptions;

/**
 * ALTCHA proof of work. The returned document is what the widget fetches.
 */
final class AltchaChallengeIssuer implements ChallengeIssuer
{
    public function __construct(
        private readonly SettingsSecret $secret,
        private readonly int $cost = 10000,
        private readonly int $ttl_seconds = 120,
    ) {
    }

    public function create(): array
    {
        $challenge = $this->altcha()->createChallenge(new CreateChallengeOptions(
            algorithm: new Pbkdf2(),
            cost: $this->cost,
            expiresAt: time() + $this->ttl_seconds,
        ));

        return $challenge->toArray();
    }

    public function verify(string $payload): bool
    {
        if ($payload === '') {
            return false;
        }

        try {
            $result = $this->altcha()->verifySolution(new VerifySolutionOptions(
                payload: $payload,
                algorithm: new Pbkdf2(),
            ));
        } catch (InvalidArgumentException) {
            return false;
        }

        return $result->verified;
    }

    private function altcha(): Altcha
    {
        return new Altcha(hmacSignatureSecret: $this->secret->get());
    }
}
