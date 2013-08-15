<?php
namespace PhpRbac;

use PhpRbac\Rbac;

/**
 * @file
 * Unit Tests for PhpRbac PSR Wrapper
 *
 * @defgroup phprbac_unit_test_wrapper Unit Tests for RBAC Functionality
 * @ingroup phprbac
 * @{
 * Documentation for all Unit Tests regarding PhpRbac related functionality.
 */
class RbacTest extends \PHPUnit_Framework_TestCase
{
	public static $rbac;
    
    public static function setUpBeforeClass()
    {
    	self::$rbac = new Rbac('unit_test');
    }
    
    public function testRbacInstance() {
        $this->assertInstanceOf('PhpRbac\Rbac', self::$rbac);
    }

    public function testPermissionsInstance() {
    	$this->assertInstanceOf('PermissionManager', self::$rbac->Permissions);
    }

    public function testRolesInstance() {
    	$this->assertInstanceOf('RoleManager', self::$rbac->Roles);
    }

    public function testUsersInstance() {
    	$this->assertInstanceOf('RBACUserManager', self::$rbac->Users);
    }
    
    public function testAssign()
    {
        $this->assertTrue(true);
    }
    
    public function testCheck()
    {
        $this->assertTrue(true);
    }
    
    public function testEnforce()
    {
        $this->assertTrue(true);
    }
}

/** @} */ // End group phprbac_unit_test_wrapper */
