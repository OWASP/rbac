<?php
require_once __DIR__."/base.php";
class LibRbacUsersTest extends PHPRBAC_Test
{
	function testAssign()
	{
		$ID1 = jf::$RBAC->Roles->Add ( "role1", "" );
		$ID11 = jf::$RBAC->Roles->Add ( "role1-1", "", $ID1 );
		$ID111 = jf::$RBAC->Roles->Add ( "role1-1-1", "", $ID11 );
		
		$ID2 = jf::$RBAC->Roles->Add ( "role2", "" );
		$ID21 = jf::$RBAC->Roles->Add ( "role2-1", "", $ID2 );
		$ID211 = jf::$RBAC->Roles->Add ( "role2-1-1", "", $ID21 );
		

		$UID = 3;
		$this->assertTrue ( jf::$RBAC->Users->Assign ( $ID21, $UID ) );
		$this->assertFalse ( jf::$RBAC->Users->Assign ( $ID21, $UID ) );
	}
	
	/**
	 * @depends testAssign
	 */
	function testUnassign()
	{
		$ID1 = jf::$RBAC->Roles->Add ( "role1", "" );
		$ID11 = jf::$RBAC->Roles->Add ( "role1-1", "", $ID1 );
		$ID111 = jf::$RBAC->Roles->Add ( "role1-1-1", "", $ID11 );
		
		$ID2 = jf::$RBAC->Roles->Add ( "role2", "" );
		$ID21 = jf::$RBAC->Roles->Add ( "role2-1", "", $ID2 );
		$ID211 = jf::$RBAC->Roles->Add ( "role2-1-1", "", $ID21 );
		

		$UID = 2;
		$this->assertTrue ( jf::$RBAC->Users->Assign ( $ID21, $UID ) );
		$this->assertTrue ( jf::$RBAC->Users->Unassign ( $ID21, $UID ) );
		$this->assertFalse ( jf::$RBAC->Users->Unassign ( $ID21, $UID ) );
		$this->assertTrue ( jf::$RBAC->Users->Assign ( $ID21, $UID ) );
	}
	function testAllRoles()
	{
		$ID1 = jf::$RBAC->Roles->Add ( "role1", "" );
		$ID11 = jf::$RBAC->Roles->Add ( "role1-1", "", $ID1 );
		$ID111 = jf::$RBAC->Roles->Add ( "role1-1-1", "", $ID11 );
		
		$ID2 = jf::$RBAC->Roles->Add ( "role2", "" );
		$ID21 = jf::$RBAC->Roles->Add ( "role2-1", "", $ID2 );
		$ID211 = jf::$RBAC->Roles->Add ( "role2-1-1", "", $ID21 );
		
		
		$UID = 2;
		
		$this->assertEquals ( null, jf::$RBAC->Users->AllRoles($UID) );
		
		jf::$RBAC->Users->Assign ( $ID21, $UID );
		$res=jf::$RBAC->Users->AllRoles( $UID );
		$this->assertArrayHasKey("Title", $res[0]);
		$this->assertArrayHasKey("ID", $res[0]);
		$this->assertEquals($ID21, $res[0]['ID']);
		
		
		#new
		jf::$RBAC->Users->Assign ( $ID211, $UID );
		$this->assertEquals ( 2, count(jf::$RBAC->Users->AllRoles ( $UID ) ));
		
	}
	function testRoleCount()
	{
		$ID1 = jf::$RBAC->Roles->Add ( "role1", "" );
		$ID11 = jf::$RBAC->Roles->Add ( "role1-1", "", $ID1 );
		$ID111 = jf::$RBAC->Roles->Add ( "role1-1-1", "", $ID11 );
		
		$ID2 = jf::$RBAC->Roles->Add ( "role2", "" );
		$ID21 = jf::$RBAC->Roles->Add ( "role2-1", "", $ID2 );
		$ID211 = jf::$RBAC->Roles->Add ( "role2-1-1", "", $ID21 );
		

		$UID = 2;
		$this->assertEquals ( 0, jf::$RBAC->Users->RoleCount ( $UID ) );
		
		jf::$RBAC->Users->Assign ( $ID21, $UID );
		$this->assertEquals ( 1, jf::$RBAC->Users->RoleCount ( $UID ) );
		
		#same
		jf::$RBAC->Users->Assign ( $ID21, $UID );
		$this->assertEquals ( 1, jf::$RBAC->Users->RoleCount ( $UID ) );
		
		#new
		jf::$RBAC->Users->Assign ( $ID211, $UID );
		$this->assertEquals ( 2, jf::$RBAC->Users->RoleCount ( $UID ) );
		
		#to another user
		jf::$RBAC->Users->Assign ( $ID211, 1 );
		$this->assertEquals ( 2, jf::$RBAC->Users->RoleCount ( $UID ) );
	}
	
	/**
	 * @depends testAssign
	 */
	function testHasRole()
	{
		$ID1 = jf::$RBAC->Roles->Add ( "role1", "" );
		$ID11 = jf::$RBAC->Roles->Add ( "role1-1", "", $ID1 );
		$ID111 = jf::$RBAC->Roles->Add ( "role1-1-1", "", $ID11 );
		
		$ID2 = jf::$RBAC->Roles->Add ( "role2", "" );
		$ID21 = jf::$RBAC->Roles->Add ( "role2-1", "", $ID2 );
		$ID211 = jf::$RBAC->Roles->Add ( "role2-1-1", "", $ID21 );
		

		$UID = 2;
		jf::$RBAC->Users->Assign ( $ID21, $UID );
		
		$this->assertTrue ( jf::$RBAC->Users->HasRole ( $ID21, $UID ) );
		$this->assertTrue ( jf::$RBAC->Users->HasRole ( $ID211, $UID ) );
		
		$this->assertFalse ( jf::$RBAC->Users->HasRole ( $ID2, $UID ) );
		$this->assertFalse ( jf::$RBAC->Users->HasRole ( $ID111, $UID ) );
		
		jf::$RBAC->Users->Unassign ( $ID21, $UID );
		$this->assertFalse ( jf::$RBAC->Users->HasRole ( $ID21, $UID ) );
	}
	
	
	function testResetAssignments()
	{
		$ID1 = jf::$RBAC->Roles->Add ( "role1", "" );
		$ID11 = jf::$RBAC->Roles->Add ( "role1-1", "", $ID1 );
		$ID111 = jf::$RBAC->Roles->Add ( "role1-1-1", "", $ID11 );
		
		$ID2 = jf::$RBAC->Roles->Add ( "role2", "" );
		$ID21 = jf::$RBAC->Roles->Add ( "role2-1", "", $ID2 );
		$ID211 = jf::$RBAC->Roles->Add ( "role2-1-1", "", $ID21 );
		
		
		$UID = 2;
		jf::$RBAC->Users->Assign ( $ID21, $UID );
		
		jf::$RBAC->Users->ResetAssignments(true);
		$this->assertEquals(1,count(jf::$RBAC->Users->AllRoles(1)));
		$this->assertEquals(0,count(jf::$RBAC->Users->AllRoles($UID)));
	}
}