<?php
namespace PhpRbac;

use PhpRbac\Rbac;

/**
 * @file
 * Unit Tests for PhpRbac PSR Wrapper
 *
 * @defgroup phprbac_unit_test_wrapper Unit Tests for RBAC Functionality
 * @ingroup phprbac
 * @{
 * Documentation for all Unit Tests regarding PhpRbac related functionality.
 */
class RbacBase extends \Generic_Tests_DatabaseTestCase
{
    /*
     * Test Setup and Fixture
     */
    
	public static $rbac;
	
    public static function setUpBeforeClass()
    {
    	self::$rbac = new Rbac('unit_test');
    }
    
    public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__) . '/datasets/database-seed.xml');
    }
    

    protected function Instance()
    {
        return self::$rbac->Permissions;
    }
    
    protected function Type()
    {
        return "permissions";
    }
    
    /*
     * Tests for proper object instantiation 
     */
    
    public function testRbacInstance() {
        $this->assertInstanceOf('PhpRbac\Rbac', self::$rbac);
    }

    public function testPermissionsInstance() {
    	$this->assertInstanceOf('PermissionManager', self::$rbac->Permissions);
    }

    public function testRolesInstance() {
    	$this->assertInstanceOf('RoleManager', self::$rbac->Roles);
    }

    public function testUsersInstance() {
    	$this->assertInstanceOf('RBACUserManager', self::$rbac->Users);
    }

    /*
     * Tests for Add()
     */

    public function testAddNullTitle()
    {
        $tableNames = array('phprbac_' . $this->Type());
        $dataSet = $this->getConnection()->createDataSet();
    
        $type_id = $this->Instance()->Add(null, $this->Type() . ' Description');
    
        $this->assertSame(0, $type_id);
    }

    public function testAddNullDescription()
    {
        $tableNames = array('phprbac_' . $this->Type());
        $dataSet = $this->getConnection()->createDataSet();
    
        $type_id = $this->Instance()->Add($this->Type() . '_title', null);
    
        $this->assertSame(0, $type_id);
    }
    
    public function testAddSequential()
    {
        $tableNames = array('phprbac_' . $this->Type());
        $dataSet = $this->getConnection()->createDataSet();

        $this->Instance()->Add($this->Type() . '_title_1', $this->Type() . ' Description 1');
        $this->Instance()->Add($this->Type() . '_title_2', $this->Type() . ' Description 2');
        $this->Instance()->Add($this->Type() . '_title_3', $this->Type() . ' Description 3');
        
        $queryTable = $this->getConnection()->createQueryTable(
            'phprbac_' . $this->Type(), 'SELECT * FROM phprbac_' . $this->Type()
        );
        
        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/expected_add_' . $this->Type() . '_sequential.xml')
            ->getTable('phprbac_' . $this->Type());
        
        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddHierarchy()
    {
        $tableNames = array('phprbac_' . $this->Type());
        $dataSet = $this->getConnection()->createDataSet();

        $type_1 = $this->Instance()->Add('blog', 'Define ' . $this->Type() . ' for the Blog');
        $this->Instance()->Add($this->Type() . '_title_1', $this->Type() . ' Description 1', $type_1);
        $this->Instance()->Add($this->Type() . '_title_2', $this->Type() . ' Description 2', $type_1);
        $this->Instance()->Add($this->Type() . '_title_3', $this->Type() . ' Description 3', $type_1);
        
        $type_2 = $this->Instance()->Add('forum', 'Define ' . $this->Type() . ' for the Forums');
        $this->Instance()->Add($this->Type() . '_title_1', $this->Type() . ' Description 1', $type_2);
        $this->Instance()->Add($this->Type() . '_title_2', $this->Type() . ' Description 2', $type_2);
        $this->Instance()->Add($this->Type() . '_title_3', $this->Type() . ' Description 3', $type_2);
    
        $queryTable = $this->getConnection()->createQueryTable(
            'phprbac_' . $this->Type(), 'SELECT * FROM phprbac_' . $this->Type()
        );
    
        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/expected_add_' . $this->Type() . '_hierarchy.xml')
            ->getTable('phprbac_' . $this->Type());
    
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    
    /*
     * Tests for Count()
     */
    
    public function testCount()
    {
        $this->Instance()->Add($this->Type() . '_title_1', $this->Type() . ' Description 1');
        $this->Instance()->Add($this->Type() . '_title_2', $this->Type() . ' Description 2');
        $this->Instance()->Add($this->Type() . '_title_3', $this->Type() . ' Description 3');
        
        $type_count = $this->Instance()->Count();
        
        $this->assertSame(4, $type_count);
    }
    
    /*
     * Tests for TitleID()
     */
    
    public function testGetTitleId()
    {
        $this->Instance()->Add($this->Type() . '_title', $this->Type() . ' Description');
        $title_id = $this->Instance()->TitleID($this->Type() . '_title');
        
        $this->assertSame('2', $title_id);        
    }

    public function testGetTitleIdNull()
    {
        $title_id = $this->Instance()->TitleID($this->Type() . '_title');
    
        $this->assertNull($title_id);
    }
    
    /*
     * Tests for GetTitle()
     */
    
    public function testGetTitle()
    {
        $type_id = $this->Instance()->Add($this->Type() . '_title', $this->Type() . ' Description');
        $type_title = $this->Instance()->GetTitle($type_id);
        
        $this->assertSame($this->Type() . '_title', $type_title);
    }

    public function testGetTitleNull()
    {
        $type_title = $this->Instance()->GetTitle(intval(3));
    
        $this->assertNull($type_title);
    }
    
    /*
     * Tests for GetDescription()
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
     * Tests for Edit()
     */
    
    public function testEditTitle()
    {
        $type_id = $this->Instance()->Add($this->Type() . '_title', $this->Type() . ' Description');
        $this->Instance()->Edit($type_id, $this->Type() . '_title_edited');
        
        $queryTable = $this->getConnection()->createQueryTable(
            'phprbac_' . $this->Type(), 'SELECT * FROM phprbac_' . $this->Type() . ' WHERE ID=2'
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/expected_edit_' . $this->Type() . '_title.xml')
            ->getTable('phprbac_' . $this->Type());
        
        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testEditDescription()
    {
        $type_id = $this->Instance()->Add($this->Type() . '_title', $this->Type() . ' Description');
        $this->Instance()->Edit($type_id, null, $this->Type() . ' Description edited');
        
        $queryTable = $this->getConnection()->createQueryTable(
            'phprbac_' . $this->Type(), 'SELECT * FROM phprbac_' . $this->Type() . ' WHERE ID=2'
        );
        
        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/expected_edit_' . $this->Type() . '_description.xml')
            ->getTable('phprbac_' . $this->Type());
        
        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testEditAll()
    {
        $type_id = $this->Instance()->Add($this->Type() . '_title', $this->Type() . ' Description');
        $this->Instance()->Edit($type_id, $this->Type() . '_title_edited', $this->Type() . ' Description edited');
        
        $queryTable = $this->getConnection()->createQueryTable(
            'phprbac_' . $this->Type(), 'SELECT * FROM phprbac_' . $this->Type() . ' WHERE ID=2'
        );
        
        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/expected_edit_' . $this->Type() . '_all.xml')
            ->getTable('phprbac_' . $this->Type());
        
        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testEditNullId()
    {
        $type_id = $this->Instance()->Add($this->Type() . '_title', $this->Type() . ' Description');
        $result = $this->Instance()->Edit(intval(3), $this->Type() . '_title', $this->Type() . ' Description');
        
        $this->assertFalse($result);
    }

    public function testEditNullParameters()
    {
        $type_id = $this->Instance()->Add($this->Type() . '_title', $this->Type() . ' Description');
        $result = $this->Instance()->Edit($type_id);
        
        $this->assertFalse($result);
    }
    
    /*
     * Tests for AddPath()
     */
    
    public function testAddPathSingle()
    {
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3');
        
        $queryTable = $this->getConnection()->createQueryTable(
            'phprbac_' . $this->Type(), 'SELECT * FROM phprbac_' . $this->Type()
        );
        
        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/expected_add_path_' . $this->Type() . '_single.xml')
            ->getTable('phprbac_' . $this->Type());
        
        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddPathSingleDescription()
    {
        $descriptions = array(
            $this->Type() . ' Description 1',
            $this->Type() . ' Description 2',
            $this->Type() . ' Description 3',
        );
        
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3', $descriptions);
        
        $queryTable = $this->getConnection()->createQueryTable(
            'phprbac_' . $this->Type(), 'SELECT * FROM phprbac_' . $this->Type()
        );
        
        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/expected_add_path_' . $this->Type() . '_single_description.xml')
            ->getTable('phprbac_' . $this->Type());
        
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    
    public function testAddPathSequential()
    {
        $this->Instance()->AddPath('/' . $this->Type() . '_1/');
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/');
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3');
        
        $queryTable = $this->getConnection()->createQueryTable(
            'phprbac_' . $this->Type(), 'SELECT * FROM phprbac_' . $this->Type()
        );
        
        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/expected_add_path_' . $this->Type() . '_sequential.xml')
            ->getTable('phprbac_' . $this->Type());
        
        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddPathSequentialDescription()
    {
        $descriptions_1 = array(
            $this->Type() . ' Description 1',
        );
        
        $this->Instance()->AddPath('/' . $this->Type() . '_1/', $descriptions_1);

        $descriptions_2 = array(
            null,
            $this->Type() . ' Description 2',
        );
        
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/', $descriptions_2);

        $descriptions_3 = array(
            null,
            null,
            $this->Type() . ' Description 3',
        );
        
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3', $descriptions_3);
        
        $queryTable = $this->getConnection()->createQueryTable(
            'phprbac_' . $this->Type(), 'SELECT * FROM phprbac_' . $this->Type()
        );
        
        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/expected_add_path_' . $this->Type() . '_sequential_description.xml')
            ->getTable('phprbac_' . $this->Type());
        
        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddPathHierarchy()
    {
        
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3');
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_4');
        
        $this->Instance()->AddPath('/' . $this->Type() . '_12/' . $this->Type() . '_13/' . $this->Type() . '_14');
        $this->Instance()->AddPath('/' . $this->Type() . '_12/' . $this->Type() . '_15/' . $this->Type() . '_11');
        
        $this->Instance()->AddPath('/' . $this->Type() . '_23/' . $this->Type() . '_24/' . $this->Type() . '_25');
        $this->Instance()->AddPath('/' . $this->Type() . '_33/' . $this->Type() . '_34/' . $this->Type() . '_35');
    
        $queryTable = $this->getConnection()->createQueryTable(
            'phprbac_' . $this->Type(), 'SELECT * FROM phprbac_' . $this->Type()
        );
    
        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/expected_add_path_' . $this->Type() . '_hierarchy.xml')
        ->getTable('phprbac_' . $this->Type());
    
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    
    public function testAddPathHierarchyDescription()
    {

        $descriptions_1 = array(
            $this->Type() . ' Description 1',
            $this->Type() . ' Description 2',
            $this->Type() . ' Description 3',
        );
        
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3', $descriptions_1);

        $descriptions_2 = array(
            null,
            null,
            $this->Type() . ' Description 4',
        );
        
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_4', $descriptions_2);

        $descriptions_3 = array(
            $this->Type() . ' Description 12',
            $this->Type() . ' Description 13',
            $this->Type() . ' Description 14',
        );
        
        $this->Instance()->AddPath('/' . $this->Type() . '_12/' . $this->Type() . '_13/' . $this->Type() . '_14', $descriptions_3);

        $descriptions_4 = array(
            null,
            $this->Type() . ' Description 15',
            $this->Type() . ' Description 11',
        );
        
        $this->Instance()->AddPath('/' . $this->Type() . '_12/' . $this->Type() . '_15/' . $this->Type() . '_11', $descriptions_4);

        $descriptions_5 = array(
            $this->Type() . ' Description 23',
            $this->Type() . ' Description 24',
            $this->Type() . ' Description 25',
        );
        
        $this->Instance()->AddPath('/' . $this->Type() . '_23/' . $this->Type() . '_24/' . $this->Type() . '_25', $descriptions_5);

        $descriptions_6 = array(
            $this->Type() . ' Description 33',
            $this->Type() . ' Description 34',
            $this->Type() . ' Description 35',
        );
        
        $this->Instance()->AddPath('/' . $this->Type() . '_33/' . $this->Type() . '_34/' . $this->Type() . '_35', $descriptions_6);
    
        $queryTable = $this->getConnection()->createQueryTable(
            'phprbac_' . $this->Type(), 'SELECT * FROM phprbac_' . $this->Type()
        );
    
        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/expected_add_path_' . $this->Type() . '_hierarchy_description.xml')
        ->getTable('phprbac_' . $this->Type());
    
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    
    /*
     * Tests for $this->Type()->PathID()
     */
    
    public function testPathID()
    {
        $this->Instance()->AddPath('/' . $this->Type() . '_1/');
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/');
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3');
        
        $path_id = $this->Instance()->PathID('/' . $this->Type() . '_1/' . $this->Type() . '_2');
        
        $this->assertSame('3', $path_id);
    }

    public function testPathIDNullBadPath()
    {
        $this->Instance()->AddPath('/' . $this->Type() . '_1/');
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/');
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3');
    
        $path_id = $this->Instance()->PathID($this->Type() . '_2');
    
        $this->assertNull($path_id);
    }

    /*
     * Tests for $this->Type()->Path()
     */
    
    public function testPath()
    {
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3');
        
        $path_returned = $this->Instance()->Path(intval(3));
        
        $this->assertSame('/' . $this->Type() . '_1/' . $this->Type() . '_2', $path_returned);
    }

    public function testPathNull()
    {
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3');
    
        $path_returned = $this->Instance()->Path(intval(5));
    
        $this->assertNull($path_returned);
    }

    /*
     * Tests for $this->Type()->Children()
     */
    
    public function testChildren()
    {
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3/' . $this->Type() . '_4/' . $this->Type() . '_5');
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3/' . $this->Type() . '_6/' . $this->Type() . '_7');
        $path_id = $this->Instance()->PathID('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3');
        
        $children = $this->Instance()->Children($path_id);
        
        $expected = array(
        	array(
        	   'ID' => '5',
        	   'Lft' => '4',
        	   'Rght' => '7',
        	   'Title' => $this->Type() . '_4',
        	   'Description' => '',
            ),
        	array(
        	   'ID' => '7',
        	   'Lft' => '8',
        	   'Rght' => '11',
        	   'Title' => $this->Type() . '_6',
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
     * Tests for $this->Type()->Descendants()
     */
    
    public function testDescendants()
    {
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3/' . $this->Type() . '_4/' . $this->Type() . '_5');
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3/' . $this->Type() . '_6/' . $this->Type() . '_7');
        $path_id = $this->Instance()->PathID('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3');
    
        $descendants = $this->Instance()->Descendants($path_id);
    
        $expected = array(
            $this->Type() . '_4' => array(
        	   'ID' => '5',
        	   'Lft' => '4',
        	   'Rght' => '7',
        	   'Title' => $this->Type() . '_4',
        	   'Description' => '',
        	   'Depth' => '1',
            ),
            $this->Type() . '_5' => array(
        	   'ID' => '6',
        	   'Lft' => '5',
        	   'Rght' => '6',
        	   'Title' => $this->Type() . '_5',
        	   'Description' => '',
        	   'Depth' => '2',
            ),
            $this->Type() . '_6' => array(
        	   'ID' => '7',
        	   'Lft' => '8',
        	   'Rght' => '11',
        	   'Title' => $this->Type() . '_6',
        	   'Description' => '',
    	       'Depth' => '1',
            ),
            $this->Type() . '_7' => array(
        	   'ID' => '8',
        	   'Lft' => '9',
        	   'Rght' => '10',
        	   'Title' => $this->Type() . '_7',
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
     * Tests for $this->Type()->Depth()
     */
    
    public function testDepth()
    {
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3/' . $this->Type() . '_4/' . $this->Type() . '_5');
        $path_id = $this->Instance()->PathID('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3');
    
        $depth = $this->Instance()->Depth($path_id);
    
        $this->assertSame(3, $depth);
    }

    public function testDepthBadID()
    {
        $depth = $this->Instance()->Depth(20);
    
        $this->assertSame(-1, $depth);
    }

    /*
     * Tests for $this->Type()->ParentNode()
     */
    
    public function testParentNode()
    {
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3/' . $this->Type() . '_4/' . $this->Type() . '_5');
        $this->Instance()->AddPath('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3/' . $this->Type() . '_6/' . $this->Type() . '_7');
        $path_id = $this->Instance()->PathID('/' . $this->Type() . '_1/' . $this->Type() . '_2/' . $this->Type() . '_3');
    
        $parent_node = $this->Instance()->ParentNode($path_id);
        
        $expected = array(
            'ID' => '3',
            'Lft' => '2',
            'Rght' => '13',
            'Title' => $this->Type() . '_2',
            'Description' => '',
        );
    
        $this->assertSame($expected, $parent_node);
    }
    
    public function testParentNodeNullBadID()
    {
        $parent_node = $this->Instance()->ParentNode(20);
    
        $this->assertNull($parent_node);
    }
}

/** @} */ // End group phprbac_unit_test_wrapper */
