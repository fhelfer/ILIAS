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

use GuzzleHttp\Psr7\Utils;
use ILIAS\HTTP\GlobalHttpState;

/**
 * Anonymous JSON endpoint for the widget.
 * The ctrl map is not regenerated in this change.
 *
 * @ilCtrl_isCalledBy ILIAS\ProofOfWork\ChallengeGUI: ilStartUpGUI
 */
class ChallengeGUI
{
    private readonly Service $service;
    private readonly GlobalHttpState $http;

    public function __construct(?Service $service = null, ?GlobalHttpState $http = null)
    {
        global $DIC;

        $this->service = $service ?? ServiceFactory::fromGlobals();
        $this->http = $http ?? $DIC->http();
    }

    public function executeCommand(): void
    {
        $this->getChallenge();
    }

    public function getChallenge(): void
    {
        $json = json_encode(
            $this->service->createChallenge(),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        $response = $this->http->response()
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withBody(Utils::streamFor($json));
        $this->http->saveResponse($response);
        $this->http->sendResponse();
        exit;
    }
}
