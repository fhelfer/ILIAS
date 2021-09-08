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
 ********************************************************************
 */

/**
 * Virtual skill tree explorer
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 */
class ilVirtualSkillTreeExplorerGUI extends ilExplorerBaseGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    protected ilLanguage $lng;
    protected ilVirtualSkillTree $vtree;

    protected bool $show_draft_nodes = false;
    protected bool $show_outdated_nodes = false;

    public function __construct(string $a_id, $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        parent::__construct($a_id, $a_parent_obj, $a_parent_cmd);

        $this->vtree = new ilVirtualSkillTree();
        
        $this->setSkipRootNode(false);
        $this->setAjax(false);
    }

    public function setShowDraftNodes(bool $a_val) : void
    {
        $this->show_draft_nodes = $a_val;
        $this->vtree->setIncludeDrafts($a_val);
    }

    public function getShowDraftNodes() : bool
    {
        return $this->show_draft_nodes;
    }

    public function setShowOutdatedNodes(bool $a_val) : void
    {
        $this->show_outdated_nodes = $a_val;
        $this->vtree->setIncludeOutdated($a_val);
    }

    public function getShowOutdatedNodes() : bool
    {
        return $this->show_outdated_nodes;
    }

    public function getRootNode() : array
    {
        return $this->vtree->getRootNode();
    }

    /**
     * @param array|object $a_node
     * @return string
     */
    public function getNodeId($a_node) : string
    {
        return $a_node["id"];
    }

    /**
     * @inheritdoc
     */
    public function getDomNodeIdForNodeId($a_node_id) : string
    {
        return parent::getDomNodeIdForNodeId(str_replace(":", "_", $a_node_id));
    }

    /**
     * @inheritdoc
     */
    public function getNodeIdForDomNodeId($a_dom_node_id) : string
    {
        $id = parent::getNodeIdForDomNodeId($a_dom_node_id);
        return str_replace("_", ":", $id);
    }

    /**
     * @param string $a_parent_node_id
     * @return array
     */
    public function getChildsOfNode($a_parent_node_id) : array
    {
        return $this->vtree->getChildsOfNode($a_parent_node_id);
    }

    /**
     * @param array|object $a_node
     * @return string
     */
    public function getNodeContent($a_node) : string
    {
        $lng = $this->lng;

        $a_parent_id_parts = explode(":", $a_node["id"]);
        $a_parent_skl_tree_id = $a_parent_id_parts[0];
        $a_parent_skl_template_tree_id = $a_parent_id_parts[1];
        
        // title
        $title = $a_node["title"];
        
        // root?
        if ($a_node["type"] == "skrt") {
            $lng->txt("skmg_skills");
        } elseif ($a_node["type"] == "sktr") {
            //				$title.= " (".ilSkillTreeNode::_lookupTitle($a_parent_skl_template_tree_id).")";
        }
        
        return $title;
    }

    /**
     * @param array|object $a_node
     * @return string
     */
    public function getNodeIcon($a_node) : string
    {
        $a_id_parts = explode(":", $a_node["id"]);
        $a_skl_template_tree_id = $a_id_parts[1];

        // root?
        if ($a_node["type"] == "skrt") {
            $icon = ilUtil::getImagePath("icon_scat.svg");
        } else {
            $type = $a_node["type"];
            if ($type == "sktr") {
                $type = ilSkillTreeNode::_lookupType($a_skl_template_tree_id);
            }
            if ($type == "sktp") {
                $type = "skll";
            }
            if ($type == "sctp") {
                $type = "scat";
            }
            $icon = ilUtil::getImagePath("icon_" . $type . ".svg");
        }
        
        return $icon;
    }

    /**
     * @param array|object $a_node
     * @return string
     */
    public function getNodeHref($a_node) : string
    {
        $ilCtrl = $this->ctrl;
        
        // we have a tree id like <skl_tree_id>:<skl_template_tree_id> here
        // use this, if you want a "common" skill id in format <skill_id>:<tref_id>
        $id_parts = explode(":", $a_node["id"]);
        if ($id_parts[1] == 0) {
            // skill in main tree
            $skill_id = $a_node["id"];
        } else {
            // skill in template
            $skill_id = $id_parts[1] . ":" . $id_parts[0];
        }
        
        return "";
    }

    /**
     * @param array|object $a_node
     * @return bool
     */
    public function isNodeClickable($a_node) : bool
    {
        return false;
    }

    /**
     * @param array|object $a_node
     * @return string
     */
    public function getNodeIconAlt($a_node) : string
    {
        $lng = $this->lng;

        if ($lng->exists("skmg_" . $a_node["type"])) {
            return $lng->txt("skmg_" . $a_node["type"]);
        }

        return $lng->txt($a_node["type"]);
    }

}
