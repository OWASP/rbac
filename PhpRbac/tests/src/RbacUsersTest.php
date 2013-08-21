<?php
namespace PhpRbac;

class RbacUsersTest extends \RbacSetup
{
    public function testUsersInstance() {
        $this->assertInstanceOf('RBACUserManager', self::$rbac->Users);
    }
    
    /*
     * Tests for self::$rbac->Users->Assign()
     * 
     * @todo: Fix for RBACUserManager
     */
    
    

    /*
     * Tests for self::$rbac->Users->Unassign()
     *
     * @todo: Fix for RBACUserManager
     */
    
    /*
    public function testUnassign()
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
    
        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/expected_unassign_' . $this->Type() . '.xml');
    
        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }
    //*/
    
    /*
     * Tests for self::$rbac->Users->ResetAssignments()
     */
    
}