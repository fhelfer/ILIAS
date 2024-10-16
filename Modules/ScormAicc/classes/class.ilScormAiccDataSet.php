<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

include_once("./Services/DataSet/classes/class.ilDataSet.php");

class ilScormAiccDataSet extends ilDataSet
{

    /**
     * @var Container $DIC
     */
    private $dic;

    /**
     * @var string
     */
    private $db_table = "sahs_lm";

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var array
     */
    private $_archive = [];

    /**
     * @var array
     */
    private $element_db_mapping = [];

    /**
     * @var string[][]
     */
    public $properties = [
        //"OfflineZipCreated" => "datetime",
        "Id" => ["db_col" => "id", "db_type" => "integer"],
        //"EntryPage" => "integer",
        "APIAdapterName" => ["db_col" => "api_adapter", "db_type" => "text"],
        "APIFunctionsPrefix" => ["db_col" => "api_func_prefix", "db_type" => "text"],
        "AssignedGlossary" => ["db_col" => "glossary", "db_type" => "integer"],
        "AutoContinue" => ["db_col" => "auto_continue", "db_type" => "text"],
        "AutoReviewChar" => ["db_col" => "auto_review", "db_type" => "text"],
        "AutoSuspend" => ["db_col" => "auto_suspend", "db_type" => "text"],
        "Auto_last_visited" => ["db_col" => "auto_last_visited", "db_type" => "text"],
        "Check_values" => ["db_col" => "check_values", "db_type" => "text"],
        "Comments" => ["db_col" => "comments", "db_type" => "text"],
        "CreditMode" => ["db_col" => "credit", "db_type" => "text"],
        "Debug" => ["db_col" => "debug", "db_type" => "text"],
        "DebugPw" => ["db_col" => "debugpw", "db_type" => "text"],
        "DefaultLessonMode" => ["db_col" => "default_lesson_mode", "db_type" => "text"],
        "Editable" => ["db_col" => "editable", "db_type" => "integer"],
        "Fourth_edition" => ["db_col" => "fourth_edition", "db_type" => "text"],
        "Height" => ["db_col" => "height", "db_type" => "integer"],
        "HideNavig" => ["db_col" => "hide_navig", "db_type" => "text"],
        "Ie_force_render" => ["db_col" => "ie_force_render", "db_type" => "text"],
        "Interactions" => ["db_col" => "interactions", "db_type" => "text"],
        "Localization" => ["db_col" => "localization", "db_type" => "text"],
        "MasteryScore" => ["db_col" => "mastery_score", "db_type" => "integer"],
        "MaxAttempt" => ["db_col" => "max_attempt", "db_type" => "integer"],
        "ModuleVersion" => ["db_col" => "module_version", "db_type" => "integer"],
        "NoMenu" => ["db_col" => "no_menu", "db_type" => "text"],
        "Objectives" => ["db_col" => "objectives", "db_type" => "text"],
        "OfflineMode" => ["db_col" => "offline_mode", "db_type" => "text"],
        "OpenMode" => ["db_col" => "open_mode", "db_type" => "integer"],
        "Sequencing" => ["db_col" => "sequencing", "db_type" => "text"],
        "SequencingExpertMode" => ["db_col" => "seq_exp_mode", "db_type" => "integer"],
        "Session" => ["db_col" => "unlimited_session", "db_type" => "text"],
        "StyleSheetId" => ["db_col" => "stylesheet", "db_type" => "integer"],
        "SubType" => ["db_col" => "c_type", "db_type" => "text"],
        "Time_from_lms" => ["db_col" => "time_from_lms", "db_type" => "text"],
        "Tries" => ["db_col" => "question_tries", "db_type" => "integer"],
        "Width" => ["db_col" => "width", "db_type" => "integer"],
        "IdSetting" => ["db_col" => "id_setting", "db_type" => "integer"],
        "NameSetting" => ["db_col" => "name_setting", "db_type" => "integer"]
    ];

    public function __construct()
    {
        global $DIC; /** @var Container $DIC */
        $this->dic = $DIC;
        parent::__construct();

        foreach ($this->properties as $key => $value) {
            $this->element_db_mapping[$value["db_col"]] = $key;
        }
    }

    /**
     * Read data
     * @param
     * @return
     */
    public function readData($a_entity, $a_version, $a_id, $a_field = "")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $obj_id = $a_id;
        $columns = [];
        foreach ($this->properties as $property) {
            array_push($columns, $property["db_col"]);
        }

        $query = "SELECT " . implode(",", $columns) . " FROM " . $this->db_table;
        $query .= " WHERE id=" . $ilDB->quote($obj_id, "integer");
        $result = $ilDB->query($query);
        $this->data = [];
        if ($dataset = $ilDB->fetchAssoc($result)) {
            $this->data = $dataset;
        }

        $query = "SELECT title,description FROM object_data";
        $query .= " WHERE obj_id=" . $ilDB->quote($obj_id, "integer");
        $result = $ilDB->query($query);
        while ($dataset = $ilDB->fetchAssoc($result)) {
            $this->data ["title"] = $dataset["title"];
            $this->data ["description"] = $dataset["description"];
        }
    }
    
    /**
     * Write properties for imported object (actually updates !!)
     * @param
     * $data contains imported module properties from xml file
     * @return
     */
    public function writeData($a_entity, $a_version, $a_id, $data = [])
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];
        if (count($data) > 0) {
            $columns = [];
            foreach ($this->properties as $key => $value) {
                if ($key == "Id" || $key == "title" || $key == "description") {
                    continue;
                }
                //fix localization and mastery_score
                if ($key == "MasteryScore" && $data[$key][0] == 0) {
                    continue;
                }
                if ($key == "Localization" && $data[$key][0] == "") {
                    continue;
                }
                //end fix
                if (isset($data[$key])) {
                    $columns [$value["db_col"]] = [$value["db_type"], $data[$key]];
                }
            }
            if (count($columns) > 0) {
                $conditions ["id"] = ["integer", $a_id];
                $ilDB->update($this->db_table, $columns, $conditions);
            }

            //setting title and description in table object_data
            $od_table = "object_data";
            $od_properties = [
                "Title" => ["db_col" => "title", "db_type" => "text"],
                "Description" => ["db_col" => "description", "db_type" => "text"]
            ];
            foreach ($od_properties as $key => $value) {
                if (isset($data[$key])) {
                    $od_columns [$value["db_col"]] = [$value["db_type"], $data[$key]];
                }

                if (isset($od_columns) && count($od_columns) > 0) {
                    $od_conditions ["obj_id"] = ["integer", $a_id];
                    $ilDB->update("object_data", $od_columns, $od_conditions);
                }
            }
        } else {
            $ilLog->write("no module properties for imported object");
        }
    }

    /* retrieve element name by database column name
     */
    public function getElementNameByDbColumn($db_col_name)
    {
        if ($db_col_name == "title") {
            return "Title";
        }
        if ($db_col_name == "description") {
            return "Description";
        }
        return $this->element_db_mapping[$db_col_name];
    }

    /**
     * own getXmlRepresentation function to embed zipfile in xml
     *
     * @param $a_entity
     * @param $a_schema_version
     * @param $a_ids (obj_id)
     * @param string $a_field
     * @param bool $a_omit_header
     * @param bool $a_omit_types
     * @return string|void
     */
    public function getExtendedXmlRepresentation($a_entity, $a_schema_version, int $a_ids, string $a_field = "", bool $a_omit_header = false, bool $a_omit_types = false)
    {
        global $DIC; /** @var Container $DIC */

        $GLOBALS["ilLog"]->write(json_encode($this->getTypes("sahs", "5.1.0"), JSON_PRETTY_PRINT));

        $this->dircnt = 1;

        $this->readData($a_entity, $a_schema_version, $a_ids);
        $id = $this->data["id"];

        // requirements
        require_once(dirname(__DIR__, 3) . "/Services/Export/classes/class.ilExport.php");
        require_once(dirname(__DIR__, 3) . "/Services/Xml/classes/class.ilXmlWriter.php");


        // prepare archive skeleton
        $objTypeAndId = "sahs_" . $id;
        $this->_archive['directories'] = [
            "exportDir" => ilExport::_getExportDirectory($id)
            ,"tempDir" => ilExport::_getExportDirectory($id) . "/temp"
            ,"archiveDir" => time() . "__" . IL_INST_ID . "__" . $objTypeAndId
            ,"moduleDir" => $objTypeAndId
        ];

        $this->_archive['files'] = [
            "properties" => "properties.xml",
            "metadata" => "metadata.xml",
            "manifest" => 'manifest.xml',
            'scormFile' => "content.zip"
        ];

        // Prepare temp storage on the local filesystem
        if (!file_exists($this->_archive['directories']['exportDir'])) {
            mkdir($this->_archive['directories']['exportDir'], 0755, true);
            //$DIC->filesystem()->storage()->createDir($this->_archive['directories']['tempDir']);
        }
        if (!file_exists($this->_archive['directories']['tempDir'])) {
            mkdir($this->_archive['directories']['tempDir'], 0755, true);
        }

        // build metadata xml file
        file_put_contents(
            $this->_archive['directories']['tempDir'] . "/" . $this->_archive['files']['metadata'],
            $this->buildMetaData($id)
        );

        // build manifest xml file
        file_put_contents(
            $this->_archive['directories']['tempDir'] . "/" . $this->_archive['files']['manifest'],
            $this->buildManifest()
        );

        // build content zip file
        if (isset($this->_archive['files']['scormFile'])) {
            $lmDir = ilUtil::getWebspaceDir("filesystem") . "/lm_data/lm_" . $id;
            ilUtil::zip($lmDir, $this->_archive['directories']['tempDir'] . "/" . substr($this->_archive['files']['scormFile'], 0, -4), true);
        }

        // build property xml file
        file_put_contents(
            $this->_archive['directories']['tempDir'] . "/" . $this->_archive['files']['properties'],
            $this->buildProperties($a_entity, $a_omit_header)
        );

        // zip tempDir and append to export folder
        $fileName = $this->_archive['directories']['exportDir'] . "/" . $this->_archive['directories']['archiveDir'] . ".zip";
        $zArchive = new ZipArchive();
        if ($zArchive->open($fileName, ZipArchive::CREATE) !== true) {
            exit("cannot open <$fileName>\n");
        }
        $zArchive->addFile(
            $this->_archive['directories']['tempDir'] . "/" . $this->_archive['files']['properties'],
            $this->_archive['directories']['archiveDir'] . '/properties.xml'
        );
        $zArchive->addFile(
            $this->_archive['directories']['tempDir'] . "/" . $this->_archive['files']['manifest'],
            $this->_archive['directories']['archiveDir'] . '/' . "manifest.xml"
        );
        $zArchive->addFile(
            $this->_archive['directories']['tempDir'] . "/" . $this->_archive['files']['metadata'],
            $this->_archive['directories']['archiveDir'] . '/' . "metadata.xml"
        );
        if (isset($this->_archive['files']['scormFile'])) {
            $zArchive->addFile(
                $this->_archive['directories']['tempDir'] . "/" . $this->_archive['files']['scormFile'],
                $this->_archive['directories']['archiveDir'] . '/content.zip'
            );
        }
        $zArchive->close();

        // unlink tempDir and its content
        unlink($this->_archive['directories']['tempDir'] . "/metadata.xml");
        unlink($this->_archive['directories']['tempDir'] . "/manifest.xml");
        unlink($this->_archive['directories']['tempDir'] . "/properties.xml");
        if (isset($this->_archive['files']['scormFile']) && file_exists($this->_archive['directories']['tempDir'] . "/content.zip")) {
            unlink($this->_archive['directories']['tempDir'] . "/content.zip");
        }

        return $fileName;
    }



    public function buildMetaData($id)
    {
        require_once("Services/MetaData/classes/class.ilMD2XML.php");
        $md2xml = new ilMD2XML($id, $id, "sahs");
        $md2xml->startExport();
        $xml = $md2xml->getXML();
        return $xml;
    }

    /**
     * Get field types for entity
     *
     * @param string $a_entity entity
     * @param string $a_version version number
     * @return array types array
     */
    protected function getTypes($a_entity, $a_version)
    {
        if ($a_entity == "sahs") {
            switch ($a_version) {
            case "5.1.0":
                $types = [];
                foreach ($this->properties as $key => $value) {
                    $types[$key] = $value["db_type"];
                }
                return $types;
                break;
            }
        }
    }

    /**
     * Get xml namespace
     * @param
     * @return
     */
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return "http://www.ilias.de/xml/Modules/ScormAicc/" . $a_entity;
    }
    
    public function getDependencies()
    {
        return null;
    }

    public function getSupportedVersions()
    {
        return ["5.1.0"];
    }

    /**
     * @return string
     */
    private function buildManifest() : string
    {
        $manWriter = new ilXmlWriter();
        $manWriter->xmlHeader();
        foreach ($this->_archive['files'] as $key => $value) {
            $manWriter->xmlElement($key, null, $value, true, true);
        }

        return $manWriter->xmlDumpMem(true);
    }

    /**
     * @param $a_entity
     * @param bool $a_omit_header
     * @return string
     */
    private function buildProperties($a_entity, $a_omit_header = false)
    {
        $writer = new ilXmlWriter();

        if (!$a_omit_header) {
            $writer->xmlHeader();
        }

        $writer->appendXML("\n");
        $writer->xmlStartTag('DataSet', array(
            "InstallationId" => IL_INST_ID,
            "InstallationUrl" => ILIAS_HTTP_PATH,
            "TopEntity" => $a_entity
        ));

        $writer->appendXML("\n");

        foreach ($this->data as $key => $value) {
            $writer->xmlElement($this->getElementNameByDbColumn($key), null, $value, true, true);
            $writer->appendXML("\n");
        }

        $writer->xmlEndTag("DataSet");

        return $writer->xmlDumpMem(false);
    }
}
