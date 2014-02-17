<?php
namespace PhpRbac;

/**
 * @file
 * Unit Tests for PhpRbac PSR Wrapper
 *
 * @defgroup phprbac_unit_test_wrapper_user_manager Unit Tests for RbacUserManager Functionality
 * @ingroup phprbac_unit_tests
 * @{
 * Documentation for all Unit Tests regarding RbacUserManager functionality.
 */

class RbacUsersTest extends \RbacSetup
{
    /*
     * Test for proper object instantiation
     */

    public function testUsersInstance() {
        $this->assertInstanceOf('RbacUserManager', self::$rbac->Users);
    }

    /*
     * Tests for self::$rbac->Users->assign()
     */

    public function testUsersAssignWithId()
    {
        $role_id = self::$rbac->Roles->add('roles_1', 'roles Description 1');

        self::$rbac->Users->assign($role_id, 5);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            self::$rbac->Users->tablePrefix() . 'userroles',
        ));

        $filterDataSet->setExcludeColumnsForTable(
            self::$rbac->Users->tablePrefix() . 'userroles',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/users/expected_assign_with_id.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    public function testUsersAssignWithPath()
    {
        self::$rbac->Roles->addPath('/roles_1/roles_2/roles_3');
        $role_id = self::$rbac->Roles->pathId('/roles_1/roles_2/roles_3');

        self::$rbac->Users->assign('/roles_1/roles_2', 5);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            self::$rbac->Users->tablePrefix() . 'userroles',
        ));

        $filterDataSet->setExcludeColumnsForTable(
            self::$rbac->Users->tablePrefix() . 'userroles',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/users/expected_assign_with_path.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    /**
     * @expectedException RbacUserNotProvidedException
     */

    public function testUsersAssignNoUserID()
    {
        $result = self::$rbac->Users->assign(5);

        $this->assertFalse($result);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */

    public function testUsersAssignPassNothing()
    {
        $result = self::$rbac->Users->assign();
    }

    /*
     * Tests for self::$rbac->Users->hasRole()
     */

    public function testUsersHasRoleId()
    {
        $role_id = self::$rbac->Roles->add('roles_1', 'roles Description 1');

        self::$rbac->Users->assign($role_id, 5);

        $result = self::$rbac->Users->hasRole($role_id, 5);

        $this->assertTrue($result);
    }

    public function testUsersHasRoleTitle()
    {
        $role_id = self::$rbac->Roles->add('roles_1', 'roles Description 1');

        self::$rbac->Users->assign($role_id, 5);

        $result = self::$rbac->Users->hasRole('roles_1', 5);

        $this->assertTrue($result);
    }

    public function testUsersHasRolePath()
    {
        self::$rbac->Roles->addPath('/roles_1/roles_2/roles_3');
        $role_id = self::$rbac->Roles->pathId('/roles_1/roles_2/roles_3');

        self::$rbac->Users->assign($role_id, 5);

        $result = self::$rbac->Users->hasRole('/roles_1/roles_2/roles_3', 5);

        $this->assertTrue($result);
    }

    public function testUsersHasRoleDoesNotHaveRole()
    {
        $role_id = self::$rbac->Roles->add('roles_1', 'roles Description 1');

        self::$rbac->Users->assign($role_id, 5);

        $result = self::$rbac->Users->hasRole(1, 5);

        $this->assertFalse($result);
    }

    public function testUsersHasRoleNullRole()
    {
        $role_id = self::$rbac->Roles->add('roles_1', 'roles Description 1');

        self::$rbac->Users->assign($role_id, 5);

        $result = self::$rbac->Users->hasRole(null, 5);

        $this->assertFalse($result);
    }

    /**
     * @expectedException RbacUserNotProvidedException
     */

    public function testUsersHasRoleNoUserId()
    {
        $result = self::$rbac->Users->hasRole(5);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */

    public function testUsersHasRolePassNothing()
    {
        $result = self::$rbac->Users->hasRole();
    }

    /*
     * Tests for self::$rbac->Users->allRoles()
     */

    public function testUsersAllRoles()
    {
        $role_id_1 = self::$rbac->Roles->add('roles_1', 'roles Description 1');
        $role_id_2 = self::$rbac->Roles->add('roles_2', 'roles Description 2');
        $role_id_3 = self::$rbac->Roles->add('roles_3', 'roles Description 3');

        self::$rbac->Users->assign($role_id_1, 5);
        self::$rbac->Users->assign($role_id_2, 5);
        self::$rbac->Users->assign($role_id_3, 5);

        $result = self::$rbac->Users->allRoles(5);

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

    public function testUsersAllRolesBadRoleNull()
    {
        $result = self::$rbac->Users->allRoles(10);

        $this->assertNull($result);
    }

    /**
     * @expectedException RbacUserNotProvidedException
     */

    public function testUsersAllRolesNoRolesEmpty()
    {
        $result = self::$rbac->Users->allRoles();
    }

    /*
     * Tests for self::$rbac->Users->roleCount()
     */

    public function testUsersRoleCount()
    {
        $role_id_1 = self::$rbac->Roles->add('roles_1', 'roles Description 1');
        $role_id_2 = self::$rbac->Roles->add('roles_2', 'roles Description 2');
        $role_id_3 = self::$rbac->Roles->add('roles_3', 'roles Description 3');

        self::$rbac->Users->assign($role_id_1, 5);
        self::$rbac->Users->assign($role_id_2, 5);
        self::$rbac->Users->assign($role_id_3, 5);

        $result = self::$rbac->Users->roleCount(5);

        $this->assertSame(3, $result);
    }

    public function testUsersRoleCountNoRoles()
    {
        $result = self::$rbac->Users->roleCount(10);

        $this->assertSame(0, $result);
    }

    /**
     * @expectedException RbacUserNotProvidedException
     */

    public function testUsersRoleCountNoRolesEmpty()
    {
        $result = self::$rbac->Users->roleCount();
    }

    /*
     * Tests for self::$rbac->Users->unassign()
     */

    public function testUsersUnassignId()
    {
        $role_id_1 = self::$rbac->Roles->add('roles_1', 'roles Description 1');
        $role_id_2 = self::$rbac->Roles->add('roles_2', 'roles Description 2');
        $role_id_3 = self::$rbac->Roles->add('roles_3', 'roles Description 3');

        self::$rbac->Users->assign($role_id_1, 5);
        self::$rbac->Users->assign($role_id_2, 5);
        self::$rbac->Users->assign($role_id_3, 5);

        self::$rbac->Users->unassign($role_id_2, 5);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            self::$rbac->Users->tablePrefix() . 'userroles',
        ));

        $filterDataSet->setExcludeColumnsForTable(
            self::$rbac->Users->tablePrefix() . 'userroles',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/users/expected_unassign.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    public function testUsersUnassignTitle()
    {
        $role_id_1 = self::$rbac->Roles->add('roles_1', 'roles Description 1');
        $role_id_2 = self::$rbac->Roles->add('roles_2', 'roles Description 2');
        $role_id_3 = self::$rbac->Roles->add('roles_3', 'roles Description 3');

        self::$rbac->Users->assign($role_id_1, 5);
        self::$rbac->Users->assign($role_id_2, 5);
        self::$rbac->Users->assign($role_id_3, 5);

        self::$rbac->Users->unassign('roles_2', 5);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            self::$rbac->Users->tablePrefix() . 'userroles',
        ));

        $filterDataSet->setExcludeColumnsForTable(
            self::$rbac->Users->tablePrefix() . 'userroles',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/users/expected_unassign.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    public function testUsersUnassignPath()
    {
        $role_id_1 = self::$rbac->Roles->add('roles_1', 'roles Description 1');
        $role_id_2 = self::$rbac->Roles->add('roles_2', 'roles Description 2');
        $role_id_3 = self::$rbac->Roles->add('roles_3', 'roles Description 3');

        self::$rbac->Users->assign($role_id_1, 5);
        self::$rbac->Users->assign($role_id_2, 5);
        self::$rbac->Users->assign($role_id_3, 5);

        self::$rbac->Users->unassign('/roles_2', 5);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            self::$rbac->Users->tablePrefix() . 'userroles',
        ));

        $filterDataSet->setExcludeColumnsForTable(
            self::$rbac->Users->tablePrefix() . 'userroles',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/users/expected_unassign.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    /**
     * @expectedException RbacUserNotProvidedException
     */

    public function testUsersUnassignNoUserIdException()
    {
        $result = self::$rbac->Users->unassign(5);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */

    public function testUsersUnassignNoRolesException()
    {
        $result = self::$rbac->Users->unassign();
    }

    /*
     * Tests for self::$rbac->Users->resetAssignments()
     */

    public function testUsersResetAssignments()
    {
        $role_id_1 = self::$rbac->Roles->add('roles_1', 'roles Description 1');
        $role_id_2 = self::$rbac->Roles->add('roles_2', 'roles Description 2');
        $role_id_3 = self::$rbac->Roles->add('roles_3', 'roles Description 3');

        self::$rbac->Users->assign($role_id_1, 5);
        self::$rbac->Users->assign($role_id_2, 5);
        self::$rbac->Users->assign($role_id_3, 5);

        self::$rbac->Users->resetAssignments(true);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            self::$rbac->Users->tablePrefix() . 'userroles',
        ));

        $filterDataSet->setExcludeColumnsForTable(
            self::$rbac->Users->tablePrefix() . 'userroles',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/users/expected_reset_assignments.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    /**
     * @expectedException Exception
     */

    public function testUsersResetAssignmentsException()
    {
        self::$rbac->Users->resetAssignments();
    }
}

/** @} */ // End group phprbac_unit_test_wrapper_user_manager */
