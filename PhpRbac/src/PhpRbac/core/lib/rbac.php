<?php
require_once __DIR__."/nestedset/base.php";
require_once __DIR__."/nestedset/full.php";

class RBACException extends \Exception
{
}
class RBACRoleNotFoundException extends RBACException
{
}
class RBACPermissionNotFoundException extends RBACException
{
}
class RBACUserNotProvidedException extends RBACException
{
}

class JModel
{
	function TablePrefix()
	{
		return jf::TablePrefix();
	}

	protected function IsSQLite()
	{
		$Adapter=get_class(jf::$DB);
		return $Adapter == "PDO" and jf::$DB->getAttribute(PDO::ATTR_DRIVER_NAME)=="sqlite";
	}
	protected function IsMySql()
	{
		$Adapter=get_class(jf::$DB);
		return $Adapter == "mysqli" or ($Adapter == "PDO" and jf::$DB->getAttribute(PDO::ATTR_DRIVER_NAME)=="mysql");
	}
}
/**
 * RBAC base class, it contains operations that are essentially the same for
 * permissions and roles
 * and is inherited by both
 *
 * @author abiusx
 * @version 1.0
 */
abstract class BaseRBAC extends JModel
{

	function RootID()
	{
		return 1;
	}

	/**
	 * Return type of current instance, e.g roles, permissions
	 *
	 * @return string
	 */
	abstract protected function Type();
	/**
	 * Adds a new role or permission
	 * Returns new entry's ID
	 *
	 * @param string $Title
	 *        	Title of the new entry
	 * @param integer $Description
	 *        	Description of the new entry
	 * @param integer $ParentID
	 *        	optional ID of the parent node in the hierarchy
	 * @return integer ID of the new entry
	 */
	function Add($Title, $Description, $ParentID = null)
	{
		if ($ParentID === null)
			$ParentID = $this->RootID ();
		return (int)$this->{$this->Type ()}->InsertChildData ( array ("Title" => $Title, "Description" => $Description ), "ID=?", $ParentID );
	}
	/**
	 * Return count of the entity
	 *
	 * @return integer
	 */
	function Count()
	{
		$Res = jf::SQL ( "SELECT COUNT(*) FROM {$this->TablePrefix()}{$this->Type()}" );
		return (int)$Res [0] ['COUNT(*)'];
	}

	/**
	 * Returns ID of a path
	 *
	 * @todo this has a limit of 1000 characters on $Path
	 * @param string $Path
	 *        	such as /role1/role2/role3 ( a single slash is root)
	 * @return integer NULL
	 */
	function PathID($Path)
	{
		$Path = "root" . $Path;
		if ($Path [strlen ( $Path ) - 1] == "/")
			$Path = substr ( $Path, 0, strlen ( $Path ) - 1 );
		$Parts = explode ( "/", $Path );

		$Adapter = get_class(jf::$DB);
		if ($Adapter == "mysqli" or ($Adapter == "PDO" and jf::$DB->getAttribute(PDO::ATTR_DRIVER_NAME)=="mysql"))
			$GroupConcat="GROUP_CONCAT(parent.Title ORDER BY parent.Lft SEPARATOR '/')";
		elseif ($Adapter == "PDO" and jf::$DB->getAttribute(PDO::ATTR_DRIVER_NAME)=="sqlite")
			$GroupConcat="GROUP_CONCAT(parent.Title,'/')";
		else
			throw new \Exception ("Unknown Group_Concat on this type of database: {$Adapter}");
		$res = jf::SQL ( "SELECT node.ID,{$GroupConcat} AS Path
				FROM {$this->TablePrefix()}{$this->Type()} AS node,
				{$this->TablePrefix()}{$this->Type()} AS parent
				WHERE node.Lft BETWEEN parent.Lft AND parent.Rght
				AND  node.Title=?
				GROUP BY node.ID
				HAVING Path = ?
				", $Parts [count ( $Parts ) - 1], $Path );
		if ($res)
			return $res [0] ['ID'];
		else
			return null;
			// TODO: make the below SQL work, so that 1024 limit is over

		$QueryBase = ("SELECT n0.ID  \nFROM {$this->TablePrefix()}{$this->Type()} AS n0");
		$QueryCondition = "\nWHERE 	n0.Title=?";

		for($i = 1; $i < count ( $Parts ); ++ $i)
		{
			$j = $i - 1;
			$QueryBase .= "\nJOIN 		{$this->TablePrefix()}{$this->Type()} AS n{$i} ON (n{$j}.Lft BETWEEN n{$i}.Lft+1 AND n{$i}.Rght)";
			$QueryCondition .= "\nAND 	n{$i}.Title=?";
			// Forcing middle elements
			$QueryBase .= "\nLEFT JOIN 	{$this->TablePrefix()}{$this->Type()} AS nn{$i} ON (nn{$i}.Lft BETWEEN n{$i}.Lft+1 AND n{$j}.Lft-1)";
			$QueryCondition .= "\nAND 	nn{$i}.Lft IS NULL";
		}
		$Query = $QueryBase . $QueryCondition;
		$PartsRev = array_reverse ( $Parts );
		array_unshift ( $PartsRev, $Query );

		print_ ( $PartsRev );
		$res = call_user_func_array ( "jf::SQL", $PartsRev );
		if ($res)
			return $res [0] ['ID'];
		else
			return null;
	}

	/**
	 * Returns ID belonging to a title, and the first one on that
	 *
	 * @param unknown_type $Title
	 */
	function TitleID($Title)
	{
		return $this->{$this->Type ()}->GetID ( "Title=?", $Title );
	}
	/**
	 * Return the whole record of a single entry (including Rght and Lft fields)
	 *
	 * @param integer $ID
	 */
	protected function GetRecord($ID)
	{
		$args = func_get_args ();
		return call_user_func_array ( array ($this->{$this->Type ()}, "GetRecord" ), $args );
	}
	/**
	 * Returns title of entity
	 *
	 * @param integer $ID
	 * @return string NULL
	 */
	function GetTitle($ID)
	{
		$r = $this->GetRecord ( "ID=?", $ID );
		if ($r)
			return $r ['Title'];
		else
			return null;
	}
	/**
	 * Return description of entity
	 *
	 * @param integer $ID
	 * @return string NULL
	 */
	function GetDescription($ID)
	{
		$r = $this->GetRecord ( "ID=?", $ID );
		if ($r)
			return $r ['Description'];
		else
			return null;
	}
	/**
	 * Adds a path and all its components.
	 * Will not replace or create siblings if a component exists.
	 *
	 * @param string $Path
	 *        	such as /some/role/some/where - Must begin with a / (slash)
	 * @param array $Descriptions
	 *        	array of descriptions (will add with empty description if not available)
	 *
	 * @return integer NULL components ID
	 */
	function AddPath($Path, array $Descriptions = null)
	{
		if ($Path[0] !== "/")
	        throw new \Exception ("The path supplied is not valid.");

		$Path = substr ( $Path, 1 );
		$Parts = explode ( "/", $Path );
		$Parent = 1;
		$index = 0;
		$CurrentPath = "";
		foreach ( $Parts as $p )
		{
			if (isset ( $Descriptions [$index] ))
				$Description = $Descriptions [$index];
			else
				$Description = "";
			$CurrentPath .= "/{$p}";
			$t = $this->PathID ( $CurrentPath );
			if (! $t)
			{
				$IID = $this->Add ( $p, $Description, $Parent );
				$Parent = $IID;
			}
			else
			{
				$Parent = $t;
			}

			$index += 1;
		}
		return (int)$Parent;
	}

	/**
	 * Edits an entity, changing title and/or description
	 *
	 * @param integer $ID
	 * @param string $NewTitle
	 * @param string $NewDescription
	 *
	 */
	function Edit($ID, $NewTitle = null, $NewDescription = null)
	{
		$Data = array ();
		if ($NewTitle !== null)
			$Data ['Title'] = $NewTitle;
		if ($NewDescription !== null)
			$Data ['Description'] = $NewDescription;
		return $this->{$this->Type ()}->EditData ( $Data, "ID=?", $ID ) == 1;
	}

	/**
	 * Returns children of an entity
	 *
	 * @return array
	 *
	 */
	function Children($ID)
	{
		return $this->{$this->Type ()}->ChildrenConditional ( "ID=?", $ID );
	}

	/**
	 * Returns descendants of a node, with their depths in integer
	 *
	 * @param integer $ID
	 * @return array with keys as titles and Title,ID, Depth and Description
	 *
	 */
	function Descendants($ID)
	{
		$res = $this->{$this->Type ()}->DescendantsConditional(/* absolute depths*/false, "ID=?", $ID );
		$out = array ();
		if (is_array ( $res ))
			foreach ( $res as $v )
				$out [$v ['Title']] = $v;
		return $out;
	}

	/**
	 * Return depth of a node
	 *
	 * @param integer $ID
	 */
	function Depth($ID)
	{
		return $this->{$this->Type ()}->DepthConditional ( "ID=?", $ID );
	}

	/**
	 * Returns path of a node
	 *
	 * @param integer $ID
	 * @return string path
	 */
	function Path($ID)
	{
		$res = $this->{$this->Type ()}->PathConditional ( "ID=?", $ID );
		$out = null;
		if (is_array ( $res ))
			foreach ( $res as $r )
				if ($r ['ID'] == 1)
					$out = '/';
				else
					$out .= "/" . $r ['Title'];
		if (strlen ( $out ) > 1)
			return substr ( $out, 1 );
		else
			return $out;
	}

	/**
	 * Returns parent of a node
	 *
	 * @param integer $ID
	 * @return array including Title, Description and ID
	 *
	 */
	function ParentNode($ID)
	{
		return $this->{$this->Type ()}->ParentNodeConditional ( "ID=?", $ID );
	}

	/**
	 * Reset the table back to its initial state
	 * Keep in mind that this will not touch relations
	 *
	 * @param boolean $Ensure
	 *        	must be true to work, otherwise error
	 * @throws \Exception
	 * @return integer number of deleted entries
	 *
	 */
	function Reset($Ensure = false)
	{
		if ($Ensure !== true)
		{
			throw new \Exception ("You must pass true to this function, otherwise it won't work.");
			return;
		}
		$res = jf::SQL ( "DELETE FROM {$this->TablePrefix()}{$this->Type()}" );
		$Adapter = get_class(jf::$DB);
		if ($this->IsMySql())
			jf::SQL ( "ALTER TABLE {$this->TablePrefix()}{$this->Type()} AUTO_INCREMENT=1 " );
		elseif ($this->IsSQLite())
			jf::SQL ( "delete from sqlite_sequence where name=? ", $this->TablePrefix () . "{$this->Type()}" );
		else
			throw new \Exception ( "RBAC can not reset table on this type of database: {$Adapter}" );
		$iid = jf::SQL ( "INSERT INTO {$this->TablePrefix()}{$this->Type()} (Title,Description,Lft,Rght) VALUES (?,?,?,?)", "root", "root",0,1 );
		return (int)$res;
	}


	/**
	 * Assigns a role to a permission (or vice-versa)
	 *
	 * @param integer $Role
	 * @param integer $Permission
	 * @return boolean inserted or existing
	 *
	 * @todo: Check for valid permissions/roles
	 * @todo: Implement custom error handler
	 */
	function Assign($Role, $Permission)
	{
		return jf::SQL ( "INSERT INTO {$this->TablePrefix()}rolepermissions
		(RoleID,PermissionID,AssignmentDate)
		VALUES (?,?,?)", $Role, $Permission, jf::time () ) >= 1;
	}
	/**
	 * Unassigns a role-permission relation
	 *
	 * @param integer $Role
	 * @param integer $Permission
	 * @return boolean
	 */
	function Unassign($Role, $Permission)
	{
		return jf::SQL ( "DELETE FROM {$this->TablePrefix()}rolepermissions WHERE
	RoleID=? AND PermissionID=?", $Role, $Permission ) == 1;
	}

	/**
	 * Remove all role-permission relations
	 * mostly used for testing
	 *
	 * @param boolean $Ensure
	 *        	must set or throws error
	 * @return number of deleted relations
	 */
	function ResetAssignments($Ensure = false)
	{
		if ($Ensure !== true)
		{
			throw new \Exception ("You must pass true to this function, otherwise it won't work.");
			return;
		}
		$res = jf::SQL ( "DELETE FROM {$this->TablePrefix()}rolepermissions" );

		$Adapter = get_class(jf::$DB);
		if ($this->IsMySql())
			jf::SQL ( "ALTER TABLE {$this->TablePrefix()}rolepermissions AUTO_INCREMENT =1 " );
		elseif ($this->IsSQLite())
			jf::SQL ( "delete from sqlite_sequence where name=? ", $this->TablePrefix () . "_rolepermissions" );
		else
			throw new \Exception ( "RBAC can not reset table on this type of database: {$Adapter}" );
		$this->Assign ( $this->RootID(), $this->RootID());
		return $res;
	}
}


/**
 * RBAC Permission Manager
 * holds specific operations for permissions
 *
 * @author abiusx
 * @version 1.0
 */
class PermissionManager extends BaseRBAC
{
	/**
	 * Permissions Nested Set
	 *
	 * @var FullNestedSet
	 */
	protected $permissions;
	protected function Type()
	{
		return "permissions";
	}
	function __construct()
	{
		$this->permissions = new FullNestedSet ( $this->TablePrefix () . "permissions", "ID", "Lft", "Rght" );
	}
	/**
	 * Remove a permission from system
	 *
	 * @param integer $ID
	 *        	permission id
	 * @param boolean $Recursive
	 *        	delete all descendants
	 *
	 */
	function Remove($ID, $Recursive = false)
	{
		$this->UnassignRoles ( $ID );
		if (! $Recursive)
			return $this->permissions->DeleteConditional ( "ID=?", $ID );
		else
			return $this->permissions->DeleteSubtreeConditional ( "ID=?", $ID );
	}

	/**
	 * Unassignes all roles of this permission, and returns their number
	 *
	 * @param integer $ID
	 * @return integer
	 */
	function UnassignRoles($ID)
	{
		$res = jf::SQL ( "DELETE FROM {$this->TablePrefix()}rolepermissions WHERE
			PermissionID=?", $ID );
		return (int)$res;
	}

	/**
	 * Returns all roles assigned to a permission
	 *
	 * @param integer $Permission
	 *        	ID
	 * @param boolean $OnlyIDs
	 *        	if true, result would be a 1D array of IDs
	 * @return Array 2D or 1D or null
	 */
	function Roles($Permission, $OnlyIDs = true)
	{
		if (! is_numeric ( $Permission ))
			$Permission = $this->Permission_ID ( $Permission );
		if ($OnlyIDs)
		{
			$Res = jf::SQL ( "SELECT RoleID AS `ID` FROM
				{$this->TablePrefix()}rolepermissions WHERE PermissionID=? ORDER BY RoleID", $Permission );
			if (is_array ( $Res ))
			{
				$out = array ();
				foreach ( $Res as $R )
					$out [] = $R ['ID'];
				return $out;
			}
			else
				return null;
		}
		else
			return jf::SQL ( "SELECT `TP`.* FROM {$this->TablePrefix()}rolepermissions AS `TR`
				RIGHT JOIN {$this->TablePrefix()}roles AS `TP` ON (`TR`.RoleID=`TP`.ID)
				WHERE PermissionID=? ORDER BY TP.RoleID", $Permission );
	}
}


/**
 * RBAC Role Manager
 * it has specific functions to the roles
 *
 * @author abiusx
 * @version 1.0
 */
class RoleManager extends BaseRBAC
{
	/**
	 * Roles Nested Set
	 *
	 * @var FullNestedSet
	 */
	protected $roles = null;
	protected function Type()
	{
		return "roles";
	}
	function __construct()
	{
		$this->Type = "roles";
		$this->roles = new FullNestedSet ( $this->TablePrefix () . "roles", "ID", "Lft", "Rght" );
	}

	/**
	 * Remove a role from system
	 *
	 * @param integer $ID
	 *        	role id
	 * @param boolean $Recursive
	 *        	delete all descendants
	 *
	 */
	function Remove($ID, $Recursive = false)
	{
		$this->UnassignPermissions ( $ID );
		$this->UnassignUsers ( $ID );
		if (! $Recursive)
			return $this->roles->DeleteConditional ( "ID=?", $ID );
		else
			return $this->roles->DeleteSubtreeConditional ( "ID=?", $ID );
	}
	/**
	 * Unassigns all permissions belonging to a role
	 *
	 * @param integer $ID
	 *        	role ID
	 * @return integer number of assignments deleted
	 */
	function UnassignPermissions($ID)
	{
		$r = jf::SQL ( "DELETE FROM {$this->TablePrefix()}rolepermissions WHERE
			RoleID=? ", $ID );
		return $r;
	}
	/**
	 * Unassign all users that have a certain role
	 *
	 * @param integer $ID
	 *        	role ID
	 * @return integer number of deleted assignments
	 */
	function UnassignUsers($ID)
	{
		return jf::SQL ( "DELETE FROM {$this->TablePrefix()}userroles WHERE
			RoleID=?", $ID );
	}

	/**
	 * Checks to see if a role has a permission or not
	 *
	 * @param integer $Role
	 *        	ID
	 * @param integer $Permission
	 *        	ID
	 * @return boolean
	 *
	 * @todo: If we pass a Role that doesn't exist the method just returns false. We may want to check for a valid Role.
	 */
	function HasPermission($Role, $Permission)
	{
		$Res = jf::SQL ( "
					SELECT COUNT(*) AS Result
					FROM {$this->TablePrefix()}rolepermissions AS TRel
					JOIN {$this->TablePrefix()}permissions AS TP ON ( TP.ID= TRel.PermissionID)
					JOIN {$this->TablePrefix()}roles AS TR ON ( TR.ID = TRel.RoleID)
					WHERE TR.Lft BETWEEN
					(SELECT Lft FROM {$this->TablePrefix()}roles WHERE ID=?)
					AND
					(SELECT Rght FROM {$this->TablePrefix()}roles WHERE ID=?)
					/* the above section means any row that is a descendants of our role (if descendant roles have some permission, then our role has it two) */
					AND TP.ID IN (
					SELECT parent.ID
					FROM {$this->TablePrefix()}permissions AS node,
					{$this->TablePrefix()}permissions AS parent
					WHERE node.Lft BETWEEN parent.Lft AND parent.Rght
					AND ( node.ID=? )
					ORDER BY parent.Lft
					);
					/*
					the above section returns all the parents of (the path to) our permission, so if one of our role or its descendants
					has an assignment to any of them, we're good.
					*/
					", $Role, $Role, $Permission );
		return $Res [0] ['Result'] >= 1;
	}
	/**
	 * Returns all permissions assigned to a role
	 *
	 * @param integer $Role
	 *        	ID
	 * @param boolean $OnlyIDs
	 *        	if true, result would be a 1D array of IDs
	 * @return Array 2D or 1D or null
	 *         the two dimensional array would have ID,Title and Description of permissions
	 */
	function Permissions($Role, $OnlyIDs = true)
	{
		if ($OnlyIDs)
		{
			$Res = jf::SQL ( "SELECT PermissionID AS `ID` FROM {$this->TablePrefix()}rolepermissions WHERE RoleID=? ORDER BY PermissionID", $Role );
			if (is_array ( $Res ))
			{
				$out = array ();
				foreach ( $Res as $R )
					$out [] = $R ['ID'];
				return $out;
			}
			else
				return null;
		}
		else
			return jf::SQL ( "SELECT `TP`.* FROM {$this->TablePrefix()}rolepermissions AS `TR`
			RIGHT JOIN {$this->TablePrefix()}permissions AS `TP` ON (`TR`.PermissionID=`TP`.ID)
			WHERE RoleID=? ORDER BY TP.PermissionID", $Role );
	}
}

/**
 * RBAC User Manager
 * holds specific operations for users
 *
 * @author abiusx
 * @version 1.0
 */
class RBACUserManager extends JModel
{
	/**
	 * Checks to see whether a user has a role or not
	 *
	 * @param integer|string $Role
	 *        	id, title or path
	 * @param integer $User
	 *        	UserID, not optional
	 *
	 * @throws RBACUserNotProvidedException
	 * @return boolean success
	 */
	function HasRole($Role, $UserID = null)
	{
	    if ($UserID === null)
		    throw new \RBACUserNotProvidedException ("\$UserID is a required argument.");

		if (is_int ( $Role ))
		{
			$RoleID = $Role;
		}
		else
		{
			if (substr ( $Role, 0, 1 ) == "/")
				$RoleID = jf::$RBAC->Roles->PathID ( $Role );
			else
				$RoleID = jf::$RBAC->Roles->TitleID ( $Role );
		}

		$R = jf::SQL ( "SELECT * FROM {$this->TablePrefix()}userroles AS TUR
			JOIN {$this->TablePrefix()}roles AS TRdirect ON (TRdirect.ID=TUR.RoleID)
			JOIN {$this->TablePrefix()}roles AS TR ON (TR.Lft BETWEEN TRdirect.Lft AND TRdirect.Rght)

			WHERE
			TUR.UserID=? AND TR.ID=?", $UserID, $RoleID );
		return $R !== null;
	}
	/**
	 * Assigns a role to a user
	 *
	 * @param integer|string $Role
	 *        	id or path or title
	 * @param integer $UserID
	 *        	UserID (use 0 for guest)
	 *
	 * @throws RBACUserNotProvidedException
	 * @return inserted or existing
	 */
	function Assign($Role, $UserID = null)
	{
	   if ($UserID === null)
		    throw new \RBACUserNotProvidedException ("\$UserID is a required argument.");

		if (is_int ( $Role ))
		{
			$RoleID = $Role;
		}
		else
		{
			if (substr ( $Role, 0, 1 ) == "/")
				$RoleID = jf::$RBAC->Roles->PathID ( $Role );
			else
				$RoleID = jf::$RBAC->Roles->TitleID ( $Role );
		}
		$res = jf::SQL ( "INSERT INTO {$this->TablePrefix()}userroles
				(UserID,RoleID,AssignmentDate)
				VALUES (?,?,?)
				", $UserID, $RoleID, jf::time () );
		return $res >= 1;
	}
	/**
	 * Unassigns a role from a user
	 *
	 * @param integer $Role
	 *        	ID
	 * @param integer $UserID
	 *        	UserID (use 0 for guest)
	 *
	 * @throws RBACUserNotProvidedException
	 * @return boolean success
	 */
	function Unassign($Role, $UserID = null)
	{
	   if ($UserID === null)
		    throw new \RBACUserNotProvidedException ("\$UserID is a required argument.");

		return jf::SQL ( "DELETE FROM {$this->TablePrefix()}userroles
		WHERE UserID=? AND RoleID=?", $UserID, $Role ) >= 1;
	}

	/**
	 * Returns all roles of a user
	 *
	 * @param integer $UserID
	 *        	Not optional
	 *
	 * @throws RBACUserNotProvidedException
	 * @return array null
	 *
	 */
	function AllRoles($UserID = null)
	{
	   if ($UserID === null)
		    throw new \RBACUserNotProvidedException ("\$UserID is a required argument.");

		return jf::SQL ( "SELECT TR.*
			FROM
			{$this->TablePrefix()}userroles AS `TRel`
			JOIN {$this->TablePrefix()}roles AS `TR` ON
			(`TRel`.RoleID=`TR`.ID)
			WHERE TRel.UserID=?", $UserID );
	}
	/**
	 * Return count of roles for a user
	 *
	 * @param integer $UserID
	 *
	 * @throws RBACUserNotProvidedException
	 * @return integer
	 */
	function RoleCount($UserID = null)
	{
		if ($UserID === null)
		    throw new \RBACUserNotProvidedException ("\$UserID is a required argument.");

		$Res = jf::SQL ( "SELECT COUNT(*) AS Result FROM {$this->TablePrefix()}userroles WHERE UserID=?", $UserID );
		return (int)$Res [0] ['Result'];
	}

	/**
	 * Remove all role-user relations
	 * mostly used for testing
	 *
	 * @param boolean $Ensure
	 *        	must set or throws error
	 * @return number of deleted relations
	 */
	function ResetAssignments($Ensure = false)
	{
		if ($Ensure !== true)
		{
			throw new \Exception ("You must pass true to this function, otherwise it won't work.");
			return;
		}
		$res = jf::SQL ( "DELETE FROM {$this->TablePrefix()}userroles" );

		$Adapter = get_class(jf::$DB);
		if ($this->IsMySql())
			jf::SQL ( "ALTER TABLE {$this->TablePrefix()}userroles AUTO_INCREMENT =1 " );
		elseif ($this->IsSQLite())
			jf::SQL ( "delete from sqlite_sequence where name=? ", $this->TablePrefix () . "_userroles" );
		else
			throw new \Exception ("RBAC can not reset table on this type of database: {$Adapter}");
		$this->Assign ( "root", 1 /* root user */ );
		return $res;
	}
}


/**
 * RBACManager class, provides NIST Level 2 Standard Hierarchical Role Based
 * Access Control
 * Has three members, Roles, Users and Permissions for specific operations
 *
 * @author abiusx
 * @version 1.0
 */
class RBACManager extends JModel
{
	function __construct()
	{
		$this->Users = new RBACUserManager ();
		$this->Roles = new RoleManager ();
		$this->Permissions = new PermissionManager ();
	}
	/**
	 *
	 * @var \jf\PermissionManager
	 */
	public $Permissions;
	/**
	 *
	 * @var \jf\RoleManager
	 */
	public $Roles;
	/**
	 *
	 * @var \jf\RBACUserManager
	 */
	public $Users;


	/**
	 * Assign a role to a permission.
	 * Alias for what's in the base class
	 *
	 * @param string|integer $Role
	 *        	path or string title or integer id
	 * @param string|integer $Permission
	 *        	path or string title or integer id
	 * @return boolean
	 */
	function Assign($Role, $Permission)
	{
		if (is_int ( $Permission ))
		{
			$PermissionID = $Permission;
		}
		else
		{
			if (substr ( $Permission, 0, 1 ) == "/")
				$PermissionID = $this->Permissions->PathID ( $Permission );
			else
				$PermissionID = $this->Permissions->TitleID ( $Permission );
		}
		if (is_int ( $Role ))
		{
			$RoleID = $Role;
		}
		else
		{
			if (substr ( $Role, 0, 1 ) == "/")
				$RoleID = $this->Roles->PathID ( $Role );
			else
				$RoleID = $this->Roles->TitleID ( $Role );
		}

		return $this->Roles->Assign ( $RoleID, $PermissionID );
	}

	/**
	 * Prepared statement for check query
	 *
	 * @var BaseDatabaseStatement
	 */
	private $ps_Check = null;

	/**
	 * Checks whether a user has a permission or not.
	 *
	 * @param string|integer $Permission
	 *        	you can provide a path like /some/permission, a title, or the
	 *        	permission ID.
	 *        	in case of ID, don't forget to provide integer (not a string
	 *        	containing a number)
	 * @param string|integer $UserID
	 *        	User ID of a user
	 *
	 * @throws RBACPermissionNotFoundException
	 * @throws RBACUserNotProvidedException
	 * @return boolean
	 */
	function Check($Permission, $UserID = null)
	{
	    if ($UserID === null)
	        throw new \RBACUserNotProvidedException ("\$UserID is a required argument.");

		// convert permission to ID
		if (is_int ( $Permission ))
		{
			$PermissionID = $Permission;
		}
		else
		{
			if (substr ( $Permission, 0, 1 ) == "/")
				$PermissionID = $this->Permissions->PathID ( $Permission );
			else
				$PermissionID = $this->Permissions->TitleID ( $Permission );
		}

		// if invalid, throw exception
		if ($PermissionID === null)
			throw new RBACPermissionNotFoundException ( "The permission '{$Permission}' not found." );

		if ($this->IsSQLite())
		{
			$LastPart="AS Temp ON ( TR.ID = Temp.RoleID)
 							WHERE
 							TUrel.UserID=?
 							AND
 							Temp.ID=?";
		}
		else //mysql
		{
			$LastPart="ON ( TR.ID = TRel.RoleID)
 							WHERE
 							TUrel.UserID=?
 							AND
 							TPdirect.ID=?";
		}
		$Res=jf::SQL ( "SELECT COUNT(*) AS Result
 							FROM
 							{$this->TablePrefix()}userroles AS TUrel

 							JOIN {$this->TablePrefix()}roles AS TRdirect ON (TRdirect.ID=TUrel.RoleID)
 							JOIN {$this->TablePrefix()}roles AS TR ON ( TR.Lft BETWEEN TRdirect.Lft AND TRdirect.Rght)
 							JOIN
	 							(	{$this->TablePrefix()}permissions AS TPdirect
	 							JOIN {$this->TablePrefix()}permissions AS TP ON ( TPdirect.Lft BETWEEN TP.Lft AND TP.Rght)
	 							JOIN {$this->TablePrefix()}rolepermissions AS TRel ON (TP.ID=TRel.PermissionID)
						) $LastPart",
				$UserID, $PermissionID );

		return $Res [0] ['Result'] >= 1;
	}

	/**
	 * Enforce a permission on a user
	 *
	 * @param string|integer $Permission
	 *        	path or title or ID of permission
	 *
	 * @param integer $UserID
	 *
	 * @throws RBACUserNotProvidedException
	 */
	function Enforce($Permission, $UserID = null)
	{
		if ($UserID === null)
	        throw new \RBACUserNotProvidedException ("\$UserID is a required argument.");

		if (! $this->Check($Permission, $UserID)) {
		    header('HTTP/1.1 403 Forbidden');
            die("<strong>Forbidden</strong>: You do not have permission to access this resource.");
		}

		return true;
	}

	/**
	 * Remove all roles, permissions and assignments
	 * mostly used for testing
	 *
	 * @param boolean $Ensure
	 *        	must set or throws error
	 * @return boolean
	 */
	function Reset($Ensure = false)
	{
		if ($Ensure !== true)
		{
			throw new \Exception ("You must pass true to this function, otherwise it won't work.");
			return;
		}
		$res = true;
		$res = $res and $this->Roles->ResetAssignments ( true );
		$res = $res and $this->Roles->Reset ( true );
		$res = $res and $this->Permissions->Reset ( true );
		$res = $res and $this->Users->ResetAssignments ( true );
		return $res;
	}
}

