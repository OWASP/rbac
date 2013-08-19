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
class RbacTest extends \Generic_Tests_DatabaseTestCase
{
	public static $rbac;
    
    public static function setUpBeforeClass()
    {
    	self::$rbac = new Rbac('unit_test');
    }
    
    public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__) . '/datasets/database-seed.xml');
    }
    
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

    public function testAddPermissionNullTitle()
    {
        $tableNames = array('phprbac_permissions');
        $dataSet = $this->getConnection()->createDataSet();
    
        $perm_id = self::$rbac->Permissions->Add(null, 'Can view content');
    
        $this->assertEquals(0, $perm_id);
    }

    public function testAddPermissionNullDescription()
    {
        $tableNames = array('phprbac_permissions');
        $dataSet = $this->getConnection()->createDataSet();
    
        $perm_id = self::$rbac->Permissions->Add('view_content', null);
    
        $this->assertEquals(0, $perm_id);
    }
    
    public function testAddPermissionSequential()
    {
        $tableNames = array('phprbac_permissions');
        $dataSet = $this->getConnection()->createDataSet();

        self::$rbac->Permissions->Add('view_content', 'Can view content');
        self::$rbac->Permissions->Add('edit_content', 'Can edit content');
        self::$rbac->Permissions->Add('delete_content', 'Can delete content');
        
        $queryTable = $this->getConnection()->createQueryTable(
            'phprbac_permissions', 'SELECT * FROM phprbac_permissions'
        );
        
        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/expected_add_permission_sequential.xml')
            ->getTable('phprbac_permissions');
        
        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddPermissionHierarchy()
    {
        $tableNames = array('phprbac_permissions');
        $dataSet = $this->getConnection()->createDataSet();

        $perm_1 = self::$rbac->Permissions->Add('blog', 'Define permissions for the Blog');
        self::$rbac->Permissions->Add('view_content', 'Can view content', $perm_1);
        self::$rbac->Permissions->Add('edit_content', 'Can edit content', $perm_1);
        self::$rbac->Permissions->Add('delete_content', 'Can delete content', $perm_1);
        
        $perm_2 = self::$rbac->Permissions->Add('forum', 'Define permissions for the Forums');
        self::$rbac->Permissions->Add('view_content', 'Can view content', $perm_2);
        self::$rbac->Permissions->Add('edit_content', 'Can edit content', $perm_2);
        self::$rbac->Permissions->Add('delete_content', 'Can delete content', $perm_2);
    
        $queryTable = $this->getConnection()->createQueryTable(
            'phprbac_permissions', 'SELECT * FROM phprbac_permissions'
        );
    
        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/expected_add_permission_hierarchy.xml')
        ->getTable('phprbac_permissions');
    
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    
    public function testReset()
    {
    	$this->assertTrue(true);
    }
    
    public function testAssign()
    {
        $this->assertTrue(true);
    }
    
    public function testCheck()
    {
        $this->assertTrue(true);
    }
    
    public function testEnforce()
    {
        $this->assertTrue(true);
    }
}

/** @} */ // End group phprbac_unit_test_wrapper */
