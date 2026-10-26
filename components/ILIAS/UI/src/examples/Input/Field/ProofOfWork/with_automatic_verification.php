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

require_once __DIR__ . '/base.php';

/**
 * ---
 * description: >
 *   The same Proof of Work input, started when the form is shown. The user
 *   does not have to click "I'm not a robot".
 *
 * expected output: >
 *   ILIAS shows a form with the label "Confirm you are human". The check starts
 *   on its own and turns green without a click. Saving the form shows either
 *   "Proof accepted." or "Proof rejected."
 * ---
 */
function with_automatic_verification()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $challenge = proofofwork_example_challenge($DIC);
    $field = $ui->input()->field()->proofOfWork(
        $challenge,
        'Confirm you are human',
        'This check starts when the form is shown.'
    )->withAutomaticVerification();

    $DIC->ctrl()->setParameterByClass(
        'ilsystemstyledocumentationgui',
        'example_name',
        'with_automatic_verification'
    );
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
    $form = $ui->input()->container()->form()->standard($form_action, [$field]);

    $outcome = '';
    $query = $request->getQueryParams();
    if ($request->getMethod() === 'POST' && ($query['example_name'] ?? '') === 'with_automatic_verification') {
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
