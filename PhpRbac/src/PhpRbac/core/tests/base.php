<?php
require_once dirname(__DIR__) . '/sql/database/database.config';
require_once __DIR__."/../lib/jf.php";
abstract class PHPRBAC_Test extends PHPUnit_Framework_TestCase
{
	function setUp()
	{
		jf::$RBAC->Reset(true);
	}
	

}
abstract class PHPRBACBaseTest extends PHPRBAC_Test
{
	function setUp()
	{
		parent::setUp ();
	}
	/**
	 *
	 * @return \jf\{$this->type()}Manager
	 */
	protected abstract function Instance();
	
	/**
	 *
	 * @return string {$this->type()} or permission
	 */
	protected abstract function type();
	function testAdd()
	{
		$ID = $this->Instance ()->Add ( "{$this->type()}1", "description of the {$this->type()}" );
		$this->assertGreaterThan ( 1, $ID );
		$this->assertGreaterThanOrEqual ( $this->Instance ()->Count (), 2 );
	}
	/**
	 * @depends testAdd
	 */
	function testRemove()
	{
		$ID = $this->Instance ()->Add ( "{$this->type()}1", "description of the {$this->type()}" );
		$ID2 = $this->Instance ()->Add ( "{$this->type()}2", "description of the {$this->type()}", $ID );
		$ID3 = $this->Instance ()->Add ( "{$this->type()}3", "description of the {$this->type()}", $ID2 );
		$ID4 = $this->Instance ()->Add ( "{$this->type()}4", "description of the {$this->type()}", $ID2 );
		$this->assertTrue ( $this->Instance ()->Remove ( $ID ) );
		$this->assertFalse ( $this->Instance ()->Remove ( $ID ) );
		$this->assertTrue ( $this->Instance ()->Remove ( $ID2, true ) );
		$this->assertFalse ( $this->Instance ()->Remove ( $ID2 ) );
		$this->assertFalse ( $this->Instance ()->Remove ( $ID3 ) );
		$this->assertFalse ( $this->Instance ()->Remove ( $ID4 ) );
	}
	function testGetInfo()
	{
		$ID = $this->Instance ()->Add ( "this is the title", "and this is description" );
		$this->assertEquals ( "this is the title", $this->Instance ()->GetTitle ( $ID ) );
		$this->assertEquals ( "and this is description", $this->Instance ()->GetDescription ( $ID ) );
	}
	function testPathID()
	{
		$this->assertEquals ( 1, $this->Instance ()->PathID ( "/" ) );
		
		$ID1 = $this->Instance ()->Add ( "folder1", "description of foler1" );
		$ID2 = $this->Instance ()->Add ( "folder2", "description of foler2", $ID1 );
		$ID3 = $this->Instance ()->Add ( "folder3", "description of foler3", $ID2 );
		
		$Res1 = $this->Instance ()->PathID ( "/folder1/folder2/folder3" );
		$this->assertEquals ( $ID3, $Res1 );
		
		$Res1 = $this->Instance ()->PathID ( "/folder1/folder3" );
		$this->assertNotEquals ( $ID3, $Res1 );
		
		$this->assertEquals ( 1, $this->Instance ()->PathID ( "/" ) );
		
		$this->assertEquals ( $ID1, $this->Instance ()->PathID ( "/folder1" ) );
		$this->assertEquals ( $ID1, $this->Instance ()->PathID ( "/folder1/" ) );
		$this->assertNotEquals ( $ID1, $this->Instance ()->PathID ( "/folder1/xyz" ) );
		
		// ong one, the current PathID has a limit of 1024 characters for path
		// (because of SQL gorup_concat)
		$ID = null;
		$Path = "";
		for($i = 0; $i < 100; ++ $i)
		{
			$ID = $this->Instance ()->Add ( "depth{$i}", "description of depth{$i}", $ID );
			$Path .= "/depth{$i}";
		}
		$this->assertEquals ( $ID, $this->Instance ()->PathID ( $Path ) );
	}
	

	/**
	 * @depends testPathID
	 */
	function testAddPath()
	{
		$ID = $this->Instance ()->AddPath ( "/some/folder/some/where" );
		$this->assertEquals ( $ID, $this->Instance ()->PathID ( "/some/folder/some/where" ) );
		$ID = $this->Instance ()->AddPath ( "/some/folder/another/where" );
		$this->Instance ()->PathID ( "/some/folder/another/where" );
		$this->assertEquals ( $ID, $this->Instance ()->PathID ( "/some/folder/another/where" ) );
		$ID = $this->Instance ()->AddPath ( "/some/folder/another/where" );
		$this->assertEquals ( $ID, $this->Instance ()->PathID ( "/some/folder/another/where" ) );
	}
	function testEdit()
	{
		$ID = $this->Instance ()->Add ( "{$this->type()}1", "description here" );
		
		// Change title
		$this->assertTrue ( $this->Instance ()->Edit ( $ID, "{$this->type()}2" ) );
		$this->assertEquals ( "{$this->type()}2", $this->Instance ()->GetTitle ( $ID ) );
		$this->assertEquals ( "description here", $this->Instance ()->GetDescription ( $ID ) );
		
		// change description
		$this->assertTrue ( $this->Instance ()->Edit ( $ID, null, "new description" ) );
		$this->assertEquals ( "{$this->type()}2", $this->Instance ()->GetTitle ( $ID ) );
		$this->assertEquals ( "new description", $this->Instance ()->GetDescription ( $ID ) );
		
		// changing both
		$this->assertTrue ( $this->Instance ()->Edit ( $ID, "new {$this->type()}", "another new description" ) );
		$this->assertEquals ( "new {$this->type()}", $this->Instance ()->GetTitle ( $ID ) );
		$this->assertEquals ( "another new description", $this->Instance ()->GetDescription ( $ID ) );
	}
	function testChildren()
	{
		$Parent = $this->Instance ()->Add ( "{$this->type()}1", "" );
		
		$Child1 = $this->Instance ()->Add ( "{$this->type()}1-1", "", $Parent );
		$Child2 = $this->Instance ()->Add ( "{$this->type()}1-2", "", $Parent );
		
		$Child11 = $this->Instance ()->Add ( "{$this->type()}1-1-1", "", $Child1 );
		$Child12 = $this->Instance ()->Add ( "{$this->type()}1-1-2", "", $Child1 );
		$Child13 = $this->Instance ()->Add ( "{$this->type()}1-1-3", "", $Child1 );
		
		$children = ($this->Instance ()->Children ( $Parent ));

		$this->assertEquals ( $children [0] ['Title'], "{$this->type()}1-1" );
		$this->assertEquals ( $children [1] ['Title'], "{$this->type()}1-2" );
		$this->assertEquals ( count ( $children ), 2 );
	}
	/**
	 * @depends testAdd
	 */
	function testTitleID()
	{
		$ID = $this->Instance ()->Add ( "{$this->type()}-1", "description of the {$this->type()}" );
		$ID2 = $this->Instance ()->Add ( "{$this->type()}-2", "description of the {$this->type()}" );
		$this->assertEquals ( $this->Instance ()->TitleID ( "{$this->type()}-1" ), $ID );
		$this->assertNotEquals ( $this->Instance ()->TitleID ( "{$this->type()}-2" ), $ID );
		$this->assertNotEquals ( $this->Instance ()->TitleID ( "{$this->type()}-3" ), $ID );
	}
	function testDescendants()
	{
		$Parent = $this->Instance ()->Add ( "{$this->type()}1", "" );
		
		$Child1 = $this->Instance ()->Add ( "{$this->type()}1-1", "", $Parent );
		$Child2 = $this->Instance ()->Add ( "{$this->type()}1-2", "", $Parent );
		
		$Child11 = $this->Instance ()->Add ( "{$this->type()}1-1-1", "", $Child1 );
		$Child12 = $this->Instance ()->Add ( "{$this->type()}1-1-2", "", $Child1 );
		$Child13 = $this->Instance ()->Add ( "{$this->type()}1-1-3", "", $Child1 );
		
		$descendants = ($this->Instance ()->Descendants ( $Parent ));
		$this->assertEquals ( $descendants ["{$this->type()}1-1"] ['Depth'], 1 );
		$this->assertEquals ( $descendants ["{$this->type()}1-1-3"] ['Depth'], 2 );
		$this->assertEquals ( count ( $descendants ), 5 );
	}
	function testDepth()
	{
		$Parent = $this->Instance ()->Add ( "{$this->type()}1", "" );
		
		$Child1 = $this->Instance ()->Add ( "{$this->type()}1-1", "", $Parent );
		
		$Child11 = $this->Instance ()->Add ( "{$this->type()}1-1-1", "", $Child1 );
		$Child13 = $this->Instance ()->Add ( "{$this->type()}1-1-3", "", $Child1 );
		$this->assertEquals ( 0, $this->Instance ()->Depth ( 1 ) );
		$this->assertEquals ( 3, $this->Instance ()->Depth ( $Child13 ) );
		$this->assertEquals ( 2, $this->Instance ()->Depth ( $Child1 ) );
	}
	function testPath()
	{
		$Parent = $this->Instance ()->Add ( "{$this->type()}1", "" );
		
		$Child1 = $this->Instance ()->Add ( "{$this->type()}1-1", "", $Parent );
		$Child2 = $this->Instance ()->Add ( "{$this->type()}1-2", "", $Parent );
		
		$Child11 = $this->Instance ()->Add ( "{$this->type()}1-1-1", "", $Child1 );
		$Child12 = $this->Instance ()->Add ( "{$this->type()}1-1-2", "", $Child1 );
		$Child13 = $this->Instance ()->Add ( "{$this->type()}1-1-3", "", $Child1 );
		

		$this->assertEquals ( "/", $this->Instance ()->Path ( 1 ) );
		$this->assertEquals ( null, $this->Instance ()->Path ( 100 ) );
		$this->assertEquals ( "/{$this->type()}1", $this->Instance ()->Path ( $Parent ) );
		$this->assertEquals ( "/{$this->type()}1/{$this->type()}1-2", $this->Instance ()->Path ( $Child2 ) );
		$this->assertEquals ( "/{$this->type()}1/{$this->type()}1-1/{$this->type()}1-1-3", $this->Instance ()->Path ( $Child13 ) );
	}
	function testParentNode()
	{
		$Parent = $this->Instance ()->Add ( "{$this->type()}1", "" );
		
		$Child1 = $this->Instance ()->Add ( "{$this->type()}1-1", "", $Parent );
		$Child2 = $this->Instance ()->Add ( "{$this->type()}1-2", "", $Parent );
		
		$Child11 = $this->Instance ()->Add ( "{$this->type()}1-1-1", "", $Child1 );
		$Child12 = $this->Instance ()->Add ( "{$this->type()}1-1-2", "", $Child1 );
		$Child13 = $this->Instance ()->Add ( "{$this->type()}1-1-3", "", $Child1 );
		
		$t = $this->Instance ()->ParentNode ( 1 );
		$this->assertEquals ( null, $t );
		$t = $this->Instance ()->ParentNode ( $Parent );
		$this->assertEquals ( 1, $t ['ID'] );
		$t = $this->Instance ()->ParentNode ( $Child2 );
		$this->assertEquals ( $Parent, $t ['ID'] );
		$t = $this->Instance ()->ParentNode ( $Child12 );
		$this->assertEquals ( $Child1, $t ['ID'] );
	}
	function testReset()
	{
		$Parent = $this->Instance ()->Add ( "{$this->type()}1", "" );
		
		$Child1 = $this->Instance ()->Add ( "{$this->type()}1-1", "", $Parent );
		$Child2 = $this->Instance ()->Add ( "{$this->type()}1-2", "", $Parent );
		
		$Child11 = $this->Instance ()->Add ( "{$this->type()}1-1-1", "", $Child1 );
		$Child12 = $this->Instance ()->Add ( "{$this->type()}1-1-2", "", $Child1 );
		$Child13 = $this->Instance ()->Add ( "{$this->type()}1-1-3", "", $Child1 );
		
		$this->Instance ()->Reset ( true );
		$this->assertEquals ( 1, $this->Instance ()->TitleID ( "root" ) );
		$this->assertEmpty ( $this->Instance ()->Children ( 1 ) );
		$this->setExpectedException ( "Exception" );
		$this->Instance ()->Reset ();
	}
	function testRemoveAgain()
	{
		$ID = $this->Instance ()->Add ( "some_{$this->type()}", "some description" );
		$this->assertEquals ( $ID, $this->Instance ()->TitleID ( "some_{$this->type()}" ) );
		
		$this->Instance ()->Remove ( $ID );
		$this->assertNotEquals ( $ID, $this->Instance ()->TitleID ( "some_{$this->type()}" ) );
		$this->assertEquals ( null, $this->Instance ()->TitleID ( "some_{$this->type()}" ) );
		

		// ow recursive
		$Parent = $this->Instance ()->Add ( "{$this->type()}1", "" );
		
		$Child1 = $this->Instance ()->Add ( "{$this->type()}1-1", "", $Parent );
		$Child2 = $this->Instance ()->Add ( "{$this->type()}1-2", "", $Parent );
		
		$Child11 = $this->Instance ()->Add ( "{$this->type()}1-1-1", "", $Child1 );
		$Child12 = $this->Instance ()->Add ( "{$this->type()}1-1-2", "", $Child1 );
		$Child13 = $this->Instance ()->Add ( "{$this->type()}1-1-3", "", $Child1 );
		

		$this->Instance ()->Remove ( $Child1, true );

		$this->assertEquals ( 3, $this->Instance ()->Count () );
		$this->assertEquals ( $Child2, $this->Instance ()->TitleID ( "{$this->type()}1-2" ) );
		$this->assertEquals ( null, $this->Instance ()->TitleID ( "{$this->type()}1-1" ) );
		$this->assertEquals ( null, $this->Instance ()->TitleID ( "{$this->type()}1-1-1" ) );
	}
	/**
	 * @depends testAdd
	 */
	function testAssign()
	{
		$ID1=jf::$RBAC->Roles->Add("role1", "description of role1");
		$ID2=jf::$RBAC->Roles->Add("role2", "description of role2");
		$ID11=jf::$RBAC->Roles->Add("role1-1", "description of role",$ID1);
		$ID12=jf::$RBAC->Roles->Add("role1-2", "description of role",$ID1);
		$ID121=jf::$RBAC->Roles->Add("role1-2-1", "description of role",$ID12);
		
		$PID1=jf::$RBAC->Permissions->Add("permission1", "description");
		$PID2=jf::$RBAC->Permissions->Add("permission2", "description");
		$PID21=jf::$RBAC->Permissions->Add("permission2-1", "description",$PID2);
		
		$this->assertTrue($this->Instance()->Assign($ID121, $PID2));
		$this->assertFalse($this->Instance()->Assign($ID121, $PID2));
		
	}
	/**
	 * @depends testAssign
	 */
	function testUnassign()
	{
		$ID1=jf::$RBAC->Roles->Add("role1", "description of role1");
		$ID2=jf::$RBAC->Roles->Add("role2", "description of role2");
		$ID11=jf::$RBAC->Roles->Add("role1-1", "description of role",$ID1);
		$ID12=jf::$RBAC->Roles->Add("role1-2", "description of role",$ID1);
		$ID121=jf::$RBAC->Roles->Add("role1-2-1", "description of role",$ID12);
		
		$PID1=jf::$RBAC->Permissions->Add("permission1", "description");
		$PID2=jf::$RBAC->Permissions->Add("permission2", "description");
		$PID21=jf::$RBAC->Permissions->Add("permission2-1", "description",$PID2);
		
		$this->Instance()->Assign($ID121, $PID2);
		
		$this->assertFalse($this->Instance()->Unassign($ID121,$PID1));
		$this->assertTrue($this->Instance()->Unassign($ID121,$PID2));
		$this->assertFalse($this->Instance()->Unassign($ID121,$PID2)); //already removed
		
		
	}
	
	function testResetAssignments()
	{
		$ID1=jf::$RBAC->Roles->Add("role1", "description of role1");
		$ID2=jf::$RBAC->Roles->Add("role2", "description of role2");
		$ID11=jf::$RBAC->Roles->Add("role1-1", "description of role",$ID1);
		$ID12=jf::$RBAC->Roles->Add("role1-2", "description of role",$ID1);
		$ID121=jf::$RBAC->Roles->Add("role1-2-1", "description of role",$ID12);
		
		$PID1=jf::$RBAC->Permissions->Add("permission1", "description");
		$PID2=jf::$RBAC->Permissions->Add("permission2", "description");
		$PID21=jf::$RBAC->Permissions->Add("permission2-1", "description",$PID2);
		
		$this->Instance()->Assign($ID121, $PID2);		
		$this->Instance()->Assign($ID1, $PID1);		
		$this->Instance()->Assign($ID12, $PID21);

		$this->Instance()->ResetAssignments(true);
		
		$this->assertFalse($this->Instance()->Unassign($ID121,$PID2));
		$this->assertFalse($this->Instance()->Unassign($ID1,$PID1));
		
		$this->setExpectedException("\Exception");
		$this->Instance()->ResetAssignments(false);
		
	}
}