<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCAMDPageLisdatat
*
* Advanced MD page list content object (see ILIAS DTD)
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilPCListItem.php 22210 2009-10-26 09:46:06Z akill $
*
* @ingroup ModulesWiki
*/
class ilPCAMDPageList extends ilPageContent
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilLanguage
     */
    protected $lng;

    public $dom;

    /**
    * Init page content component.
    */
    public function init()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->setType("amdpl");

        $this->ref_id = (int) $_GET["ref_id"];
    }
    
    /**
     * Get lang vars needed for editing
     * @return array array of lang var keys
     */
    public static function getLangVars()
    {
        return array("ed_insert_amd_page_list", "pc_amdpl");
    }

    /**
    * Set node
    */
    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->amdpl_node = $a_node->first_child();		// this is the courses node
    }

    /**
    * Create list node in xml.
    *
    * @param	object	$a_pg_obj		Page Object
    * @param	string	$a_hier_id		Hierarchical ID
    */
    public function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
    {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->amdpl_node = $this->dom->create_element("AMDPageList");
        $this->amdpl_node = $this->node->append_child($this->amdpl_node);
    }

    /**
     * Set list settings
     */
    public function setData(array $a_fields_data, $a_mode = null)
    {
        $ilDB = $this->db;
        
        $data_id = $this->amdpl_node->get_attribute("Id");
        if ($data_id) {
            $ilDB->manipulate("DELETE FROM pg_amd_page_list" .
                " WHERE id = " . $ilDB->quote($data_id, "integer"));
        } else {
            $data_id = $ilDB->nextId("pg_amd_page_list");
            $this->amdpl_node->set_attribute("Id", $data_id);
        };
        
        $this->amdpl_node->set_attribute("Mode", (int) $a_mode);
        
        foreach ($a_fields_data as $field_id => $field_data) {
            $fields = array(
                "id" => array("integer", $data_id)
                ,"field_id" => array("integer", $field_id)
                ,"sdata" => array("text", serialize($field_data))
            );
            $ilDB->insert("pg_amd_page_list", $fields);
        }
    }

    public function getMode()
    {
        if (is_object($this->amdpl_node)) {
            return (int) $this->amdpl_node->get_attribute("Mode");
        }
    }
    
    /**
     * Get filter field values
     *
     * @param int $a_data_id
     * @return string
     */
    public function getFieldValues($a_data_id = null)
    {
        $ilDB = $this->db;
            
        $res = array();
        
        if (!$a_data_id) {
            if (is_object($this->amdpl_node)) {
                $a_data_id = $this->amdpl_node->get_attribute("Id");
            }
        }
    
        if ($a_data_id) {
            $set = $ilDB->query("SELECT * FROM pg_amd_page_list" .
                " WHERE id = " . $ilDB->quote($a_data_id, "integer"));
            while ($row = $ilDB->fetchAssoc($set)) {
                $res[$row["field_id"]] = unserialize($row["sdata"]);
            }
        }
        
        return $res;
    }
    
    public static function handleCopiedContent(DOMDocument $a_domdoc, $a_self_ass = true, $a_clone_mobs = false)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        // #15688
        
        $xpath = new DOMXPath($a_domdoc);
        $nodes = $xpath->query("//AMDPageList");
        foreach ($nodes as $node) {
            $old_id = $node->getAttribute("Id");
            break;
        }
        
        if ($old_id) {
            $new_id = $ilDB->nextId("pg_amd_page_list");

            $set = $ilDB->query("SELECT * FROM pg_amd_page_list" .
                    " WHERE id = " . $ilDB->quote($old_id, "integer"));
            while ($row = $ilDB->fetchAssoc($set)) {
                $fields = array(
                    "id" => array("integer", $new_id)
                    ,"field_id" => array("integer", $row["field_id"])
                    ,"sdata" => array("text", $row["sdata"])
                );
                $ilDB->insert("pg_amd_page_list", $fields);
            }
            
            $node->setAttribute("Id", $new_id);
        }
    }
    
    
    //
    // presentation
    //
    
    protected function findPages($a_list_id)
    {
        $ilDB = $this->db;
        
        $list_values = $this->getFieldValues($a_list_id);
        $wiki_id = $this->getPage()->getWikiId();

        $found_result = array();

        // only search in active fields
        $found_ids = null;
        $recs = ilAdvancedMDRecord::_getSelectedRecordsByObject("wiki", $this->ref_id, "wpg");
        foreach ($recs as $record) {
            foreach (ilAdvancedMDFieldDefinition::getInstancesByRecordId($record->getRecordId(), true) as $field) {
                if (isset($list_values[$field->getFieldId()])) {
                    $field_form = ilADTFactory::getInstance()->getSearchBridgeForDefinitionInstance($field->getADTDefinition(), true, false);
                    $field->setSearchValueSerialized($field_form, $list_values[$field->getFieldId()]);
                    $found_pages = $field->searchSubObjects($field_form, $wiki_id, "wpg");
                    if (is_array($found_ids)) {
                        $found_ids = array_intersect($found_ids, $found_pages);
                    } else {
                        $found_ids = $found_pages;
                    }
                }
            }
        }
        
        if (is_array($found_ids) && count($found_ids) > 0) {
            $sql = "SELECT id,title FROM il_wiki_page" .
                " WHERE " . $ilDB->in("id", $found_ids, "", "integer") .
                " ORDER BY title";
            $set = $ilDB->query($sql);
            while ($row = $ilDB->fetchAssoc($set)) {
                $found_result[$row["id"]] = $row["title"];
            }
        }
            
        return $found_result;
    }

    /**
     * @inheritDoc
     */
    public function modifyPageContentPostXsl($a_html, $a_mode, $a_abstract_only = false)
    {
        $lng = $this->lng;
        
        if ($this->getPage()->getParentType() != "wpg") {
            return $a_html;
        }
                            
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
        include_once('Modules/Wiki/classes/class.ilWikiUtil.php');
        
        $wiki_id = $this->getPage()->getWikiId();
        
        $c_pos = 0;
        $start = strpos($a_html, "[[[[[AMDPageList;");
        if (is_int($start)) {
            $end = strpos($a_html, "]]]]]", $start);
        }
        $i = 1;
        while ($end > 0) {
            $parts = explode(";", substr($a_html, $start + 17, $end - $start - 17));
            
            $list_id = (int) $parts[0];
            $list_mode = (sizeof($parts) == 2)
                ? (int) $parts[1]
                : 0;
            
            $ltpl = new ilTemplate("tpl.wiki_amd_page_list.html", true, true, "Modules/Wiki");
                
            $pages = $this->findPages($list_id);
            if (sizeof($pages)) {
                $ltpl->setCurrentBlock("page_bl");
                foreach ($pages as $page_id => $page_title) {
                    // see ilWikiUtil::makeLink()
                    $frag = new stdClass;
                    $frag->mFragment = null;
                    $frag->mTextform = $page_title;
                
                    $ltpl->setVariable("PAGE", ilWikiUtil::makeLink($frag, $wiki_id, $page_title));
                    $ltpl->parseCurrentBlock();
                }
            } else {
                $ltpl->touchBlock("no_hits_bl");
            }
            
            $ltpl->setVariable("LIST_MODE", $list_mode ? "ol" : "ul");
                                            
            $a_html = substr($a_html, 0, $start) .
                $ltpl->get() .
                substr($a_html, $end + 5);
            
            $start = strpos($a_html, "[[[[[AMDPageList;", $start + 5);
            $end = 0;
            if (is_int($start)) {
                $end = strpos($a_html, "]]]]]", $start);
            }
        }
                
        return $a_html;
    }
    
    /**
     * Migrate search/filter values on advmd change. In the mapping, keys are
     * indices of old options, and values indices of associated new options.
     * @param int   $a_field_id
     * @param string[] $mapping
     */
    public static function migrateField(
        int $a_field_id,
        array $mapping
    ): void {
        global $DIC;

        $ilDB = $DIC->database();
        
        // this does only work for select and select multi
        
        $set = $ilDB->query("SELECT * FROM pg_amd_page_list" .
            " WHERE field_id = " . $ilDB->quote($a_field_id, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $data = unserialize(unserialize($row["sdata"], ["allowed_classes" => false]), ["allowed_classes" => false]);
            if (!is_array($data)) {
                continue;
            }
            $updated_data = $data;
            foreach ($mapping as $old_option => $new_option) {
                if (!in_array($old_option, $data)) {
                    continue;
                }
                $idx = array_search($old_option, $data);
                if ($new_option !== '') {
                    $updated_data[$idx] = $new_option;
                } else {
                    unset($updated_data[$idx]);
                }
            }
            $fields = array(
                "sdata" => array("text", serialize(serialize($updated_data)))
            );
            $primary = array(
                "id" => array("integer", $row["id"]),
                "field_id" => array("integer", $row["field_id"])
            );
            $ilDB->update("pg_amd_page_list", $fields, $primary);
        }
    }
}
