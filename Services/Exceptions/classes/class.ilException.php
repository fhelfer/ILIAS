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

/**
* Base class for ILIAS Exception handling. Any Exception class should inherit from it
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
*/
class ilException extends Exception
{
    /**
     * A message isn't optional as in build in class Exception
     *
     * @access public
     *
     */
    public function __construct($a_message, $a_code = 0, Throwable $previous = null)
    {
        parent::__construct($a_message, $a_code, $previous);
    }
}
