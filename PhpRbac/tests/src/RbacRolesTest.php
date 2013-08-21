<?php
namespace PhpRbac;

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

    public function testRolesInstance() {
        $this->assertInstanceOf('RoleManager', self::$rbac->Roles);
    }
}