<?php
namespace PhpRbac;

class RbacPermissionsTest extends \RbacBase
{
    protected function Instance()
    {
        return self::$rbac->Permissions;
    }
    
    protected function Type()
    {
        return "permissions";
    }
    
    public function testPermissionsInstance() {
        $this->assertInstanceOf('PermissionManager', self::$rbac->Permissions);
    }
}