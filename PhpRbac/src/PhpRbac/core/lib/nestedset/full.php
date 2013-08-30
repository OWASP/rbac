<?php
interface ExtendedNestedSet extends NestedSetInterface
{
	//All functions with ConditionString, accept other parameters in variable numbers
	function getID($ConditionString);

	function insertChildData($FieldValueArray=array(),$ConditionString=null);
	function insertSiblingData($FieldValueArray=array(),$ConditionString=null);

	function deleteSubtreeConditional($ConditionString);
	function deleteConditional($ConditionString);


	function childrenConditional($ConditionString);
	function descendantsConditional($AbsoluteDepths=false,$ConditionString);
	function leavesConditional($ConditionString=null);
	function pathConditional($ConditionString);

	function depthConditional($ConditionString);
	function parentNodeConditional($ConditionString);
	function siblingConditional($SiblingDistance=1,$ConditionString);
	/**/
}
/**
 * FullNestedSet Class
 * This class provides a means to implement Hierarchical data in flat SQL tables.
 * Queries extracted from http://dev.mysql.com/tech-resources/articles/hierarchical-data.html
 * Tested and working properly.
 *
 * Usage:
 * have a table with at least 3 INT fields for ID,Left and Right.
 * Create a new instance of this class and pass the name of table and name of the 3 fields above
 */
class FullNestedSet extends BaseNestedSet implements ExtendedNestedSet
{
	/**
    public $AutoRipRightLeft=true;

  	private  function RipRightLeft(&$ResultSet)
    {
        if ($this->AutoRipRightLeft && $ResultSet)
        foreach ($ResultSet as &$v)
        {
            if (isset($v[$this->Left]))
                unset($v[$this->Left]);
            if (isset($v[$this->Right]))
                unset($v[$this->Right]);
        }
    }
    **/
    protected function lock()
    {
    	Jf::sql("LOCK TABLE {$this->table()} WRITE");
    }
    protected function unlock()
    {
    	Jf::sql("UNLOCK TABLES");
    }
    /**
     * Returns the ID of a node based on a SQL conditional string
     * It accepts other params in the PreparedStatements format
     * @param string $Condition the SQL condition, such as Title=?
     * @param string $Rest optional, rest of variables to fill in placeholders of condition string, one variable for each ? in condition
     * @return Integer ID
     */
    function getID($ConditionString,$Rest=null)
    {
        $args=func_get_args();
        array_shift($args);
        $Query="SELECT {$this->id()} AS ID FROM {$this->table()} WHERE $ConditionString LIMIT 1";
        array_unshift($args,$Query);
        $Res=call_user_func_array(("Jf::sql"),$args);
        if ($Res)
        return $Res[0]["ID"];
        else
        	return null;
    }
    /**
     * Returns the record of a node based on a SQL conditional string
     * It accepts other params in the PreparedStatements format
     * @param String $Condition
     * @param string $Rest optional, rest of variables to fill in placeholders of condition string, one variable for each ? in condition
     * @return Array Record
     */
    function getRecord($ConditionString,$Rest=null)
    {
        $args=func_get_args();
        array_shift($args);
        $Query="SELECT * FROM {$this->table()} WHERE $ConditionString";
        array_unshift($args,$Query);
        $Res=call_user_func_array(("Jf::sql"),$args);
        if ($Res)
	        return $Res[0];
        else
        	return null;
    }
    /**
     * Returns the depth of a node in the tree
     * Note: this uses path
     * @param String $ConditionString
     * @param string $Rest optional, rest of variables to fill in placeholders of condition string, one variable for each ? in condition
     * @return Integer Depth from zero upwards
     * @seealso path
     */
    function depthConditional($ConditionString,$Rest=null)
    {
        $Arguments=func_get_args();
        $Path=call_user_func_array(array($this,"pathConditional"),$Arguments);

        return count($Path)-1;
    }
    /**
     * Returns a sibling of the current node
     * Note: You can't find siblings of roots
     * Note: this is a heavy function on nested sets, uses both children (which is quite heavy) and path
     * @param Integer $SiblingDistance from current node (negative or positive)
     * @param string $ConditionString
     * @param string $Rest optional, rest of variables to fill in placeholders of condition string, one variable for each ? in condition
     * @return Array Node on success, null on failure
     */
    function siblingConditional($SiblingDistance=1,$ConditionString,$Rest=null)
    {
        $Arguments=func_get_args();
        $ConditionString=$ConditionString; //prevent warning
        array_shift($Arguments); //Rid $SiblingDistance
        $Parent=call_user_func_array(array($this,"parentNodeConditional"),$Arguments);
        $Siblings=$this->children($Parent[$this->id()]);
        if (!$Siblings) return null;
        $ID=call_user_func_array(array($this,"getID"),$Arguments);
        foreach ($Siblings as &$Sibling)
        {
            if ($Sibling[$this->id()]==$ID) break;
            $n++;
        }
        return $Siblings[$n+$SiblingDistance];
    }
    /**
     * Returns the parent of a node
     * Note: this uses path
     * @param string $ConditionString
     * @param string $Rest optional, rest of variables to fill in placeholders of condition string, one variable for each ? in condition
     * @return Array parentNode (null on failure)
     * @seealso path
     */
    function parentNodeConditional($ConditionString,$Rest=null)
    {
        $Arguments=func_get_args();
        $Path=call_user_func_array(array($this,"pathConditional"),$Arguments);
        if (count($Path)<2) return null;
        else return $Path[count($Path)-2];
    }
	/**
     * Deletes a node and shifts the children up
     * Note: use a condition to support only 1 row, LIMIT 1 used.
     * @param String $ConditionString
     * @param string $Rest optional, rest of variables to fill in placeholders of condition string, one variable for each ? in condition
     * @return boolean
     */
    function deleteConditional($ConditionString,$Rest=null)
    {
    	$this->lock();
    	$Arguments=func_get_args();
        array_shift($Arguments);
        $Query="SELECT {$this->left()} AS `Left`,{$this->right()} AS `Right`
			FROM {$this->table()}
			WHERE $ConditionString LIMIT 1";

        array_unshift($Arguments,$Query);
        $Info=call_user_func_array("Jf::sql",$Arguments);
        if (!$Info)
        {
        	$this->unlock();
        	return false;
        }
        $Info=$Info[0];

        $count=Jf::sql("DELETE FROM {$this->table()} WHERE {$this->left()} = ?",$Info["Left"]);

        Jf::sql("UPDATE {$this->table()} SET {$this->right()} = {$this->right()} - 1, {$this->left()} = {$this->left()} - 1 WHERE {$this->left()} BETWEEN ? AND ?",$Info["Left"],$Info["Right"]);
        Jf::sql("UPDATE {$this->table()} SET {$this->right()} = {$this->right()} - 2 WHERE {$this->right()} > ?",$Info["Right"]);
        Jf::sql("UPDATE {$this->table()} SET {$this->left()} = {$this->left()} - 2 WHERE {$this->left()} > ?",$Info["Right"]);
        $this->unlock();
        return $count==1;
    }
    /**
     * Deletes a node and all its descendants
     *
     * @param String $ConditionString
     * @param string $Rest optional, rest of variables to fill in placeholders of condition string, one variable for each ? in condition
     */
    function deleteSubtreeConditional($ConditionString,$Rest=null)
    {
		$this->lock();
    	$Arguments=func_get_args();
        array_shift($Arguments);
        $Query="SELECT {$this->left()} AS `Left`,{$this->right()} AS `Right` ,{$this->right()}-{$this->left()}+ 1 AS Width
			FROM {$this->table()}
			WHERE $ConditionString";

        array_unshift($Arguments,$Query);
        $Info=call_user_func_array("Jf::sql",$Arguments);

        $Info=$Info[0];

        $count=Jf::sql("
            DELETE FROM {$this->table()} WHERE {$this->left()} BETWEEN ? AND ?
        ",$Info["Left"],$Info["Right"]);

        Jf::sql("
            UPDATE {$this->table()} SET {$this->right()} = {$this->right()} - ? WHERE {$this->right()} > ?
        ",$Info["Width"],$Info["Right"]);
        Jf::sql("
            UPDATE {$this->table()} SET {$this->left()} = {$this->left()} - ? WHERE {$this->left()} > ?
        ",$Info["Width"],$Info["Right"]);
        $this->unlock();
        return $count>=1;
    }
    /**
     * Returns all descendants of a node
     * Note: use only a sinlge condition here
     * @param boolean $AbsoluteDepths to return Depth of sub-tree from zero or absolutely from the whole tree
     * @param string $Condition
     * @param string $Rest optional, rest of variables to fill in placeholders of condition string, one variable for each ? in condition
	 * @return Rowset including Depth field
	 * @seealso children
     */
    function descendantsConditional($AbsoluteDepths=false,$ConditionString,$Rest=null)
    {
        if (!$AbsoluteDepths)
            $DepthConcat="- (sub_tree.innerDepth )";
        $Arguments=func_get_args();
        array_shift($Arguments);
        array_shift($Arguments); //second argument, $AbsoluteDepths
        $Query="
            SELECT node.*, (COUNT(parent.{$this->id()})-1 $DepthConcat) AS Depth
            FROM {$this->table()} AS node,
            	{$this->table()} AS parent,
            	{$this->table()} AS sub_parent,
            	(
            		SELECT node.{$this->id()}, (COUNT(parent.{$this->id()}) - 1) AS innerDepth
            		FROM {$this->table()} AS node,
            		{$this->table()} AS parent
            		WHERE node.{$this->left()} BETWEEN parent.{$this->left()} AND parent.{$this->right()}
            		AND (node.$ConditionString)
            		GROUP BY node.{$this->id()}
            		ORDER BY node.{$this->left()}
            	) AS sub_tree
            WHERE node.{$this->left()} BETWEEN parent.{$this->left()} AND parent.{$this->right()}
            	AND node.{$this->left()} BETWEEN sub_parent.{$this->left()} AND sub_parent.{$this->right()}
            	AND sub_parent.{$this->id()} = sub_tree.{$this->id()}
            GROUP BY node.{$this->id()}
            HAVING Depth > 0
            ORDER BY node.{$this->left()}";

        array_unshift($Arguments,$Query);
        $Res=call_user_func_array("Jf::sql",$Arguments);

        return $Res;
    }
    /**
     * Returns immediate children of a node
     * Note: this function performs the same as descendants but only returns results with Depth=1
     * Note: use only a sinlge condition here
     * @param string $ConditionString
     * @param string $Rest optional, rest of variables to fill in placeholders of condition string, one variable for each ? in condition
     * @return Rowset not including Depth
     * @seealso descendants
     */
    function childrenConditional($ConditionString,$Rest=null)
    {
        $Arguments=func_get_args();
        array_shift($Arguments);
        $Query="
            SELECT node.*, (COUNT(parent.{$this->id()})-1 - (sub_tree.innerDepth )) AS Depth
            FROM {$this->table()} AS node,
            	{$this->table()} AS parent,
            	{$this->table()} AS sub_parent,
           	(
            		SELECT node.{$this->id()}, (COUNT(parent.{$this->id()}) - 1) AS innerDepth
            		FROM {$this->table()} AS node,
            		{$this->table()} AS parent
            		WHERE node.{$this->left()} BETWEEN parent.{$this->left()} AND parent.{$this->right()}
            		AND (node.$ConditionString)
            		GROUP BY node.{$this->id()}
            		ORDER BY node.{$this->left()}
            ) AS sub_tree
            WHERE node.{$this->left()} BETWEEN parent.{$this->left()} AND parent.{$this->right()}
            	AND node.{$this->left()} BETWEEN sub_parent.{$this->left()} AND sub_parent.{$this->right()}
            	AND sub_parent.{$this->id()} = sub_tree.{$this->id()}
            GROUP BY node.{$this->id()}
            HAVING Depth = 1
            ORDER BY node.{$this->left()}";

        array_unshift($Arguments,$Query);
        $Res=call_user_func_array("Jf::sql",$Arguments);
        if ($Res)
        foreach ($Res as &$v)
            unset($v["Depth"]);
        return $Res;
    }
	/**
     * Returns the path to a node, including the node
     * Note: use a single condition, or supply "node." before condition fields.
     * @param string $ConditionString
     * @param string $Rest optional, rest of variables to fill in placeholders of condition string, one variable for each ? in condition
     * @return Rowset nodes in path
     */
    function pathConditional($ConditionString,$Rest=null)
    {
        $Arguments=func_get_args();
        array_shift($Arguments);
        $Query="
            SELECT parent.*
            FROM {$this->table()} AS node,
            {$this->table()} AS parent
            WHERE node.{$this->left()} BETWEEN parent.{$this->left()} AND parent.{$this->right()}
            AND ( node.$ConditionString )
            ORDER BY parent.{$this->left()}";

        array_unshift($Arguments,$Query);
        $Res=call_user_func_array("Jf::sql",$Arguments);
        return $Res;
    }

    /**
     * Finds all leaves of a parent
     *	Note: if you don' specify $PID, There would be one less AND in the SQL Query
     * @param String $ConditionString
     * @param string $Rest optional, rest of variables to fill in placeholders of condition string, one variable for each ? in condition
     * @return Rowset Leaves
     */
    function leavesConditional($ConditionString=null,$Rest=null)
    {
        if ($ConditionString)
        {
            $Arguments=func_get_args();
            array_shift($Arguments);
            if ($ConditionString) $ConditionString="WHERE $ConditionString";

            $Query="SELECT *
                FROM {$this->table()}
                WHERE {$this->right()} = {$this->left()} + 1
            	AND {$this->left()} BETWEEN
                (SELECT {$this->left()} FROM {$this->table()} $ConditionString)
                	AND
                (SELECT {$this->right()} FROM {$this->table()} $ConditionString)";

            $Arguments=array_merge($Arguments,$Arguments);
            array_unshift($Arguments,$Query);
            $Res=call_user_func_array("Jf::sql",$Arguments);
        }
        else
        $Res=Jf::sql("SELECT *
            FROM {$this->table()}
            WHERE {$this->right()} = {$this->left()} + 1");
        return $Res;
    }
    /**
     * Adds a sibling after a node
     *
     * @param array $FieldValueArray Pairs of Key/Value as Field/Value in the table
     * @param string $ConditionString
     * @param string $Rest optional, rest of variables to fill in placeholders of condition string
     * @return Integer SiblingID
     */
    function insertSiblingData($FieldValueArray=array(),$ConditionString=null,$Rest=null)
    {
		$this->lock();
    	//Find the Sibling
        $Arguments=func_get_args();
        array_shift($Arguments); //first argument, the array
        array_shift($Arguments);
        if ($ConditionString) $ConditionString="WHERE $ConditionString";
        $Query="SELECT {$this->right()} AS `Right`".
        	" FROM {$this->table()} $ConditionString";

        array_unshift($Arguments,$Query);
        $Sibl=call_user_func_array("Jf::sql",$Arguments);

        $Sibl=$Sibl[0];
        if ($Sibl==null)
        {
            $Sibl["Left"]=$Sibl["Right"]=0;
        }
        Jf::sql("UPDATE {$this->table()} SET {$this->right()} = {$this->right()} + 2 WHERE {$this->right()} > ?",$Sibl["Right"]);
        Jf::sql("UPDATE {$this->table()} SET {$this->left()} = {$this->left()} + 2 WHERE {$this->left()} > ?",$Sibl["Right"]);

        $FieldsString=$ValuesString="";
        $Values=array();
        if ($FieldValueArray)
        foreach($FieldValueArray as $k=>$v)
        {
            $FieldsString.=",";
            $FieldsString.="`".$k."`";
            $ValuesString.=",?";
            $Values[]=$v;
        }

        $Query= "INSERT INTO {$this->table()} ({$this->left()},{$this->right()} $FieldsString) ".
        	"VALUES(?,? $ValuesString)";
        array_unshift($Values,$Sibl["Right"]+2);
        array_unshift($Values,$Sibl["Right"]+1);
        array_unshift($Values,$Query);

        $Res=call_user_func_array("Jf::sql",$Values);
		$this->unlock();
        return $Res;
    }
    /**
     * Adds a child to the beginning of a node's children
     *
     * @param Array $FieldValueArray key-paired field-values to insert
     * @param string $ConditionString of the parent node
     * @param string $Rest optional, rest of variables to fill in placeholders of condition string, one variable for each ? in condition
     * @return Integer ChildID
     */
    function insertChildData($FieldValueArray=array(),$ConditionString=null,$Rest=null)
    {
		$this->lock();
    	//Find the Sibling
        $Arguments=func_get_args();
        array_shift($Arguments); //first argument, the array
        array_shift($Arguments);
        if ($ConditionString) $ConditionString="WHERE $ConditionString";
        $Query="SELECT {$this->right()} AS `Right`, {$this->left()} AS `Left`".
        	" FROM {$this->table()} $ConditionString";
        array_unshift($Arguments,$Query);
        $Parent=call_user_func_array("Jf::sql",$Arguments);

        $Parent=$Parent[0];
        if ($Parent==null)
        {
            $Parent["Left"]=$Parent["Right"]=0;
        }
        Jf::sql("UPDATE {$this->table()} SET {$this->right()} = {$this->right()} + 2 WHERE {$this->right()} >= ?",$Parent["Right"]);
        Jf::sql("UPDATE {$this->table()} SET {$this->left()} = {$this->left()} + 2 WHERE {$this->left()} > ?",$Parent["Right"]);

        $FieldsString=$ValuesString="";
        $Values=array();
        if ($FieldValueArray)
        foreach($FieldValueArray as $k=>$v)
        {
            $FieldsString.=",";
            $FieldsString.="`".$k."`";
            $ValuesString.=",?";
            $Values[]=$v;
        }
        $Query= "INSERT INTO {$this->table()} ({$this->left()},{$this->right()} $FieldsString) ".
        	"VALUES(?,? $ValuesString)";
        array_unshift($Values,$Parent["Right"]+1);
        array_unshift($Values,$Parent["Right"]);
        array_unshift($Values,$Query);
        $Res=call_user_func_array("Jf::sql",$Values);
        $this->unlock();
        return $Res;
    }
    /**
     * Edits a node
     *
     * @param Array $FieldValueArray Pairs of Key/Value as Field/Value in the table to edit
     * @param string $ConditionString
     * @param string $Rest optional, rest of variables to fill in placeholders of condition string, one variable for each ? in condition
     * @return Integer SiblingID
     */
    function editData($FieldValueArray=array(),$ConditionString=null,$Rest=null)
    {
        //Find the Sibling
        $Arguments=func_get_args();
        array_shift($Arguments); //first argument, the array
        array_shift($Arguments);
        if ($ConditionString) $ConditionString="WHERE $ConditionString";



        $FieldsString="";
        $Values=array();
        if ($FieldValueArray)
        foreach($FieldValueArray as $k=>$v)
        {
            if ($FieldsString!="") $FieldsString.=",";
            $FieldsString.="`".$k."`=?";
            $Values[]=$v;
        }
        $Query="UPDATE {$this->table()} SET $FieldsString $ConditionString";

        array_unshift($Values,$Query);
        $Arguments=array_merge($Values,$Arguments);

        return call_user_func_array("Jf::sql",$Arguments);
    }

}

?>