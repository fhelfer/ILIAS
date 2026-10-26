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

/**
 * Issues challenges and accepts each valid proof only once.
 * Issued challenges are not stored. A valid proof is remembered until it expires.
 */
final class Service
{
    public function __construct(
        private readonly ChallengeIssuer $issuer,
        private readonly ConsumedProofStore $consumed,
        private readonly int $ttl_seconds = 120,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function createChallenge(): array
    {
        return $this->issuer->create();
    }

    public function verify(string $payload): bool
    {
        if ($payload === '' || !$this->issuer->verify($payload)) {
            return false;
        }

        return $this->consumed->claim($payload, $this->ttl_seconds);
    }
}
