<?php
namespace PhpRbac;

/**
 * @file
 * Unit Tests for PhpRbac PSR Wrapper
 *
 * @defgroup phprbac_unit_test_wrapper_role_manager Unit Tests for Base RBAC Functionality
 * @ingroup phprbac
 * @{
 * Documentation for all Unit Tests regarding RoleManager functionality.
 */

class RbacRolesTest extends \RbacBase
{
    protected function Instance()
    {
        return self::$rbac->Roles;
    }
    
    protected function Type()
    {
        return "roles";
    }

    /*
     * Test for proper object instantiation
     */
    
    public function testRolesInstance() {
        $this->assertInstanceOf('RoleManager', self::$rbac->Roles);
    }

    /*
     * Tests for $this->Instance()->Remove()
     */

    /*
     * Tests for $this->Instance()->HasPermission()
     */
    
    /*
     * Tests for $this->Instance()->Permissions()
     */
    
    /*
     * Tests for $this->Instance()->UnassignPermissions()
     */

    /*
     * Tests for $this->Instance()->UnassignUsers()
     */
    
}

/** @} */ // End group phprbac_unit_test_wrapper_role_manager */
