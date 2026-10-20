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

use ILIAS\ProofOfWork\Service;
use PHPUnit\Framework\TestCase;
use ILIAS\ProofOfWork\ChallengeIssuer;
use ILIAS\ProofOfWork\ConsumedProofStore;

class ServiceTest extends TestCase
{
    public function testCreateReturnsTheIssuerDocument(): void
    {
        $service = new Service(new FixedIssuer(['signature' => 'sig']), new MemoryStore());

        $this->assertSame(['signature' => 'sig'], $service->createChallenge());
    }

    public function testVerifyAcceptsAProofOnce(): void
    {
        $store = new MemoryStore();
        $service = new Service(new FixedIssuer([]), $store, 90);

        $this->assertTrue($service->verify('proof'));
        $this->assertFalse($service->verify('proof'));
        $this->assertSame(90, $store->ttlFor('proof'));
    }

    public function testInvalidProofIsNotStored(): void
    {
        $store = new MemoryStore();
        $service = new Service(new FixedIssuer([], false), $store);

        $this->assertFalse($service->verify('proof'));
        $this->assertFalse($service->verify(''));
        $this->assertSame([], $store->claimed);
    }
}

final class FixedIssuer implements ChallengeIssuer
{
    /**
     * @param array<string, mixed> $document
     */
    public function __construct(private readonly array $document, private readonly bool $valid = true)
    {
    }

    public function create(): array
    {
        return $this->document;
    }

    public function verify(string $payload): bool
    {
        return $this->valid && $payload !== '';
    }
}

final class MemoryStore implements ConsumedProofStore
{
    /** @var array<string, int> */
    public array $claimed = [];

    public function claim(string $proof_id, int $ttl_seconds): bool
    {
        if (isset($this->claimed[$proof_id])) {
            return false;
        }

        $this->claimed[$proof_id] = $ttl_seconds;

        return true;
    }

    public function ttlFor(string $proof_id): int
    {
        return $this->claimed[$proof_id];
    }
}
