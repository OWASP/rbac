<?php
require_once __DIR__."/base.php";
class PhpRbacPermissionsTest extends PhpRbacBaseTest
{
	/**
	 * 
	 * @return \Jf\PermissionManager
	 */
	protected function Instance()
	{
		return Jf::$Rbac->Permissions;
	}
	
	protected function type()
	{
		return "permissions";
	}
	
	function testUnassignRoles()
	{
		$ID1 = Jf::$Rbac->Roles->add ( "role1", "description of role1" );
		$ID2 = Jf::$Rbac->Roles->add ( "role2", "description of role2" );
		$ID11 = Jf::$Rbac->Roles->add ( "role1-1", "description of role", $ID1 );
		$ID12 = Jf::$Rbac->Roles->add ( "role1-2", "description of role", $ID1 );
		$ID121 = Jf::$Rbac->Roles->add ( "role1-2-1", "description of role", $ID12 );
		
		$PID1 = Jf::$Rbac->Permissions->add ( "permission1", "description" );
		$PID2 = Jf::$Rbac->Permissions->add ( "permission2", "description" );
		$PID21 = Jf::$Rbac->Permissions->add ( "permission2-1", "description", $PID2 );

		$this->Instance()->assign($ID121,$PID2);
		$this->assertTrue(Jf::$Rbac->Roles->hasPermission($ID121, $PID2));
		$this->Instance()->unassignRoles($PID2);
		$this->assertFalse(Jf::$Rbac->Roles->hasPermission($ID121, $PID2));
	}
	
	function testRoles()
	{
		$ID1 = Jf::$Rbac->Roles->add ( "role1", "description of role1" );
		$ID2 = Jf::$Rbac->Roles->add ( "role2", "description of role2" );
		$ID11 = Jf::$Rbac->Roles->add ( "role1-1", "description of role", $ID1 );
		$ID12 = Jf::$Rbac->Roles->add ( "role1-2", "description of role", $ID1 );
		$ID121 = Jf::$Rbac->Roles->add ( "role1-2-1", "description of role", $ID12 );
		
		$PID1 = Jf::$Rbac->Permissions->add ( "permission1", "description" );
		$PID2 = Jf::$Rbac->Permissions->add ( "permission2", "description" );
		$PID21 = Jf::$Rbac->Permissions->add ( "permission2-1", "description", $PID2 );

		Jf::$Rbac->Permissions->assign ( $ID121, $PID2 );
		$this->assertEquals(Jf::$Rbac->Permissions->roles($PID2,true),array($ID121));
		Jf::$Rbac->Permissions->assign ( $ID2, $PID2 );
		$this->assertEquals(Jf::$Rbac->Permissions->roles($PID2,true),array($ID2,$ID121));
		$this->assertEquals(2,count(Jf::$Rbac->Permissions->roles($PID2)));
		
	}
}