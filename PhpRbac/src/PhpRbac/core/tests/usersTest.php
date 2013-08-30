<?php
require_once __DIR__."/base.php";
class LibRbacUsersTest extends PhpRbac_Test
{
	function testAssign()
	{
		$ID1 = Jf::$Rbac->Roles->add ( "role1", "" );
		$ID11 = Jf::$Rbac->Roles->add ( "role1-1", "", $ID1 );
		$ID111 = Jf::$Rbac->Roles->add ( "role1-1-1", "", $ID11 );
		
		$ID2 = Jf::$Rbac->Roles->add ( "role2", "" );
		$ID21 = Jf::$Rbac->Roles->add ( "role2-1", "", $ID2 );
		$ID211 = Jf::$Rbac->Roles->add ( "role2-1-1", "", $ID21 );
		

		$UID = 3;
		$this->assertTrue ( Jf::$Rbac->Users->assign ( $ID21, $UID ) );
		$this->assertFalse ( Jf::$Rbac->Users->assign ( $ID21, $UID ) );
	}
	
	/**
	 * @depends testAssign
	 */
	function testUnassign()
	{
		$ID1 = Jf::$Rbac->Roles->add ( "role1", "" );
		$ID11 = Jf::$Rbac->Roles->add ( "role1-1", "", $ID1 );
		$ID111 = Jf::$Rbac->Roles->add ( "role1-1-1", "", $ID11 );
		
		$ID2 = Jf::$Rbac->Roles->add ( "role2", "" );
		$ID21 = Jf::$Rbac->Roles->add ( "role2-1", "", $ID2 );
		$ID211 = Jf::$Rbac->Roles->add ( "role2-1-1", "", $ID21 );
		

		$UID = 2;
		$this->assertTrue ( Jf::$Rbac->Users->assign ( $ID21, $UID ) );
		$this->assertTrue ( Jf::$Rbac->Users->unassign ( $ID21, $UID ) );
		$this->assertFalse ( Jf::$Rbac->Users->unassign ( $ID21, $UID ) );
		$this->assertTrue ( Jf::$Rbac->Users->assign ( $ID21, $UID ) );
	}
	function testallRoles()
	{
		$ID1 = Jf::$Rbac->Roles->add ( "role1", "" );
		$ID11 = Jf::$Rbac->Roles->add ( "role1-1", "", $ID1 );
		$ID111 = Jf::$Rbac->Roles->add ( "role1-1-1", "", $ID11 );
		
		$ID2 = Jf::$Rbac->Roles->add ( "role2", "" );
		$ID21 = Jf::$Rbac->Roles->add ( "role2-1", "", $ID2 );
		$ID211 = Jf::$Rbac->Roles->add ( "role2-1-1", "", $ID21 );
		
		
		$UID = 2;
		
		$this->assertEquals ( null, Jf::$Rbac->Users->allRoles($UID) );
		
		Jf::$Rbac->Users->assign ( $ID21, $UID );
		$res=Jf::$Rbac->Users->allRoles( $UID );
		$this->assertArrayHasKey("Title", $res[0]);
		$this->assertArrayHasKey("ID", $res[0]);
		$this->assertEquals($ID21, $res[0]['ID']);
		
		
		#new
		Jf::$Rbac->Users->assign ( $ID211, $UID );
		$this->assertEquals ( 2, count(Jf::$Rbac->Users->allRoles ( $UID ) ));
		
	}
	function testRoleCount()
	{
		$ID1 = Jf::$Rbac->Roles->add ( "role1", "" );
		$ID11 = Jf::$Rbac->Roles->add ( "role1-1", "", $ID1 );
		$ID111 = Jf::$Rbac->Roles->add ( "role1-1-1", "", $ID11 );
		
		$ID2 = Jf::$Rbac->Roles->add ( "role2", "" );
		$ID21 = Jf::$Rbac->Roles->add ( "role2-1", "", $ID2 );
		$ID211 = Jf::$Rbac->Roles->add ( "role2-1-1", "", $ID21 );
		

		$UID = 2;
		$this->assertEquals ( 0, Jf::$Rbac->Users->roleCount ( $UID ) );
		
		Jf::$Rbac->Users->assign ( $ID21, $UID );
		$this->assertEquals ( 1, Jf::$Rbac->Users->roleCount ( $UID ) );
		
		#same
		Jf::$Rbac->Users->assign ( $ID21, $UID );
		$this->assertEquals ( 1, Jf::$Rbac->Users->roleCount ( $UID ) );
		
		#new
		Jf::$Rbac->Users->assign ( $ID211, $UID );
		$this->assertEquals ( 2, Jf::$Rbac->Users->roleCount ( $UID ) );
		
		#to another user
		Jf::$Rbac->Users->assign ( $ID211, 1 );
		$this->assertEquals ( 2, Jf::$Rbac->Users->roleCount ( $UID ) );
	}
	
	/**
	 * @depends testAssign
	 */
	function testHasRole()
	{
		$ID1 = Jf::$Rbac->Roles->add ( "role1", "" );
		$ID11 = Jf::$Rbac->Roles->add ( "role1-1", "", $ID1 );
		$ID111 = Jf::$Rbac->Roles->add ( "role1-1-1", "", $ID11 );
		
		$ID2 = Jf::$Rbac->Roles->add ( "role2", "" );
		$ID21 = Jf::$Rbac->Roles->add ( "role2-1", "", $ID2 );
		$ID211 = Jf::$Rbac->Roles->add ( "role2-1-1", "", $ID21 );
		

		$UID = 2;
		Jf::$Rbac->Users->assign ( $ID21, $UID );
		
		$this->assertTrue ( Jf::$Rbac->Users->hasRole ( $ID21, $UID ) );
		$this->assertTrue ( Jf::$Rbac->Users->hasRole ( $ID211, $UID ) );
		
		$this->assertFalse ( Jf::$Rbac->Users->hasRole ( $ID2, $UID ) );
		$this->assertFalse ( Jf::$Rbac->Users->hasRole ( $ID111, $UID ) );
		
		Jf::$Rbac->Users->unassign ( $ID21, $UID );
		$this->assertFalse ( Jf::$Rbac->Users->hasRole ( $ID21, $UID ) );
	}
	
	
	function testResetAssignments()
	{
		$ID1 = Jf::$Rbac->Roles->add ( "role1", "" );
		$ID11 = Jf::$Rbac->Roles->add ( "role1-1", "", $ID1 );
		$ID111 = Jf::$Rbac->Roles->add ( "role1-1-1", "", $ID11 );
		
		$ID2 = Jf::$Rbac->Roles->add ( "role2", "" );
		$ID21 = Jf::$Rbac->Roles->add ( "role2-1", "", $ID2 );
		$ID211 = Jf::$Rbac->Roles->add ( "role2-1-1", "", $ID21 );
		
		
		$UID = 2;
		Jf::$Rbac->Users->assign ( $ID21, $UID );
		
		Jf::$Rbac->Users->resetAssignments(true);
		$this->assertEquals(1,count(Jf::$Rbac->Users->allRoles(1)));
		$this->assertEquals(0,count(Jf::$Rbac->Users->allRoles($UID)));
	}
}