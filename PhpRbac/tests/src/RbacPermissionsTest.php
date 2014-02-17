<?php
namespace PhpRbac;

/**
 * @file
 * Unit Tests for PhpRbac PSR Wrapper
 *
 * @defgroup phprbac_unit_test_wrapper_permission_manager Unit Tests for PermissionManager Functionality
 * @ingroup phprbac_unit_tests
 * @{
 * Documentation for all Unit Tests regarding PermissionManager functionality.
 */

class RbacPermissionsTest extends \RbacBase
{
    protected function Instance()
    {
        return self::$rbac->Permissions;
    }

    protected function type()
    {
        return "permissions";
    }

    /*
     * Test for proper object instantiation
     */

    public function testPermissionsInstance() {
        $this->assertInstanceOf('PermissionManager', self::$rbac->Permissions);
    }

    /*
     * Tests for $this->Instance()->remove()
     */

    public function testPermissionsRemoveSingle()
    {
        $perm_id_1 = $this->Instance()->add($this->type() . '_1', $this->type() . ' Description 1');

        $this->Instance()->remove($perm_id_1);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            $this->Instance()->tablePrefix() . $this->type(),
        ));

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/' . $this->type() . '/expected_remove_single.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    public function testPermissionsRemoveSingleRole()
    {
        $perm_id_1 = $this->Instance()->add($this->type() . '_1', $this->type() . ' Description 1');
        $role_id_1 = self::$rbac->Roles->add('roles_1', 'roles Description 1');

        $this->Instance()->assign($role_id_1, $perm_id_1);

        $this->Instance()->remove($perm_id_1);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            $this->Instance()->tablePrefix() . $this->type(),
            $this->Instance()->tablePrefix() . 'rolepermissions',
            $this->Instance()->tablePrefix() . 'roles',
        ));

        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/' . $this->type() . '/expected_remove_single_role.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    public function testPermissionsRemoveRecursive()
    {
        $perm_id_1 = $this->Instance()->add($this->type() . '_1', $this->type() . ' Description 1');
        $perm_id_2 = $this->Instance()->add($this->type() . '_2', $this->type() . ' Description 2', $perm_id_1);
        $perm_id_3 = $this->Instance()->add($this->type() . '_3', $this->type() . ' Description 3', $perm_id_1);
        $perm_id_4 = $this->Instance()->add($this->type() . '_4', $this->type() . ' Description 4');

        $role_id_1 = self::$rbac->Roles->add('roles_1', 'roles Description 1');

        $this->Instance()->assign($role_id_1, $perm_id_2);

        $result = $this->Instance()->remove($perm_id_1, true);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            $this->Instance()->tablePrefix() . $this->type(),
            $this->Instance()->tablePrefix() . 'rolepermissions',
            $this->Instance()->tablePrefix() . 'roles',
        ));

        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/' . $this->type() . '/expected_remove_recursive.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    public function testPermissionsRemoveFalse()
    {
        $result = $this->Instance()->remove(5);

        $this->assertFalse($result);
    }

    /*
     * Tests for $this->Instance()->roles()
     */

    public function testPermissionsRolesOnlyID()
    {
        $perm_id_1 = $this->Instance()->add($this->type() . '_1', $this->type() . ' Description 1');

        $role_id_1 = self::$rbac->Roles->add('roles_1', 'roles Description 1');
        $role_id_2 = self::$rbac->Roles->add('roles_2', 'roles Description 2');
        $role_id_3 = self::$rbac->Roles->add('roles_3', 'roles Description 3');

        $this->Instance()->assign($role_id_1, $perm_id_1);
        $this->Instance()->assign($role_id_2, $perm_id_1);
        $this->Instance()->assign($role_id_3, $perm_id_1);

        $result = $this->Instance()->roles($perm_id_1);

        $expected = array('2', '3', '4');

        $this->assertSame($expected, $result);
    }

    public function testPermissionsRolesBadIDNull()
    {
        $result = $this->Instance()->roles(20);

        $this->assertNull($result);
    }

    public function testPermissionsRolesNotOnlyID()
    {
        self::$rbac->Roles->addPath("/roles_1/roles_2");
        self::$rbac->Permissions->addPath("/permissions_1/permissions_2");

        self::$rbac->assign("/roles_1/roles_2", "/permissions_1/permissions_2");

        $rolesAssigned = self::$rbac->Permissions->roles('/permissions_1/permissions_2', false);

        $expected = array(
            array(
                'ID' => '3',
                'Title' => 'roles_2',
                'Description' => '',
            ),
        );

        $this->assertSame($expected, $rolesAssigned);
    }

    public function testPermissionsRolesNotOnlyIDNullBadParameters()
    {
        $rolesAssigned = self::$rbac->Permissions->roles('/permissions_1/permissions_2', false);

        $this->assertSame(null, $rolesAssigned);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */

    public function testPermissionsRolesPassNothing()
    {
        $result = $this->Instance()->roles();
    }

    /*
     * Tests for $this->Instance()->unassignRoles()
     */

    public function testPermissionsUnassignRoles()
    {
        $perm_id_1 = $this->Instance()->add($this->type() . '_1', $this->type() . ' Description 1');

        $role_id_1 = self::$rbac->Roles->add('roles_1', 'roles Description 1');
        $role_id_2 = self::$rbac->Roles->add('roles_2', 'roles Description 2');
        $role_id_3 = self::$rbac->Roles->add('roles_3', 'roles Description 3');

        $this->Instance()->assign($role_id_1, $perm_id_1);
        $this->Instance()->assign($role_id_2, $perm_id_1);
        $this->Instance()->assign($role_id_3, $perm_id_1);

        $result = $this->Instance()->unassignRoles($perm_id_1);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            $this->Instance()->tablePrefix() . 'rolepermissions',
        ));

        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/' . $this->type() . '/expected_unassign_roles.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    public function testPermissionsUnassignRolesBadID()
    {
        $result = $this->Instance()->unassignRoles(20);

        $this->assertSame(0, $result);
    }
}

/** @} */ // End group phprbac_unit_test_wrapper_permission_manager */
