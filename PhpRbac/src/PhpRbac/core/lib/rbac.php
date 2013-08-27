<?php
require_once __DIR__."/nestedset/base.php";
require_once __DIR__."/nestedset/full.php";

class RbacException extends \Exception
{
}
class RbacRoleNotFoundException extends RbacException
{
}
class RbacPermissionNotFoundException extends RbacException
{
}
class RbacUserNotProvidedException extends RbacException
{
}

class JModel
{
	function tablePrefix()
	{
		return jf::tablePrefix();
	}

	protected function isSQLite()
	{
		$Adapter=get_class(jf::$Db);
		return $Adapter == "PDO" and jf::$Db->getAttribute(PDO::ATTR_DRIVER_NAME)=="sqlite";
	}
	protected function isMySql()
	{
		$Adapter=get_class(jf::$Db);
		return $Adapter == "mysqli" or ($Adapter == "PDO" and jf::$Db->getAttribute(PDO::ATTR_DRIVER_NAME)=="mysql");
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
abstract class BaseRbac extends JModel
{

	function rootId()
	{
		return 1;
	}

	/**
	 * Return type of current instance, e.g roles, permissions
	 *
	 * @return string
	 */
	abstract protected function type();
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
			$ParentID = $this->rootId ();
		return (int)$this->{$this->type ()}->insertChildData ( array ("Title" => $Title, "Description" => $Description ), "ID=?", $ParentID );
	}
	/**
	 * Return count of the entity
	 *
	 * @return integer
	 */
	function Count()
	{
		$Res = jf::SQL ( "SELECT COUNT(*) FROM {$this->tablePrefix()}{$this->type()}" );
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

		$Adapter = get_class(jf::$Db);
		if ($Adapter == "mysqli" or ($Adapter == "PDO" and jf::$Db->getAttribute(PDO::ATTR_DRIVER_NAME)=="mysql")) {
			$GroupConcat="GROUP_CONCAT(parent.Title ORDER BY parent.Lft SEPARATOR '/')";

            $query = jf::SQL ( "SELECT sum(char_length(Title)) FROM " . $this->tablePrefix() . $this->type());
            $query_char_count = $query[0]['sum(char_length(Title))'];
		} elseif ($Adapter == "PDO" and jf::$Db->getAttribute(PDO::ATTR_DRIVER_NAME)=="sqlite") {
			$GroupConcat="GROUP_CONCAT(parent.Title,'/')";

            $query = jf::SQL ( "SELECT sum(length(Title)) FROM " . $this->tablePrefix() . $this->type());
            $query_char_count = $query[0]['sum(length(Title))'];
		} else {
			throw new \Exception ("Unknown Group_Concat on this type of database: {$Adapter}");
		}

		$row_count = jf::SQL ( "SELECT count(*) FROM " . $this->tablePrefix() . $this->type());
		$separator_count = --$row_count[0]['count(*)'];
		$total_char_count = $query_char_count + $separator_count;

		if ((int) $total_char_count >= 1024)
		    throw new Exception ( "Path exceeds character count limit." );

		$res = jf::SQL ( "SELECT node.ID,{$GroupConcat} AS Path
				FROM {$this->tablePrefix()}{$this->type()} AS node,
				{$this->tablePrefix()}{$this->type()} AS parent
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

		$QueryBase = ("SELECT n0.ID  \nFROM {$this->tablePrefix()}{$this->type()} AS n0");
		$QueryCondition = "\nWHERE 	n0.Title=?";

		for($i = 1; $i < count ( $Parts ); ++ $i)
		{
			$j = $i - 1;
			$QueryBase .= "\nJOIN 		{$this->tablePrefix()}{$this->type()} AS n{$i} ON (n{$j}.Lft BETWEEN n{$i}.Lft+1 AND n{$i}.Rght)";
			$QueryCondition .= "\nAND 	n{$i}.Title=?";
			// Forcing middle elements
			$QueryBase .= "\nLEFT JOIN 	{$this->tablePrefix()}{$this->type()} AS nn{$i} ON (nn{$i}.Lft BETWEEN n{$i}.Lft+1 AND n{$j}.Lft-1)";
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
		return $this->{$this->type ()}->GetID ( "Title=?", $Title );
	}
	/**
	 * Return the whole record of a single entry (including Rght and Lft fields)
	 *
	 * @param integer $ID
	 */
	protected function GetRecord($ID)
	{
		$args = func_get_args ();
		return call_user_func_array ( array ($this->{$this->type ()}, "GetRecord" ), $args );
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

        return $this->{$this->type ()}->EditData ( $Data, "ID=?", $ID ) == 1;
	}

	/**
	 * Returns children of an entity
	 *
	 * @return array
	 *
	 */
	function Children($ID)
	{
		return $this->{$this->type ()}->ChildrenConditional ( "ID=?", $ID );
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
		$res = $this->{$this->type ()}->DescendantsConditional(/* absolute depths*/false, "ID=?", $ID );
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
		return $this->{$this->type ()}->DepthConditional ( "ID=?", $ID );
	}

	/**
	 * Returns path of a node
	 *
	 * @param integer $ID
	 * @return string path
	 */
	function Path($ID)
	{
		$res = $this->{$this->type ()}->PathConditional ( "ID=?", $ID );
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
		return $this->{$this->type ()}->ParentNodeConditional ( "ID=?", $ID );
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
		$res = jf::SQL ( "DELETE FROM {$this->tablePrefix()}{$this->type()}" );
		$Adapter = get_class(jf::$Db);
		if ($this->isMySql())
			jf::SQL ( "ALTER TABLE {$this->tablePrefix()}{$this->type()} AUTO_INCREMENT=1 " );
		elseif ($this->isSQLite())
			jf::SQL ( "delete from sqlite_sequence where name=? ", $this->tablePrefix () . "{$this->type()}" );
		else
			throw new \Exception ( "RBAC can not reset table on this type of database: {$Adapter}" );
		$iid = jf::SQL ( "INSERT INTO {$this->tablePrefix()}{$this->type()} (Title,Description,Lft,Rght) VALUES (?,?,?,?)", "root", "root",0,1 );
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
		return jf::SQL ( "INSERT INTO {$this->tablePrefix()}rolepermissions
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
		return jf::SQL ( "DELETE FROM {$this->tablePrefix()}rolepermissions WHERE
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
		$res = jf::SQL ( "DELETE FROM {$this->tablePrefix()}rolepermissions" );

		$Adapter = get_class(jf::$Db);
		if ($this->isMySql())
			jf::SQL ( "ALTER TABLE {$this->tablePrefix()}rolepermissions AUTO_INCREMENT =1 " );
		elseif ($this->isSQLite())
			jf::SQL ( "delete from sqlite_sequence where name=? ", $this->tablePrefix () . "_rolepermissions" );
		else
			throw new \Exception ( "RBAC can not reset table on this type of database: {$Adapter}" );
		$this->Assign ( $this->rootId(), $this->rootId());
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
class PermissionManager extends BaseRbac
{
	/**
	 * Permissions Nested Set
	 *
	 * @var FullNestedSet
	 */
	protected $permissions;
	protected function type()
	{
		return "permissions";
	}
	function __construct()
	{
		$this->permissions = new FullNestedSet ( $this->tablePrefix () . "permissions", "ID", "Lft", "Rght" );
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
		$res = jf::SQL ( "DELETE FROM {$this->tablePrefix()}rolepermissions WHERE
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
				{$this->tablePrefix()}rolepermissions WHERE PermissionID=? ORDER BY RoleID", $Permission );
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
			return jf::SQL ( "SELECT `TP`.* FROM {$this->tablePrefix()}rolepermissions AS `TR`
				RIGHT JOIN {$this->tablePrefix()}roles AS `TP` ON (`TR`.RoleID=`TP`.ID)
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
class RoleManager extends BaseRbac
{
	/**
	 * Roles Nested Set
	 *
	 * @var FullNestedSet
	 */
	protected $roles = null;
	protected function type()
	{
		return "roles";
	}
	function __construct()
	{
		$this->type = "roles";
		$this->roles = new FullNestedSet ( $this->tablePrefix () . "roles", "ID", "Lft", "Rght" );
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
		$r = jf::SQL ( "DELETE FROM {$this->tablePrefix()}rolepermissions WHERE
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
		return jf::SQL ( "DELETE FROM {$this->tablePrefix()}userroles WHERE
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
					FROM {$this->tablePrefix()}rolepermissions AS TRel
					JOIN {$this->tablePrefix()}permissions AS TP ON ( TP.ID= TRel.PermissionID)
					JOIN {$this->tablePrefix()}roles AS TR ON ( TR.ID = TRel.RoleID)
					WHERE TR.Lft BETWEEN
					(SELECT Lft FROM {$this->tablePrefix()}roles WHERE ID=?)
					AND
					(SELECT Rght FROM {$this->tablePrefix()}roles WHERE ID=?)
					/* the above section means any row that is a descendants of our role (if descendant roles have some permission, then our role has it two) */
					AND TP.ID IN (
					SELECT parent.ID
					FROM {$this->tablePrefix()}permissions AS node,
					{$this->tablePrefix()}permissions AS parent
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
			$Res = jf::SQL ( "SELECT PermissionID AS `ID` FROM {$this->tablePrefix()}rolepermissions WHERE RoleID=? ORDER BY PermissionID", $Role );
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
			return jf::SQL ( "SELECT `TP`.* FROM {$this->tablePrefix()}rolepermissions AS `TR`
			RIGHT JOIN {$this->tablePrefix()}permissions AS `TP` ON (`TR`.PermissionID=`TP`.ID)
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
	 * @throws RbacUserNotProvidedException
	 * @return boolean success
	 */
	function HasRole($Role, $UserID = null)
	{
	    if ($UserID === null)
		    throw new \RbacUserNotProvidedException ("\$UserID is a required argument.");

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

		$R = jf::SQL ( "SELECT * FROM {$this->tablePrefix()}userroles AS TUR
			JOIN {$this->tablePrefix()}roles AS TRdirect ON (TRdirect.ID=TUR.RoleID)
			JOIN {$this->tablePrefix()}roles AS TR ON (TR.Lft BETWEEN TRdirect.Lft AND TRdirect.Rght)

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
	 * @throws RbacUserNotProvidedException
	 * @return inserted or existing
	 */
	function Assign($Role, $UserID = null)
	{
	   if ($UserID === null)
		    throw new \RbacUserNotProvidedException ("\$UserID is a required argument.");

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
		$res = jf::SQL ( "INSERT INTO {$this->tablePrefix()}userroles
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
	 * @throws RbacUserNotProvidedException
	 * @return boolean success
	 */
	function Unassign($Role, $UserID = null)
	{
	   if ($UserID === null)
		    throw new \RbacUserNotProvidedException ("\$UserID is a required argument.");

		return jf::SQL ( "DELETE FROM {$this->tablePrefix()}userroles
		WHERE UserID=? AND RoleID=?", $UserID, $Role ) >= 1;
	}

	/**
	 * Returns all roles of a user
	 *
	 * @param integer $UserID
	 *        	Not optional
	 *
	 * @throws RbacUserNotProvidedException
	 * @return array null
	 *
	 */
	function AllRoles($UserID = null)
	{
	   if ($UserID === null)
		    throw new \RbacUserNotProvidedException ("\$UserID is a required argument.");

		return jf::SQL ( "SELECT TR.*
			FROM
			{$this->tablePrefix()}userroles AS `TRel`
			JOIN {$this->tablePrefix()}roles AS `TR` ON
			(`TRel`.RoleID=`TR`.ID)
			WHERE TRel.UserID=?", $UserID );
	}
	/**
	 * Return count of roles for a user
	 *
	 * @param integer $UserID
	 *
	 * @throws RbacUserNotProvidedException
	 * @return integer
	 */
	function RoleCount($UserID = null)
	{
		if ($UserID === null)
		    throw new \RbacUserNotProvidedException ("\$UserID is a required argument.");

		$Res = jf::SQL ( "SELECT COUNT(*) AS Result FROM {$this->tablePrefix()}userroles WHERE UserID=?", $UserID );
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
		$res = jf::SQL ( "DELETE FROM {$this->tablePrefix()}userroles" );

		$Adapter = get_class(jf::$Db);
		if ($this->isMySql())
			jf::SQL ( "ALTER TABLE {$this->tablePrefix()}userroles AUTO_INCREMENT =1 " );
		elseif ($this->isSQLite())
			jf::SQL ( "delete from sqlite_sequence where name=? ", $this->tablePrefix () . "_userroles" );
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
	 * @throws RbacPermissionNotFoundException
	 * @throws RbacUserNotProvidedException
	 * @return boolean
	 */
	function Check($Permission, $UserID = null)
	{
	    if ($UserID === null)
	        throw new \RbacUserNotProvidedException ("\$UserID is a required argument.");

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
			throw new RbacPermissionNotFoundException ( "The permission '{$Permission}' not found." );

		if ($this->isSQLite())
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
 							{$this->tablePrefix()}userroles AS TUrel

 							JOIN {$this->tablePrefix()}roles AS TRdirect ON (TRdirect.ID=TUrel.RoleID)
 							JOIN {$this->tablePrefix()}roles AS TR ON ( TR.Lft BETWEEN TRdirect.Lft AND TRdirect.Rght)
 							JOIN
	 							(	{$this->tablePrefix()}permissions AS TPdirect
	 							JOIN {$this->tablePrefix()}permissions AS TP ON ( TPdirect.Lft BETWEEN TP.Lft AND TP.Rght)
	 							JOIN {$this->tablePrefix()}rolepermissions AS TRel ON (TP.ID=TRel.PermissionID)
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
	 * @throws RbacUserNotProvidedException
	 */
	function Enforce($Permission, $UserID = null)
	{
		if ($UserID === null)
	        throw new \RbacUserNotProvidedException ("\$UserID is a required argument.");

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

