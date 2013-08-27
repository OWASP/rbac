<?php
require_once __DIR__."/base.php";
class PHPRBACPermissionsTest extends PHPRBACBaseTest
{
	/**
	 * 
	 * @return \jf\PermissionManager
	 */
	protected function Instance()
	{
		return jf::$RBAC->Permissions;
	}
	
	protected function type()
	{
		return "permissions";
	}
	
	function testUnassignRoles()
	{
		$ID1 = jf::$RBAC->Roles->Add ( "role1", "description of role1" );
		$ID2 = jf::$RBAC->Roles->Add ( "role2", "description of role2" );
		$ID11 = jf::$RBAC->Roles->Add ( "role1-1", "description of role", $ID1 );
		$ID12 = jf::$RBAC->Roles->Add ( "role1-2", "description of role", $ID1 );
		$ID121 = jf::$RBAC->Roles->Add ( "role1-2-1", "description of role", $ID12 );
		
		$PID1 = jf::$RBAC->Permissions->Add ( "permission1", "description" );
		$PID2 = jf::$RBAC->Permissions->Add ( "permission2", "description" );
		$PID21 = jf::$RBAC->Permissions->Add ( "permission2-1", "description", $PID2 );

		$this->Instance()->Assign($ID121,$PID2);
		$this->assertTrue(jf::$RBAC->Roles->HasPermission($ID121, $PID2));
		$this->Instance()->UnassignRoles($PID2);
		$this->assertFalse(jf::$RBAC->Roles->HasPermission($ID121, $PID2));
	}
	
	function testRoles()
	{
		$ID1 = jf::$RBAC->Roles->Add ( "role1", "description of role1" );
		$ID2 = jf::$RBAC->Roles->Add ( "role2", "description of role2" );
		$ID11 = jf::$RBAC->Roles->Add ( "role1-1", "description of role", $ID1 );
		$ID12 = jf::$RBAC->Roles->Add ( "role1-2", "description of role", $ID1 );
		$ID121 = jf::$RBAC->Roles->Add ( "role1-2-1", "description of role", $ID12 );
		
		$PID1 = jf::$RBAC->Permissions->Add ( "permission1", "description" );
		$PID2 = jf::$RBAC->Permissions->Add ( "permission2", "description" );
		$PID21 = jf::$RBAC->Permissions->Add ( "permission2-1", "description", $PID2 );

		jf::$RBAC->Permissions->Assign ( $ID121, $PID2 );
		$this->assertEquals(jf::$RBAC->Permissions->Roles($PID2,true),array($ID121));
		jf::$RBAC->Permissions->Assign ( $ID2, $PID2 );
		$this->assertEquals(jf::$RBAC->Permissions->Roles($PID2,true),array($ID2,$ID121));
		$this->assertEquals(2,count(jf::$RBAC->Permissions->Roles($PID2)));
		
	}
}