<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * User interface class for page content map editor
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPCIIMTriggerEditorGUI: ilInternalLinkGUI
 */
class ilPCIIMTriggerEditorGUI extends ilPCImageMapEditorGUI
{
    public function __construct(
        ilPCInteractiveImage $a_content_obj,
        ilPageObject $a_page
    ) {
        iljQueryUtil::initjQueryUI();
        parent::__construct($a_content_obj, $a_page);

        $this->main_tpl->addJavaScript("./Services/COPage/js/ilCOPagePres.js");
        $this->main_tpl->addJavaScript("./Services/COPage/js/ilCOPagePCInteractiveImage.js");

        ilAccordionGUI::addJavaScript();
        ilAccordionGUI::addCss();
    }

    public function getParentNodeName() : string
    {
        return "InteractiveImage";
    }

    public function getEditorTitle() : string
    {
        $lng = $this->lng;
        
        return $lng->txt("cont_pc_iim");
    }

    /**
     * Get trigger table
     */
    public function getImageMapTableHTML() : string
    {
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        

        $ilToolbar->addText($lng->txt("cont_drag_element_click_save"));
        $ilToolbar->setId("drag_toolbar");
        $ilToolbar->setHidden(true);
        $ilToolbar->addButton($lng->txt("save"), "#", "", "", "", "save_pos_button");
        
        $ilToolbar->addButton(
            $lng->txt("cancel"),
            $ilCtrl->getLinkTarget($this, "editMapAreas")
        );
        
        $image_map_table = new ilPCIIMTriggerTableGUI(
            $this,
            "editMapAreas",
            $this->content_obj,
            $this->getParentNodeName()
        );
        return $image_map_table->getHTML();
    }

    public function getToolbar() : ilToolbarGUI
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        // toolbar
        $tb = new ilToolbarGUI();
        $tb->setFormAction($ilCtrl->getFormAction($this));
        $options = array(
            "Rect" => $lng->txt("cont_Rect"),
            "Circle" => $lng->txt("cont_Circle"),
            "Poly" => $lng->txt("cont_Poly"),
            "Marker" => $lng->txt("cont_marker")
            );
        $si = new ilSelectInputGUI($lng->txt("cont_trigger_area"), "shape");
        $si->setOptions($options);
        $tb->addInputItem($si, true);
        $tb->addFormButton($lng->txt("add"), "addNewArea");
        
        return $tb;
    }

    public function addNewArea() : string
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        if ($_POST["shape"] == "Marker") {
            $this->content_obj->addTriggerMarker();
            $this->page->update();
            ilUtil::sendSuccess($lng->txt("cont_saved_map_data"), true);
            $ilCtrl->redirect($this, "editMapAreas");
        } else {
            return parent::addNewArea();
        }
        return "";
    }
    
    /**
     * Init area editing form.
     */
    public function initAreaEditingForm(
        string $a_edit_property
    ) : ilPropertyFormGUI {
        $lng = $this->lng;
        $form = new ilPropertyFormGUI();
        $form->setOpenTag(false);
        $form->setCloseTag(false);

        // name
        if ($a_edit_property != "link" && $a_edit_property != "shape") {
            $ti = new ilTextInputGUI($lng->txt("cont_name"), "area_name");
            $ti->setMaxLength(200);
            $ti->setSize(20);
            //$ti->setRequired(true);
            $form->addItem($ti);
        }
        
        // save and cancel commands
        if ($a_edit_property == "") {
            $form->setTitle($lng->txt("cont_new_trigger_area"));
        } else {
            $form->setTitle($lng->txt("cont_new_area"));
        }
        $form->addCommandButton("saveArea", $lng->txt("save"));

        return $form;
    }

    /**
     * Save new or updated map area
     */
    public function saveArea() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        switch ($_SESSION["il_map_edit_mode"]) {
            // save edited shape
            case "edit_shape":
                $this->std_alias_item->setShape(
                    $_SESSION["il_map_area_nr"],
                    $_SESSION["il_map_edit_area_type"],
                    $_SESSION["il_map_edit_coords"]
                );
                $this->page->update();
                break;

            // save new area
            default:
                $area_type = $_SESSION["il_map_edit_area_type"];
                $coords = $_SESSION["il_map_edit_coords"];
                $this->content_obj->addTriggerArea(
                    $this->std_alias_item,
                    $area_type,
                    $coords,
                    ilUtil::stripSlashes($_POST["area_name"])
                );
                $this->page->update();
                break;
        }

        //$this->initMapParameters();
        ilUtil::sendSuccess($lng->txt("cont_saved_map_area"), true);
        $ilCtrl->redirect($this, "editMapAreas");
    }
    
    /**
     * Update trigger
     */
    public function updateTrigger() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $this->content_obj->setTriggerOverlays($_POST["ov"]);
        $this->content_obj->setTriggerPopups($_POST["pop"]);
        $this->content_obj->setTriggerOverlayPositions($_POST["ovpos"]);
        $this->content_obj->setTriggerMarkerPositions($_POST["markpos"]);
        $this->content_obj->setTriggerPopupPositions($_POST["poppos"]);
        $this->content_obj->setTriggerPopupSize($_POST["popsize"]);
        $this->content_obj->setTriggerTitles($_POST["title"]);
        $this->page->update();
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "editMapAreas");
    }
    
    /**
     * Confirm trigger deletion
     */
    public function confirmDeleteTrigger() : void
    {
        $ilCtrl = $this->ctrl;
        $main_tpl = $this->main_tpl;
        $lng = $this->lng;

        if (!is_array($_POST["tr"]) || count($_POST["tr"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "editMapAreas");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("cont_really_delete_triggers"));
            $cgui->setCancel($lng->txt("cancel"), "editMapAreas");
            $cgui->setConfirm($lng->txt("delete"), "deleteTrigger");
            
            foreach ($_POST["tr"] as $i) {
                $cgui->addItem("tr[]", $i, $_POST["title"][$i]);
            }
            $main_tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete trigger
     * @throws ilDateTimeException
     */
    public function deleteTrigger() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        if (is_array($_POST["tr"]) && count($_POST["tr"]) > 0) {
            foreach ($_POST["tr"] as $tr_nr) {
                $this->content_obj->deleteTrigger($this->std_alias_item, $tr_nr);
            }
            $this->page->update();
            ilUtil::sendSuccess($lng->txt("cont_areas_deleted"), true);
        }

        $ilCtrl->redirect($this, "editMapAreas");
    }

    /**
     * Get additional page xml (to be overwritten)
     */
    public function getAdditionalPageXML() : string
    {
        return $this->page->getMultimediaXML();
    }
    
    public function outputPostProcessing(string $a_output) : string
    {

        // for question html get the page gui object
        $pg_gui = new ilPageObjectGUI($this->page->getParentType(), $this->page->getId());
        $pg_gui->setOutputMode(ilPageObjectGUI::PREVIEW);
        $pg_gui->getPageConfig()->setEnableSelfAssessment(true);
        //		$pg_gui->initSelfAssessmentRendering(true);		// todo: solve in other way
        $qhtml = $pg_gui->getQuestionHTML();
        if (is_array($qhtml)) {
            foreach ($qhtml as $k => $h) {
                $a_output = str_replace($pg_gui->pl_start . "Question;il__qst_$k" . $pg_gui->pl_end, " " . $h, $a_output);
            }
        }

        return $a_output;
    }
}
