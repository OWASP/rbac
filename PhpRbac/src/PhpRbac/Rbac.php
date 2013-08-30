<?php
namespace PhpRbac;

use \Jf;

/**
 * @file
 * Provides NIST Level 2 Standard Role Based Access Control functionality
 *
 * @defgroup phprbac RBAC Functionality
 * @{
 * Documentation for all PhpRbac related functionality.
 */
class Rbac
{
    public function __construct($unit_test = '')
    {
        if ((string) $unit_test === 'unit_test') {
            require_once dirname(dirname(__DIR__)) . '/tests/database/database.config';
        } else {
            require_once dirname(dirname(__DIR__)) . '/database/database.config';
        }

        require_once 'core/lib/Jf.php';

        $this->Permissions = Jf::$RBAC->Permissions;
        $this->Roles = Jf::$RBAC->Roles;
        $this->Users = Jf::$RBAC->Users;
    }

    public function assign($role, $permission)
    {
        return Jf::$RBAC->assign($role, $permission);
    }

    public function check($permission, $user_id)
    {
        return Jf::$RBAC->check($permission, $user_id);
    }

    public function enforce($permission, $user_id)
    {
        return Jf::$RBAC->enforce($permission, $user_id);
    }

    public function reset($ensure = false)
    {
        return Jf::$RBAC->reset($ensure);
    }

    public function tablePrefix()
    {
        return Jf::$RBAC->tablePrefix();
    }
}

/** @} */ // End group phprbac */
