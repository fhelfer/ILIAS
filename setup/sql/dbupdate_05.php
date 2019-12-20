<#5432>
<?php
$template = 'il_lso_admin';
$perms = [
    'create_htlm',
    'create_iass',
    'create_copa',
    'create_svy',
    'create_lm',
    'create_exc',
    'create_tst',
    'create_sahs',
    'create_file',
    'participate',
    'unparticipate',
    'edit_learning_progress',
    'manage_members',
    'copy'
];

$query = "SELECT obj_id FROM object_data"
    . " WHERE object_data.type = " . $ilDB->quote('rolt', 'text')
    . " AND title = " . $ilDB->quote($template, 'text');
$result = $ilDB->query($query);
$row = $ilDB->fetchAssoc($result);
$rol_id = array_shift($row);

$op_ids = [];
$query = "SELECT ops_id FROM rbac_operations"
    . " WHERE operation IN ('"
    . implode("', '", $perms)
    . "')";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $op_ids[] = $row['ops_id'];
}

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::setRolePermission($rol_id, 'lso', $op_ids, ROLE_FOLDER_ID);
?>

<#5433>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$template = 'il_lso_member';
$op_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('unparticipate');

$query = "SELECT obj_id FROM object_data"
    . " WHERE object_data.type = " . $ilDB->quote('rolt', 'text')
    . " AND title = " . $ilDB->quote($template, 'text');
$result = $ilDB->query($query);
$row = $ilDB->fetchAssoc($result);
$rol_id = array_shift($row);

ilDBUpdateNewObjectType::setRolePermission($rol_id, 'lso', [$op_id], ROLE_FOLDER_ID);
?>
<#5434>
<?php
if ($ilDB->tableExists('license_data')) {
    $ilDB->dropTable('license_data');
}
?>
<#5435>
<?php
$ilDB->manipulateF(
    'DELETE FROM settings WHERE module = %s',
    ['text'],
    ['license']
);
?>
<#5436>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5437>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5438>
<?php
require_once 'Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
ilDBUpdateNewObjectType::applyInitialPermissionGuideline('iass', true, false);
?>
<#5439>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5440>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5441>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5442>
<?php
$set = $ilDB->queryF("SELECT DISTINCT s.user_id FROM skl_personal_skill s LEFT JOIN usr_data u ON (s.user_id = u.usr_id) " .
    " WHERE u.usr_id IS NULL ", [], []);
$user_ids = [];
while ($rec = $ilDB->fetchAssoc($set)) {
    $user_ids[] = $rec["user_id"];
}
if (count($user_ids) > 0) {
    $ilDB->manipulate("DELETE FROM skl_personal_skill WHERE "
        . $ilDB->in("user_id", $user_ids, false, "integer"));
}
?>
<#5443>
<?php
$set = $ilDB->queryF("SELECT DISTINCT s.user_id FROM skl_assigned_material s LEFT JOIN usr_data u ON (s.user_id = u.usr_id) " .
    " WHERE u.usr_id IS NULL ", [], []);
$user_ids = [];
while ($rec = $ilDB->fetchAssoc($set)) {
    $user_ids[] = $rec["user_id"];
}
if (count($user_ids) > 0) {
    $ilDB->manipulate("DELETE FROM skl_assigned_material WHERE "
        . $ilDB->in("user_id", $user_ids, false, "integer"));
}
?>
<#5444>
<?php
$set = $ilDB->queryF("SELECT DISTINCT s.user_id FROM skl_profile_user s LEFT JOIN usr_data u ON (s.user_id = u.usr_id) " .
    " WHERE u.usr_id IS NULL ", [], []);
$user_ids = [];
while ($rec = $ilDB->fetchAssoc($set)) {
    $user_ids[] = $rec["user_id"];
}
if (count($user_ids) > 0) {
    $ilDB->manipulate("DELETE FROM skl_profile_user WHERE "
        . $ilDB->in("user_id", $user_ids, false, "integer"));
}
?>
<#5445>
<?php
$set = $ilDB->queryF("SELECT DISTINCT s.user_id FROM skl_user_skill_level s LEFT JOIN usr_data u ON (s.user_id = u.usr_id) " .
    " WHERE u.usr_id IS NULL ", [], []);
$user_ids = [];
while ($rec = $ilDB->fetchAssoc($set)) {
    $user_ids[] = $rec["user_id"];
}
if (count($user_ids) > 0) {
    $ilDB->manipulate("DELETE FROM skl_user_skill_level WHERE "
        . $ilDB->in("user_id", $user_ids, false, "integer"));
}
?>
<#5446>
<?php
$set = $ilDB->queryF("SELECT DISTINCT s.user_id FROM skl_user_has_level s LEFT JOIN usr_data u ON (s.user_id = u.usr_id) " .
    " WHERE u.usr_id IS NULL ", [], []);
$user_ids = [];
while ($rec = $ilDB->fetchAssoc($set)) {
    $user_ids[] = $rec["user_id"];
}
if (count($user_ids) > 0) {
    $ilDB->manipulate("DELETE FROM skl_user_has_level WHERE "
        . $ilDB->in("user_id", $user_ids, false, "integer"));
}
?>
<#5447>
<?php
//FIX 0020168: Delete orgus in Trash - Organisational units could not be restored from trash / imports lead to ambiguous import_ids
$set = $ilDB->query("SELECT * FROM object_data as obj inner join object_reference as ref on ref.obj_id = obj.obj_id and ref.deleted is not null where type = 'orgu'");
while ($rec = $ilDB->fetchAssoc($set)) {
    $ilDB->manipulate("DELETE FROM object_data where obj_id = " . $ilDB->quote($rec['obj_id'], 'integer'));
    $ilDB->manipulate("DELETE FROM object_reference where obj_id = " . $ilDB->quote($rec['obj_id'], 'integer'));
}
?>

<#5448>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$tpl_perms = [
    'il_grp_member' => [
        'participate'
    ],
    'il_crs_member' => [
        'participate'
    ],
    'il_grp_admin' => [
        'participate',
        'unparticipate',
        'manage_members',
        'create_htlm',
        'create_iass',
        'create_copa',
        'create_svy',
        'create_svy',
        'create_lm',
        'create_exc',
        'create_tst',
        'create_sahs',
        'create_file',
        'edit_learning_progress'
    ],
    'il_crs_admin' => [
        'participate',
        'unparticipate',
        'manage_members',
        'create_htlm',
        'create_iass',
        'create_copa',
        'create_svy',
        'create_svy',
        'create_lm',
        'create_exc',
        'create_tst',
        'create_sahs',
        'create_file',
        'edit_learning_progress'
    ],
    'il_crs_tutor' => [
        'participate',
        'unparticipate',
        'manage_members',
        'edit_learning_progress',
        'create_htlm',
        'create_iass',
        'create_copa',
        'create_svy',
        'create_svy',
        'create_lm',
        'create_exc',
        'create_tst',
        'create_sahs',
        'create_file'
    ]
];

foreach ($tpl_perms as $template=>$perms) {
    $query = "SELECT obj_id FROM object_data"
        . " WHERE object_data.type = " . $ilDB->quote('rolt', 'text')
        . " AND title = " . $ilDB->quote($template, 'text');
    $result = $ilDB->query($query);
    $row = $ilDB->fetchAssoc($result);
    $rol_id = array_shift($row);

    $op_ids = [];
    $query = "SELECT ops_id FROM rbac_operations"
        . " WHERE operation IN ('"
        . implode("', '", $perms)
        . "')";
    $result = $ilDB->query($query);
    while ($row = $ilDB->fetchAssoc($result)) {
        $op_ids[] = $row['ops_id'];
    }
    ilDBUpdateNewObjectType::setRolePermission($rol_id, 'lso', $op_ids, ROLE_FOLDER_ID);
}
?>
<#5449>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5450>
<?php
if (!$ilDB->tableColumnExists('tst_tests', 'block_after_passed')) {
    $ilDB->addTableColumn('tst_tests', 'block_after_passed', array(
        'type' => 'integer',
        'notnull' => false,
        'length' => 1,
        'default' => 0
    ));
}
?>

<#5451>
<?php
if (!$ilDB->tableColumnExists('cal_entries', 'context_info')) {
    $ilDB->addTableColumn(
        'cal_entries',
        'context_info',
        [
            'type' => 'text',
            'length' => 255,
            'notnull' => false
        ]
    );
}
?>
<#5452>
<?php
// Create migration table
if (!$ilDB->tableExists('frm_thread_tree_mig')) {
    $fields = [
        'thread_id' => [
            'type'    => 'integer',
            'length'  => 4,
            'notnull' => true,
            'default' => 0
        ]
    ];

    $ilDB->createTable('frm_thread_tree_mig', $fields);
    $ilDB->addPrimaryKey('frm_thread_tree_mig', ['thread_id']);
    $GLOBALS['ilLog']->info(sprintf(
        'Created thread migration table: frm_thread_tree_mig'
    ));
}
?>
<#5453>
<?php
$query = "
	SELECT frmpt.thr_fk
	FROM frm_posts_tree frmpt
	INNER JOIN frm_posts fp ON fp.pos_pk = frmpt.pos_fk
	WHERE frmpt.parent_pos = 0
	GROUP BY frmpt.thr_fk
	HAVING COUNT(frmpt.fpt_pk) > 1
";
$ignoredThreadIds = [];
$res = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($res)) {
    $ignoredThreadIds[$row['thr_fk']] = $row['thr_fk'];
}

$step = 5453;

$query = "
	SELECT fp.*, fpt.fpt_pk, fpt.thr_fk, fpt.lft, fpt.rgt, fpt.fpt_date
	FROM frm_posts_tree fpt
	INNER JOIN frm_posts fp ON fp.pos_pk = fpt.pos_fk
	LEFT JOIN frm_thread_tree_mig ON frm_thread_tree_mig.thread_id = fpt.thr_fk
	WHERE fpt.parent_pos = 0 AND fpt.depth = 1 AND frm_thread_tree_mig.thread_id IS NULL
";
$res = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($res)) {
    $GLOBALS['ilLog']->info(sprintf(
        "Started migration of thread with id %s",
        $row['thr_fk']
    ));
    if (isset($ignoredThreadIds[$row['thr_fk']])) {
        $GLOBALS['ilLog']->warning(sprintf(
            "Cannot migrate forum tree for thread id %s in database update step %s",
            $row['thr_fk'],
            $step
        ));
        continue;
    }

    // Create space for a new root node, increment depth of all nodes, increment lft and rgt values
    $ilDB->manipulateF(
        "
			UPDATE frm_posts_tree
			SET
				lft = lft + 1,
				rgt = rgt + 1,
				depth = depth + 1
			WHERE thr_fk = %s
		",
        ['integer'],
        [$row['thr_fk']]
    );
    $GLOBALS['ilLog']->info(sprintf(
        "Created gaps in tree for thread with id %s in database update step %s",
        $row['thr_fk'],
        $step
    ));

    // Create a posting as new root
    $postId = $ilDB->nextId('frm_posts');
    $ilDB->insert('frm_posts', array(
        'pos_pk'		=> array('integer', $postId),
        'pos_top_fk'	=> array('integer', $row['pos_top_fk']),
        'pos_thr_fk'	=> array('integer', $row['pos_thr_fk']),
        'pos_display_user_id'	=> array('integer', $row['pos_display_user_id']),
        'pos_usr_alias'	=> array('text', $row['pos_usr_alias']),
        'pos_subject'	=> array('text', $row['pos_subject']),
        'pos_message'	=> array('clob', $row['pos_message']),
        'pos_date'		=> array('timestamp', $row['pos_date']),
        'pos_update'	=> array('timestamp', null),
        'update_user'	=> array('integer', 0),
        'pos_cens'		=> array('integer', 0),
        'notify'		=> array('integer', 0),
        'import_name'	=> array('text', (string) $row['import_name']),
        'pos_status'	=> array('integer', 1),
        'pos_author_id' => array('integer', (int) $row['pos_author_id']),
        'is_author_moderator' => array('integer', $row['is_author_moderator']),
        'pos_activation_date' => array('timestamp', $row['pos_activation_date'])
    ));
    $GLOBALS['ilLog']->info(sprintf(
        "Created new root posting with id %s in thread with id %s in database update step %s",
        $postId,
        $row['thr_fk'],
        $step
    ));

    // Insert the new root and, set dept = 1, lft = 1, rgt = <OLR_ROOT_RGT> + 2
    $nextId = $ilDB->nextId('frm_posts_tree');
    $ilDB->manipulateF(
        '
		INSERT INTO frm_posts_tree
		(
			fpt_pk,
			thr_fk,
			pos_fk,
			parent_pos,
			lft,
			rgt,
			depth,
			fpt_date
		) VALUES (%s, %s, %s, %s,  %s,  %s, %s, %s)',
        ['integer','integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'timestamp'],
        [$nextId, $row['thr_fk'], $postId, 0, 1, $row['rgt'] + 2, 1, $row['fpt_date']]
    );
    $GLOBALS['ilLog']->info(sprintf(
        "Created new tree root with id %s in thread with id %s in database update step %s",
        $nextId,
        $row['thr_fk'],
        $step
    ));

    // Set parent_pos for old root
    $ilDB->manipulateF(
        "
			UPDATE frm_posts_tree
			SET
				parent_pos = %s
			WHERE thr_fk = %s AND fpt_pk = %s
		",
        ['integer', 'integer', 'integer'],
        [$nextId, $row['thr_fk'], $row['fpt_pk']]
    );
    $GLOBALS['ilLog']->info(sprintf(
        "Set parent to %s for posting with id %s in thread with id %s in database update step %s",
        $nextId,
        $row['fpt_pk'],
        $row['thr_fk'],
        $step
    ));

    // Mark as migrated
    $ilDB->insert('frm_thread_tree_mig', array(
        'thread_id' => array('integer', $row['thr_fk'])
    ));
}
?>
<#5454>
<?php
// Drop migration table
if ($ilDB->tableExists('frm_thread_tree_mig')) {
    $ilDB->dropTable('frm_thread_tree_mig');
    $GLOBALS['ilLog']->info(sprintf(
        'Dropped thread migration table: frm_thread_tree_mig'
    ));
}
?>
<#5455>
<?php
// Add new index
if (!$ilDB->indexExistsByFields('frm_posts_tree', ['parent_pos'])) {
    $ilDB->addIndex('frm_posts_tree', ['parent_pos'], 'i3');
}
?>
<#5456>
<?php
if (!$ilDB->tableExists('lso_activation')) {
    $ilDB->createTable('lso_activation', array(
        'ref_id' => array(
            "type"    => "integer",
            "length"  => 4,
            'notnull' => true
        ),
        'online' => array(
            "type" => "integer",
            "notnull" => true,
            "length" => 1,
            "default" => 0
        ),
        'activation_start' => array(
            'type' => 'timestamp',
            "notnull" => false
        ),
        'activation_end' => array(
            'type' => 'timestamp',
            "notnull" => false
        )
    ));
    $ilDB->addPrimaryKey("lso_activation", array("ref_id"));
}
?>
<#5457>
<?php
if ($ilDB->tableColumnExists('lso_settings', 'online')) {
    $ilDB->dropTableColumn('lso_settings', 'online');
}
?>
<#5458>
<?php
if (!$ilDB->tableColumnExists('lso_activation', 'effective_online')) {
    $ilDB->addTableColumn(
        'lso_activation',
        'effective_online',
        array(
            "type"    => "integer",
            "notnull" => true,
            "length"  => 1,
            "default" => 0
        )
    );
}
?>
<#5459>
<?php
require_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::updateOperationOrder('participate', 1010);
ilDBUpdateNewObjectType::updateOperationOrder('unparticipate', 1020);
?>
<#5460>
<?php
/**
 * @var $ilDB ilDBInterface
 */
// $ilDB->modifyTableColumn('il_gs_identifications', 'identification', ['length' => 255]);
$ilDB->modifyTableColumn('il_mm_items', 'identification', ['length' => 255]);
?>
<#5461>
<?php
if (!$ilDB->tableColumnExists('qpl_questions', 'lifecycle')) {
    $ilDB->addTableColumn('qpl_questions', 'lifecycle', array(
        'type' => 'text',
        'length' => 16,
        'notnull' => false,
        'default' => 'draft'
    ));

    $ilDB->queryF('UPDATE qpl_questions SET lifecycle = %s', array('text'), array('draft'));
}
?>
<#5462>
<?php
if (!$ilDB->tableColumnExists('tst_rnd_quest_set_qpls', 'lifecycle_filter')) {
    $ilDB->addTableColumn(
        'tst_rnd_quest_set_qpls',
        'lifecycle_filter',
        array('type' => 'text', 'length' => 250, 'notnull'	=> false, 'default'	=> null)
    );
}
?>
<#5463>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_permissions', ['context_id'])) {
    $ilDB->addIndex('il_orgu_permissions', array( 'context_id' ), 'co');
}
?>
<#5464>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_permissions', ['position_id'])) {
    $ilDB->addIndex('il_orgu_permissions', array('position_id'), 'po');
}
?>
<#5465>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_permissions', ['operations'])) {
    $ilDB->modifyTableColumn('il_orgu_permissions', 'operations', array("length" => 256));
}
?>
<#5466>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_ua', ['position_id'])) {
    $ilDB->addIndex('il_orgu_ua', array('position_id'), 'pi');
}
?>
<#5467>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_ua', ['user_id'])) {
    $ilDB->addIndex('il_orgu_ua', array('user_id'), 'ui');
}
?>
<#5468>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_ua', ['orgu_id'])) {
    $ilDB->addIndex('il_orgu_ua', array('orgu_id'), 'oi');
}
?>
<#5469>
<?php
/*if (!$ilDB->indexExistsByFields('il_orgu_permissions', ['operations'])) {
$ilDB->addIndex('il_orgu_permissions', array('operations'), 'oi');
}*/
?>
<#5470>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_ua', ['position_id','orgu_id'])) {
    $ilDB->addIndex('il_orgu_ua', array('position_id','orgu_id'), 'po');
}
?>
<#5471>
<?php
if (!$ilDB->indexExistsByFields('il_orgu_ua', ['position_id','user_id'])) {
    $ilDB->addIndex('il_orgu_ua', array('position_id','user_id'), 'pu');
}
?>
<#5472>
<?php
/*if (!$ilDB->indexExistsByFields('il_orgu_permissions', ['operations','parent_id'])) {
$ilDB->addIndex('il_orgu_permissions', array('operations','parent_id'), 'op');
}*/
?>
<#5473>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$query = "SELECT obj_id FROM object_data"
    . " WHERE object_data.type = " . $ilDB->quote('rolt', 'text')
    . " AND title = " . $ilDB->quote('il_lso_member', 'text');
$result = $ilDB->query($query);
$row = $ilDB->fetchAssoc($result);
$rol_id_member = array_shift($row);

$query = "SELECT obj_id FROM object_data"
    . " WHERE object_data.type = " . $ilDB->quote('rolt', 'text')
    . " AND title = " . $ilDB->quote('il_lso_admin', 'text');
$result = $ilDB->query($query);
$row = $ilDB->fetchAssoc($result);
$rol_id_admin = array_shift($row);

$op_ids = [];
$query = "SELECT operation, ops_id FROM rbac_operations";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $op_ids[$row['operation']] = $row['ops_id'];
}

$types = [
    'copa',
    'exc',
    'file',
    'htlm',
    'sahs',
    'lm',
    'svy',
    'tst'
];

$member_ops = [
    $op_ids['visible'],
    $op_ids['read'],
];
$admin_ops = [
    $op_ids['visible'],
    $op_ids['read'],
    $op_ids['edit_learning_progress'],
    $op_ids['read_learning_progress']
];

foreach ($types as $type) {
    ilDBUpdateNewObjectType::setRolePermission($rol_id_member, $type, $member_ops, ROLE_FOLDER_ID);
    ilDBUpdateNewObjectType::setRolePermission($rol_id_admin, $type, $admin_ops, ROLE_FOLDER_ID);
}

$type_perms = [
    'iass' => [
        $op_ids['visible'],
        $op_ids['read'],
        $op_ids['manage_members'],
        $op_ids['edit_members'],
        $op_ids['edit_learning_progress'],
        $op_ids['read_learning_progress']
    ],
    'exc' => [
        $op_ids['edit_submissions_grades']
    ],
    'svy' => [
        $op_ids['invite'],
        $op_ids['read_results']
    ],
    'tst' => [
        $op_ids['tst_results'],
        $op_ids['tst_statistics']
    ]
];

foreach ($type_perms as $type => $ops) {
    ilDBUpdateNewObjectType::setRolePermission($rol_id_admin, $type, $ops, ROLE_FOLDER_ID);
}
?>
<#5474>
<?php
if (!$ilDB->tableColumnExists('lso_activation', 'activation_start_ts')) {
    $ilDB->addTableColumn(
        'lso_activation',
        'activation_start_ts',
        array(
            "type"    => "integer",
            "notnull" => false,
            "length"  => 4
        )
    );
}
?>
<#5475>
<?php
if (!$ilDB->tableColumnExists('lso_activation', 'activation_end_ts')) {
    $ilDB->addTableColumn(
        'lso_activation',
        'activation_end_ts',
        array(
            "type"    => "integer",
            "notnull" => false,
            "length"  => 4
        )
    );
}
?>
<#5476>
<?php
if ($ilDB->tableColumnExists('lso_activation', 'activation_start')) {
    $ilDB->manipulate(
        'UPDATE lso_activation'
        . '	SET activation_start_ts = UNIX_TIMESTAMP(activation_start)'
        . '	WHERE activation_start IS NOT NULL'
    );
}
?>
<#5477>
<?php
if ($ilDB->tableColumnExists('lso_activation', 'activation_end')) {
    $ilDB->manipulate(
        'UPDATE lso_activation'
        . '	SET activation_end_ts = UNIX_TIMESTAMP(activation_end)'
        . '	WHERE activation_end IS NOT NULL'
    );
}
?>
<#5478>
<?php
if ($ilDB->tableColumnExists('lso_activation', 'activation_start')) {
    $ilDB->dropTableColumn("lso_activation", "activation_start");
}
?>
<#5479>
<?php
if ($ilDB->tableColumnExists('lso_activation', 'activation_end')) {
    $ilDB->dropTableColumn("lso_activation", "activation_end");
}
?>
<#5480>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId('lso');
if ($lp_type_id) {
    $new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('lp_other_users', 'See learning progress overview of other users', 'object', 3595);
    if ($new_ops_id) {
        ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $new_ops_id);
    }
}
?>
<#5481>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5482>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5483>
<?php
if (!$ilDB->tableColumnExists("post_conditions", "condition_operator")) {
    $ilDB->addTableColumn("post_conditions", "condition_operator", [
            "type" => "text",
            "notnull" => false,
            "length" => 32,
            "fixed" => false
    ]);
}

if ($ilDB->tableColumnExists("post_conditions", "condition_type")) {
    $ilDB->manipulate("UPDATE post_conditions SET condition_operator = 'always' WHERE condition_type = 0");
    $ilDB->manipulate("UPDATE post_conditions SET condition_operator = 'finished' WHERE condition_type = 1");
    $ilDB->manipulate("UPDATE post_conditions SET condition_operator = 'passed' WHERE condition_type = 2");
    $ilDB->manipulate("UPDATE post_conditions SET condition_operator = 'failed' WHERE condition_type = 3");

    $ilDB->dropPrimaryKey('post_conditions');
    $ilDB->addPrimaryKey('post_conditions', ['ref_id', 'condition_operator', 'value']);
    $ilDB->dropTableColumn('post_conditions', 'condition_type');
}
?>

<#5484>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId('lso');
if ($lp_type_id) {
    $ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("lp_other_users");
    ilDBUpdateNewObjectType::deleteRBACOperation($lp_type_id, $ops_id);
}

?>

<#5485>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId('lso');
if ($lp_type_id) {
    $ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("read_learning_progress");
    ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $ops_id);
}

?>
<#5486>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#5487>
<?php
    $ilDB->dropPrimaryKey('post_conditions');
    $ilDB->addPrimaryKey('post_conditions', ['ref_id', 'condition_operator', 'value']);
?>

<#5488>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId("lp_other_users");
ilDBUpdateNewObjectType::deleteRBACOperation("lso", $ops_id);

?>
<#5489>
<?php
if (!$ilDB->tableColumnExists('qpl_qst_essay', 'word_cnt_enabled')) {
    $ilDB->addTableColumn('qpl_qst_essay', 'word_cnt_enabled', array(
        'type'    => 'integer',
        'length'  => 1,
        'notnull' => false,
        'default' => 0
    ));
}
?>
<#5490>
<?php
if (!$ilDB->tableColumnExists('exc_assignment_peer', 'is_valid')) {
    $ilDB->addTableColumn('exc_assignment_peer', 'is_valid', array(
        "type" => "integer",
        "notnull" => true,
        "length" => 1,
        "default" => 0
    ));
}
?>
<#5491>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5492>
<?php
if (!$ilDB->tableColumnExists('exc_returned', 'web_dir_access_time')) {
    $ilDB->addTableColumn('exc_returned', 'web_dir_access_time', array(
        'type' => 'timestamp',
        'notnull' => false,
        'default' => null
    ));
}
$ilCtrlStructureReader->getStructure();
?>
<#5493>
<?php
$settings = new \ilSetting('chatroom');
$settings->set('conversation_idle_state_in_minutes', 1);

$res = $ilDB->query("SELECT * FROM chatroom_admconfig");
while ($row = $ilDB->fetchAssoc($res)) {
    $settings = json_decode($row['client_settings'], true);

    if (!is_numeric($settings['conversation_idle_state_in_minutes'])) {
        $settings['conversation_idle_state_in_minutes'] = 1;
    }

    $ilDB->update('chatroom_admconfig', [
        'client_settings' => ['text', json_encode($settings)]
    ], [
        'instance_id' => ['integer', $row['instance_id']]
    ]);
}
?>
<#5494>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5495>
<?php
if ($ilDB->tableColumnExists("map_area", "href")) {
    $field = array(
        'type' 		=> 'text',
        'length' 	=> 800,
        'notnull' 	=> false
    );

    $ilDB->modifyTableColumn("map_area", "href", $field);
}
?>
<#5496>
<?php
if (!$ilDB->tableColumnExists('usr_data', 'passwd_policy_reset')) {
    $ilDB->addTableColumn('usr_data', 'passwd_policy_reset', array(
        'type' => 'integer',
        'notnull' => true,
        'length' => 1,
        'default' => 0
    ));
}
?>
<#5497>
<?php
$ilDB->manipulateF(
    'DELETE FROM settings WHERE keyword = %s',
    ['text'],
    ['block_activated_chatviewer']
);

$ilDB->manipulateF(
    'DELETE FROM usr_pref WHERE keyword = %s',
    ['text'],
    ['chatviewer_last_selected_room']
);
?>
<#5498>
<?php
if ($ilDB->tableColumnExists('mail_saved', 'm_type')) {
    $ilDB->dropTableColumn('mail_saved', 'm_type');
}

if ($ilDB->tableColumnExists('mail', 'm_type')) {
    $ilDB->dropTableColumn('mail', 'm_type');
}

$ilDB->manipulateF(
    'DELETE FROM settings WHERE keyword = %s',
    ['text'],
    ['pd_sys_msg_mode']
);
?>
<#5499>
<?php
$res = $ilDB->queryF('SELECT * FROM rbac_operations WHERE operation = %s', ['text'], ['system_message']);
$row = $ilDB->fetchAssoc($res);

if ($row['ops_id']) {
    $opsId = $row['ops_id'];

    $ilDB->manipulateF('DELETE FROM rbac_templates WHERE ops_id = %s', ['integer'], [$opsId]);
    $ilDB->manipulateF('DELETE FROM rbac_ta WHERE ops_id = %s', ['integer'], [$opsId]);
    $ilDB->manipulateF('DELETE FROM rbac_operations WHERE ops_id = %s', ['integer'], [$opsId]);
}
?>
<#5500>
<?php
$ilDB->manipulateF(
    'DELETE FROM settings WHERE keyword = %s',
    ['text'],
    ['block_activated_pdfrmpostdraft']
);
?>
<#5501>
<?php

$tempTableName = 'tmp_tst_qst_fixparent';

$tempTableFields = array(
        'qst_id' => array(
            'type' => 'integer',
            'notnull' => true,
            'length' => 4,
            'default' => 0
        ),
        'tst_obj_id' => array(
            'type' => 'integer',
            'notnull' => true,
            'length' => 4,
            'default' => 0
        ),
        'qpl_obj_id' => array(
            'type' => 'integer',
            'notnull' => true,
            'length' => 4,
            'default' => 0
        )
);

$brokenFixedTestQuestionsQuery = "
    SELECT qq.question_id qst_id, t.obj_fi tst_obj_id, qq.obj_fi qpl_obj_id
    FROM tst_tests t
    INNER JOIN tst_test_question tq
    ON t.test_id = tq.test_fi
    INNER JOIN qpl_questions qq
    ON qq.question_id = tq.question_fi
    WHERE t.question_set_type = 'FIXED_QUEST_SET'
    AND t.obj_fi != qq.obj_fi
";

$brokenRandomTestQuestionsQuery = "
    SELECT qq.question_id qst_id, t.obj_fi tst_obj_id, qq.obj_fi qpl_obj_id
    FROM tst_tests t
    INNER JOIN tst_rnd_cpy tq
    ON t.test_id = tq.tst_fi
    INNER JOIN qpl_questions qq
    ON qq.question_id = tq.qst_fi
    WHERE t.question_set_type = 'RANDOM_QUEST_SET'
    AND t.obj_fi != qq.obj_fi
";

$brokenQuestionCountQuery = "
    SELECT COUNT(broken.qst_id) cnt FROM (
        SELECT q1.qst_id FROM ( {$brokenFixedTestQuestionsQuery} ) q1
        UNION
        SELECT q2.qst_id FROM ( {$brokenRandomTestQuestionsQuery} ) q2
    ) broken
";

$brokenQuestionSelectQuery = "
    SELECT q1.qst_id, q1.tst_obj_id, q1.qpl_obj_id FROM ( {$brokenFixedTestQuestionsQuery} ) q1
    UNION
    SELECT q2.qst_id, q2.tst_obj_id, q2.qpl_obj_id FROM ( {$brokenRandomTestQuestionsQuery} ) q2
";

$res = $ilDB->query($brokenQuestionCountQuery);
$row = $ilDB->fetchAssoc($res);

if ($ilDB->tableExists($tempTableName)) {
    $ilDB->dropTable($tempTableName);
}

if ($row['cnt'] > 0) {
    $ilDB->createTable($tempTableName, $tempTableFields);
    $ilDB->addPrimaryKey($tempTableName, array('qst_id'));
    $ilDB->addIndex($tempTableName, array('tst_obj_id', 'qpl_obj_id'), 'i1');

    $ilDB->manipulate("
        INSERT INTO {$tempTableName} (qst_id, tst_obj_id, qpl_obj_id) {$brokenQuestionSelectQuery}
    ");
}

?>
<#5502>
<?php

$tempTableName = 'tmp_tst_qst_fixparent';

if ($ilDB->tableExists($tempTableName)) {
    $updateStatement = $ilDB->prepareManip(
        "
        UPDATE qpl_questions SET obj_fi = ? WHERE obj_fi = ? AND question_id IN(
            SELECT qst_id FROM {$tempTableName} WHERE tst_obj_id = ? AND qpl_obj_id = ?
        )
    ",
        array('integer', 'integer', 'integer', 'integer')
    );

    $deleteStatement = $ilDB->prepareManip(
        "
        DELETE FROM {$tempTableName} WHERE tst_obj_id = ? AND qpl_obj_id = ?
    ",
        array('integer', 'integer')
    );

    $res = $ilDB->query("SELECT DISTINCT tst_obj_id, qpl_obj_id FROM {$tempTableName}");

    while ($row = $ilDB->fetchAssoc($res)) {
        $ilDB->execute($updateStatement, array(
            $row['tst_obj_id'], $row['qpl_obj_id'], $row['tst_obj_id'], $row['qpl_obj_id']
        ));

        $ilDB->execute($deleteStatement, array(
            $row['tst_obj_id'], $row['qpl_obj_id']
        ));
    }

    $ilDB->dropTable($tempTableName);
}

?>
<#5503>
<?php
if (!$ilDB->tableExists('cont_filter_field')) {
    $ilDB->createTable('cont_filter_field', array(
        'ref_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'record_set_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'field_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    ));
}
?>
<#5504>
<?php
if (!$ilDB->tableExists('il_cert_bgtask_migr')) {
    $ilDB->dropTable('il_cert_bgtask_migr');
}
?>
<#5505>
<?php
if ($ilDB->tableExists('il_bt_task')) {
    if ($ilDB->tableExists('il_bt_value_to_task')) {
        if ($ilDB->tableExists('il_bt_value')) {
            $deleteBucketValuesSql = '
DELETE FROM il_bt_value WHERE id IN (
    SELECT value_id FROM il_bt_value_to_task WHERE task_id IN (
        SELECT id FROM il_bt_task WHERE ' . $ilDB->like('type', 'text', 'ilCertificateMigration%') . '
    )
)';
            $ilDB->manipulate($deleteBucketValuesSql);
        }

        $deleteValueToTask = '
DELETE FROM il_bt_value_to_task
WHERE task_id IN (
    SELECT id FROM il_bt_task WHERE ' . $ilDB->like('type', 'text', 'ilCertificateMigration%') . '
)';

        $ilDB->manipulate($deleteValueToTask);
    }
    $deleteBackgroundTasksSql = 'DELETE FROM il_bt_task WHERE ' . $ilDB->like('type', 'text', 'ilCertificateMigration%');
    $ilDB->manipulate($deleteBackgroundTasksSql);
}

if ($ilDB->tableExists('il_bt_bucket')) {
    $deleteBucketsSql = 'DELETE FROM il_bt_bucket WHERE title = ' . $ilDB->quote('Certificate Migration', 'text') ;
    $ilDB->manipulate($deleteBucketsSql);
}
?>
<#5506>
<?php

// get pdts type id
$row = $ilDB->fetchAssoc($ilDB->queryF(
    "SELECT obj_id FROM object_data WHERE type = %s AND title = %s",
    array('text', 'text'),
    array('typ', 'pdts')
));
$pdts_id = $row['obj_id'];

// register new 'object' rbac operation for tst
$op_id = $ilDB->nextId('rbac_operations');
$ilDB->insert('rbac_operations', array(
    'ops_id' => array('integer', $op_id),
    'operation' => array('text', 'change_presentation'),
    'description' => array('text', 'change presentation of a view'),
    'class' => array('text', 'object'),
    'op_order' => array('integer', 200)
));
$ilDB->insert('rbac_ta', array(
    'typ_id' => array('integer', $pdts_id),
    'ops_id' => array('integer', $op_id)
));

?>
<#5507>
<?php
// We should ensure that settings are set for new installations and ILIAS version upgrades
$setting = new ilSetting();

$setting->set('pd_active_sort_view_0', serialize(['location', 'type']));
$setting->set('pd_active_sort_view_1', serialize(['location', 'type', 'start_date']));
$setting->set('pd_active_pres_view_0', serialize(['list', 'tile']));
$setting->set('pd_active_pres_view_1', serialize(['list', 'tile']));
$setting->set('pd_def_pres_view_0', 'list');
$setting->set('pd_def_pres_view_1', 'list');
?>
<#5508>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$tgt_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('upload_blacklisted_files', "Upload Blacklisted Files", "object", 1);
if ($tgt_ops_id) {
    $lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId('facs');
    if ($lp_type_id) {
        ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $tgt_ops_id);
    }
}
?>
<#5509>
<?php

if ($ilDB->indexExistsByFields('read_event', array('usr_id'))) {
    $ilDB->dropIndexByFields('read_event', array('usr_id'));
}
$ilDB->addIndex('read_event', array('usr_id'), 'i1');

?>
<#5510>
<?php

if ($ilDB->tableExists('il_gs_identifications')) {
    $ilDB->dropTable('il_gs_identifications');
}

if ($ilDB->tableExists('il_gs_providers')) {
    $ilDB->dropTable('il_gs_providers');
}
?>
<#5511>
<?php
if (!$ilDB->tableColumnExists('tst_manual_fb', 'finalized_tstamp')) {
    $ilDB->addTableColumn('tst_manual_fb', 'finalized_tstamp', array(
        "type"   => "integer",
        "length" => 8,
    ));
}
if (!$ilDB->tableColumnExists('tst_manual_fb', 'finalized_evaluation')) {
    $ilDB->addTableColumn('tst_manual_fb', 'finalized_evaluation', array(
        "type"   => "integer",
        "length" => 1,
    ));
    $ilDB->manipulateF(
        'UPDATE tst_manual_fb SET finalized_evaluation = %s WHERE feedback IS NOT NULL',
        ['integer'],
        [1]
    );
}
if (!$ilDB->tableColumnExists('tst_manual_fb', 'finalized_by_usr_id')) {
    $ilDB->addTableColumn('tst_manual_fb', 'finalized_by_usr_id', array(
        "type"   => "integer",
        "length" => 8,
    ));
}
?>
<#5512>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5513>
<?php

$map = [
    'ilMMCustomProvider' => 'ILIAS\MainMenu\Provider\CustomMainBarProvider',
    'ilAdmGlobalScreenProvider' => 'ILIAS\\Administration\\AdministrationMainBarProvider',
    'ilBadgeGlobalScreenProvider' => 'ILIAS\\Badge\\Provider\\BadgeMainBarProvider',
    'ilCalendarGlobalScreenProvider' => 'ILIAS\\Certificate\\Provider\\CertificateMainBarProvider',
    'ilContactGlobalScreenProvider' => 'ILIAS\\Contact\\Provider\\ContactMainBarProvider',
    'ilDerivedTaskGlobalScreenProvider' => 'ILIAS\\Tasks\\DerivedTasks\\Provider\\DerivedTaskMainBarProvider',
    'ilLPGlobalScreenProvider' => 'ILIAS\\LearningProgress\\LPMainBarProvider',
    'ilMailGlobalScreenProvider' => 'ILIAS\\Mail\\Provider\\MailMainBarProvider',
    'ilNewsGlobalScreenProvider' => 'ILIAS\\News\\Provider\\NewsMainBarProvider',
    'ilNotesGlobalScreenProvider' => 'ILIAS\\Notes\\Provider\\NotesMainBarProvider',
    'ilPDGlobalScreenProvider' => 'ILIAS\\PersonalDesktop\\PDMainBarProvider',
    'ilPrtfGlobalScreenProvider' => 'ILIAS\\Portfolio\\Provider\\PortfolioMainBarProvider',
    'ilRepositoryGlobalScreenProvider' => 'ILIAS\\Repository\\Provider\\RepositoryMainBarProvider',
    'ilSkillGlobalScreenProvider' => 'ILIAS\\Skill\\Provider\\SkillMainBarProvider',
    'ilStaffGlobalScreenProvider' => 'ILIAS\\MyStaff\\Provider\\StaffMainBarProvider',
    'ilWorkspaceGlobalScreenProvider' => 'ILIAS\\PersonalWorkspace\\Provider\\WorkspaceMainBarProvider',
];

foreach ($map as $old => $new) {
    $ilDB->manipulateF("UPDATE il_mm_items SET 
identification = REPLACE(identification, %s, %s) WHERE identification LIKE %s", ['text', 'text', 'text'], [$old, $new, "$old|%"]);

    $ilDB->manipulateF("UPDATE il_mm_items SET 
parent_identification = REPLACE(parent_identification, %s, %s) WHERE parent_identification LIKE %s", ['text', 'text', 'text'], [$old, $new, "$old|%"]);

    $ilDB->manipulateF("UPDATE il_mm_translation SET 
id = REPLACE(id, %s, %s) WHERE id LIKE %s", ['text', 'text', 'text'], [$old, $new, "$old|%|%"]);

    $ilDB->manipulateF("UPDATE il_mm_translation SET 
identification = REPLACE(id, %s, %s) WHERE identification LIKE %s", ['text', 'text', 'text'], [$old, $new, "$old|%"]);

    $ilDB->manipulateF("UPDATE il_mm_actions SET 
identification = REPLACE(identification, %s, %s) WHERE identification LIKE %s", ['text', 'text', 'text'], [$old, $new, "$old|%"]);
}


?>


<#5514>
<?php
if (!$ilDB->tableExists('crs_timings_exceeded')) {
    $ilDB->createTable('crs_timings_exceeded', array(
        'user_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'ref_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    ,
        'sent' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    ));
    $ilDB->addPrimaryKey('crs_timings_exceeded', array('user_id', 'ref_id'));
}
?>
<#5515>
<?php
if (!$ilDB->tableExists('crs_timings_started')) {
    $ilDB->createTable('crs_timings_started', array(
        'user_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'ref_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    ,
        'sent' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    ));
    $ilDB->addPrimaryKey('crs_timings_started', array('user_id', 'ref_id'));
}
?>
<#5516>
<?php
$setting = new ilSetting();
$idx = $setting->get('ilfrmposidx5', 0);
if (!$idx) {
    $ilDB->addIndex('frm_posts', ['pos_thr_fk', 'pos_date'], 'i5');
} else {
    $setting->delete('ilfrmposidx5');
}
?>
<#5517>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5518>
<?php
$ilDB->modifyTableColumn('frm_notification', 'frm_id', array(
    'type'    => 'integer',
    'length'  => 8,
    'notnull' => true,
    'default' => 0
));
?>
<#5519>
<?php
$ilDB->modifyTableColumn('frm_notification', 'thread_id', array(
    'type'    => 'integer',
    'length'  => 8,
    'notnull' => true,
    'default' => 0
));
?>
<#5520>
<?php
$ilDB->modifyTableColumn('il_cert_template', 'version', array(
    'type'    => 'integer',
    'length'  => 8,
    'notnull' => true,
    'default' => 0
));
?>
<#5521>
<?php
$ilDB->addIndex('rbac_log', ['created'], 'i2');
?>
<#5522>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5523>
<?php
$q = "SELECT prg_settings.obj_id FROM prg_settings"
    . "	JOIN object_reference prg_ref ON prg_settings.obj_id = prg_ref.obj_id"
    . "	JOIN tree ON parent = prg_ref.ref_id"
    . "	LEFT JOIN object_reference child_ref ON tree.child = child_ref.ref_id"
    . "	LEFT JOIN object_data child ON child_ref.obj_id = child.obj_id"
    . "	WHERE lp_mode = 2 AND prg_ref.deleted IS NULL AND child.obj_id IS NULL";
$res = $ilDB->query($q);
$to_adjust = [];
while ($rec = $ilDB->fetchAssoc($res)) {
    $to_adjust[] = (int) $rec['obj_id'];
}
$ilDB->manipulate('UPDATE prg_settings SET lp_mode = 0 WHERE ' . $ilDB->in('obj_id', $to_adjust, false, 'integer'));
$q = "SELECT prg_settings.obj_id FROM prg_settings"
    . "	JOIN object_reference prg_ref ON prg_settings.obj_id = prg_ref.obj_id"
    . "	JOIN tree ON parent = prg_ref.ref_id"
    . "	JOIN object_reference child_ref ON tree.child = child_ref.ref_id"
    . "	JOIN object_data child ON child_ref.obj_id = child.obj_id"
    . "	WHERE lp_mode = 2 AND prg_ref.deleted IS NULL AND child.type = 'prg'";
$res = $ilDB->query($q);
$to_adjust = [];
while ($rec = $ilDB->fetchAssoc($res)) {
    $to_adjust[] = (int) $rec['obj_id'];
}
$ilDB->manipulate('UPDATE prg_settings SET lp_mode = 1 WHERE ' . $ilDB->in('obj_id', $to_adjust, false, 'integer'));
?>
<#5524>
<?php
if (!$ilDB->tableExists('wfld_user_setting')) {
    $ilDB->createTable('wfld_user_setting', array(
        'user_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'wfld_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'sortation' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0,
        )
    ));
    $ilDB->addPrimaryKey('wfld_user_setting', array('user_id','wfld_id'));
}
?>
<#5525>
<?php
    if (!$ilDB->tableExists("book_obj_use_book")) {
        $fields = array(
            "obj_id" => array(
                "type" => "integer",
                "notnull" => true,
                "length" => 4,
                "default" => 0
            ),
            "book_obj_id" => array(
                "type" => "integer",
                "notnull" => true,
                "length" => 4,
                "default" => 0
            )
        );
        $ilDB->createTable("book_obj_use_book", $fields);
    }
?>
<#5526>
<?php
    $ilDB->addPrimaryKey("book_obj_use_book", array("obj_id", "book_obj_id"));
?>
<#5527>
<?php
if (!$ilDB->tableColumnExists('booking_reservation', 'context_obj_id')) {
    $ilDB->addTableColumn(
        'booking_reservation',
        'context_obj_id',
        array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>
<#5528>
<?php
$ilDB->dropTableColumn('booking_reservation', 'context_obj_id');
if (!$ilDB->tableColumnExists('booking_reservation', 'context_obj_id')) {
    $ilDB->addTableColumn(
        'booking_reservation',
        'context_obj_id',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
            'default' => 0
        )
    );
}
?>
<#5529>
<?php
$ilDB->renameTableColumn('book_obj_use_book', "book_obj_id", 'book_ref_id');
?>
<#5530>
<?php
if (!$ilDB->tableColumnExists('skl_tree_node', 'description')) {
    $ilDB->addTableColumn(
        'skl_tree_node',
        'description',
        array(
            'type' 		=> 'clob',
            'notnull'	=> false
        )
    );
}
?>
<#5531>
<?php
// old competences (+ templates) and competence categories (+ templates) get an empty string as description instead of null
$ilDB->manipulate("UPDATE skl_tree_node SET description = '' WHERE description IS NULL AND type IN ('scat', 'skll', 'sctp', 'sktp')");
?>
<#5532>
<?php
if (!$ilDB->tableExists('skl_profile_role')) {
    $fields = array(
        'profile_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true),
        'role_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true)
    );
    $ilDB->createTable('skl_profile_role', $fields);
    $ilDB->addPrimaryKey('skl_profile_role', array('profile_id', 'role_id'));
}
?>
<#5533>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5534>
<?php
if (!$ilDB->tableColumnExists('booking_settings', 'preference_nr')) {
    $ilDB->addTableColumn('booking_settings', 'preference_nr', array(
        "type" => "integer",
        "notnull" => true,
        "length" => 4,
        "default" => 0
    ));
}
?>
<#5535>
<?php
if (!$ilDB->tableColumnExists('booking_settings', 'pref_deadline')) {
    $ilDB->addTableColumn('booking_settings', 'pref_deadline', array(
        "type" => "integer",
        "notnull" => true,
        "length" => 4,
        "default" => 0
    ));
}
?>
<#5536>
<?php
if (!$ilDB->tableExists('booking_preferences')) {
    $ilDB->createTable('booking_preferences', array(
        'book_pool_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'user_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'book_obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    ));
    $ilDB->addPrimaryKey('booking_preferences', ['book_pool_id', 'user_id', 'book_obj_id']);
}
?>
<#5537>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5538>
<?php
if (!$ilDB->tableColumnExists('booking_settings', 'pref_booking_hash')) {
    $ilDB->addTableColumn('booking_settings', 'pref_booking_hash', array(
        "type" => "text",
        "notnull" => true,
        "length" => 23,
        "default" => "0"
    ));
}
?>
<#5539>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5540>
<?php

include_once 'Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';

$ltiTypeId = ilDBUpdateNewObjectType::getObjectTypeId('lti');

if (!$ltiTypeId) {
    // add basic object type
    
    $ltiTypeId = ilDBUpdateNewObjectType::addNewType('lti', 'LTI Consumer Object');
    
    // common rbac operations
    
    $rbacOperations = array(
        ilDBUpdateNewObjectType::RBAC_OP_EDIT_PERMISSIONS,
        ilDBUpdateNewObjectType::RBAC_OP_VISIBLE,
        ilDBUpdateNewObjectType::RBAC_OP_READ,
        ilDBUpdateNewObjectType::RBAC_OP_WRITE,
        ilDBUpdateNewObjectType::RBAC_OP_DELETE,
        ilDBUpdateNewObjectType::RBAC_OP_COPY
    );
    
    ilDBUpdateNewObjectType::addRBACOperations($ltiTypeId, $rbacOperations);
    
    // lp rbac operations
    
    $operationId = ilDBUpdateNewObjectType::getCustomRBACOperationId('read_learning_progress');
    ilDBUpdateNewObjectType::addRBACOperation($ltiTypeId, $operationId);
    
    $operationId = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_learning_progress');
    ilDBUpdateNewObjectType::addRBACOperation($ltiTypeId, $operationId);
    
    // custom rbac operations
    
    $operationId = ilDBUpdateNewObjectType::addCustomRBACOperation(
        'read_outcomes',
        'Access Outcomes',
        'object',
        '2250'
    );
    
    ilDBUpdateNewObjectType::addRBACOperation($ltiTypeId, $operationId);
    
    // add create operation for relevant container types
    
    // (!) TRUNK SHOULD CONSIDER LSO PARENT AS WELL (!)
    $parentTypes = array('root', 'cat', 'crs', 'fold', 'grp');
    // (!) TRUNK SHOULD CONSIDER LSO PARENT AS WELL (!)
    ilDBUpdateNewObjectType::addRBACCreate('create_lti', 'Create LTI Consumer Object', $parentTypes);
    ilDBUpdateNewObjectType::applyInitialPermissionGuideline('lti', true);
}

?>
<#5541>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5542>
<?php

include_once 'Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';

$cmixTypeId = ilDBUpdateNewObjectType::getObjectTypeId('cmix');

if (!$cmixTypeId) {
    // add basic object type
    
    $cmixTypeId = ilDBUpdateNewObjectType::addNewType('cmix', 'cmi5/xAPI Object');
    
    // common rbac operations
    
    $rbacOperations = array(
        ilDBUpdateNewObjectType::RBAC_OP_EDIT_PERMISSIONS,
        ilDBUpdateNewObjectType::RBAC_OP_VISIBLE,
        ilDBUpdateNewObjectType::RBAC_OP_READ,
        ilDBUpdateNewObjectType::RBAC_OP_WRITE,
        ilDBUpdateNewObjectType::RBAC_OP_DELETE,
        ilDBUpdateNewObjectType::RBAC_OP_COPY
    );
    
    ilDBUpdateNewObjectType::addRBACOperations($cmixTypeId, $rbacOperations);
    
    // lp rbac operations
    
    $operationId = ilDBUpdateNewObjectType::getCustomRBACOperationId('read_learning_progress');
    ilDBUpdateNewObjectType::addRBACOperation($cmixTypeId, $operationId);
    
    $operationId = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_learning_progress');
    ilDBUpdateNewObjectType::addRBACOperation($cmixTypeId, $operationId);
    
    // custom rbac operations
    
    $operationId = ilDBUpdateNewObjectType::getCustomRBACOperationId('read_outcomes');
    ilDBUpdateNewObjectType::addRBACOperation($cmixTypeId, $operationId);
    
    // add create operation for relevant container types
    
    // (!) TRUNK SHOULD CONSIDER LSO PARENT AS WELL (!)
    $parentTypes = array('root', 'cat', 'crs', 'fold', 'grp');
    // (!) TRUNK SHOULD CONSIDER LSO PARENT AS WELL (!)
    ilDBUpdateNewObjectType::addRBACCreate('create_cmix', 'Create cmi5/xAPI Object', $parentTypes);
    ilDBUpdateNewObjectType::applyInitialPermissionGuideline('cmix', true);
}

?>
<#5543>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5544>
<?php

include_once 'Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';

ilDBUpdateNewObjectType::addAdminNode(
    'cmis',
    'cmi5/xAPI Administration'
);

?>
<#5545>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5546>
<?php
/**
 * Type definitions
 */
if (!$ilDB->tableExists('cmix_lrs_types')) {
    $types = array(
        'type_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'title' => array(
            'type' => 'text',
            'length' => 255
        ),
        'description' => array(
            'type' => 'text',
            'length' => 4000
        ),
        'availability' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 1
        ),
        'remarks' => array(
            'type' => 'text',
            'length' => 4000
        ),
        'time_to_delete' => array(
            'type' => 'integer',
            'length' => 4
        ),
        'lrs_endpoint' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'lrs_key' => array(
            'type' => 'text',
            'length' => 128,
            'notnull' => true
        ),
        'lrs_secret' => array(
            'type' => 'text',
            'length' => 128,
            'notnull' => true
        ),
        'privacy_comment_default' => array(
            'type' => 'text',
            'length' => 2000,
            'notnull' => true
        ),
        'external_lrs' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'user_ident' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => false,
            'default' => ''
        ),
        'user_name' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => false,
            'default' => ''
        ),
        'force_privacy_settings' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'bypass_proxy' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    );
    $ilDB->createTable("cmix_lrs_types", $types);
    $ilDB->addPrimaryKey("cmix_lrs_types", array("type_id"));
    $ilDB->createSequence("cmix_lrs_types");
}

?>
<#5547>
<?php
/**
 * settings for xapi-objects
 *
 * !!! ILIAS 6.0 implementation needs migration of offline status in case of table allready exists !!!
 */
if (!$ilDB->tableExists('cmix_settings')) {
    $fields = array(
        'obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'lrs_type_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'content_type' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => false
        ),
        'source_type' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => false
        ),
        'activity_id' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false
        ),
        'instructions' => array(
            'type' => 'text',
            'length' => 4000,
            'notnull' => false
        ),
        'offline_status' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ),
        'launch_url' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false
        ),
        'auth_fetch_url' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'launch_method' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => false
        ),
        'launch_mode' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => false
        ),
        'mastery_score' => array(
            'type' => 'float',
            'notnull' => true,
            'default' => 0.0
        ),
        'keep_lp' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'user_ident' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => false
        ),
        'user_name' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => false
        ),
        'usr_privacy_comment' => array(
            'type' => 'text',
            'length' => 4000,
            'notnull' => false
        ),
        'show_statements' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'xml_manifest' => array(
            'type' => 'clob'
        ),
        'version' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 1
        ),
        'highscore_enabled' => array(
            'type' => 'integer',
            'length'  => 1,
            'notnull' => true,
            'default' => 0
        ),
        'highscore_achieved_ts' => array(
            'type' => 'integer',
            'length'  => 1,
            'notnull' => true,
            'default' => 0
        ),
        'highscore_percentage' => array(
            'type' => 'integer',
            'length'  => 1,
            'notnull' => true,
            'default' => 0
        ),
        'highscore_wtime' => array(
            'type' => 'integer',
            'length'  => 1,
            'notnull' => true,
            'default' => 0
        ),
        'highscore_own_table' => array(
            'type' => 'integer',
            'length'  => 1,
            'notnull' => true,
            'default' => 0
        ),
        'highscore_top_table' => array(
            'type' => 'integer',
            'length'  => 1,
            'notnull' => true,
            'default' => 0
        ),
        'highscore_top_num' => array(
            'type' => 'integer',
            'length'  => 4,
            'notnull' => true,
            'default' => 0
        ),
        'bypass_proxy' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        )
    );
    $ilDB->createTable("cmix_settings", $fields);
    $ilDB->addPrimaryKey("cmix_settings", array("obj_id"));
}
?>
<#5548>
<?php
/**
 * table for detailed learning progress
 */
if (!$ilDB->tableExists('cmix_results')) {
    $values = array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
        ),
        'obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
        ),
        'usr_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
        ),
        'version' => array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => true,
            'default' => 1
        ),
        'score' => array(
            'type' => 'float',
            'notnull' => false,
        ),
        'status' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => true,
            'default' => 0
        ),
        'last_update' => array(
            'type' => 'timestamp',
            'notnull' => true,
            'default' => ''
        )
    );
    $ilDB->createTable("cmix_results", $values);
    $ilDB->addPrimaryKey("cmix_results", array("id"));
    $ilDB->createSequence("cmix_results");
    $ilDB->addIndex("cmix_results", array("obj_id","usr_id"), 'i1', false);
}
?>
<#5549>
<?php

if (!$ilDB->tableExists('cmix_users')) {
    $ilDB->createTable('cmix_users', array(
        'obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'usr_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'proxy_success' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'fetched_until' => array(
            'type' => 'timestamp',
            'notnull' => false
        ),
        'usr_ident' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false
        )
    ));
    
    $ilDB->addPrimaryKey('cmix_users', array('obj_id', 'usr_id'));
}

?>
<#5550>
<?php
/**
 * table token for auth
 */
if (!$ilDB->tableExists('cmix_token')) {
    $token = array(
        'token' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
            'default' => 0
        ),
        'valid_until' => array(
            'type' => 'timestamp',
            'notnull' => true,
            'default' => ''
        ),
        'lrs_type_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'ref_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'usr_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    );
    $ilDB->createTable("cmix_token", $token);

    $ilDB->addPrimaryKey("cmix_token", array('token'));
    $ilDB->addIndex("cmix_token", array('token', 'valid_until'), 'i1');
    $ilDB->addUniqueConstraint("cmix_token", array('obj_id', 'usr_id'), 'c1');
}
?>
<#5551>
<?php

$setting = new ilSetting('cmix');

if (!$setting->get('ilias_uuid', false)) {
    $uuid = (new \Ramsey\Uuid\UuidFactory())->uuid4()->toString();
    $setting->set('ilias_uuid', $uuid);
}

?>
<#5552>
<?php
/**
 * Type definitions
 */
if (!$ilDB->tableExists('lti_ext_provider')) {
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'title' => array(
            'type' => 'text',
            'length' => 255
        ),
        'description' => array(
            'type' => 'text',
            'length' => 4000
        ),
        'availability' => array(
            'type' => 'integer',
            'length' => 2,
            'notnull' => true,
            'default' => 1
        ),
        'remarks' => array(
            'type' => 'text',
            'length' => 4000
        ),
        'time_to_delete' => array(
            'type' => 'integer',
            'length' => 4
        ),
        'provider_url' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'provider_key' => array(
            'type' => 'text',
            'length' => 128,
            'notnull' => true
        ),
        'provider_secret' => array(
            'type' => 'text',
            'length' => 128,
            'notnull' => true
        ),
        'provider_key_customizable' => array( //key and secret changeable
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'provider_icon' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false
        ),
        'provider_xml' => array(
            'type' => 'clob'
        ),
        'external_provider' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'launch_method' => array( // Launch Method
            'type' => 'text',
            'length' => 32,
            'notnull' => false
        ),
        'has_outcome' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'mastery_score' => array(
            'type' => 'float',
            'notnull' => false
        ),
        'keep_lp' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'privacy_comment_default' => array(
            'type' => 'text',
            'length' => 2000,
            'notnull' => true
        ),
        'creator' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
        ),
        'accepted_by' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
        ),
        'global' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'use_xapi' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'xapi_launch_key' => array(
            'type' => 'text',
            'length' => 64,
            'notnull' => false
        ),
        'xapi_launch_secret' => array(
            'type' => 'text',
            'length' => 64,
            'notnull' => false
        ),
        'xapi_launch_url' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false
        ),
        'custom_params' => array(
            'type' => 'text',
            'length' => 1020,
            'notnull' => false
        ),
        'use_provider_id' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'always_learner' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'xapi_activity_id' => array(
            'type' => 'text',
            'length' => 128,
            'notnull' => false
        ),
        'keywords' => array(
            'type' => 'text',
            'length' => 1000,
            'notnull' => false
        ),
        'user_ident' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => true,
            'default' => ''
        ),
        'user_name' => array(
            'type' => 'text',
            'length' => 32,
            'notnull' => true,
            'default' => ''
        ),
        'inc_usr_pic' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'category' => array(
            'type' => 'text',
            'length' => 16,
            'notnull' => true,
            'default' => ''
        )
    );
    $ilDB->createTable("lti_ext_provider", $fields);
    $ilDB->addPrimaryKey("lti_ext_provider", array("id"));
    $ilDB->createSequence("lti_ext_provider");
}

?>
<#5553>
<?php

if (!$ilDB->tableExists('lti_consumer_settings')) {
    $ilDB->createTable('lti_consumer_settings', array(
        'obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'provider_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'launch_method' => array(
            'type' => 'text',
            'length' => 16,
            'notnull' => true,
            'default' => ''
        ),
        'offline_status' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ),
        'show_statements' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'highscore_enabled' => array(
            'type' => 'integer',
            'length'  => 1,
            'notnull' => true,
            'default' => 0
        ),
        'highscore_achieved_ts' => array(
            'type' => 'integer',
            'length'  => 1,
            'notnull' => true,
            'default' => 0
        ),
        'highscore_percentage' => array(
            'type' => 'integer',
            'length'  => 1,
            'notnull' => true,
            'default' => 0
        ),
        'highscore_wtime' => array(
            'type' => 'integer',
            'length'  => 1,
            'notnull' => true,
            'default' => 0
        ),
        'highscore_own_table' => array(
            'type' => 'integer',
            'length'  => 1,
            'notnull' => true,
            'default' => 0
        ),
        'highscore_top_table' => array(
            'type' => 'integer',
            'length'  => 1,
            'notnull' => true,
            'default' => 0
        ),
        'highscore_top_num' => array(
            'type' => 'integer',
            'length'  => 4,
            'notnull' => true,
            'default' => 0
        ),
        'mastery_score' => array(
            'type' => 'float',
            'notnull' => true,
            'default' => 0.5
        ),
        'keep_lp' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'use_xapi' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'activity_id' => array(
            'type' => 'text',
            'length' => 128,
            'notnull' => false
        ),
        'launch_key' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false
        ),
        'launch_secret' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false
        )
    ));
    
    $ilDB->addPrimaryKey('lti_consumer_settings', array('obj_id'));
}

?>
<#5554>
<?php
//changes for celtic/lti

if ($ilDB->tableExists('lti2_consumer')) {
    if (!$ilDB->tableColumnExists('lti2_consumer', 'signature_method')) {
        $ilDB->addTableColumn('lti2_consumer', 'signature_method', array(
            "type" => "text",
            "notnull" => true,
            "length" => 15,
            "default" => 'HMAC-SHA1'
        ));
    }
}

if ($ilDB->tableExists('lti2_context')) {
    if (!$ilDB->tableColumnExists('lti2_context', 'title')) {
        $ilDB->addTableColumn('lti2_context', 'title', array(
            "type" => "text",
            "notnull" => false,
            "length" => 255,
            "default" => null
        ));
    }
}

if ($ilDB->tableExists('lti2_context')) {
    if (!$ilDB->tableColumnExists('lti2_context', 'type')) {
        $ilDB->addTableColumn('lti2_context', 'type', array(
            "type" => "text",
            "notnull" => false,
            "length" => 50,
            "default" => null
        ));
    }
}

if ($ilDB->tableExists('lti2_resource_link')) {
    if (!$ilDB->tableColumnExists('lti2_resource_link', 'title')) {
        $ilDB->addTableColumn('lti2_resource_link', 'title', array(
            "type" => "text",
            "notnull" => false,
            "length" => 255,
            "default" => null
        ));
    }
}

//note: field user_result_pk in table lti2_user_result is not used in ILIAS; use user_pk as in implementation of IMSGLOBAL

if ($ilDB->tableExists('lti2_nonce')) {
    if ($ilDB->tableColumnExists('lti2_nonce', 'value')) {
        $ilDB->modifyTableColumn('lti2_nonce', 'value', array(
            'type' => 'text',
            'length' => 50,
            'notnull' => true
        ));
    }
}

//todo: drop lti2_tool_proxy table


?>
<#5555>
<?php
/**
 * add the table for type input values
 */
if (!$ilDB->tableExists('lti_consumer_results')) {
    $values = array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
        ),
        'obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
        ),
        'usr_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
        ),
        'result' => array(
            'type' => 'float',
            'notnull' => false,
        ),
    );
    $ilDB->createTable("lti_consumer_results", $values);
    $ilDB->addPrimaryKey("lti_consumer_results", array("id"));
    $ilDB->createSequence("lti_consumer_results");
    $ilDB->addIndex("lti_consumer_results", array("obj_id","usr_id"), 'i1');
}
?>
<#5556>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5557>
<?php
if ($ilDB->tableColumnExists("lng_data", "identifier")) {
    $field = array(
        'type'    => 'text',
        'length'  => 200,
        'notnull' => true,
        'default' => ' '
    );
    $ilDB->modifyTableColumn("lng_data", "identifier", $field);
}
?>
<#5558>
<?php
if ($ilDB->tableColumnExists("lng_log", "identifier")) {
    $field = array(
        'type'    => 'text',
        'length'  => 200,
        'notnull' => true,
        'default' => ' '
    );
    $ilDB->modifyTableColumn("lng_log", "identifier", $field);
}
?>
<#5559>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5560>
<?php
if (!$ilDB->tableColumnExists('exc_data', 'nr_mandatory_random')) {
    $ilDB->addTableColumn(
        'exc_data',
        'nr_mandatory_random',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    );
}
?>
<#5561>
<?php

if (!$ilDB->tableExists('exc_mandatory_random')) {
    $ilDB->createTable('exc_mandatory_random', array(
        'exc_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'usr_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'ass_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
    ));

    $ilDB->addPrimaryKey('exc_mandatory_random', array('exc_id', 'usr_id', 'ass_id'));
}

?>
<#5562>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5563>
<?php
if (!$ilDB->tableColumnExists('exc_assignment', 'rel_deadline_last_subm')) {
    $ilDB->addTableColumn(
        'exc_assignment',
        'rel_deadline_last_subm',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    );
}
?>
<#5564>
<?php
// Add new index
if (!$ilDB->indexExistsByFields('object_data', ['owner'])) {
    $ilDB->addIndex('object_data', ['owner'], 'i5');
}
?>
<#5565>
<?php
include_once 'Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';

$typeId = ilDBUpdateNewObjectType::getObjectTypeId('ltis');

$opsId = ilDBUpdateNewObjectType::addCustomRBACOperation(
    'add_consume_provider',
    'Allow Add Own Provider',
    'object',
    3510
);

ilDBUpdateNewObjectType::addRBACOperation($typeId, $opsId);

?>
<#5566>
<?php

require_once 'Services/Administration/classes/class.ilSetting.php';
$setting = new ilSetting('lti');
$setting->delete('custom_provider_create_role');

?>
<#5567>
<?php
if (!$ilDB->tableExists('crs_reference_settings')) {
    $ilDB->createTable('crs_reference_settings', [
        'obj_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'member_update' => [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ]
    ]);
}
?>
<#5568>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5569>
<?php
    $ilDB->dropPrimaryKey('role_desktop_items');
?>
<#5570>
<?php
    $ilDB->renameTableColumn('role_desktop_items', "item_id", 'ref_id');
?>
<#5571>
<?php
    $ilDB->renameTable('role_desktop_items', 'rep_rec_content_role');
?>
<#5572>
<?php
    $ilDB->dropTableColumn("rep_rec_content_role", "role_item_id");
?>
<#5573>
<?php
    $ilDB->dropTableColumn("rep_rec_content_role", "item_type");
?>
<#5574>
<?php
    $ilDB->addPrimaryKey('rep_rec_content_role', ['role_id','ref_id']);
?>
<#5575>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5576>
<?php
if (!$ilDB->tableExists('rep_rec_content_obj')) {
    $ilDB->createTable('rep_rec_content_obj', [
        'user_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'ref_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'declined' => [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ]
    ]);
}
?>
<#5577>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addAdminNode('nots', 'Notes Settings');
?>
<#5578>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addAdminNode('coms', 'Comments Settings');
?>
<#5579>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addAdminNode('lhts', 'Learning History Settings');
?>
<#5580>
<?php
$ilDB->update(
    "object_data",
    [
        "title" => ["text", "dshs"],
        "description" => ["text", "Dashboard Settings"]
    ],
    [	// where
        "title" => ["text", "pdts"],
        "type" => ["text", "typ"],
    ]
);
?>
<#5581>
<?php
$ilDB->update(
    "object_data",
    [
    "type" => ["text", "dshs"],
    "title" => ["text", "__DashboardSettings"],
    "description" => ["text", "Dashboard Settings"]
],
    [	// where
        "type" => ["text", "pdts"]
    ]
);
?>
<#5582>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addAdminNode('prss', 'Personal Resources Settings');
?>
<#5583>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#5584>
<?php

    $set = $ilDB->queryF(
        "SELECT * FROM svy_svy " .
        " WHERE invitation_mode = %s ",
        ["integer"],
        [0]
    );
    while ($rec = $ilDB->fetchAssoc($set)) {
        $ilDB->manipulateF(
            "DELETE FROM svy_inv_usr WHERE " .
            " survey_fi = %s",
            ["integer"],
            [$rec["survey_id"]]
        );
    }

?>
<#5585>
<?php
if (!$ilDB->tableExists('svy_invitation')) {
    $ilDB->createTable('svy_invitation', [
        'user_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'survey_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ]
    ]);
    $ilDB->addPrimaryKey("svy_invitation", ["user_id", "survey_id"]);
}
?>
<#5586>
<?php
    $set = $ilDB->queryF(
    "SELECT DISTINCT survey_fi, user_fi FROM svy_inv_usr ",
    [],
    []
    );
    while ($rec = $ilDB->fetchAssoc($set)) {
        $ilDB->insert("svy_invitation", [
            "survey_id" => ["integer", $rec["survey_fi"]],
            "user_id" => ["integer", $rec["user_fi"]]
        ]);
    }

?>
<#5587>
<?php
    $ilDB->dropTable('svy_inv_usr');
?>
<#5588>
<?php
$ilDB->manipulate(
    "UPDATE il_cert_template SET background_image_path = " .
    "REPLACE(" .
    "background_image_path , " .
    $ilDB->quote('//exercise/certificates//', 'text') . " , " .
    "CONCAT( CONCAT(" . $ilDB->quote('/', 'text') . ",obj_id)," . $ilDB->quote('/', 'text') . ") " .
    ") " .
    "WHERE background_image_path LIKE " . $ilDB->quote('%//background%', 'text')
);
$ilDB->manipulate(
    "UPDATE il_cert_template SET background_image_path = " .
    "REPLACE(" .
    "background_image_path , " .
    $ilDB->quote('//course/certificates//', 'text') . " , " .
    "CONCAT( CONCAT(" . $ilDB->quote('/', 'text') . ",obj_id)," . $ilDB->quote('/', 'text') . ") " .
    ") " .
    "WHERE background_image_path LIKE " . $ilDB->quote('%//background%', 'text')
);
$ilDB->manipulate(
    "UPDATE il_cert_template SET background_image_path = " .
    "REPLACE(" .
    "background_image_path , " .
    $ilDB->quote('//assessment/certificates//', 'text') . " , " .
    "CONCAT( CONCAT(" . $ilDB->quote('/', 'text') . ",obj_id)," . $ilDB->quote('/', 'text') . ") " .
    ") " .
    "WHERE background_image_path LIKE " . $ilDB->quote('%//background%', 'text')
);
$ilDB->manipulate(
    "UPDATE il_cert_template SET background_image_path = " .
    "REPLACE(" .
    "background_image_path , " .
    $ilDB->quote('//certificates/scorm//', 'text') . " , " .
    "CONCAT( CONCAT(" . $ilDB->quote('/', 'text') . ",obj_id)," . $ilDB->quote('/', 'text') . ") " .
    ") " .
    "WHERE background_image_path LIKE " . $ilDB->quote('%//background%', 'text')
);
?>
<#5589>
<?php

$settings = new \ilSetting();
$tree_type = $settings->get('main_tree_impl', \ilTree::TREE_TYPE_NESTED_SET);

$tree = new \ilTree(1);
switch ($tree_type) {

        case \ilTree::TREE_TYPE_NESTED_SET:
                $tree->renumber();
                break;

        case \ilTree::TREE_TYPE_MATERIALIZED_PATH:
                \ilMaterializedPathTree::createFromParentReleation();
                break;


}
?>
<#5590>
<?php
if (!$ilDB->tableColumnExists('object_reference', 'deleted_by')) {
    $ilDB->addTableColumn(
            'object_reference',
            'deleted_by',
            [
                        'type' => 'integer',
                        'notnull' => false,
                        'length' => 4,
                        'default' => 0
                ]
        );
}
?>
<#5591>
<?php
if (!$ilDB->tableExists('webdav_instructions')) {
    $ilDB->createTable('webdav_instructions', [
        'id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'title' => [
            'type' => 'text',
            'length' => '255',
            'default' => ''
        ],
        'uploaded_instructions' => [
            'type' => 'clob',
            'default' => ''
        ],
        'processed_instructions' => [
            'type' => 'clob',
            'default' => ''
        ],
        'lng' => [
            'type' => 'text',
            'length' => 5
        ],
        'creation_ts' => [
            'type' => 'timestamp'
        ],
        'modification_ts' => [
            'type' => 'timestamp'
        ],
        'owner_usr_id' => [
            'type' => 'integer',
            'length' => 4
        ],
        'last_modification_usr_id' => [
            'type' => 'integer',
            'length' => 4
        ],
        'sorting' => [
            'type' => 'integer',
            'length' => 4
        ]
    ]);
    $ilDB->addPrimaryKey("webdav_instructions", ["id"]);
    $ilDB->createSequence('webdav_instructions');
}
?>

<#5592>
<?php

/**
 * @var $ilDB \ilDBInterface
 */

if (!$ilDB->tableColumnExists('crs_settings', 'period_start')) {
    $ilDB->addTableColumn(
            'crs_settings',
            'period_start',
            [
                        'type' => \ilDBConstants::T_TIMESTAMP,
                        'notnull' => false,
                        'default' => null
                ]
        );
    $ilDB->addTableColumn(
            'crs_settings',
            'period_end',
            [
                        'type' => \ilDBConstants::T_TIMESTAMP,
                        'notnull' => false,
                        'default' => null
                ]
        );
}
?>

<#5593>
<?php

$query = 'select obj_id, crs_start, crs_end from crs_settings where crs_start IS NOT NULL or crs_end IS NOT NULL';
$res = $ilDB->query($query);
while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
    $dtstart = $dtend = null;
    if ($row->crs_start != null) {
        $start = new DateTime();
        $start->setTimezone(new DateTimeZone('UTC'));
        $start->setTimestamp((int) $row->crs_start);
        $dtstart = $start->format('Y-m-d');
    }
    if ($row->crs_end != null) {
        $end = new DateTime();
        $end->setTimezone(new DateTimeZone('UTC'));
        $end->setTimestamp((int) $row->crs_end);
        $dtend = $end->format('Y-m-d');
    }

    $query = 'update crs_settings set ' .
                'period_start = ' . $ilDB->quote($dtstart, \ilDBConstants::T_TIMESTAMP) . ', ' .
                'period_end = ' . $ilDB->quote($dtend, \ilDBConstants::T_TIMESTAMP) . ' ' .
                'where obj_id = ' . $ilDB->quote($row->obj_id, \ilDBConstants::T_INTEGER);
    $ilDB->manipulate($query);
}
?>
<#5594>
<?php
if (!$ilDB->tableColumnExists('crs_settings', 'period_time_indication')) {
    $ilDB->addTableColumn(
            'crs_settings',
            'period_time_indication',
            [
                        'type' => \ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'default' => 0
                ]
        );
}
?>

<#5595>
<?php

/**
 * @var $ilDB \ilDBInterface
 */

if (!$ilDB->tableColumnExists('grp_settings', 'period_start')) {
    $ilDB->addTableColumn(
            'grp_settings',
            'period_start',
            [
                        'type' => \ilDBConstants::T_TIMESTAMP,
                        'notnull' => false,
                        'default' => null
                ]
        );
    $ilDB->addTableColumn(
            'grp_settings',
            'period_end',
            [
                        'type' => \ilDBConstants::T_TIMESTAMP,
                        'notnull' => false,
                        'default' => null
                ]
        );
}
?>

<#5596>
<?php

$query = 'select obj_id, grp_start, grp_end from grp_settings where grp_start IS NOT NULL or grp_end IS NOT NULL';
$res = $ilDB->query($query);
while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
    $dtstart = $dtend = null;
    if ($row->grp_start != null) {
        $start = new DateTime();
        $start->setTimezone(new DateTimeZone('UTC'));
        $start->setTimestamp((int) $row->grp_start);
        $dtstart = $start->format('Y-m-d');
    }
    if ($row->grp_end != null) {
        $end = new DateTime();
        $end->setTimezone(new DateTimeZone('UTC'));
        $end->setTimestamp((int) $row->grp_end);
        $dtend = $end->format('Y-m-d');
    }

    $query = 'update grp_settings set ' .
                'period_start = ' . $ilDB->quote($dtstart, \ilDBConstants::T_TIMESTAMP) . ', ' .
                'period_end = ' . $ilDB->quote($dtend, \ilDBConstants::T_TIMESTAMP) . ' ' .
                'where obj_id = ' . $ilDB->quote($row->obj_id, \ilDBConstants::T_INTEGER);
    $ilDB->manipulate($query);
}
?>
<#5597>
<?php
if (!$ilDB->tableColumnExists('grp_settings', 'period_time_indication')) {
    $ilDB->addTableColumn(
            'grp_settings',
            'period_time_indication',
            [
                        'type' => \ilDBConstants::T_INTEGER,
                        'notnull' => true,
                        'default' => 0
                ]
        );
}
?>
<#5598>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$read_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('read_learning_progress');
$edit_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_learning_progress');
$write_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
if ($read_ops_id && $edit_ops_id) {
    $lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId('crsr');
    if ($lp_type_id) {
        ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $read_ops_id);
        ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $edit_ops_id);
        ilDBUpdateNewObjectType::cloneOperation('crsr', $write_ops_id, $read_ops_id);
        ilDBUpdateNewObjectType::cloneOperation('crsr', $write_ops_id, $edit_ops_id);
    }
}
?>

<#5599>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5600>
<?php
$fields = array(
    'internal'       => array(
        'type'   => 'text',
        'length' => '250',

    ),
    'identification' => array(
        'type'   => 'text',
        'length' => '250',

    ),
    'title'          => array(
        'type'   => 'text',
        'length' => '250',

    ),
    'suffix'         => array(
        'type'   => 'text',
        'length' => '64',

    ),
    'mime_type'      => array(
        'type'   => 'text',
        'length' => '250',

    ),
    'size'           => array(
        'type'   => 'integer',
        'length' => '8',

    ),

);
if (!$ilDB->tableExists('il_resource_info')) {
    $ilDB->createTable('il_resource_info', $fields);
    $ilDB->addPrimaryKey('il_resource_info', array('internal'));
}
?>
<#5601>
<?php
$fields = array(
    'identification' => array(
        'type'   => 'text',
        'length' => '250',

    ),
    'storage_id'     => array(
        'type'   => 'text',
        'length' => '8',

    ),

);
if (!$ilDB->tableExists('il_resource')) {
    $ilDB->createTable('il_resource', $fields);
    $ilDB->addPrimaryKey('il_resource', array('identification'));
}
?>
<#5602>
<?php
$fields = array(
    'internal'       => array(
        'type'   => 'text',
        'length' => '250',

    ),
    'identification' => array(
        'type'   => 'text',
        'length' => '250',

    ),
    'available'      => array(
        'type'   => 'integer',
        'length' => '1',

    ),
    'version_number' => array(
        'type'   => 'integer',
        'length' => '8',

    ),

);
if (!$ilDB->tableExists('il_resource_revision')) {
    $ilDB->createTable('il_resource_revision', $fields);
    $ilDB->addPrimaryKey('il_resource_revision', array('internal'));
}
?>
<#5603>
<?php
if (!$ilDB->tableColumnExists('il_mm_items', 'icon_id')) {
    $ilDB->addTableColumn(
        'il_mm_items',
        'icon_id',
        array(
            'type'   => 'text',
            'length' => 250,
        )
    );
}
?>

<#5604>
<?php
require_once './Services/PDFGeneration/classes/class.ilPDFCompInstaller.php';
$renderer = 'WkhtmlToPdf';
$path =  'Services/PDFGeneration/classes/renderer/wkhtmltopdf/class.ilWkhtmlToPdfRenderer.php';
ilPDFCompInstaller::registerRenderer($renderer, $path);
$service = 'Test';
$purpose = 'UserResult'; // According to name given. Call multiple times.
ilPDFCompInstaller::registerRendererAvailability($renderer, $service, $purpose);

$purpose = 'PrintViewOfQuestions'; // According to name given. Call multiple times.
ilPDFCompInstaller::registerRendererAvailability($renderer, $service, $purpose);
?>
<#5605>
<?php
if ($ilDB->tableExists('event')) {
    $ilDB->addTableColumn(
        'event',
        'reg_notification',
        array(
            'type'    => 'integer',
            'notnull' => true,
            'default' => 0
        )
    );

    $ilDB->addTableColumn(
        'event',
        'notification_opt',
        array(
            'type'    => 'text',
            'length'  => '50',
            'notnull' => false,
            'default' => 'notification_option_manual' // ilSessionConstants::NOTIFICATION_INHERIT_OPTION
        )
    );
}
?>
<#5606>
<?php
if ($ilDB->tableExists('event_participants')) {
    $ilDB->addTableColumn(
        'event_participants',
        'notification_enabled',
        array(
            'type'    => 'integer',
            'notnull' => true,
            'default' => 0
        )
    );
}
?>
<#5607>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5608>
<?php
if (!$ilDB->tableColumnExists('page_layout', 'mod_lm')) {
    $ilDB->addTableColumn(
        'page_layout',
        'mod_lm',
        array(
            'type'	=> 'integer',
            'length'=> 1,
            'notnull' => false
        )
    );
}
?>
<#5609>
<?php

$query = "
	UPDATE object_data odat SET offline = (
		SELECT offline_status from cmix_settings cset
        WHERE cset.obj_id = odat.obj_id
    ) WHERE odat.type = %s
";

$ilDB->manipulateF($query, array('text'), array('cmix'));

?>
<#5610>
<?php

$query = "
	UPDATE object_data odat SET offline = (
		SELECT offline_status from lti_consumer_settings lset
        WHERE lset.obj_id = odat.obj_id
    ) WHERE odat.type = %s
";

$ilDB->manipulateF($query, array('text'), array('lti'));
?>

<#5611>
<?php
if (!$ilDB->tableColumnExists('il_blog_posting', 'last_withdrawn')) {
    $ilDB->addTableColumn(
        'il_blog_posting',
        'last_withdrawn',
        array(
            'type' 		=> 'timestamp',
            'notnull'	=> false,
            'default'	=> null
        )
    );
}
?>
<#5612>
<?php
global $ilDB;
if (!$ilDB->tableColumnExists('crs_settings', 'target_group')) {
    $ilDB->addTableColumn(
            'crs_settings',
            'target_group',
            [
                                'type' => \ilDBConstants::T_TEXT,
                                'length' => 4000,
                                'notnull' => false
                        ]
        );
}
?>
<#5613>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('prg_settings', 'deadline_period')) {
    $db->addTableColumn(
        'prg_settings',
        'deadline_period',
        [
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
                'default' => 0
            ]
        );
}
if (!$db->tableColumnExists('prg_settings', 'deadline_date')) {
    $db->addTableColumn(
        'prg_settings',
        'deadline_date',
        [
                'type' => 'timestamp',
                'notnull' => false
            ]
        );
}
?>

<#5614>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('prg_usr_progress', 'assignment_date')) {
    $db->addTableColumn(
        'prg_usr_progress',
        'assignment_date',
        [
                'type' => 'timestamp',
                'notnull' => false
            ]
        );
}
?>

<#5615>
<?php
global $DIC;
$db = $DIC['ilDB'];
if ($db->tableColumnExists('prg_usr_progress', 'assignment_date') && $db->tableColumnExists('prg_usr_assignments', 'last_change')) {
    $db->manipulate(
        'UPDATE prg_usr_progress'
        . '	JOIN prg_usr_assignments'
        . '		ON prg_usr_assignments.id = prg_usr_progress.assignment_id'
        . '	SET prg_usr_progress.assignment_date = prg_usr_assignments.last_change'
    );
}
?>

<#5616>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('prg_usr_progress', 'completion_date')) {
    $db->addTableColumn(
        'prg_usr_progress',
        'completion_date',
        [
                'type' => 'timestamp',
                'notnull' => false
            ]
        );
}
?>

<#5617>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('prg_settings', 'vq_period')) {
    $db->addTableColumn(
        'prg_settings',
        'vq_period',
        [
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
                'default' => -1
            ]
        );
}
if (!$db->tableColumnExists('prg_settings', 'vq_date')) {
    $db->addTableColumn(
        'prg_settings',
        'vq_date',
        [
                'type' => 'timestamp',
                'notnull' => false
            ]
        );
}
if (!$db->tableColumnExists('prg_settings', 'vq_restart_period')) {
    $db->addTableColumn(
        'prg_settings',
        'vq_restart_period',
        [
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
                'default' => -1
            ]
        );
}
?>

<#5618>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('prg_usr_progress', 'vq_date')) {
    $db->addTableColumn(
        'prg_usr_progress',
        'vq_date',
        [
                'type' => 'timestamp',
                'notnull' => false
            ]
        );
}
?>

<#5619>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('prg_usr_assignments', 'restart_date')) {
    $db->addTableColumn(
        'prg_usr_assignments',
        'restart_date',
        [
                'type' => 'timestamp',
                'notnull' => false
            ]
        );
}
?>

<#5620>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('prg_usr_assignments', 'restarted_assignment_id')) {
    $db->addTableColumn(
        'prg_usr_assignments',
        'restarted_assignment_id',
        [
                'type' => 'integer',
                'notnull' => true,
                'default' => -1
            ]
        );
}
?>
<#5621>
<?php

global $ilDB;

if (!$ilDB->tableColumnExists('crs_settings', 'target_group')) {
    $ilDB->addTableColumn(
            'crs_settings',
            'target_group',
            [
                                'type' => \ilDBConstants::T_TEXT,
                                'length' => 4000,
                                'notnull' => false
                        ]
        );
}
?>
<#5622>
<?php
if (!$ilDB->tableExists('prg_auto_content')) {
    $ilDB->createTable('prg_auto_content', array(
        'prg_obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'cat_ref_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'title' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ),
        'last_usr_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'last_edited' => array(
            'type' => 'timestamp',
            'notnull' => false
        )
    ));
    $ilDB->addPrimaryKey('prg_auto_content', ['prg_obj_id', 'cat_ref_id']);
}
?>

<#5623>
<?php
require_once './Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';

$type_id  = ilDBUpdateNewObjectType::addNewType('prgr', 'Study Programme Reference');

ilDBUpdateNewObjectType::addRBACOperations($type_id, [
    ilDBUpdateNewObjectType::RBAC_OP_EDIT_PERMISSIONS,
    ilDBUpdateNewObjectType::RBAC_OP_VISIBLE,
    ilDBUpdateNewObjectType::RBAC_OP_READ,
    ilDBUpdateNewObjectType::RBAC_OP_WRITE,
    ilDBUpdateNewObjectType::RBAC_OP_DELETE,
    ilDBUpdateNewObjectType::RBAC_OP_COPY
]);

ilDBUpdateNewObjectType::addRBACCreate('create_prgr', 'Create Study Programme Reference', [
    'prg'
]);
?>

<#5624>
<?php
if (!$ilDB->tableExists('prg_auto_membership')) {
    $ilDB->createTable('prg_auto_membership', array(
        'prg_obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'source_type' => array(
            'type' => 'text',
            'length' => 8,
            'notnull' => true
        ),
        'source_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'enabled' => array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ),
        'last_usr_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ),
        'last_edited' => array(
            'type' => 'timestamp',
            'notnull' => false
        )
    ));
    $ilDB->addPrimaryKey('prg_auto_membership', ['prg_obj_id', 'source_type', 'source_id']);
}
?>

<#5625>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('prg_usr_progress', 'invalidated')) {
    $db->addTableColumn(
        'prg_usr_progress',
        'invalidated',
        [
                'type' => 'integer',
                'length' => 1,
                'notnull' => false
            ]
        );
}
?>

<#5626>
<?php
ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_PRG, ilOrgUnitOperationContext::CONTEXT_OBJECT);
?>

<#5627>
<?php
    ilOrgUnitOperationQueries::registerNewOperation(
    ilOrgUnitOperation::OP_VIEW_MEMBERS,
    'View Memberships of other users',
    ilOrgUnitOperationContext::CONTEXT_PRG
    );
    ilOrgUnitOperationQueries::registerNewOperation(
        ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS,
        'View learning progress of other users',
        ilOrgUnitOperationContext::CONTEXT_PRG
    );
    ilOrgUnitOperationQueries::registerNewOperation(
        ilOrgUnitOperation::OP_VIEW_INDIVIDUAL_PLAN,
        'View Individual Plans of other users',
        ilOrgUnitOperationContext::CONTEXT_PRG
    );
    ilOrgUnitOperationQueries::registerNewOperation(
        ilOrgUnitOperation::OP_EDIT_INDIVIDUAL_PLAN,
        'Edit Individual Plans of other users',
        ilOrgUnitOperationContext::CONTEXT_PRG
    );
    ilOrgUnitOperationQueries::registerNewOperation(
        ilOrgUnitOperation::OP_MANAGE_MEMBERS,
        'Manage Memberships of other users',
        ilOrgUnitOperationContext::CONTEXT_PRG
    );
?>

<#5628>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('prg_settings', 'access_ctrl_org_pos')) {
    $db->addTableColumn(
        'prg_settings',
        'access_ctrl_org_pos',
        [
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 0
            ]
        );
}
?>

<#5629>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('prg_settings', 'rm_nr_by_usr_days')) {
    $db->addTableColumn(
        'prg_settings',
        'rm_nr_by_usr_days',
        [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ]
    );
}
?>

<#5630>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('prg_settings', 'proc_end_no_success')) {
    $db->addTableColumn(
        'prg_settings',
        'proc_end_no_success',
        [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ]
    );
}
?>

<#5631>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('prg_usr_assignments', 'restart_mail_send')) {
    $db->addTableColumn(
        'prg_usr_assignments',
        'restart_mail_send',
        [
            'type' => 'timestamp',
            'notnull' => false
        ]
    );
}
?>

<#5632>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('prg_usr_progress', 'risky_to_fail_mail_send')) {
    $db->addTableColumn(
        'prg_usr_progress',
        'risky_to_fail_mail_send',
        [
            'type' => 'timestamp',
            'notnull' => false
        ]
    );
}
?>

<#5633>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('prg_settings', 'send_re_assigned_mail')) {
    $db->addTableColumn(
        'prg_settings',
        'send_re_assigned_mail',
        [
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        ]
    );
}
?>

<#5634>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('prg_settings', 'send_info_to_re_assign_mail')) {
    $db->addTableColumn(
        'prg_settings',
        'send_info_to_re_assign_mail',
        [
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        ]
    );
}
?>

<#5635>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('prg_settings', 'send_risky_to_fail_mail')) {
    $db->addTableColumn(
        'prg_settings',
        'send_risky_to_fail_mail',
        [
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        ]
    );
}
?>
<#5636>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5637>
<?php

if (!$ilDB->tableColumnExists('tst_tests', 'info_screen')) {
    $ilDB->addTableColumn('tst_tests', 'info_screen', [
        'type' => \ilDBConstants::T_INTEGER, 'length' => 1, 'notnull' => false
    ]);

    $ilDB->manipulateF(
        "UPDATE tst_tests SET info_screen = %s",
        [\ilDBConstants::T_INTEGER],
        [1]
    );
}

?>
<#5638>
<?php
if (!$ilDB->tableExists('acc_documents')) {
    $fields = [
        'id' => [
            'type'    => 'integer',
            'length'  => 4,
            'notnull' => true,
            'default' => 0
        ],
        'title' => [
            'type'     => 'text',
            'length'   => 255,
            'notnull'  => false,
            'default'  => null
        ],
        'creation_ts' => [
            'type'     => 'integer',
            'length'   => 4,
            'notnull'  => true,
            'default'  => 0
        ],
        'modification_ts' => [
            'type'     => 'integer',
            'length'   => 4,
            'notnull'  => true,
            'default'  => 0
        ],
        'sorting' => [
            'type'    => 'integer',
            'length'  => 4,
            'notnull' => true,
            'default' => 0
        ],
        'owner_usr_id' => [
            'type'    => 'integer',
            'length'  => 4,
            'notnull' => true,
            'default' => 0
        ],
        'last_modified_usr_id' => [
            'type'    => 'integer',
            'length'  => 4,
            'notnull' => true,
            'default' => 0
        ],
        'text' => [
            'type'    => 'clob',
            'notnull' => false,
            'default' => null
        ]
    ];
    $ilDB->createTable('acc_documents', $fields);
    $ilDB->addPrimaryKey('acc_documents', ['id']);
    $ilDB->createSequence('acc_documents');
}
?>
<#5639>
<?php
if (!$ilDB->tableExists('acc_criterion_to_doc')) {
    $fields = [
        'id' => [
            'type'    => 'integer',
            'length'  => 4,
            'notnull' => true,
            'default' => 0
        ],
        'doc_id' => [
            'type'    => 'integer',
            'length'  => 4,
            'notnull' => true,
            'default' => 0
        ],
        'criterion_id' => [
            'type'     => 'text',
            'length'   => 50,
            'notnull'  => true
        ],
        'criterion_value' => [
            'type'     => 'text',
            'length'   => 255,
            'notnull'  => false,
            'default'  => null,
        ],
        'assigned_ts' => [
            'type'     => 'integer',
            'length'   => 4,
            'notnull'  => true,
            'default'  => 0
        ],
        'modification_ts' => [
            'type'     => 'integer',
            'length'   => 4,
            'notnull'  => true,
            'default'  => 0
        ],
        'owner_usr_id' => [
            'type'    => 'integer',
            'length'  => 4,
            'notnull' => true,
            'default' => 0
        ],
        'last_modified_usr_id' => [
            'type'    => 'integer',
            'length'  => 4,
            'notnull' => true,
            'default' => 0
        ]
    ];
    $ilDB->createTable('acc_criterion_to_doc', $fields);
    $ilDB->addPrimaryKey('acc_criterion_to_doc', ['id']);
    $ilDB->createSequence('acc_criterion_to_doc');
}
?>
<#5640>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5641>
<?php
        $ilCtrlStructureReader->getStructure();
?>
<#5642>
<?php
        if (!$ilDB->tableColumnExists('svy_svy', 'reminder_tmpl')) {
            $ilDB->addTableColumn('svy_svy', 'reminder_tmpl', array(
                        "type" => "integer",
                        "notnull" => false,
                        "length" => 4
                ));
        }
?>
<#5643>
        <?php
        if (!$ilDB->tableColumnExists('svy_svy', 'tutor_res_status')) {
            $ilDB->addTableColumn('svy_svy', 'tutor_res_status', array(
                        "type" => "integer",
                        "notnull" => false,
                        "length" => 1
                ));
        }
        if (!$ilDB->tableColumnExists('svy_svy', 'tutor_res_reci')) {
            $ilDB->addTableColumn('svy_svy', 'tutor_res_reci', array(
                        'type' => 'text',
                        'length'  => 2000,
                        'notnull' => false,
                        'fixed' => false
                ));
        }
        ?>
<#5644>
<?php
        if (!$ilDB->tableColumnExists('svy_svy', 'tutor_res_cron')) {
            $ilDB->addTableColumn('svy_svy', 'tutor_res_cron', array(
                        "type" => "integer",
                        "notnull" => false,
                        "length" => 1
                ));
        }
?>
<#5645>
<?php
ilOrgUnitOperationContextQueries::registerNewContext(
    ilOrgUnitOperationContext::CONTEXT_USRF,
    ilOrgUnitOperationContext::CONTEXT_OBJECT
);

ilOrgUnitOperationQueries::registerNewOperation(
    ilOrgUnitOperation::OP_EDIT_USER_ACCOUNTS,
    'Edit User in User Administration',
    ilOrgUnitOperationContext::CONTEXT_USRF
);
?>
<#5646>
<?php
if (!$ilDB->tableColumnExists('grp_settings', 'auto_notification')) {
    $ilDB->addTableColumn(
            'grp_settings',
            'auto_notification',
            [
                        'type'    => 'integer',
                        'notnull' => true,
                        'default' => 1
                ]
        );
}
?>
<#5647>
<?php
if (!$ilDB->tableColumnExists('event_participants', 'excused')) {
    $ilDB->addTableColumn(
            'event_participants',
            'excused',
            [
                        'type'    => 'integer',
                        'length' => 1,
                        'notnull' => true,
                        'default' => 0
                ]
        );
}
?>
<#5648>
<?php
if (!$ilDB->tableColumnExists("il_cert_template", "certificate_content_bu")) {
    $ilDB->addTableColumn(
        "il_cert_template",
        'certificate_content_bu',
        array(
            'type' => 'clob',
            'default' => '',
            'notnull' => true,
        )
    );
}

if (!$ilDB->tableColumnExists("il_cert_user_cert", "certificate_content_bu")) {
    $ilDB->addTableColumn(
        "il_cert_user_cert",
        'certificate_content_bu',
        array(
            'type' => 'clob',
            'default' => '',
            'notnull' => true,
        )
    );
}

$ilDB->manipulate("UPDATE il_cert_template SET certificate_content_bu = certificate_content WHERE (certificate_content_bu IS NULL OR certificate_content_bu = '')");
$ilDB->manipulate("UPDATE il_cert_user_cert SET certificate_content_bu = certificate_content WHERE (certificate_content_bu IS NULL OR certificate_content_bu = '')");
$res = $ilDB->query("SELECT * FROM il_cert_template WHERE certificate_content NOT LIKE '%[BACKGROUND_IMAGE]%'");
$updateStatement = $ilDB->prepareManip("UPDATE il_cert_template SET certificate_content = ?, certificate_hash = ? WHERE id = ?", ['clob', 'text', 'integer']);
while ($row = $ilDB->fetchAssoc($res)) {
    $row['certificate_content'] = preg_replace(
        '/src="url\((.*?)\/certificates\/(.*?)\)"/',
        'src="url([BACKGROUND_IMAGE])"',
        $row['certificate_content']
    );
    $row['certificate_hash'] = hash(
        'sha256',
        $row['certificate_content'] . $row['background_image_path'] . $row['template_values'] . $row['thumbnail_image_path']
    );
    $ilDB->execute($updateStatement, [$row['certificate_content'], $row['certificate_hash'], $row['id']]);
}

$res = $ilDB->query("SELECT * FROM il_cert_user_cert WHERE certificate_content NOT LIKE '%[BACKGROUND_IMAGE]%'");
$updateStatement = $ilDB->prepareManip("UPDATE il_cert_user_cert SET certificate_content = ? WHERE id = ?", ['clob', 'integer']);
while ($row = $ilDB->fetchAssoc($res)) {
    $row['certificate_content'] = preg_replace(
        '/src="url\((.*?)\/certificates\/(.*?)\)"/',
        'src="url([BACKGROUND_IMAGE])"',
        $row['certificate_content']
    );
    $ilDB->execute($updateStatement, [$row['certificate_content'], $row['id']]);
}
?>
<#5649>
<?php
ilOrgUnitOperationQueries::registerNewOperationForMultipleContexts(ilOrgUnitOperation::OP_VIEW_CERTIFICATES, 'Read the certificates of a User', [
    ilOrgUnitOperationContext::CONTEXT_TST,
    ilOrgUnitOperationContext::CONTEXT_EXC,
    ilOrgUnitOperationContext::CONTEXT_CRS,
]);
ilOrgUnitOperationQueries::registerNewOperationForMultipleContexts(ilOrgUnitOperation::OP_VIEW_COMPETENCES, 'Read the competences of a User', [
    ilOrgUnitOperationContext::CONTEXT_TST,
    ilOrgUnitOperationContext::CONTEXT_GRP,
    ilOrgUnitOperationContext::CONTEXT_CRS,
    ilOrgUnitOperationContext::CONTEXT_SVY,
]);
?>
<#5650>
<?php
if (!$ilDB->tableColumnExists("exc_ass_reminders", "last_send_day")) {
    $field = array(
        'type'    => 'date',
        'notnull' => false,
    );
    $ilDB->addTableColumn("exc_ass_reminders", "last_send_day", $field);
}
?>
<#5651>
<?php
$set = $ilDB->queryF("SELECT * FROM exc_ass_reminders ".
    " WHERE last_send > %s ",
    ["integer"],
    [0]
);
while ($rec = $ilDB->fetchAssoc($set))
{
    $last_send_day = date("Y-m-d", $rec["last_send"]);
    $ilDB->update("exc_ass_reminders", [
        "last_send_day" => ["date", $last_send_day]
    ], [    // where
            "ass_id" => ["integer", $rec["ass_id"]],
            "last_send" => ["integer", $rec["last_send"]]
        ]
    );
}
?>
