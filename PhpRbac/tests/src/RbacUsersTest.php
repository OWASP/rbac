<?php
namespace PhpRbac;

/**
 * @file
 * Unit Tests for PhpRbac PSR Wrapper
 *
 * @defgroup phprbac_unit_test_wrapper_user_manager Unit Tests for RBACUserManager Functionality
 * @ingroup phprbac
 * @{
 * Documentation for all Unit Tests regarding RBACUserManager functionality.
 */

class RbacUsersTest extends \RbacSetup
{
    /*
     * Test for proper object instantiation
     */
    
    public function testUsersInstance() {
        $this->assertInstanceOf('RBACUserManager', self::$rbac->Users);
    }
    
    /*
     * Tests for self::$rbac->Users->Assign()
     */
    
    public function testUsersAssignWithId()
    {
        $role_id = self::$rbac->Roles->Add('roles_1', 'roles Description 1');
    
        self::$rbac->Users->Assign($role_id, 5);
    
        $dataSet = $this->getConnection()->createDataSet();
    
        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            self::$rbac->Users->TablePrefix() . 'userroles',
        ));
        
        $filterDataSet->setExcludeColumnsForTable(
            self::$rbac->Users->TablePrefix() . 'userroles',
            array('AssignmentDate')
        );
    
        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/users/expected_assign_with_id.xml');
    
        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    public function testUsersAssignWithPath()
    {
        $role_id = self::$rbac->Roles->AddPath('/roles_1/roles_2/roles_3');
    
        self::$rbac->Users->Assign($role_id, 5);
    
        $dataSet = $this->getConnection()->createDataSet();
    
        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            self::$rbac->Users->TablePrefix() . 'userroles',
        ));
    
        $filterDataSet->setExcludeColumnsForTable(
            self::$rbac->Users->TablePrefix() . 'userroles',
            array('AssignmentDate')
        );
    
        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/users/expected_assign_with_path.xml');
    
        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }
    
    public function testUsersAssignNoId()
    {
        $role_id = self::$rbac->Roles->Add('roles_1', 'roles Description 1');
    
        self::$rbac->Users->Assign($role_id);
    
        $dataSet = $this->getConnection()->createDataSet();
    
        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            self::$rbac->Users->TablePrefix() . 'userroles',
        ));
    
        $filterDataSet->setExcludeColumnsForTable(
            self::$rbac->Users->TablePrefix() . 'userroles',
            array('AssignmentDate')
        );
    
        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/users/expected_assign_no_id.xml');
    
        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    public function testUsersAssignNullRole()
    {
        $result = self::$rbac->Users->Assign(null);
    
        $this->assertFalse($result);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    
    public function testUsersAssignPassNothing()
    {
        $result = self::$rbac->Users->Assign();
    }
    
    /*
     * Tests for self::$rbac->Users->HasRole()
     */
    
    public function testUsersHasRoleId()
    {
        $role_id = self::$rbac->Roles->Add('roles_1', 'roles Description 1');
        
        self::$rbac->Users->Assign($role_id, 5);

        $result = self::$rbac->Users->HasRole($role_id, 5);
        
        $this->assertTrue($result);
    }

    public function testUsersHasRoleTitle()
    {
        $role_id = self::$rbac->Roles->Add('roles_1', 'roles Description 1');
    
        self::$rbac->Users->Assign($role_id, 5);
    
        $result = self::$rbac->Users->HasRole('roles_1', 5);
    
        $this->assertTrue($result);
    }

    public function testUsersHasRolePath()
    {
        $role_id = self::$rbac->Roles->AddPath('/roles_1/roles_2/roles_3');
    
        self::$rbac->Users->Assign($role_id, 5);
    
        $result = self::$rbac->Users->HasRole('/roles_1/roles_2/roles_3', 5);
    
        $this->assertTrue($result);
    }

    public function testUsersHasRoleNoUserId()
    {
        $role_id = self::$rbac->Roles->Add('roles_1', 'roles Description 1');
    
        self::$rbac->Users->Assign($role_id);
    
        $result = self::$rbac->Users->HasRole($role_id);
    
        $this->assertTrue($result);
    }

    public function testUsersHasRoleDoesNotHaveRole()
    {
        $role_id = self::$rbac->Roles->Add('roles_1', 'roles Description 1');
    
        self::$rbac->Users->Assign($role_id, 5);
    
        $result = self::$rbac->Users->HasRole(1, 5);
    
        $this->assertFalse($result);
    }

    public function testUsersHasRoleNullRole()
    {
        $role_id = self::$rbac->Roles->Add('roles_1', 'roles Description 1');
    
        self::$rbac->Users->Assign($role_id, 5);
    
        $result = self::$rbac->Users->HasRole(null);
    
        $this->assertFalse($result);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    
    public function testUsersHasRolePassNothing()
    {
        $result = self::$rbac->Users->HasRole();
    }

    /*
     * Tests for self::$rbac->Users->AllRoles()
     */
    
    public function testUsersAllRoles()
    {
        $role_id_1 = self::$rbac->Roles->Add('roles_1', 'roles Description 1');
        $role_id_2 = self::$rbac->Roles->Add('roles_2', 'roles Description 2');
        $role_id_3 = self::$rbac->Roles->Add('roles_3', 'roles Description 3');
        
        self::$rbac->Users->Assign($role_id_1, 5);
        self::$rbac->Users->Assign($role_id_2, 5);
        self::$rbac->Users->Assign($role_id_3, 5);
        
        $result = self::$rbac->Users->AllRoles(5);
        
        $expected = array(
        	array(
                'ID' => '2',
        	    'Lft' => '1',
        	    'Rght' => '2',
        	    'Title' => 'roles_1',
        	    'Description' => 'roles Description 1',
            ),
        	array(
                'ID' => '3',
        	    'Lft' => '3',
        	    'Rght' => '4',
        	    'Title' => 'roles_2',
        	    'Description' => 'roles Description 2',
            ),
        	array(
                'ID' => '4',
        	    'Lft' => '5',
        	    'Rght' => '6',
        	    'Title' => 'roles_3',
        	    'Description' => 'roles Description 3',
            ),
        );
        
        $this->assertSame($expected, $result);
    }
    
    public function testUsersAllRolesNoRolesNull()
    {
        $result = self::$rbac->Users->AllRoles(10);
        
        $this->assertNull($result);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    
    public function testUsersAllRolesNoRolesEmpty()
    {
        $result = self::$rbac->Users->AllRoles();
    }
    
    /*
     * Tests for self::$rbac->Users->Unassign()
     *
     * @todo: Fix for RBACUserManager
     */
    
    /*
    public function testUsersUnassign()
    {
        $perm_id = self::$rbac->Permissions->Add('permissions_1', 'permissions Description 1');
        $role_id = self::$rbac->Roles->Add('roles_1', 'roles Description 1');
    
        $this->Instance()->Assign($role_id, $perm_id);
        $this->Instance()->Unassign($role_id, $perm_id);
    
        $dataSet = $this->getConnection()->createDataSet();
    
        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addExcludeTables(array(
            $this->Instance()->TablePrefix() . 'permissions',
            $this->Instance()->TablePrefix() . 'roles',
            $this->Instance()->TablePrefix() . 'userroles',
        ));
        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->TablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );
    
        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/users/expected_unassign_' . $this->Type() . '.xml');
    
        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }
    //*/
    
    /*
     * Tests for self::$rbac->Users->ResetAssignments()
     */

    /*
     * Tests for self::$rbac->Users->HasRole()
     */
    
    /*
     * Tests for self::$rbac->Users->AllRoles()
    */

    /*
     * Tests for self::$rbac->Users->RoleCount()
     */
    
}

/** @} */ // End group phprbac_unit_test_wrapper_user_manager */
