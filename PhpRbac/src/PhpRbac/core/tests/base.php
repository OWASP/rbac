<?php
require_once dirname(__DIR__) . '/sql/database/database.config';
require_once __DIR__."/../lib/Jf.php";
abstract class PHPRBAC_Test extends PHPUnit_Framework_TestCase
{
	function setUp()
	{
		Jf::$RBAC->reset(true);
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
	 * @return \Jf\{$this->type()}Manager
	 */
	protected abstract function Instance();
	
	/**
	 *
	 * @return string {$this->type()} or permission
	 */
	protected abstract function type();
	function testAdd()
	{
		$ID = $this->Instance ()->add ( "{$this->type()}1", "description of the {$this->type()}" );
		$this->assertGreaterThan ( 1, $ID );
		$this->assertGreaterThanOrEqual ( $this->Instance ()->count (), 2 );
	}
	/**
	 * @depends testAdd
	 */
	function testRemove()
	{
		$ID = $this->Instance ()->add ( "{$this->type()}1", "description of the {$this->type()}" );
		$ID2 = $this->Instance ()->add ( "{$this->type()}2", "description of the {$this->type()}", $ID );
		$ID3 = $this->Instance ()->add ( "{$this->type()}3", "description of the {$this->type()}", $ID2 );
		$ID4 = $this->Instance ()->add ( "{$this->type()}4", "description of the {$this->type()}", $ID2 );
		$this->assertTrue ( $this->Instance ()->remove ( $ID ) );
		$this->assertFalse ( $this->Instance ()->remove ( $ID ) );
		$this->assertTrue ( $this->Instance ()->remove ( $ID2, true ) );
		$this->assertFalse ( $this->Instance ()->remove ( $ID2 ) );
		$this->assertFalse ( $this->Instance ()->remove ( $ID3 ) );
		$this->assertFalse ( $this->Instance ()->remove ( $ID4 ) );
	}
	function testGetInfo()
	{
		$ID = $this->Instance ()->add ( "this is the title", "and this is description" );
		$this->assertEquals ( "this is the title", $this->Instance ()->getTitle ( $ID ) );
		$this->assertEquals ( "and this is description", $this->Instance ()->GetDescription ( $ID ) );
	}
	function testPathID()
	{
		$this->assertEquals ( 1, $this->Instance ()->pathId ( "/" ) );
		
		$ID1 = $this->Instance ()->add ( "folder1", "description of foler1" );
		$ID2 = $this->Instance ()->add ( "folder2", "description of foler2", $ID1 );
		$ID3 = $this->Instance ()->add ( "folder3", "description of foler3", $ID2 );
		
		$Res1 = $this->Instance ()->pathId ( "/folder1/folder2/folder3" );
		$this->assertEquals ( $ID3, $Res1 );
		
		$Res1 = $this->Instance ()->pathId ( "/folder1/folder3" );
		$this->assertNotEquals ( $ID3, $Res1 );
		
		$this->assertEquals ( 1, $this->Instance ()->pathId ( "/" ) );
		
		$this->assertEquals ( $ID1, $this->Instance ()->pathId ( "/folder1" ) );
		$this->assertEquals ( $ID1, $this->Instance ()->pathId ( "/folder1/" ) );
		$this->assertNotEquals ( $ID1, $this->Instance ()->pathId ( "/folder1/xyz" ) );
		
		// ong one, the current pathId has a limit of 1024 characters for path
		// (because of SQL gorup_concat)
		$ID = null;
		$Path = "";
		for($i = 0; $i < 100; ++ $i)
		{
			$ID = $this->Instance ()->add ( "depth{$i}", "description of depth{$i}", $ID );
			$Path .= "/depth{$i}";
		}
		$this->assertEquals ( $ID, $this->Instance ()->pathId ( $Path ) );
	}
	

	/**
	 * @depends testPathID
	 */
	function testAddPath()
	{
		$ID = $this->Instance ()->addPath ( "/some/folder/some/where" );
		$this->assertEquals ( $ID, $this->Instance ()->pathId ( "/some/folder/some/where" ) );
		$ID = $this->Instance ()->addPath ( "/some/folder/another/where" );
		$this->Instance ()->pathId ( "/some/folder/another/where" );
		$this->assertEquals ( $ID, $this->Instance ()->pathId ( "/some/folder/another/where" ) );
		$ID = $this->Instance ()->addPath ( "/some/folder/another/where" );
		$this->assertEquals ( $ID, $this->Instance ()->pathId ( "/some/folder/another/where" ) );
	}
	function testEdit()
	{
		$ID = $this->Instance ()->add ( "{$this->type()}1", "description here" );
		
		// Change title
		$this->assertTrue ( $this->Instance ()->edit ( $ID, "{$this->type()}2" ) );
		$this->assertEquals ( "{$this->type()}2", $this->Instance ()->getTitle ( $ID ) );
		$this->assertEquals ( "description here", $this->Instance ()->GetDescription ( $ID ) );
		
		// change description
		$this->assertTrue ( $this->Instance ()->edit ( $ID, null, "new description" ) );
		$this->assertEquals ( "{$this->type()}2", $this->Instance ()->getTitle ( $ID ) );
		$this->assertEquals ( "new description", $this->Instance ()->GetDescription ( $ID ) );
		
		// changing both
		$this->assertTrue ( $this->Instance ()->edit ( $ID, "new {$this->type()}", "another new description" ) );
		$this->assertEquals ( "new {$this->type()}", $this->Instance ()->getTitle ( $ID ) );
		$this->assertEquals ( "another new description", $this->Instance ()->GetDescription ( $ID ) );
	}
	function testChildren()
	{
		$Parent = $this->Instance ()->add ( "{$this->type()}1", "" );
		
		$Child1 = $this->Instance ()->add ( "{$this->type()}1-1", "", $Parent );
		$Child2 = $this->Instance ()->add ( "{$this->type()}1-2", "", $Parent );
		
		$Child11 = $this->Instance ()->add ( "{$this->type()}1-1-1", "", $Child1 );
		$Child12 = $this->Instance ()->add ( "{$this->type()}1-1-2", "", $Child1 );
		$Child13 = $this->Instance ()->add ( "{$this->type()}1-1-3", "", $Child1 );
		
		$children = ($this->Instance ()->children ( $Parent ));

		$this->assertEquals ( $children [0] ['Title'], "{$this->type()}1-1" );
		$this->assertEquals ( $children [1] ['Title'], "{$this->type()}1-2" );
		$this->assertEquals ( count ( $children ), 2 );
	}
	/**
	 * @depends testAdd
	 */
	function testTitleID()
	{
		$ID = $this->Instance ()->add ( "{$this->type()}-1", "description of the {$this->type()}" );
		$ID2 = $this->Instance ()->add ( "{$this->type()}-2", "description of the {$this->type()}" );
		$this->assertEquals ( $this->Instance ()->titleId ( "{$this->type()}-1" ), $ID );
		$this->assertNotEquals ( $this->Instance ()->titleId ( "{$this->type()}-2" ), $ID );
		$this->assertNotEquals ( $this->Instance ()->titleId ( "{$this->type()}-3" ), $ID );
	}
	function testDescendants()
	{
		$Parent = $this->Instance ()->add ( "{$this->type()}1", "" );
		
		$Child1 = $this->Instance ()->add ( "{$this->type()}1-1", "", $Parent );
		$Child2 = $this->Instance ()->add ( "{$this->type()}1-2", "", $Parent );
		
		$Child11 = $this->Instance ()->add ( "{$this->type()}1-1-1", "", $Child1 );
		$Child12 = $this->Instance ()->add ( "{$this->type()}1-1-2", "", $Child1 );
		$Child13 = $this->Instance ()->add ( "{$this->type()}1-1-3", "", $Child1 );
		
		$descendants = ($this->Instance ()->descendants ( $Parent ));
		$this->assertEquals ( $descendants ["{$this->type()}1-1"] ['Depth'], 1 );
		$this->assertEquals ( $descendants ["{$this->type()}1-1-3"] ['Depth'], 2 );
		$this->assertEquals ( count ( $descendants ), 5 );
	}
	function testDepth()
	{
		$Parent = $this->Instance ()->add ( "{$this->type()}1", "" );
		
		$Child1 = $this->Instance ()->add ( "{$this->type()}1-1", "", $Parent );
		
		$Child11 = $this->Instance ()->add ( "{$this->type()}1-1-1", "", $Child1 );
		$Child13 = $this->Instance ()->add ( "{$this->type()}1-1-3", "", $Child1 );
		$this->assertEquals ( 0, $this->Instance ()->depth ( 1 ) );
		$this->assertEquals ( 3, $this->Instance ()->depth ( $Child13 ) );
		$this->assertEquals ( 2, $this->Instance ()->depth ( $Child1 ) );
	}
	function testPath()
	{
		$Parent = $this->Instance ()->add ( "{$this->type()}1", "" );
		
		$Child1 = $this->Instance ()->add ( "{$this->type()}1-1", "", $Parent );
		$Child2 = $this->Instance ()->add ( "{$this->type()}1-2", "", $Parent );
		
		$Child11 = $this->Instance ()->add ( "{$this->type()}1-1-1", "", $Child1 );
		$Child12 = $this->Instance ()->add ( "{$this->type()}1-1-2", "", $Child1 );
		$Child13 = $this->Instance ()->add ( "{$this->type()}1-1-3", "", $Child1 );
		

		$this->assertEquals ( "/", $this->Instance ()->path ( 1 ) );
		$this->assertEquals ( null, $this->Instance ()->path ( 100 ) );
		$this->assertEquals ( "/{$this->type()}1", $this->Instance ()->path ( $Parent ) );
		$this->assertEquals ( "/{$this->type()}1/{$this->type()}1-2", $this->Instance ()->path ( $Child2 ) );
		$this->assertEquals ( "/{$this->type()}1/{$this->type()}1-1/{$this->type()}1-1-3", $this->Instance ()->path ( $Child13 ) );
	}
	function testParentNode()
	{
		$Parent = $this->Instance ()->add ( "{$this->type()}1", "" );
		
		$Child1 = $this->Instance ()->add ( "{$this->type()}1-1", "", $Parent );
		$Child2 = $this->Instance ()->add ( "{$this->type()}1-2", "", $Parent );
		
		$Child11 = $this->Instance ()->add ( "{$this->type()}1-1-1", "", $Child1 );
		$Child12 = $this->Instance ()->add ( "{$this->type()}1-1-2", "", $Child1 );
		$Child13 = $this->Instance ()->add ( "{$this->type()}1-1-3", "", $Child1 );
		
		$t = $this->Instance ()->parentNode ( 1 );
		$this->assertEquals ( null, $t );
		$t = $this->Instance ()->parentNode ( $Parent );
		$this->assertEquals ( 1, $t ['ID'] );
		$t = $this->Instance ()->parentNode ( $Child2 );
		$this->assertEquals ( $Parent, $t ['ID'] );
		$t = $this->Instance ()->parentNode ( $Child12 );
		$this->assertEquals ( $Child1, $t ['ID'] );
	}
	function testReset()
	{
		$Parent = $this->Instance ()->add ( "{$this->type()}1", "" );
		
		$Child1 = $this->Instance ()->add ( "{$this->type()}1-1", "", $Parent );
		$Child2 = $this->Instance ()->add ( "{$this->type()}1-2", "", $Parent );
		
		$Child11 = $this->Instance ()->add ( "{$this->type()}1-1-1", "", $Child1 );
		$Child12 = $this->Instance ()->add ( "{$this->type()}1-1-2", "", $Child1 );
		$Child13 = $this->Instance ()->add ( "{$this->type()}1-1-3", "", $Child1 );
		
		$this->Instance ()->reset ( true );
		$this->assertEquals ( 1, $this->Instance ()->titleId ( "root" ) );
		$this->assertEmpty ( $this->Instance ()->children ( 1 ) );
		$this->setExpectedException ( "Exception" );
		$this->Instance ()->reset ();
	}
	function testRemoveAgain()
	{
		$ID = $this->Instance ()->add ( "some_{$this->type()}", "some description" );
		$this->assertEquals ( $ID, $this->Instance ()->titleId ( "some_{$this->type()}" ) );
		
		$this->Instance ()->remove ( $ID );
		$this->assertNotEquals ( $ID, $this->Instance ()->titleId ( "some_{$this->type()}" ) );
		$this->assertEquals ( null, $this->Instance ()->titleId ( "some_{$this->type()}" ) );
		

		// ow recursive
		$Parent = $this->Instance ()->add ( "{$this->type()}1", "" );
		
		$Child1 = $this->Instance ()->add ( "{$this->type()}1-1", "", $Parent );
		$Child2 = $this->Instance ()->add ( "{$this->type()}1-2", "", $Parent );
		
		$Child11 = $this->Instance ()->add ( "{$this->type()}1-1-1", "", $Child1 );
		$Child12 = $this->Instance ()->add ( "{$this->type()}1-1-2", "", $Child1 );
		$Child13 = $this->Instance ()->add ( "{$this->type()}1-1-3", "", $Child1 );
		

		$this->Instance ()->remove ( $Child1, true );

		$this->assertEquals ( 3, $this->Instance ()->count () );
		$this->assertEquals ( $Child2, $this->Instance ()->titleId ( "{$this->type()}1-2" ) );
		$this->assertEquals ( null, $this->Instance ()->titleId ( "{$this->type()}1-1" ) );
		$this->assertEquals ( null, $this->Instance ()->titleId ( "{$this->type()}1-1-1" ) );
	}
	/**
	 * @depends testAdd
	 */
	function testAssign()
	{
		$ID1=Jf::$RBAC->Roles->add("role1", "description of role1");
		$ID2=Jf::$RBAC->Roles->add("role2", "description of role2");
		$ID11=Jf::$RBAC->Roles->add("role1-1", "description of role",$ID1);
		$ID12=Jf::$RBAC->Roles->add("role1-2", "description of role",$ID1);
		$ID121=Jf::$RBAC->Roles->add("role1-2-1", "description of role",$ID12);
		
		$PID1=Jf::$RBAC->Permissions->add("permission1", "description");
		$PID2=Jf::$RBAC->Permissions->add("permission2", "description");
		$PID21=Jf::$RBAC->Permissions->add("permission2-1", "description",$PID2);
		
		$this->assertTrue($this->Instance()->assign($ID121, $PID2));
		$this->assertFalse($this->Instance()->assign($ID121, $PID2));
		
	}
	/**
	 * @depends testAssign
	 */
	function testUnassign()
	{
		$ID1=Jf::$RBAC->Roles->add("role1", "description of role1");
		$ID2=Jf::$RBAC->Roles->add("role2", "description of role2");
		$ID11=Jf::$RBAC->Roles->add("role1-1", "description of role",$ID1);
		$ID12=Jf::$RBAC->Roles->add("role1-2", "description of role",$ID1);
		$ID121=Jf::$RBAC->Roles->add("role1-2-1", "description of role",$ID12);
		
		$PID1=Jf::$RBAC->Permissions->add("permission1", "description");
		$PID2=Jf::$RBAC->Permissions->add("permission2", "description");
		$PID21=Jf::$RBAC->Permissions->add("permission2-1", "description",$PID2);
		
		$this->Instance()->assign($ID121, $PID2);
		
		$this->assertFalse($this->Instance()->unassign($ID121,$PID1));
		$this->assertTrue($this->Instance()->unassign($ID121,$PID2));
		$this->assertFalse($this->Instance()->unassign($ID121,$PID2)); //already removed
		
		
	}
	
	function testResetAssignments()
	{
		$ID1=Jf::$RBAC->Roles->add("role1", "description of role1");
		$ID2=Jf::$RBAC->Roles->add("role2", "description of role2");
		$ID11=Jf::$RBAC->Roles->add("role1-1", "description of role",$ID1);
		$ID12=Jf::$RBAC->Roles->add("role1-2", "description of role",$ID1);
		$ID121=Jf::$RBAC->Roles->add("role1-2-1", "description of role",$ID12);
		
		$PID1=Jf::$RBAC->Permissions->add("permission1", "description");
		$PID2=Jf::$RBAC->Permissions->add("permission2", "description");
		$PID21=Jf::$RBAC->Permissions->add("permission2-1", "description",$PID2);
		
		$this->Instance()->assign($ID121, $PID2);		
		$this->Instance()->assign($ID1, $PID1);		
		$this->Instance()->assign($ID12, $PID21);

		$this->Instance()->resetAssignments(true);
		
		$this->assertFalse($this->Instance()->unassign($ID121,$PID2));
		$this->assertFalse($this->Instance()->unassign($ID1,$PID1));
		
		$this->setExpectedException("\Exception");
		$this->Instance()->resetAssignments(false);
		
	}
}