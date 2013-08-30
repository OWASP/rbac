<?php
require_once __DIR__."/base.php";
class PHPRBACPermissionsTest extends PHPRBACBaseTest
{
	/**
	 * 
	 * @return \Jf\PermissionManager
	 */
	protected function Instance()
	{
		return Jf::$RBAC->Permissions;
	}
	
	protected function type()
	{
		return "permissions";
	}
	
	function testUnassignRoles()
	{
		$ID1 = Jf::$RBAC->Roles->Add ( "role1", "description of role1" );
		$ID2 = Jf::$RBAC->Roles->Add ( "role2", "description of role2" );
		$ID11 = Jf::$RBAC->Roles->Add ( "role1-1", "description of role", $ID1 );
		$ID12 = Jf::$RBAC->Roles->Add ( "role1-2", "description of role", $ID1 );
		$ID121 = Jf::$RBAC->Roles->Add ( "role1-2-1", "description of role", $ID12 );
		
		$PID1 = Jf::$RBAC->Permissions->Add ( "permission1", "description" );
		$PID2 = Jf::$RBAC->Permissions->Add ( "permission2", "description" );
		$PID21 = Jf::$RBAC->Permissions->Add ( "permission2-1", "description", $PID2 );

		$this->Instance()->assign($ID121,$PID2);
		$this->assertTrue(Jf::$RBAC->Roles->hasPermission($ID121, $PID2));
		$this->Instance()->unassignRoles($PID2);
		$this->assertFalse(Jf::$RBAC->Roles->hasPermission($ID121, $PID2));
	}
	
	function testRoles()
	{
		$ID1 = Jf::$RBAC->Roles->Add ( "role1", "description of role1" );
		$ID2 = Jf::$RBAC->Roles->Add ( "role2", "description of role2" );
		$ID11 = Jf::$RBAC->Roles->Add ( "role1-1", "description of role", $ID1 );
		$ID12 = Jf::$RBAC->Roles->Add ( "role1-2", "description of role", $ID1 );
		$ID121 = Jf::$RBAC->Roles->Add ( "role1-2-1", "description of role", $ID12 );
		
		$PID1 = Jf::$RBAC->Permissions->Add ( "permission1", "description" );
		$PID2 = Jf::$RBAC->Permissions->Add ( "permission2", "description" );
		$PID21 = Jf::$RBAC->Permissions->Add ( "permission2-1", "description", $PID2 );

		Jf::$RBAC->Permissions->assign ( $ID121, $PID2 );
		$this->assertEquals(Jf::$RBAC->Permissions->roles($PID2,true),array($ID121));
		Jf::$RBAC->Permissions->assign ( $ID2, $PID2 );
		$this->assertEquals(Jf::$RBAC->Permissions->roles($PID2,true),array($ID2,$ID121));
		$this->assertEquals(2,count(Jf::$RBAC->Permissions->roles($PID2)));
		
	}
}