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
		$RID = jf::$RBAC->Roles->AddPath ( "/CEO/CIO/Admin" );
		jf::$RBAC->Permissions->AddPath ( "/Users/add" );
		jf::$RBAC->Permissions->AddPath ( "/Users/edit" );
		jf::$RBAC->Permissions->AddPath ( "/Users/remove" );
		$PID = jf::$RBAC->Permissions->AddPath ( "/Users/changepass" );
		
		$this->assertTrue ( jf::$RBAC->Assign ( $RID, $PID ) );
		$this->assertTrue ( jf::$RBAC->Assign ( $RID, "/Users/edit" ) );
		$this->assertTrue ( jf::$RBAC->Assign ( $RID, "add" ) );
		$this->assertTrue ( jf::$RBAC->Assign ( "/CEO/CIO", "/Users/remove" ) );
		$this->assertTrue ( jf::$RBAC->Assign ( "CEO", "Users" ) );
		$this->assertTrue ( jf::$RBAC->Assign ( "CEO", $PID ) );
		$this->assertTrue ( jf::$RBAC->Assign ( "/CEO/CIO", $PID ) );
		$this->assertTrue ( jf::$RBAC->Assign ( "/CEO", "/Users/add" ) );
		$this->assertTrue ( jf::$RBAC->Assign ( "/CEO/CIO/Admin", "remove" ) );
	}
	function testCheck()
	{
		
		// adding roles
		jf::$RBAC->Roles->AddPath ( "/CEO/CIO/Admin" );
		jf::$RBAC->Roles->AddPath ( "/CEO/CIO/Networking" );
		jf::$RBAC->Roles->AddPath ( "/CEO/CIO/CISO" );
		jf::$RBAC->Roles->AddPath ( "/CEO/Financial" );
		jf::$RBAC->Roles->AddPath ( "/CEO/Secretary" );
		
		// assingning roles to users
		$res = jf::$RBAC->Users->Assign ( "/CEO", 2 );
		$res = $res and jf::$RBAC->Users->Assign ( "/CEO/Financial", 2 );
		
		$res = $res and jf::$RBAC->Users->Assign ( "/CEO/CIO/Admin", 3 );
		$res = $res and jf::$RBAC->Users->Assign ( "/CEO/CIO/Networking", 3 );
		$res = $res and jf::$RBAC->Users->Assign ( "/CEO/CIO/CISO", 3 );
		
		$res = $res and jf::$RBAC->Users->Assign ( "/CEO/Secretary", 4 );
		$this->assertTrue ( $res );
		
		// adding permissions
		jf::$RBAC->Permissions->AddPath ( "/Users/add" );
		jf::$RBAC->Permissions->AddPath ( "/Users/edit" );
		jf::$RBAC->Permissions->AddPath ( "/Users/remove" );
		jf::$RBAC->Permissions->AddPath ( "/Users/changepass" );
		jf::$RBAC->Permissions->AddPath ( "/Signature/financial" );
		jf::$RBAC->Permissions->AddPath ( "/Signature/office" );
		jf::$RBAC->Permissions->AddPath ( "/Signature/order" );
		jf::$RBAC->Permissions->AddPath ( "/Signature/network" );
		jf::$RBAC->Permissions->AddPath ( "/reports/IT/network" );
		jf::$RBAC->Permissions->AddPath ( "/reports/IT/security" );
		jf::$RBAC->Permissions->AddPath ( "/reports/financial" );
		jf::$RBAC->Permissions->AddPath ( "/reports/general" );
		
		// assigning permissions to roles
		$res = jf::$RBAC->Assign ( "CEO", "Users" );
		$res = $res and jf::$RBAC->Assign ( "CEO", "Signature" );
		$res = $res and jf::$RBAC->Assign ( "CEO", "/reports" );
		$this->assertTrue ( $res );
		
		$res = $res and jf::$RBAC->Assign ( "CIO", "/reports/IT" );
		$res = $res and jf::$RBAC->Assign ( "CIO", "/Users" );
		
		$res = $res and jf::$RBAC->Assign ( "Admin", "/Users" );
		$res = $res and jf::$RBAC->Assign ( "Admin", "/reports/IT" );
		
		$res = $res and jf::$RBAC->Assign ( "Networking", "/reports/network" );
		$res = $res and jf::$RBAC->Assign ( "Networking", "/Signature/network" );
		
		$res = $res and jf::$RBAC->Assign ( "CISO", "/reports/security" );
		$res = $res and jf::$RBAC->Assign ( "CISO", "/Users/changepass" );
		$this->assertTrue ( $res );
		
		$res = $res and jf::$RBAC->Assign ( "Financial", "/Signature/order" );
		$res = $res and jf::$RBAC->Assign ( "Financial", "/Signature/financial" );
		$res = $res and jf::$RBAC->Assign ( "Financial", "/reports/financial" );
		
		$res = $res and jf::$RBAC->Assign ( "Secretary", "/reports/financial" );
		$res = $res and jf::$RBAC->Assign ( "Secretary", "/Signature/office" );
		$this->assertTrue ( $res );
		

		// now checking
		
		$this->assertTrue ( jf::$RBAC->Users->HasRole ( "/CEO/Financial", 2 ) );
		$this->assertTrue ( jf::$RBAC->Check ( "/Signature/financial", 2 ) );
		$this->assertTrue ( jf::$RBAC->Check ( "/reports/general", 2 ) );
		$this->assertTrue ( jf::$RBAC->Check ( "/reports/IT/security", 2 ) );
		
		$this->assertTrue ( jf::$RBAC->Check ( "/reports/IT/security", 3 ) );
		$this->assertTrue ( jf::$RBAC->Check ( "/reports/IT/network", 3 ) );
		$this->assertTrue ( jf::$RBAC->Check ( "/Users", 3 ) );
		
		$this->assertTrue ( jf::$RBAC->Check ( "/Signature/office", 4 ) );
		$this->assertFalse ( jf::$RBAC->Check ( "/Signature/order", 4 ) );
		$this->assertTrue ( jf::$RBAC->Check ( "/reports/financial", 4 ) );
		$this->assertFalse ( jf::$RBAC->Check ( "/reports/general", 4 ) );
		
		
		try
		{
			$this->assertFalse ( jf::$RBAC->Check ( "/reports/generalz", 4 ) );
			$this->fail ( "No error on unknown permission" );
		} catch ( RbacPermissionNotFoundException $e )
		{
		}
	}
	function testEnforce()
	{
		
		try
		{
			$this->assertFalse ( jf::$RBAC->Check ( "/reports/generalz", "root" ) );
			$this->fail ( "No error on unknown permission" );
		} catch ( RbacPermissionNotFoundException $e )
		{
		}
	}
}