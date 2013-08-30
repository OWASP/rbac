<?php
require_once __DIR__."/base.php";

class PHPRBACRolesTest extends PHPRBACBaseTest
{
	/**
	 *
	 * @return \Jf\RoleManager
	 */
	protected function Instance()
	{
		return Jf::$Rbac->Roles;
	}
	protected function type()
	{
		return "role";
	}
	
	function testAssignPermission()
	{
		$ID1 = Jf::$Rbac->Roles->add ( "role1", "description of role1" );
		$ID2 = Jf::$Rbac->Roles->add ( "role2", "description of role2" );
		$ID11 = Jf::$Rbac->Roles->add ( "role1-1", "description of role", $ID1 );
		$ID12 = Jf::$Rbac->Roles->add ( "role1-2", "description of role", $ID1 );
		$ID121 = Jf::$Rbac->Roles->add ( "role1-2-1", "description of role", $ID12 );
		
		$PID1 = Jf::$Rbac->Permissions->add ( "permission1", "description" );
		$PID2 = Jf::$Rbac->Permissions->add ( "permission2", "description" );
		$PID21 = Jf::$Rbac->Permissions->add ( "permission2-1", "description", $PID2 );
		
		Jf::$Rbac->Roles->assign ( $ID121, $PID2 );
		
		$this->assertTrue ( Jf::$Rbac->Roles->hasPermission ( $ID121, $PID2 ) );
		
		$this->assertTrue ( Jf::$Rbac->Roles->hasPermission ( $ID1, $PID21 ) );
		$this->assertTrue ( Jf::$Rbac->Roles->hasPermission ( $ID12, $PID2 ) );
		$this->assertTrue ( Jf::$Rbac->Roles->hasPermission ( $ID121, $PID21 ) );
		
		$this->assertFalse ( Jf::$Rbac->Roles->hasPermission ( $ID11, $PID21 ) );
		$this->assertFalse ( Jf::$Rbac->Roles->hasPermission ( $ID2, $PID1 ) );
		$this->assertFalse ( Jf::$Rbac->Roles->hasPermission ( $ID2, $PID2 ) );
		$this->assertFalse ( Jf::$Rbac->Roles->hasPermission ( $ID2, $PID21 ) );
	}
	function testHasPermission()
	{
		$ID1 = Jf::$Rbac->Roles->add ( "role1", "description of role1" );
		$ID2 = Jf::$Rbac->Roles->add ( "role2", "description of role2" );
		$ID11 = Jf::$Rbac->Roles->add ( "role1-1", "description of role", $ID1 );
		$ID12 = Jf::$Rbac->Roles->add ( "role1-2", "description of role", $ID1 );
		$ID121 = Jf::$Rbac->Roles->add ( "role1-2-1", "description of role", $ID12 );
		
		$PID1 = Jf::$Rbac->Permissions->add ( "permission1", "description" );
		$PID2 = Jf::$Rbac->Permissions->add ( "permission2", "description" );
		$PID21 = Jf::$Rbac->Permissions->add ( "permission2-1", "description", $PID2 );
		
		Jf::$Rbac->Roles->assign ( $ID121, $PID2 );
		
		$this->assertTrue ( Jf::$Rbac->Roles->hasPermission ( $ID1, $PID21 ) );
		$this->assertTrue ( Jf::$Rbac->Roles->hasPermission ( $ID12, $PID2 ) );
		$this->assertTrue ( Jf::$Rbac->Roles->hasPermission ( $ID121, $PID21 ) );
	}
	// @depends LibRbacBaseTest::testAssign # how can i depend on another class'
	// test?
	/**
	 * @depends testHasPermission
	 */
	function testUnassignPermissions()
	{
		$ID1 = Jf::$Rbac->Roles->add ( "role1", "description of role1" );
		$ID2 = Jf::$Rbac->Roles->add ( "role2", "description of role2" );
		$ID11 = Jf::$Rbac->Roles->add ( "role1-1", "description of role", $ID1 );
		$ID12 = Jf::$Rbac->Roles->add ( "role1-2", "description of role", $ID1 );
		$ID121 = Jf::$Rbac->Roles->add ( "role1-2-1", "description of role", $ID12 );
		
		$PID1 = Jf::$Rbac->Permissions->add ( "permission1", "description" );
		$PID2 = Jf::$Rbac->Permissions->add ( "permission2", "description" );
		$PID21 = Jf::$Rbac->Permissions->add ( "permission2-1", "description", $PID2 );
		
		Jf::$Rbac->Roles->assign ( $ID121, $PID2 );
		

		Jf::$Rbac->Roles->unassignPermissions ( $ID121 );
		
		$this->assertFalse ( Jf::$Rbac->Roles->hasPermission ( $ID1, $PID21 ) );
		$this->assertFalse ( Jf::$Rbac->Roles->hasPermission ( $ID12, $PID2 ) );
		$this->assertFalse ( Jf::$Rbac->Roles->hasPermission ( $ID121, $PID21 ) );
	}
	
	/**
	 * depends LibRbacUsersTest::testAssign
	 */
	function testUnassignUsers()
	{
		$UID = 2;
		$ID1 = Jf::$Rbac->Roles->add ( "role1", "description of role1" );
		$ID2 = Jf::$Rbac->Roles->add ( "role2", "description of role2" );
		$ID11 = Jf::$Rbac->Roles->add ( "role1-1", "description of role", $ID1 );
		$ID12 = Jf::$Rbac->Roles->add ( "role1-2", "description of role", $ID1 );
		$ID121 = Jf::$Rbac->Roles->add ( "role1-2-1", "description of role", $ID12 );
		$this->assertTrue ( Jf::$Rbac->Users->assign ( $ID1, $UID ) );
		
		$this->assertTrue ( Jf::$Rbac->Users->hasRole ( $ID1, $UID ) );
		Jf::$Rbac->Roles->unassignUsers ( $ID1 );
		$this->assertFalse ( Jf::$Rbac->Users->hasRole ( $ID1, $UID ) );
	}
	function testPermissions()
	{
		$ID1 = Jf::$Rbac->Roles->add ( "role1", "description of role1" );
		$ID2 = Jf::$Rbac->Roles->add ( "role2", "description of role2" );
		$ID11 = Jf::$Rbac->Roles->add ( "role1-1", "description of role", $ID1 );
		$ID12 = Jf::$Rbac->Roles->add ( "role1-2", "description of role", $ID1 );
		$ID121 = Jf::$Rbac->Roles->add ( "role1-2-1", "description of role", $ID12 );
		
		$PID1 = Jf::$Rbac->Permissions->add ( "permission1", "description" );
		$PID2 = Jf::$Rbac->Permissions->add ( "permission2", "description" );
		$PID21 = Jf::$Rbac->Permissions->add ( "permission2-1", "description", $PID2 );
		
		Jf::$Rbac->Roles->assign ( $ID121, $PID2 );
		$this->assertEquals(Jf::$Rbac->Roles->permissions($ID121,true),array($PID2));		
		Jf::$Rbac->Roles->assign ( $ID121, $PID1 );
		$this->assertEquals(Jf::$Rbac->Roles->permissions($ID121,true),array($PID1,$PID2));		
		$this->assertEquals(2,count(Jf::$Rbac->Roles->permissions($ID121)));		
	
	}
}