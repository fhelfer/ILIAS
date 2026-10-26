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

use ILIAS\Cache\Container\Container;

/**
 * Replay protection in the ILIAS cache. When the global cache is disabled,
 * the container ignores writes and every claim succeeds.
 */
final class CacheConsumedProofStore implements ConsumedProofStore
{
    public function __construct(private readonly Container $cache)
    {
    }

    public function claim(string $proof_id, int $ttl_seconds): bool
    {
        $key = hash('sha256', $proof_id);
        if ($this->cache->has($key)) {
            return false;
        }

        $this->cache->set($key, '1', max(1, $ttl_seconds));

        return true;
    }
}
