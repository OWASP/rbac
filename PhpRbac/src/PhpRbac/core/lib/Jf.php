<?php
require_once __DIR__."/rbac.php";

class Jf
{
	/**
	 * @var RbacManager
	 */
	public static $Rbac;

	public static $Db = null;

	public static $TABLE_PREFIX;

	private static $groupConcatLimitChanged = false;

	public static function setTablePrefix($tablePrefix)
	{
	    self::$TABLE_PREFIX = $tablePrefix;
	}

	public static function tablePrefix()
	{
	    return self::$TABLE_PREFIX;
	}

	/**
	 * The Jf::sql function. The behavior of this function is as follows:
	 *
	 * * On queries with no parameters, it should use query function and fetch all results (no prepared statement)
	 * * On queries with parameters, parameters are provided as question marks (?) and then additional function arguments will be
	 * 	 bound to question marks.
	 * * On SELECT, it will return 2D array of results or NULL if no result.
	 * * On DELETE, UPDATE it returns affected rows
	 * * On INSERT, if auto-increment is available last insert id, otherwise affected rows
	 *
	 * @todo currently sqlite always returns sequence number for lastInsertId, so there's no way of knowing if insert worked instead of execute result. all instances of ==1 replaced with >=1 to check for insert
	 *
	 * @param string $Query
	 * @throws Exception
	 * @return array|integer|null
	 */
	static function sql($Query)
	{
		$args = func_get_args ();
		if (get_class ( self::$Db ) == "PDO")
			return call_user_func_array ( "self::sqlPdo", $args );
		else
			if (get_class ( self::$Db ) == "mysqli")
				return call_user_func_array ( "self::sqlMysqli", $args );
			else
				throw new Exception ( "Unknown database interface type." );
	}

	static function sqlPdo($Query)
	{
	    $debug_backtrace = debug_backtrace();

	    if((isset($debug_backtrace[3])) && ($debug_backtrace[3]['function'] == 'pathId')) {
    	    if (!self::$groupConcatLimitChanged) {
    	        $success = self::$Db->query ("SET SESSION group_concat_max_len = 1000000");

    	        if ($success) {
    	            self::$groupConcatLimitChanged = true;
    	        }
    	    }
	    }

		$args = func_get_args ();

		if (count ( $args ) == 1)
		{
			$result = self::$Db->query ( $Query );
			if ($result===false)
				return null;
			$res=$result->fetchAll ( PDO::FETCH_ASSOC );
			if ($res===array())
				return null;
			return $res;
		}
		else
		{
			if (! $stmt = self::$Db->prepare ( $Query ))
			{
				return false;
			}
			array_shift ( $args ); // remove $Query from args
			$i = 0;
			foreach ( $args as &$v )
				$stmt->bindValue ( ++ $i, $v );

			$success=$stmt->execute ();

			$type = substr ( trim ( strtoupper ( $Query ) ), 0, 6 );
			if ($type == "INSERT")
			{
				if (!$success)
					return null;
				$res = self::$Db->lastInsertId ();
				if ($res == 0)
					return $stmt->rowCount ();
				return $res;
			}
			elseif ($type == "DELETE" or $type == "UPDATE" or $type == "REPLACE")
				return $stmt->rowCount();
			elseif ($type == "SELECT")
			{
				$res=$stmt->fetchAll ( PDO::FETCH_ASSOC );
				if ($res===array())
					return null;
				else
					return $res;
			}
		}
	}

	static function sqlMysqli( $Query)
	{
	    $debug_backtrace = debug_backtrace();

	    if((isset($debug_backtrace[3])) && ($debug_backtrace[3]['function'] == 'pathId')) {
    	    if (!self::$groupConcatLimitChanged) {
    	        $success = self::$Db->query ("SET SESSION group_concat_max_len = 1000000");

    	        if ($success) {
    	            self::$groupConcatLimitChanged = true;
    	        }
    	    }
	    }

		$args = func_get_args ();
		if (count ( $args ) == 1)
		{
			$result = self::$Db->query ( $Query );
			if ($result===true)
				return true;
			if ($result && $result->num_rows)
			{
				$out = array ();
				while ( null != ($r = $result->fetch_array ( MYSQLI_ASSOC )) )
					$out [] = $r;
				return $out;
			}
			return null;
		}
		else
		{
			if (! $preparedStatement = self::$Db->prepare ( $Query ))
				trigger_error ( "Unable to prepare statement: {$Query}, reason: ".self::$Db->error );
			array_shift ( $args ); // remove $Query from args
			$a = array ();
			foreach ( $args as $k => &$v )
				$a [$k] = &$v;
			$types = str_repeat ( "s", count ( $args ) ); // all params are
			                                              // strings, works well on
			                                              // MySQL
			                                              // and SQLite
			array_unshift ( $a, $types );
			call_user_func_array ( array ($preparedStatement, 'bind_param' ), $a );
			$preparedStatement->execute ();

			$type = substr ( trim ( strtoupper ( $Query ) ), 0, 6 );
			if ($type == "INSERT")
			{
				$res = self::$Db->insert_id;
				if ($res == 0)
					return self::$Db->affected_rows;
				return $res;
			}
			elseif ($type == "DELETE" or $type == "UPDATE" or $type == "REPLAC")
				return self::$Db->affected_rows;
			elseif ($type == "SELECT")
			{
				// fetching all results in a 2D array
				$metadata = $preparedStatement->result_metadata ();
				$out = array ();
				$fields = array ();
				if (! $metadata)
					return null;
				while ( null != ($field = $metadata->fetch_field ()) )
					$fields [] = &$out [$field->name];
				call_user_func_array ( array ($preparedStatement, "bind_result" ), $fields );
				$output = array ();
				$count = 0;
				while ( $preparedStatement->fetch () )
				{
					foreach ( $out as $k => $v )
						$output [$count] [$k] = $v;
					$count ++;
				}
				$preparedStatement->free_result ();
				return ($count == 0) ? null : $output;
			}
			else
				return null;
		}
	}

	static function time()
	{
		return time();
	}
}

Jf::setTablePrefix($tablePrefix);
Jf::$Rbac=new RbacManager();
require_once __DIR__."/../setup.php";