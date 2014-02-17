<?php
namespace PhpRbac;

/**
 * @file
 * Unit Tests for PhpRbac PSR Wrapper
 *
 * @defgroup phprbac_unit_test_wrapper_manager Unit Tests for RbacManager Functionality
 * @ingroup phprbac_unit_tests
 * @{
 * Documentation for all Unit Tests regarding RbacManager functionality.
 */

class RbacManagerTest extends \RbacSetup
{


    /*
     * Tests for self::$rbac->assign()
     */

    public function testManagerAssignWithId()
    {
        $perm_id = self::$rbac->Permissions->add('permissions_1', 'permissions Description 1');
        $role_id = self::$rbac->Roles->add('roles_1', 'roles Description 1');

        self::$rbac->assign($role_id, $perm_id);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addExcludeTables(array(self::$rbac->tablePrefix() . 'userroles'));
        $filterDataSet->setExcludeColumnsForTable(
            self::$rbac->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/manager/expected_assign_id.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    public function testManagerAssignWithTitle()
    {
        $perm_id = self::$rbac->Permissions->add('permissions_1', 'permissions Description 1');
        $role_id = self::$rbac->Roles->add('roles_1', 'roles Description 1');

        self::$rbac->assign('roles_1', 'permissions_1');

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addExcludeTables(array(self::$rbac->tablePrefix() . 'userroles'));
        $filterDataSet->setExcludeColumnsForTable(
            self::$rbac->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/manager/expected_assign_title.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    public function testManagerAssignWithPath()
    {
        self::$rbac->Permissions->addPath('/permissions_1/permissions_2/permissions_3');
        self::$rbac->Roles->addPath('/roles_1/roles_2/roles_3');

        self::$rbac->assign('/roles_1/roles_2', '/permissions_1/permissions_2');

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addExcludeTables(array(self::$rbac->tablePrefix() . 'userroles'));

        $filterDataSet->setExcludeColumnsForTable(
            self::$rbac->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/manager/expected_assign_path.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    public function testManagerAssignWithNullRoleNullPermFalse()
    {
        $return = self::$rbac->assign(null, null);

        $this->assertFalse($return);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */

    public function testManagerAssignWithNullRoleNoPermError()
    {
        $return = self::$rbac->assign(null);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */

    public function testManagerAssignWithNoParametersError()
    {
        $return = self::$rbac->assign(null);
    }

    /*
     * Tests for self::$rbac->check()
     */

    public function testManagerCheckId()
    {
        $role_id_1 = self::$rbac->Roles->add('roles_1', 'roles Description 1');
        $perm_id_1 = self::$rbac->Permissions->add('permissions_1', 'permissions Description 1');

        self::$rbac->Roles->assign($role_id_1, $perm_id_1);
        self::$rbac->Users->assign($role_id_1, 5);

        $result = self::$rbac->check($perm_id_1, 5);

        $this->assertTrue($result);
    }

    public function testManagerCheckTitle()
    {
        $role_id_1 = self::$rbac->Roles->add('roles_1', 'roles Description 1');
        $perm_id_1 = self::$rbac->Permissions->add('permissions_1', 'permissions Description 1');

        self::$rbac->Roles->assign($role_id_1, $perm_id_1);
        self::$rbac->Users->assign($role_id_1, 5);

        $result = self::$rbac->check('permissions_1', 5);

        $this->assertTrue($result);
    }

    public function testManagerCheckPath()
    {
        self::$rbac->Permissions->addPath('/permissions_1/permissions_2/permissions_3');
        $perm_id_1 = self::$rbac->Permissions->pathId('/permissions_1/permissions_2/permissions_3');

        self::$rbac->Roles->addPath('/roles_1/roles_2/roles_3');
        $role_id_1 = self::$rbac->Roles->pathId('/roles_1/roles_2/roles_3');

        self::$rbac->Roles->assign($role_id_1, 3);
        self::$rbac->Users->assign($role_id_1, 5);

        $result = self::$rbac->check('/permissions_1/permissions_2', 5);

        $this->assertTrue($result);
    }

    public function testManagerCheckBadPermBadUserFalse()
    {
        $result = self::$rbac->check(5, 5);

        $this->assertFalse($result);
    }

    /**
     * @expectedException RbacUserNotProvidedException
     */

    public function testManagerCheckWithNullUserIdException()
    {
        self::$rbac->check(5, null);
    }

    /**
     * @expectedException RbacPermissionNotFoundException
     */

    public function testManagerCheckWithNullPermException()
    {
        $perm_id = self::$rbac->Permissions->add('permissions_1', 'permissions Description 1');
        self::$rbac->check(null, $perm_id);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */

    public function testManagerCheckWithNoUserIdException()
    {
        self::$rbac->check(5);
    }

    /*
     * Tests for self::$rbac->enforce()
     */

    public function testManagerEnforceId()
    {
        $role_id_1 = self::$rbac->Roles->add('roles_1', 'roles Description 1');
        $perm_id_1 = self::$rbac->Permissions->add('permissions_1', 'permissions Description 1');

        self::$rbac->Roles->assign($role_id_1, $perm_id_1);
        self::$rbac->Users->assign($role_id_1, 5);

        $result = self::$rbac->enforce($perm_id_1, 5);

        $this->assertTrue($result);
    }

    public function testManagerEnforceTitle()
    {
        $role_id_1 = self::$rbac->Roles->add('roles_1', 'roles Description 1');
        $perm_id_1 = self::$rbac->Permissions->add('permissions_1', 'permissions Description 1');

        self::$rbac->Roles->assign($role_id_1, $perm_id_1);
        self::$rbac->Users->assign($role_id_1, 5);

        $result = self::$rbac->enforce('permissions_1', 5);

        $this->assertTrue($result);
    }

    public function testManagerEnforcePath()
    {
        self::$rbac->Permissions->addPath('/permissions_1/permissions_2/permissions_3');
        $perm_id_1 = self::$rbac->Permissions->pathId('/permissions_1/permissions_2/permissions_3');

        self::$rbac->Roles->addPath('/roles_1/roles_2/roles_3');
        $role_id_1 = self::$rbac->Roles->pathId('/roles_1/roles_2/roles_3');

        self::$rbac->Roles->assign($role_id_1, 3);
        self::$rbac->Users->assign($role_id_1, 5);

        $result = self::$rbac->enforce('/permissions_1/permissions_2', 5);

        $this->assertTrue($result);
    }

    /**
     * @expectedException RbacUserNotProvidedException
     */

    public function testManagerEnforceWithNullUserIdException()
    {
        self::$rbac->enforce(5, null);
    }

    /**
     * @expectedException RbacPermissionNotFoundException
     */

    public function testManagerEnforceWithNullPermException()
    {
        $perm_id = self::$rbac->Permissions->add('permissions_1', 'permissions Description 1');
        self::$rbac->enforce(null, $perm_id);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */

    public function testManagerEnforceWithNoUserIdException()
    {
        self::$rbac->enforce(5);
    }

    /*
     * Tests for self::$rbac->reset()
     */

    public function testManagerReset()
    {
        $role_id_1 = self::$rbac->Roles->add('roles_1', 'roles Description 1');
        $perm_id_1 = self::$rbac->Permissions->add('permissions_1', 'permissions Description 1');

        self::$rbac->Roles->assign($role_id_1, $perm_id_1);
        self::$rbac->Users->assign($role_id_1, 5);

        $result = self::$rbac->reset(true);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);

        $filterDataSet->setExcludeColumnsForTable(
            self::$rbac->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $filterDataSet->setExcludeColumnsForTable(
            self::$rbac->tablePrefix() . 'userroles',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/manager/expected_reset.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    /**
     * @expectedException Exception
     */

    public function testManagerResetFalseException()
    {
        self::$rbac->reset();
    }
}

/** @} */ // End group phprbac_unit_test_wrapper_manager */
