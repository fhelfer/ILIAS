<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Skill tresholds for 360 surveys
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesSurvey
 */
class ilSurveySkillThresholds
{
    /**
     * @var ilDB
     */
    protected $db;

    
    /**
     * Constructor
     *
     * @param
     * @return
     */
    public function __construct(ilObjSurvey $a_survey)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->survey = $a_survey;
        $this->read();
    }
    
    /**
     * Read
     *
     * @param
     * @return
     */
    public function read()
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query(
            "SELECT * FROM svy_skill_threshold " .
            " WHERE survey_id = " . $ilDB->quote($this->survey->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->threshold[$rec['level_id']][$rec['tref_id']] =
                $rec['threshold'];
        }
    }

    /**
     * Get thresholds
     *
     * @param
     * @return
     */
    public function getThresholds()
    {
        return $this->threshold;
    }
    
    /**
     * Write threshold
     *
     * @param
     * @return
     */
    public function writeThreshold($a_base_skill_id, $a_tref_id, $a_level_id, $a_threshold)
    {
        $ilDB = $this->db;
        
        $ilDB->replace(
            "svy_skill_threshold",
            array("survey_id" => array("integer", $this->survey->getId()),
                "base_skill_id" => array("integer", (int) $a_base_skill_id),
                "tref_id" => array("integer", (int) $a_tref_id),
                "level_id" => array("integer", (int) $a_level_id)
                ),
            array("threshold" => array("integer", (int) $a_threshold))
        );
    }

    public function cloneTo(ilObjSurvey $target_survey, array $mapping) : void
    {
        $target_thresholds = new self($target_survey);
        $set = $this->db->queryF("SELECT * FROM svy_skill_threshold " .
            " WHERE survey_id = %s ",
            ["integer"],
            [$this->survey->getId()]
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            $target_thresholds->writeThreshold(
                (int) $rec["base_skill_id"],
                (int) $rec["tref_id"],
                (int) $rec["level_id"],
                (int) $rec["threshold"]
            );
        }
    }
}
