<?php
require_once __DIR__."/base.php";
class PhpRbacMainTest extends PhpRbac_Test
{
	function setUp()
	{
		parent::setUp ();
	}
	function testAssign()
	{
		$RID = Jf::$Rbac->Roles->addPath ( "/CEO/CIO/Admin" );
		Jf::$Rbac->Permissions->addPath ( "/Users/add" );
		Jf::$Rbac->Permissions->addPath ( "/Users/edit" );
		Jf::$Rbac->Permissions->addPath ( "/Users/remove" );
		$PID = Jf::$Rbac->Permissions->addPath ( "/Users/changepass" );
		
		$this->assertTrue ( Jf::$Rbac->assign ( $RID, $PID ) );
		$this->assertTrue ( Jf::$Rbac->assign ( $RID, "/Users/edit" ) );
		$this->assertTrue ( Jf::$Rbac->assign ( $RID, "add" ) );
		$this->assertTrue ( Jf::$Rbac->assign ( "/CEO/CIO", "/Users/remove" ) );
		$this->assertTrue ( Jf::$Rbac->assign ( "CEO", "Users" ) );
		$this->assertTrue ( Jf::$Rbac->assign ( "CEO", $PID ) );
		$this->assertTrue ( Jf::$Rbac->assign ( "/CEO/CIO", $PID ) );
		$this->assertTrue ( Jf::$Rbac->assign ( "/CEO", "/Users/add" ) );
		$this->assertTrue ( Jf::$Rbac->assign ( "/CEO/CIO/Admin", "remove" ) );
	}
	function testCheck()
	{
		
		// adding roles
		Jf::$Rbac->Roles->addPath ( "/CEO/CIO/Admin" );
		Jf::$Rbac->Roles->addPath ( "/CEO/CIO/Networking" );
		Jf::$Rbac->Roles->addPath ( "/CEO/CIO/CISO" );
		Jf::$Rbac->Roles->addPath ( "/CEO/Financial" );
		Jf::$Rbac->Roles->addPath ( "/CEO/Secretary" );
		
		// assingning roles to users
		$res = Jf::$Rbac->Users->assign ( "/CEO", 2 );
		$res = $res and Jf::$Rbac->Users->assign ( "/CEO/Financial", 2 );
		
		$res = $res and Jf::$Rbac->Users->assign ( "/CEO/CIO/Admin", 3 );
		$res = $res and Jf::$Rbac->Users->assign ( "/CEO/CIO/Networking", 3 );
		$res = $res and Jf::$Rbac->Users->assign ( "/CEO/CIO/CISO", 3 );
		
		$res = $res and Jf::$Rbac->Users->assign ( "/CEO/Secretary", 4 );
		$this->assertTrue ( $res );
		
		// adding permissions
		Jf::$Rbac->Permissions->addPath ( "/Users/add" );
		Jf::$Rbac->Permissions->addPath ( "/Users/edit" );
		Jf::$Rbac->Permissions->addPath ( "/Users/remove" );
		Jf::$Rbac->Permissions->addPath ( "/Users/changepass" );
		Jf::$Rbac->Permissions->addPath ( "/Signature/financial" );
		Jf::$Rbac->Permissions->addPath ( "/Signature/office" );
		Jf::$Rbac->Permissions->addPath ( "/Signature/order" );
		Jf::$Rbac->Permissions->addPath ( "/Signature/network" );
		Jf::$Rbac->Permissions->addPath ( "/reports/IT/network" );
		Jf::$Rbac->Permissions->addPath ( "/reports/IT/security" );
		Jf::$Rbac->Permissions->addPath ( "/reports/financial" );
		Jf::$Rbac->Permissions->addPath ( "/reports/general" );
		
		// assigning permissions to roles
		$res = Jf::$Rbac->assign ( "CEO", "Users" );
		$res = $res and Jf::$Rbac->assign ( "CEO", "Signature" );
		$res = $res and Jf::$Rbac->assign ( "CEO", "/reports" );
		$this->assertTrue ( $res );
		
		$res = $res and Jf::$Rbac->assign ( "CIO", "/reports/IT" );
		$res = $res and Jf::$Rbac->assign ( "CIO", "/Users" );
		
		$res = $res and Jf::$Rbac->assign ( "Admin", "/Users" );
		$res = $res and Jf::$Rbac->assign ( "Admin", "/reports/IT" );
		
		$res = $res and Jf::$Rbac->assign ( "Networking", "/reports/network" );
		$res = $res and Jf::$Rbac->assign ( "Networking", "/Signature/network" );
		
		$res = $res and Jf::$Rbac->assign ( "CISO", "/reports/security" );
		$res = $res and Jf::$Rbac->assign ( "CISO", "/Users/changepass" );
		$this->assertTrue ( $res );
		
		$res = $res and Jf::$Rbac->assign ( "Financial", "/Signature/order" );
		$res = $res and Jf::$Rbac->assign ( "Financial", "/Signature/financial" );
		$res = $res and Jf::$Rbac->assign ( "Financial", "/reports/financial" );
		
		$res = $res and Jf::$Rbac->assign ( "Secretary", "/reports/financial" );
		$res = $res and Jf::$Rbac->assign ( "Secretary", "/Signature/office" );
		$this->assertTrue ( $res );
		

		// now checking
		
		$this->assertTrue ( Jf::$Rbac->Users->hasRole ( "/CEO/Financial", 2 ) );
		$this->assertTrue ( Jf::$Rbac->check ( "/Signature/financial", 2 ) );
		$this->assertTrue ( Jf::$Rbac->check ( "/reports/general", 2 ) );
		$this->assertTrue ( Jf::$Rbac->check ( "/reports/IT/security", 2 ) );
		
		$this->assertTrue ( Jf::$Rbac->check ( "/reports/IT/security", 3 ) );
		$this->assertTrue ( Jf::$Rbac->check ( "/reports/IT/network", 3 ) );
		$this->assertTrue ( Jf::$Rbac->check ( "/Users", 3 ) );
		
		$this->assertTrue ( Jf::$Rbac->check ( "/Signature/office", 4 ) );
		$this->assertFalse ( Jf::$Rbac->check ( "/Signature/order", 4 ) );
		$this->assertTrue ( Jf::$Rbac->check ( "/reports/financial", 4 ) );
		$this->assertFalse ( Jf::$Rbac->check ( "/reports/general", 4 ) );
		
		
		try
		{
			$this->assertFalse ( Jf::$Rbac->check ( "/reports/generalz", 4 ) );
			$this->fail ( "No error on unknown permission" );
		} catch ( RbacPermissionNotFoundException $e )
		{
		}
	}
	function testEnforce()
	{
		
		try
		{
			$this->assertFalse ( Jf::$Rbac->check ( "/reports/generalz", "root" ) );
			$this->fail ( "No error on unknown permission" );
		} catch ( RbacPermissionNotFoundException $e )
		{
		}
	}
}