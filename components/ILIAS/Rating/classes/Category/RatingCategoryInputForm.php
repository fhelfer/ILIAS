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

namespace ILIAS\Rating\Category;

use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Factory as UiFactory;
use ilCtrlInterface;
use ilRating;
use ilRatingGUI;

class RatingCategoryInputForm
{
    public function __construct(
        private readonly UiFactory $ui_factory,
        private readonly array $ctrl_path,
        private readonly ilCtrlInterface $ctrl,
        private readonly array $categories,
        private readonly int $obj_id,
        private readonly string $obj_type,
        private readonly int $sub_obj_id = 0,
        private readonly string $sub_obj_type = "",
        private readonly int $user_id = 0
    ) {
    }

    public function getForm(): Form
    {
        if (!$this->ctrl_path) {
            $url_form = $this->ctrl->getFormActionByClass(ilRatingGUI::class, "saveRating");
        } else {
            $url_form = $this->ctrl->getFormActionByClass($this->ctrl_path, "saveRating");
        }

        $inputs = [];
        foreach ($this->categories as $category) {
            $user_rating = round(ilRating::getRatingForUserAndObject(
                $this->obj_id,
                $this->obj_type,
                $this->sub_obj_id,
                $this->sub_obj_type,
                $this->user_id,
                $category["id"]
            ));

            $overall_rating = ilRating::getOverallRatingForObject(
                $this->obj_id,
                $this->obj_type,
                $this->sub_obj_id,
                $this->sub_obj_type,
                $category["id"]
            );

            $inputs[] = $this->ui_factory->input()->field()->rating($category['title'])->withValue($user_rating)->withCurrentAverage($overall_rating['avg']);
        }

        return $this->ui_factory->input()->container()->form()->standard(
            $url_form,
            $inputs
        );
    }
}
