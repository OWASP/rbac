<?php
require_once __DIR__."/base.php";
class PHPRBACMainTest extends PHPRBAC_Test
{
	function setUp()
	{
		parent::setUp ();
	}
	function testAssign()
	{
		$RID = Jf::$RBAC->Roles->AddPath ( "/CEO/CIO/Admin" );
		Jf::$RBAC->Permissions->AddPath ( "/Users/add" );
		Jf::$RBAC->Permissions->AddPath ( "/Users/edit" );
		Jf::$RBAC->Permissions->AddPath ( "/Users/remove" );
		$PID = Jf::$RBAC->Permissions->AddPath ( "/Users/changepass" );
		
		$this->assertTrue ( Jf::$RBAC->assign ( $RID, $PID ) );
		$this->assertTrue ( Jf::$RBAC->assign ( $RID, "/Users/edit" ) );
		$this->assertTrue ( Jf::$RBAC->assign ( $RID, "add" ) );
		$this->assertTrue ( Jf::$RBAC->assign ( "/CEO/CIO", "/Users/remove" ) );
		$this->assertTrue ( Jf::$RBAC->assign ( "CEO", "Users" ) );
		$this->assertTrue ( Jf::$RBAC->assign ( "CEO", $PID ) );
		$this->assertTrue ( Jf::$RBAC->assign ( "/CEO/CIO", $PID ) );
		$this->assertTrue ( Jf::$RBAC->assign ( "/CEO", "/Users/add" ) );
		$this->assertTrue ( Jf::$RBAC->assign ( "/CEO/CIO/Admin", "remove" ) );
	}
	function testCheck()
	{
		
		// adding roles
		Jf::$RBAC->Roles->AddPath ( "/CEO/CIO/Admin" );
		Jf::$RBAC->Roles->AddPath ( "/CEO/CIO/Networking" );
		Jf::$RBAC->Roles->AddPath ( "/CEO/CIO/CISO" );
		Jf::$RBAC->Roles->AddPath ( "/CEO/Financial" );
		Jf::$RBAC->Roles->AddPath ( "/CEO/Secretary" );
		
		// assingning roles to users
		$res = Jf::$RBAC->Users->assign ( "/CEO", 2 );
		$res = $res and Jf::$RBAC->Users->assign ( "/CEO/Financial", 2 );
		
		$res = $res and Jf::$RBAC->Users->assign ( "/CEO/CIO/Admin", 3 );
		$res = $res and Jf::$RBAC->Users->assign ( "/CEO/CIO/Networking", 3 );
		$res = $res and Jf::$RBAC->Users->assign ( "/CEO/CIO/CISO", 3 );
		
		$res = $res and Jf::$RBAC->Users->assign ( "/CEO/Secretary", 4 );
		$this->assertTrue ( $res );
		
		// adding permissions
		Jf::$RBAC->Permissions->AddPath ( "/Users/add" );
		Jf::$RBAC->Permissions->AddPath ( "/Users/edit" );
		Jf::$RBAC->Permissions->AddPath ( "/Users/remove" );
		Jf::$RBAC->Permissions->AddPath ( "/Users/changepass" );
		Jf::$RBAC->Permissions->AddPath ( "/Signature/financial" );
		Jf::$RBAC->Permissions->AddPath ( "/Signature/office" );
		Jf::$RBAC->Permissions->AddPath ( "/Signature/order" );
		Jf::$RBAC->Permissions->AddPath ( "/Signature/network" );
		Jf::$RBAC->Permissions->AddPath ( "/reports/IT/network" );
		Jf::$RBAC->Permissions->AddPath ( "/reports/IT/security" );
		Jf::$RBAC->Permissions->AddPath ( "/reports/financial" );
		Jf::$RBAC->Permissions->AddPath ( "/reports/general" );
		
		// assigning permissions to roles
		$res = Jf::$RBAC->assign ( "CEO", "Users" );
		$res = $res and Jf::$RBAC->assign ( "CEO", "Signature" );
		$res = $res and Jf::$RBAC->assign ( "CEO", "/reports" );
		$this->assertTrue ( $res );
		
		$res = $res and Jf::$RBAC->assign ( "CIO", "/reports/IT" );
		$res = $res and Jf::$RBAC->assign ( "CIO", "/Users" );
		
		$res = $res and Jf::$RBAC->assign ( "Admin", "/Users" );
		$res = $res and Jf::$RBAC->assign ( "Admin", "/reports/IT" );
		
		$res = $res and Jf::$RBAC->assign ( "Networking", "/reports/network" );
		$res = $res and Jf::$RBAC->assign ( "Networking", "/Signature/network" );
		
		$res = $res and Jf::$RBAC->assign ( "CISO", "/reports/security" );
		$res = $res and Jf::$RBAC->assign ( "CISO", "/Users/changepass" );
		$this->assertTrue ( $res );
		
		$res = $res and Jf::$RBAC->assign ( "Financial", "/Signature/order" );
		$res = $res and Jf::$RBAC->assign ( "Financial", "/Signature/financial" );
		$res = $res and Jf::$RBAC->assign ( "Financial", "/reports/financial" );
		
		$res = $res and Jf::$RBAC->assign ( "Secretary", "/reports/financial" );
		$res = $res and Jf::$RBAC->assign ( "Secretary", "/Signature/office" );
		$this->assertTrue ( $res );
		

		// now checking
		
		$this->assertTrue ( Jf::$RBAC->Users->hasRole ( "/CEO/Financial", 2 ) );
		$this->assertTrue ( Jf::$RBAC->check ( "/Signature/financial", 2 ) );
		$this->assertTrue ( Jf::$RBAC->check ( "/reports/general", 2 ) );
		$this->assertTrue ( Jf::$RBAC->check ( "/reports/IT/security", 2 ) );
		
		$this->assertTrue ( Jf::$RBAC->check ( "/reports/IT/security", 3 ) );
		$this->assertTrue ( Jf::$RBAC->check ( "/reports/IT/network", 3 ) );
		$this->assertTrue ( Jf::$RBAC->check ( "/Users", 3 ) );
		
		$this->assertTrue ( Jf::$RBAC->check ( "/Signature/office", 4 ) );
		$this->assertFalse ( Jf::$RBAC->check ( "/Signature/order", 4 ) );
		$this->assertTrue ( Jf::$RBAC->check ( "/reports/financial", 4 ) );
		$this->assertFalse ( Jf::$RBAC->check ( "/reports/general", 4 ) );
		
		
		try
		{
			$this->assertFalse ( Jf::$RBAC->check ( "/reports/generalz", 4 ) );
			$this->fail ( "No error on unknown permission" );
		} catch ( RbacPermissionNotFoundException $e )
		{
		}
	}
	function testEnforce()
	{
		
		try
		{
			$this->assertFalse ( Jf::$RBAC->check ( "/reports/generalz", "root" ) );
			$this->fail ( "No error on unknown permission" );
		} catch ( RbacPermissionNotFoundException $e )
		{
		}
	}
}