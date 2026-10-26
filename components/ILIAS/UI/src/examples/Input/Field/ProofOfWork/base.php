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

namespace ILIAS\UI\examples\Input\Field\ProofOfWork;

use ILIAS\ProofOfWork\ServiceFactory;

/**
 * ---
 * description: >
 *   Example of the Proof of Work input in a form. The user starts the check
 *   with "I'm not a robot". The example inlines a fresh challenge document so
 *   the kitchen sink can run the widget. A URL that returns the same JSON works
 *   the same way. Submitting the form checks the proof on the server.
 *
 * expected output: >
 *   ILIAS shows a form with the label "Confirm you are human" and a checkbox
 *   "I'm not a robot". The checkbox stays idle until it is activated. After the
 *   check finishes, saving the form shows either "Proof accepted." or
 *   "Proof rejected."
 * ---
 */
function base()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $challenge = proofofwork_example_challenge($DIC);
    $field = $ui->input()->field()->proofOfWork(
        $challenge,
        'Confirm you are human',
        'This check runs in the browser. You do not need to solve a puzzle.'
    );

    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name',
        'base'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
    $form = $ui->input()->container()->form()->standard($form_action, [$field]);

    $outcome = '';
    $query = $request->getQueryParams();
    if ($request->getMethod() === 'POST' && ($query['example_name'] ?? '') === 'base') {
        $form = $form->withRequest($request);
        $data = $form->getData();
        $payload = is_array($data) ? (string) ($data[0] ?? '') : '';
        $accepted = $payload !== '' && proofofwork_example_verify($DIC, $payload);
        $box = $accepted
            ? $ui->messageBox()->success('Proof accepted.')
            : $ui->messageBox()->failure('Proof rejected.');
        $outcome = $renderer->render($box);
    }

    return $outcome . $renderer->render($form);
}

/**
 * The widget accepts a challenge URL or the challenge JSON itself.
 * Without a database this example keeps a placeholder, so unit tests still render.
 */
function proofofwork_example_challenge(\ILIAS\DI\Container $dic): string
{
    if (!$dic->offsetExists('ilDB')) {
        return '#';
    }

    try {
        $document = ServiceFactory::fromGlobals()->createChallenge();
        return json_encode($document, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    } catch (\Throwable) {
        return '#';
    }
}

function proofofwork_example_verify(\ILIAS\DI\Container $dic, string $payload): bool
{
    if (!$dic->offsetExists('ilDB')) {
        return false;
    }

    try {
        return ServiceFactory::fromGlobals()->verify($payload);
    } catch (\Throwable) {
        return false;
    }
}
