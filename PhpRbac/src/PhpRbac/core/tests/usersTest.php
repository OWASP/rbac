<?php
require_once __DIR__."/base.php";
class LibRbacUsersTest extends PHPRBAC_Test
{
	function testAssign()
	{
		$ID1 = Jf::$RBAC->Roles->Add ( "role1", "" );
		$ID11 = Jf::$RBAC->Roles->Add ( "role1-1", "", $ID1 );
		$ID111 = Jf::$RBAC->Roles->Add ( "role1-1-1", "", $ID11 );
		
		$ID2 = Jf::$RBAC->Roles->Add ( "role2", "" );
		$ID21 = Jf::$RBAC->Roles->Add ( "role2-1", "", $ID2 );
		$ID211 = Jf::$RBAC->Roles->Add ( "role2-1-1", "", $ID21 );
		

		$UID = 3;
		$this->assertTrue ( Jf::$RBAC->Users->assign ( $ID21, $UID ) );
		$this->assertFalse ( Jf::$RBAC->Users->assign ( $ID21, $UID ) );
	}
	
	/**
	 * @depends testAssign
	 */
	function testUnassign()
	{
		$ID1 = Jf::$RBAC->Roles->Add ( "role1", "" );
		$ID11 = Jf::$RBAC->Roles->Add ( "role1-1", "", $ID1 );
		$ID111 = Jf::$RBAC->Roles->Add ( "role1-1-1", "", $ID11 );
		
		$ID2 = Jf::$RBAC->Roles->Add ( "role2", "" );
		$ID21 = Jf::$RBAC->Roles->Add ( "role2-1", "", $ID2 );
		$ID211 = Jf::$RBAC->Roles->Add ( "role2-1-1", "", $ID21 );
		

		$UID = 2;
		$this->assertTrue ( Jf::$RBAC->Users->assign ( $ID21, $UID ) );
		$this->assertTrue ( Jf::$RBAC->Users->unassign ( $ID21, $UID ) );
		$this->assertFalse ( Jf::$RBAC->Users->unassign ( $ID21, $UID ) );
		$this->assertTrue ( Jf::$RBAC->Users->assign ( $ID21, $UID ) );
	}
	function testallRoles()
	{
		$ID1 = Jf::$RBAC->Roles->Add ( "role1", "" );
		$ID11 = Jf::$RBAC->Roles->Add ( "role1-1", "", $ID1 );
		$ID111 = Jf::$RBAC->Roles->Add ( "role1-1-1", "", $ID11 );
		
		$ID2 = Jf::$RBAC->Roles->Add ( "role2", "" );
		$ID21 = Jf::$RBAC->Roles->Add ( "role2-1", "", $ID2 );
		$ID211 = Jf::$RBAC->Roles->Add ( "role2-1-1", "", $ID21 );
		
		
		$UID = 2;
		
		$this->assertEquals ( null, Jf::$RBAC->Users->allRoles($UID) );
		
		Jf::$RBAC->Users->assign ( $ID21, $UID );
		$res=Jf::$RBAC->Users->allRoles( $UID );
		$this->assertArrayHasKey("Title", $res[0]);
		$this->assertArrayHasKey("ID", $res[0]);
		$this->assertEquals($ID21, $res[0]['ID']);
		
		
		#new
		Jf::$RBAC->Users->assign ( $ID211, $UID );
		$this->assertEquals ( 2, count(Jf::$RBAC->Users->allRoles ( $UID ) ));
		
	}
	function testRoleCount()
	{
		$ID1 = Jf::$RBAC->Roles->Add ( "role1", "" );
		$ID11 = Jf::$RBAC->Roles->Add ( "role1-1", "", $ID1 );
		$ID111 = Jf::$RBAC->Roles->Add ( "role1-1-1", "", $ID11 );
		
		$ID2 = Jf::$RBAC->Roles->Add ( "role2", "" );
		$ID21 = Jf::$RBAC->Roles->Add ( "role2-1", "", $ID2 );
		$ID211 = Jf::$RBAC->Roles->Add ( "role2-1-1", "", $ID21 );
		

		$UID = 2;
		$this->assertEquals ( 0, Jf::$RBAC->Users->roleCount ( $UID ) );
		
		Jf::$RBAC->Users->assign ( $ID21, $UID );
		$this->assertEquals ( 1, Jf::$RBAC->Users->roleCount ( $UID ) );
		
		#same
		Jf::$RBAC->Users->assign ( $ID21, $UID );
		$this->assertEquals ( 1, Jf::$RBAC->Users->roleCount ( $UID ) );
		
		#new
		Jf::$RBAC->Users->assign ( $ID211, $UID );
		$this->assertEquals ( 2, Jf::$RBAC->Users->roleCount ( $UID ) );
		
		#to another user
		Jf::$RBAC->Users->assign ( $ID211, 1 );
		$this->assertEquals ( 2, Jf::$RBAC->Users->roleCount ( $UID ) );
	}
	
	/**
	 * @depends testAssign
	 */
	function testHasRole()
	{
		$ID1 = Jf::$RBAC->Roles->Add ( "role1", "" );
		$ID11 = Jf::$RBAC->Roles->Add ( "role1-1", "", $ID1 );
		$ID111 = Jf::$RBAC->Roles->Add ( "role1-1-1", "", $ID11 );
		
		$ID2 = Jf::$RBAC->Roles->Add ( "role2", "" );
		$ID21 = Jf::$RBAC->Roles->Add ( "role2-1", "", $ID2 );
		$ID211 = Jf::$RBAC->Roles->Add ( "role2-1-1", "", $ID21 );
		

		$UID = 2;
		Jf::$RBAC->Users->assign ( $ID21, $UID );
		
		$this->assertTrue ( Jf::$RBAC->Users->hasRole ( $ID21, $UID ) );
		$this->assertTrue ( Jf::$RBAC->Users->hasRole ( $ID211, $UID ) );
		
		$this->assertFalse ( Jf::$RBAC->Users->hasRole ( $ID2, $UID ) );
		$this->assertFalse ( Jf::$RBAC->Users->hasRole ( $ID111, $UID ) );
		
		Jf::$RBAC->Users->unassign ( $ID21, $UID );
		$this->assertFalse ( Jf::$RBAC->Users->hasRole ( $ID21, $UID ) );
	}
	
	
	function testResetAssignments()
	{
		$ID1 = Jf::$RBAC->Roles->Add ( "role1", "" );
		$ID11 = Jf::$RBAC->Roles->Add ( "role1-1", "", $ID1 );
		$ID111 = Jf::$RBAC->Roles->Add ( "role1-1-1", "", $ID11 );
		
		$ID2 = Jf::$RBAC->Roles->Add ( "role2", "" );
		$ID21 = Jf::$RBAC->Roles->Add ( "role2-1", "", $ID2 );
		$ID211 = Jf::$RBAC->Roles->Add ( "role2-1-1", "", $ID21 );
		
		
		$UID = 2;
		Jf::$RBAC->Users->assign ( $ID21, $UID );
		
		Jf::$RBAC->Users->resetAssignments(true);
		$this->assertEquals(1,count(Jf::$RBAC->Users->allRoles(1)));
		$this->assertEquals(0,count(Jf::$RBAC->Users->allRoles($UID)));
	}
}