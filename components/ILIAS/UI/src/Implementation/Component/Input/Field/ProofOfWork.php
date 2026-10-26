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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Constraint;
use Closure;

/**
 * Proof-of-work input. The value is the client proof. This class does not verify it.
 */
class ProofOfWork extends FormInput implements C\Input\Field\ProofOfWork
{
    private bool $automatic_verification = false;

    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        private readonly string $challenge_url,
        string $label,
        ?string $byline,
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
    }

    public function getChallengeUrl(): string
    {
        return $this->challenge_url;
    }

    public function withAutomaticVerification(bool $automatic = true): C\Input\Field\ProofOfWork
    {
        $clone = clone $this;
        $clone->automatic_verification = $automatic;
        return $clone;
    }

    public function isAutomaticVerification(): bool
    {
        return $this->automatic_verification;
    }

    protected function isClientSideValueOk($value): bool
    {
        return is_string($value);
    }

    protected function getConstraintForRequirement(): ?Constraint
    {
        return $this->refinery->string()->hasMinLength(1);
    }

    public function getUpdateOnLoadCode(): Closure
    {
        return static function (): void {
        };
    }
}
