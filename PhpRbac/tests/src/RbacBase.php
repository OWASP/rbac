<?php
//namespace PhpRbac;

use PhpRbac\Rbac;

/**
 * @file
 * Unit Tests for PhpRbac PSR Wrapper
 *
 * @defgroup phprbac_unit_test_wrapper_base Unit Tests for BaseRbac Functionality
 * @ingroup phprbac
 * @{
 * Documentation for all Unit Tests regarding BaseRbac functionality.
 */

class RbacBase extends \RbacSetup
{
    /*
     * Tests for $this->Instance()->Add()
     */

    public function testAddNullTitle()
    {
        $dataSet = $this->getConnection()->createDataSet();

        $type_id = $this->Instance()->Add(null, $this->type() . ' Description');

        $this->assertSame(0, $type_id);
    }

    public function testAddNullDescription()
    {
        $dataSet = $this->getConnection()->createDataSet();

        $type_id = $this->Instance()->Add($this->type() . '_title', null);

        $this->assertSame(0, $type_id);
    }

    public function testAddSequential()
    {
        $dataSet = $this->getConnection()->createDataSet();

        $this->Instance()->Add($this->type() . '_title_1', $this->type() . ' Description 1');
        $this->Instance()->Add($this->type() . '_title_2', $this->type() . ' Description 2');
        $this->Instance()->Add($this->type() . '_title_3', $this->type() . ' Description 3');

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type()
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_add_' . $this->type() . '_sequential.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddHierarchy()
    {
        $dataSet = $this->getConnection()->createDataSet();

        $type_1 = $this->Instance()->Add('blog', 'Define ' . $this->type() . ' for the Blog');
        $this->Instance()->Add($this->type() . '_title_1', $this->type() . ' Description 1', $type_1);
        $this->Instance()->Add($this->type() . '_title_2', $this->type() . ' Description 2', $type_1);
        $this->Instance()->Add($this->type() . '_title_3', $this->type() . ' Description 3', $type_1);

        $type_2 = $this->Instance()->Add('forum', 'Define ' . $this->type() . ' for the Forums');
        $this->Instance()->Add($this->type() . '_title_1', $this->type() . ' Description 1', $type_2);
        $this->Instance()->Add($this->type() . '_title_2', $this->type() . ' Description 2', $type_2);
        $this->Instance()->Add($this->type() . '_title_3', $this->type() . ' Description 3', $type_2);

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type()
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_add_' . $this->type() . '_hierarchy.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    /*
     * Tests for $this->Instance()->Count()
     */

    public function testCount()
    {
        $this->Instance()->Add($this->type() . '_title_1', $this->type() . ' Description 1');
        $this->Instance()->Add($this->type() . '_title_2', $this->type() . ' Description 2');
        $this->Instance()->Add($this->type() . '_title_3', $this->type() . ' Description 3');

        $type_count = $this->Instance()->Count();

        $this->assertSame(4, $type_count);
    }

    /*
     * Tests for $this->Instance()->TitleID()
     */

    public function testGetTitleId()
    {
        $this->Instance()->Add($this->type() . '_title', $this->type() . ' Description');
        $title_id = $this->Instance()->TitleID($this->type() . '_title');

        $this->assertSame('2', $title_id);
    }

    public function testGetTitleIdNull()
    {
        $title_id = $this->Instance()->TitleID($this->type() . '_title');

        $this->assertNull($title_id);
    }

    /*
     * Tests for $this->Instance()->GetTitle()
     */

    public function testGetTitle()
    {
        $type_id = $this->Instance()->Add($this->type() . '_title', $this->type() . ' Description');
        $type_title = $this->Instance()->GetTitle($type_id);

        $this->assertSame($this->type() . '_title', $type_title);
    }

    public function testGetTitleNull()
    {
        $type_title = $this->Instance()->GetTitle(intval(3));

        $this->assertNull($type_title);
    }

    /*
     * Tests for $this->Instance()->GetDescription()
     */

    public function testGetDescription()
    {
        $type_description = $this->Instance()->GetDescription(intval(1));

        $this->assertSame('root', $type_description);
    }

    public function testGetDescriptionNull()
    {
        $type_description = $this->Instance()->GetDescription(intval(2));

        $this->assertNull($type_description);
    }

    /*
     * Tests for $this->Instance()->Edit()
     */

    public function testEditTitle()
    {
        $type_id = $this->Instance()->Add($this->type() . '_title', $this->type() . ' Description');
        $this->Instance()->Edit($type_id, $this->type() . '_title_edited');

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type() . ' WHERE ID=2'
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_edit_' . $this->type() . '_title.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testEditDescription()
    {
        $type_id = $this->Instance()->Add($this->type() . '_title', $this->type() . ' Description');
        $this->Instance()->Edit($type_id, null, $this->type() . ' Description edited');

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type() . ' WHERE ID=2'
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_edit_' . $this->type() . '_description.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testEditAll()
    {
        $type_id = $this->Instance()->Add($this->type() . '_title', $this->type() . ' Description');
        $this->Instance()->Edit($type_id, $this->type() . '_title_edited', $this->type() . ' Description edited');

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type() . ' WHERE ID=2'
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_edit_' . $this->type() . '_all.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testEditNullId()
    {
        $type_id = $this->Instance()->Add($this->type() . '_title', $this->type() . ' Description');
        $result = $this->Instance()->Edit(intval(3), $this->type() . '_title', $this->type() . ' Description');

        $this->assertFalse($result);
    }

    public function testEditNullParameters()
    {
        $type_id = $this->Instance()->Add($this->type() . '_title', $this->type() . ' Description');
        $result = $this->Instance()->Edit($type_id);

        $this->assertFalse($result);
    }

    /*
     * Tests for $this->Instance()->AddPath()
     */

    public function testAddPathSingle()
    {
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type()
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_add_path_' . $this->type() . '_single.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddPathSingleDescription()
    {
        $descriptions = array(
            $this->type() . ' Description 1',
            $this->type() . ' Description 2',
            $this->type() . ' Description 3',
        );

        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3', $descriptions);

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type()
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_add_path_' . $this->type() . '_single_description.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddPathSequential()
    {
        $this->Instance()->AddPath('/' . $this->type() . '_1/');
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/');
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type()
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_add_path_' . $this->type() . '_sequential.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddPathSequentialDescription()
    {
        $descriptions_1 = array(
            $this->type() . ' Description 1',
        );

        $this->Instance()->AddPath('/' . $this->type() . '_1/', $descriptions_1);

        $descriptions_2 = array(
            null,
            $this->type() . ' Description 2',
        );

        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/', $descriptions_2);

        $descriptions_3 = array(
            null,
            null,
            $this->type() . ' Description 3',
        );

        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3', $descriptions_3);

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type()
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_add_path_' . $this->type() . '_sequential_description.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddPathHierarchy()
    {

        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_4');

        $this->Instance()->AddPath('/' . $this->type() . '_12/' . $this->type() . '_13/' . $this->type() . '_14');
        $this->Instance()->AddPath('/' . $this->type() . '_12/' . $this->type() . '_15/' . $this->type() . '_11');

        $this->Instance()->AddPath('/' . $this->type() . '_23/' . $this->type() . '_24/' . $this->type() . '_25');
        $this->Instance()->AddPath('/' . $this->type() . '_33/' . $this->type() . '_34/' . $this->type() . '_35');

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type()
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_add_path_' . $this->type() . '_hierarchy.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddPathHierarchyDescription()
    {
        $descriptions_1 = array(
            $this->type() . ' Description 1',
            $this->type() . ' Description 2',
            $this->type() . ' Description 3',
        );

        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3', $descriptions_1);

        $descriptions_2 = array(
            null,
            null,
            $this->type() . ' Description 4',
        );

        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_4', $descriptions_2);

        $descriptions_3 = array(
            $this->type() . ' Description 12',
            $this->type() . ' Description 13',
            $this->type() . ' Description 14',
        );

        $this->Instance()->AddPath('/' . $this->type() . '_12/' . $this->type() . '_13/' . $this->type() . '_14', $descriptions_3);

        $descriptions_4 = array(
            null,
            $this->type() . ' Description 15',
            $this->type() . ' Description 11',
        );

        $this->Instance()->AddPath('/' . $this->type() . '_12/' . $this->type() . '_15/' . $this->type() . '_11', $descriptions_4);

        $descriptions_5 = array(
            $this->type() . ' Description 23',
            $this->type() . ' Description 24',
            $this->type() . ' Description 25',
        );

        $this->Instance()->AddPath('/' . $this->type() . '_23/' . $this->type() . '_24/' . $this->type() . '_25', $descriptions_5);

        $descriptions_6 = array(
            $this->type() . ' Description 33',
            $this->type() . ' Description 34',
            $this->type() . ' Description 35',
        );

        $this->Instance()->AddPath('/' . $this->type() . '_33/' . $this->type() . '_34/' . $this->type() . '_35', $descriptions_6);

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type()
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_add_path_' . $this->type() . '_hierarchy_description.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    /**
     * @expectedException Exception
     */

    public function testAddPathBadPath()
    {
        $this->Instance()->AddPath('permissions_1/permissions_2//permissions_3');
    }

    /*
     * Tests for $this->Instance()->PathID()
     */

    public function testPathID()
    {
        $this->Instance()->AddPath('/' . $this->type() . '_1/');
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/');
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $path_id = $this->Instance()->PathID('/' . $this->type() . '_1/' . $this->type() . '_2');

        $this->assertSame('3', $path_id);
    }

    public function testPathIDNullBadPath()
    {
        $this->Instance()->AddPath('/' . $this->type() . '_1/');
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/');
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $path_id = $this->Instance()->PathID($this->type() . '_2');

        $this->assertNull($path_id);
    }

    /**
     * @expectedException Exception
     */

    public function testPathIDGroupConcatExceedCharCount()
    {
        $id = null;
        $path = "";
        for($i = 0; $i < 100; ++ $i)
        {
            $id = $this->Instance()->Add("lgd depth{$i}", "description of depth{$i}", $id);
            $path .= "/depth{$i}";
        }

        $this->Instance()->PathID($path);
    }

    /*
     * Tests for $this->Instance()->Path()
     */

    public function testPath()
    {
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $path_returned = $this->Instance()->Path(intval(3));

        $this->assertSame('/' . $this->type() . '_1/' . $this->type() . '_2', $path_returned);
    }

    public function testPathNull()
    {
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $path_returned = $this->Instance()->Path(intval(5));

        $this->assertNull($path_returned);
    }

    /*
     * Tests for $this->Instance()->Children()
     */

    public function testChildren()
    {
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3/' . $this->type() . '_4/' . $this->type() . '_5');
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3/' . $this->type() . '_6/' . $this->type() . '_7');
        $path_id = $this->Instance()->PathID('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $children = $this->Instance()->Children($path_id);

        $expected = array(
            array(
            	   'ID' => '5',
            	   'Lft' => '4',
            	   'Rght' => '7',
            	   'Title' => $this->type() . '_4',
            	   'Description' => '',
            ),
            array(
            	   'ID' => '7',
            	   'Lft' => '8',
            	   'Rght' => '11',
            	   'Title' => $this->type() . '_6',
            	   'Description' => '',
            )
        );

        $this->assertSame($expected, $children);
    }

    public function testChildrenNullBadID()
    {
        $children = $this->Instance()->Children(20);

        $this->assertNull($children);
    }

    /*
     * Tests for $this->Instance()->Descendants()
     */

    public function testDescendants()
    {
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3/' . $this->type() . '_4/' . $this->type() . '_5');
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3/' . $this->type() . '_6/' . $this->type() . '_7');
        $path_id = $this->Instance()->PathID('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $descendants = $this->Instance()->Descendants($path_id);

        $expected = array(
            $this->type() . '_4' => array(
            	   'ID' => '5',
            	   'Lft' => '4',
            	   'Rght' => '7',
            	   'Title' => $this->type() . '_4',
            	   'Description' => '',
            	   'Depth' => '1',
            ),
            $this->type() . '_5' => array(
            	   'ID' => '6',
            	   'Lft' => '5',
            	   'Rght' => '6',
            	   'Title' => $this->type() . '_5',
            	   'Description' => '',
            	   'Depth' => '2',
            ),
            $this->type() . '_6' => array(
            	   'ID' => '7',
            	   'Lft' => '8',
            	   'Rght' => '11',
            	   'Title' => $this->type() . '_6',
            	   'Description' => '',
        	       'Depth' => '1',
            ),
            $this->type() . '_7' => array(
            	   'ID' => '8',
            	   'Lft' => '9',
            	   'Rght' => '10',
            	   'Title' => $this->type() . '_7',
            	   'Description' => '',
            	   'Depth' => '2',
            ),
        );

        $this->assertSame($expected, $descendants);
    }

    public function testDescendantsEmptyBadID()
    {
        $descendants = $this->Instance()->Descendants(20);

        $this->assertEmpty($descendants);
    }

    /*
     * Tests for $this->Instance()->Depth()
     */

    public function testDepth()
    {
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3/' . $this->type() . '_4/' . $this->type() . '_5');
        $path_id = $this->Instance()->PathID('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $depth = $this->Instance()->Depth($path_id);

        $this->assertSame(3, $depth);
    }

    public function testDepthBadID()
    {
        $depth = $this->Instance()->Depth(20);

        $this->assertSame(-1, $depth);
    }

    /*
     * Tests for $this->Instance()->ParentNode()
     */

    public function testParentNode()
    {
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3/' . $this->type() . '_4/' . $this->type() . '_5');
        $this->Instance()->AddPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3/' . $this->type() . '_6/' . $this->type() . '_7');
        $path_id = $this->Instance()->PathID('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $parent_node = $this->Instance()->ParentNode($path_id);

        $expected = array(
            'ID' => '3',
            'Lft' => '2',
            'Rght' => '13',
            'Title' => $this->type() . '_2',
            'Description' => '',
        );

        $this->assertSame($expected, $parent_node);
    }

    public function testParentNodeNullBadID()
    {
        $parent_node = $this->Instance()->ParentNode(20);

        $this->assertNull($parent_node);
    }

    /*
     * Tests for $this->Instance()->Assign()
     */

    public function testAssignWithId()
    {
        $perm_id = self::$rbac->Permissions->Add('permissions_1', 'permissions Description 1');
        $role_id = self::$rbac->Roles->Add('roles_1', 'roles Description 1');

        $this->Instance()->Assign($role_id, $perm_id);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addExcludeTables(array($this->Instance()->tablePrefix() . 'userroles'));

        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_assign_' . $this->type() . '_id.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    /*
     * Tests for $this->Instance()->Unassign()
     */

    public function testUnassign()
    {
        $perm_id = self::$rbac->Permissions->Add('permissions_1', 'permissions Description 1');
        $role_id = self::$rbac->Roles->Add('roles_1', 'roles Description 1');

        $this->Instance()->Assign($role_id, $perm_id);
        $this->Instance()->Unassign($role_id, $perm_id);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            self::$rbac->Users->tablePrefix() . 'rolepermissions',
        ));

        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_unassign_' . $this->type() . '.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    /*
     * Tests for $this->Instance()->ResetAssignments()
     */

    public function testResetPermRoleAssignments()
    {
        $perm_id_1 = self::$rbac->Permissions->Add('permissions_1', 'permissions Description 1');
        $perm_id_2 = self::$rbac->Permissions->Add('permissions_2', 'permissions Description 2');
        $perm_id_3 = self::$rbac->Permissions->Add('permissions_3', 'permissions Description 3');

        $role_id_1 = self::$rbac->Roles->Add('roles_1', 'roles Description 1');
        $role_id_2 = self::$rbac->Roles->Add('roles_2', 'roles Description 2');
        $role_id_3 = self::$rbac->Roles->Add('roles_3', 'roles Description 3');

        $this->Instance()->Assign($role_id_1, $perm_id_1);
        $this->Instance()->Assign($role_id_2, $perm_id_2);
        $this->Instance()->Assign($role_id_3, $perm_id_3);

        $this->Instance()->ResetAssignments(true);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            self::$rbac->Users->tablePrefix() . 'rolepermissions',
        ));

        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_reset_assignments_' . $this->type() . '.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    /**
     * @expectedException Exception
     */

    public function testResetPermRoleAssignmentsException()
    {
        $this->Instance()->ResetAssignments();
    }

    /*
     * Tests for $this->Instance()->Reset()
     */

    public function testReset()
    {
        $this->Instance()->Add($this->type() . '_title_1', $this->type() . ' Description 1');
        $this->Instance()->Add($this->type() . '_title_2', $this->type() . ' Description 2');
        $this->Instance()->Add($this->type() . '_title_3', $this->type() . ' Description 3');

        $this->Instance()->Reset(true);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            $this->Instance()->tablePrefix() . $this->type(),
        ));

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_reset_' . $this->type() . '.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    /**
     * @expectedException Exception
     */

    public function testResetException()
    {
        $this->Instance()->Reset();
    }
}

/** @} */ // End group phprbac_unit_test_wrapper_base */
