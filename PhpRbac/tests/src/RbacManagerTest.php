<?php
namespace PhpRbac;

class RbacManagerTest extends \RbacSetup
{


    /*
     * Tests for $this->Instance()->Assign()
     */
    
    public function testManagerAssignWithId()
    {
        $perm_id = self::$rbac->Permissions->Add('permissions_1', 'permissions Description 1');
        $role_id = self::$rbac->Roles->Add('roles_1', 'roles Description 1');
    
        self::$rbac->Assign($role_id, $perm_id);
    
        $dataSet = $this->getConnection()->createDataSet();
    
        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addExcludeTables(array(self::$rbac->TablePrefix() . 'userroles'));
        $filterDataSet->setExcludeColumnsForTable(
            self::$rbac->TablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );
    
        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/expected_assign_manager_id.xml');
    
        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }
    
    /*
     * $Role, $Permission
     * id, id
     * path, path
     *
     * good, bad
     * bad, good
     * bad, bad
     */
    
    /*
    // Can only assign by path in RBACManager
    // @todo: Fix for RBACManager
    //
    // Note: Can assign by path or title
    
    public function testAssignWithPath()
    {
        self::$rbac->Permissions->AddPath('/permissions_1/permissions_2/permissions_3');
        self::$rbac->Roles->AddPath('/roles_1/roles_2/roles_3');
        
        $this->Instance()->Assign('/roles_1/roles_2', '/permissions_1/permissions_2');
        
        $dataSet = $this->getConnection()->createDataSet();
        
        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addExcludeTables(array($this->Instance()->TablePrefix() . 'userroles'));
        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->TablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );
        
        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/expected_assign_' . $this->Type() . '_id.xml');
        
        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }
    //*/
    
}