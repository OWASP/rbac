<?php
namespace PhpRbac;

use \jf;

class Rbac
{
    public function __construct()
    {
        require_once dirname(dirname(__DIR__)) . '/database/database.config';
        require_once 'core/lib/jf.php';
        jf::setTablePrefix($table_prefix);
        
        $this->Permissions = jf::$RBAC->Permissions;
        $this->Roles = jf::$RBAC->Roles;
        $this->Users = jf::$RBAC->Users;
    }
    
    public function assign($role, $permission)
    {
        return jf::$RBAC->Assign($role, $permission);
    }

    public function check($permission, $user_id)
    {
        return jf::$RBAC->Check($permission, $user_id);
    }
    
    public function enforce($permission)
    {
        return jf::$RBAC->Enforce($permission);
    }
    
    public function reset($ensure = false)
    {
        return jf::$RBAC->Reset($ensure);
    }
    
    public function tablePrefix()
    {
        return jf::$RBAC->TablePrefix();
    }
}