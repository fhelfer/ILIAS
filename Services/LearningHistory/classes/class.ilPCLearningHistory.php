<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Learning history page content
 *
 * @author killing@leifos.com
 *
 * @ingroup ServicesLearningHistory
 */
class ilPCLearningHistory extends ilPageContent
{
    protected ilObjUser $user;

    /**
     * Init page content component.
     */
    public function init() : void
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->setType("lhist");
    }

    /**
     * Set node
     */
    public function setNode(php4DOMElement $a_node) : void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->lhist_node = $a_node->first_child();		// this is the skill node
    }

    /**
     * Create learning history node
     *
     * @param ilPageObject $a_pg_obj
     * @param string $a_hier_id
     * @param string $a_pc_id
     */
    public function create(ilPageObject $a_pg_obj, string $a_hier_id, $a_pc_id = "")
    {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->lhist_node = $this->dom->create_element("LearningHistory");
        $this->lhist_node = $this->node->append_child($this->lhist_node);
    }
    
    /**
     * Set from
     *
     * @param string $a_val from
     */
    public function setFrom($a_val)
    {
        $this->lhist_node->set_attribute("From", $a_val);
    }
    
    /**
     * Get from
     *
     * @return string from
     */
    public function getFrom()
    {
        return $this->lhist_node->get_attribute("From");
    }
    
    /**
     * Set to
     *
     * @param string $a_val to
     */
    public function setTo($a_val)
    {
        $this->lhist_node->set_attribute("To", $a_val);
    }

    /**
     * Get to
     *
     * @return string to
     */
    public function getTo()
    {
        return $this->lhist_node->get_attribute("To");
    }
    
    /**
     * Set classes
     *
     * @param array $a_val classes
     */
    public function setClasses($a_val)
    {
        // delete properties
        $children = $this->lhist_node->child_nodes();
        for ($i = 0; $i < count($children); $i++) {
            $this->lhist_node->remove_child($children[$i]);
        }
        // set classes
        foreach ($a_val as $key => $class) {
            $prop_node = $this->dom->create_element("LearningHistoryProvider");
            $prop_node = $this->lhist_node->append_child($prop_node);
            $prop_node->set_attribute("Name", $class);
        }
    }
    
    /**
     * Get classes
     *
     * @return array classes
     */
    public function getClasses()
    {
        $classes = [];
        // delete properties
        $children = $this->lhist_node->child_nodes();
        for ($i = 0; $i < count($children); $i++) {
            $classes[] = $children[$i]->get_attribute("Name");
        }
        return $classes;
    }
    

    /**
     * After page has been updated (or created)
     * @param ilPageObject $a_page     page object
     * @param DOMDocument  $a_domdoc   dom document
     * @param string       $a_xml      xml
     * @param bool         $a_creation true on creation, otherwise false
     */
    public static function afterPageUpdate(ilPageObject $a_page, DOMDocument $a_domdoc, string $a_xml, bool $a_creation) : void
    {
    }
    
    /**
     * Before page is being deleted
     * @param ilPageObject $a_page page object
     */
    public static function beforePageDelete(ilPageObject $a_page) : void
    {
    }

    /**
     * After page history entry has been created
     * @param ilPageObject $a_page       page object
     * @param DOMDocument  $a_old_domdoc old dom document
     * @param string       $a_old_xml    old xml
     * @param integer      $a_old_nr     history number
     */
    public static function afterPageHistoryEntry(ilPageObject $a_page, DOMDocument $a_old_domdoc, string $a_old_xml, int $a_old_nr) : void
    {
    }

    /**
     * Get lang vars needed for editing
     * @return array array of lang var keys
     */
    public static function getLangVars() : array
    {
        return array("ed_insert_learning_history", "pc_learning_history");
    }

    /**
     * @inheritDoc
     */
    public function modifyPageContentPostXsl(string $a_output, string $a_mode, bool $a_abstract_only = false) : string
    {
        $start = strpos($a_output, "{{{{{LearningHistory");
        $end = 0;
        if (is_int($start)) {
            $end = strpos($a_output, "}}}}}", $start);
        }

        while ($end > 0) {
            $param = substr($a_output, $start + 5, $end - $start - 5);
            $param = str_replace(' xmlns:xhtml="http://www.w3.org/1999/xhtml"', "", $param);
            $param = explode("#", $param);
            $from = $param[1];
            $to = $param[2];
            $classes = explode(";", $param[3]);
            $classes = array_map(function ($i) {
                return trim($i);
            }, $classes);


            $a_output = substr($a_output, 0, $start) .
                $this->getPresentation($from, $to, $classes, $a_mode) .
                substr($a_output, $end + 5);

            if (strlen($a_output) > $start + 5) {
                $start = strpos($a_output, "{{{{{LearningHistory", $start + 5);
            } else {
                $start = false;
            }
            $end = 0;
            if (is_int($start)) {
                $end = strpos($a_output, "}}}}}", $start);
            }
        }

        return $a_output;
    }

    /**
     * Get presentation
     *
     * @param int $from unix timestamp
     * @param int $to unix timestamp
     * @param array $classes
     * @param string $mode
     * @return string
     * @throws ilCtrlException
     */
    protected function getPresentation($from, $to, $classes, $a_mode) : string
    {
        if ($a_mode == "preview" || $a_mode == "presentation" || $a_mode == "print") {
            if ($this->getPage()->getParentType() == "prtf") {
                $user_id = ilObject::_lookupOwner($this->getPage()->getPortfolioId());
            }
        }
        if ($user_id > 0) {
            $tpl = new ilTemplate("tpl.pc_lhist.html", true, true, "Services/LearningHistory");
            $hist_gui = new ilLearningHistoryGUI();
            $hist_gui->setUserId($user_id);
            $from_unix = ($from != "")
                ? (new ilDateTime($from . " 00:00:00", IL_CAL_DATETIME))->get(IL_CAL_UNIX)
                : null;
            $to_unix = ($to != "")
                ? (new ilDateTime($to . " 23:59:59", IL_CAL_DATETIME))->get(IL_CAL_UNIX)
                : null;
            $classes = (is_array($classes))
                ? array_filter($classes, function ($i) {
                    return ($i != "");
                })
                : null;
            if (count($classes) == 0) {
                $classes = null;
            }
            $tpl->setVariable("LHIST", $hist_gui->getEmbeddedHtml($from_unix, $to_unix, $classes, $a_mode));
            return $tpl->get();
        }

        return ilPCLearningHistoryGUI::getPlaceHolderPresentation();
    }
}
