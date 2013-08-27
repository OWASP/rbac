<?php
namespace PhpRbac;

/**
 * @file
 * Unit Tests for PhpRbac PSR Wrapper
 *
 * @defgroup phprbac_unit_test_wrapper_role_manager Unit Tests for Base RBAC Functionality
 * @ingroup phprbac
 * @{
 * Documentation for all Unit Tests regarding RoleManager functionality.
 */

class RbacRolesTest extends \RbacBase
{
    protected function Instance()
    {
        return self::$rbac->Roles;
    }
    
    protected function type()
    {
        return "roles";
    }

    /*
     * Test for proper object instantiation
     */
    
    public function testRolesInstance() {
        $this->assertInstanceOf('RoleManager', self::$rbac->Roles);
    }

    /*
     * Tests for self::$rbac->Roles->Permissions()
    */
    
    public function testRolesPermissionsIdOnly()
    {
        $perm_id_1 = self::$rbac->Permissions->Add('permissions_1', 'permissions Description 1');
        $perm_id_2 = self::$rbac->Permissions->Add('permissions_1', 'permissions Description 1');
        $perm_id_3 = self::$rbac->Permissions->Add('permissions_1', 'permissions Description 1');
        
        $role_id_1 = $this->Instance()->Add($this->type() . '_1', $this->type() . ' Description 1');
        
        $this->Instance()->Assign($role_id_1, $perm_id_1);
        $this->Instance()->Assign($role_id_1, $perm_id_2);
        $this->Instance()->Assign($role_id_1, $perm_id_3);
        
        $result = $this->Instance()->Permissions($perm_id_1);
        
        $expected = array('2', '3', '4');
        
        $this->assertSame($expected, $result);
    }

    /*
    // @todo: Need to come back to this one, returns null when it shouldn't
    public function testRolesPermissionsNotOnlyID()
    {
    
    }
    //*/

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    
    public function testRolesPermissionsPassNothing()
    {
        $result = $this->Instance()->Permissions();
    }
    
    /*
     * Tests for self::$rbac->Roles->HasPermission()
     */
    
    public function testRolesHasPermission()
    {
        $perm_id_1 = self::$rbac->Permissions->Add('permissions_1', 'permissions Description 1');
        $role_id_1 = $this->Instance()->Add($this->type() . '_1', $this->type() . ' Description 1');
        
        $this->Instance()->Assign($role_id_1, $perm_id_1);
        
        $result = self::$rbac->Roles->HasPermission($role_id_1, $perm_id_1);
        
        $this->assertTrue($result);
    }

    public function testRolesHasPermissionFalse()
    {
        $role_id_1 = $this->Instance()->Add($this->type() . '_1', $this->type() . ' Description 1');
    
        $result = self::$rbac->Roles->HasPermission($role_id_1, 4);
    
        $this->assertFalse($result);
    }
    
    /*
     * Tests for self::$rbac->Roles->UnassignPermissions()
     */
    
    public function testRolesUnassignPermissions()
    {
        $role_id_1 = $this->Instance()->Add($this->type() . '_1', $this->type() . ' Description 1');
    
        $perm_id_1 = self::$rbac->Permissions->Add('permissions_1', 'permissions Description 1');
        $perm_id_2 = self::$rbac->Permissions->Add('permissions_2', 'permissions Description 2');
        $perm_id_3 = self::$rbac->Permissions->Add('permissions_3', 'permissions Description 3');
    
        $this->Instance()->Assign($role_id_1, $perm_id_1);
        $this->Instance()->Assign($role_id_1, $perm_id_2);
        $this->Instance()->Assign($role_id_1, $perm_id_3);
    
        $result = $this->Instance()->UnassignPermissions($role_id_1);
    
        $dataSet = $this->getConnection()->createDataSet();
    
        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            $this->Instance()->tablePrefix() . 'rolepermissions',
        ));
    
        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );
    
        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/' . $this->type() . '/expected_unassign_permissions.xml');
    
        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }
    
    public function testRolesUnassignPermissionsBadID()
    {
        $result = $this->Instance()->UnassignPermissions(20);
    
        $this->assertSame(0, $result);
    }

    /*
     * Tests for self::$rbac->Roles->UnassignUsers()
     */
    
    public function testRolesUnassignUsers()
    {
        $role_id_1 = $this->Instance()->Add($this->type() . '_1', $this->type() . ' Description 1');
        $role_id_2 = $this->Instance()->Add($this->type() . '_1', $this->type() . ' Description 1');
        $role_id_3 = $this->Instance()->Add($this->type() . '_1', $this->type() . ' Description 1');
    
        self::$rbac->Users->Assign($role_id_1, 5);
        self::$rbac->Users->Assign($role_id_2, 5);
        self::$rbac->Users->Assign($role_id_3, 5);
    
        $result = $this->Instance()->UnassignUsers($role_id_2);
    
        $dataSet = $this->getConnection()->createDataSet();
    
        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            $this->Instance()->tablePrefix() . 'userroles',
        ));
    
        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'userroles',
            array('AssignmentDate')
        );
    
        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/' . $this->type() . '/expected_unassign_users.xml');
    
        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }
    
    public function testRolesUnassignUsersBadID()
    {
        $result = $this->Instance()->UnassignUsers(20);
    
        $this->assertSame(0, $result);
    }
    
    /*
     * Tests for self::$rbac->Roles->Remove()
     */
    
    public function testRolesRemoveSingle()
    {
        $role_id_1 = $this->Instance()->Add($this->type() . '_1', $this->type() . ' Description 1');
    
        $this->Instance()->Remove($role_id_1);
    
        $dataSet = $this->getConnection()->createDataSet();
    
        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            $this->Instance()->tablePrefix() . $this->type(),
        ));
    
        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/' . $this->type() . '/expected_remove_single.xml');
    
        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }
    
    public function testRolesRemoveSinglePermission()
    {
        $perm_id_1 = self::$rbac->Permissions->Add('permissions_1', 'permissions Description 1');
        $perm_id_2 = self::$rbac->Permissions->Add('permissions_2', 'permissions Description 2');
        $perm_id_3 = self::$rbac->Permissions->Add('permissions_3', 'permissions Description 3');

        $role_id_1 = $this->Instance()->Add($this->type() . '_1', $this->type() . ' Description 1');
        $role_id_2 = $this->Instance()->Add($this->type() . '_2', $this->type() . ' Description 2');
        $role_id_3 = $this->Instance()->Add($this->type() . '_3', $this->type() . ' Description 3');
        
        $this->Instance()->Assign($role_id_1, $perm_id_1);
        $this->Instance()->Assign($role_id_1, $perm_id_2);
        $this->Instance()->Assign($role_id_1, $perm_id_3);
    
        self::$rbac->Users->Assign($role_id_1, 5);
    
        $this->Instance()->Remove($role_id_1);
    
        $dataSet = $this->getConnection()->createDataSet();
    
        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addExcludeTables(array(
            $this->Instance()->tablePrefix() . 'permissions',
        ));
        
        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );
        
        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'userroles',
            array('AssignmentDate')
        );
    
        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/' . $this->type() . '/expected_remove_single_permission.xml');
    
        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }
    
    public function testRolesRemoveRecursive()
    {
        $role_id_1 = $this->Instance()->Add($this->type() . '_1', $this->type() . ' Description 1');
        $role_id_2 = $this->Instance()->Add($this->type() . '_2', $this->type() . ' Description 2', $role_id_1);
        $role_id_3 = $this->Instance()->Add($this->type() . '_3', $this->type() . ' Description 3', $role_id_1);
        $role_id_4 = $this->Instance()->Add($this->type() . '_4', $this->type() . ' Description 4');
    
        $perm_id_1 = self::$rbac->Permissions->Add('permissions_1', 'permissions Description 1');
    
        $this->Instance()->Assign($role_id_1, $perm_id_1);
    
        self::$rbac->Users->Assign($role_id_1, 5);
        
        $result = $this->Instance()->Remove($role_id_1, true);
    
        $dataSet = $this->getConnection()->createDataSet();
    
        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            $this->Instance()->tablePrefix() . $this->type(),
            $this->Instance()->tablePrefix() . 'userroles',
        ));
    
        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'userroles',
            array('AssignmentDate')
        );
        
        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/' . $this->type() . '/expected_remove_recursive.xml');
    
        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }
    
    public function testRolesRemoveFalse()
    {
        $result = $this->Instance()->Remove(5);
    
        $this->assertFalse($result);
    }
}

/** @} */ // End group phprbac_unit_test_wrapper_role_manager */
