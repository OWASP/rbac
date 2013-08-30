<?php
interface NestedSetInterface
{
    public function insertChild($PID=0);
    public function insertSibling($ID=0);
    
    function deleteSubtree($ID);
    function delete($ID);
    
    //function Move($ID,$NewPID);
    //function Copy($ID,$NewPID);
    
    function fullTree();
    function children($ID);
    function descendants($ID,$AbsoluteDepths=false);
    function leaves($PID=null);
    function path($ID);
    
    function depth($ID);
    function parentNode($ID);
    function sibling($ID,$SiblingDistance=1);
}

/**
 * BaseNestedSet Class
 * This class provides a means to implement Hierarchical data in flat SQL tables.
 * Queries extracted from http://dev.mysql.com/tech-resources/articles/hierarchical-data.html
 *
 * Tested and working properly
 * 
 * Usage:
 * have a table with at least 3 INT fields for ID,Left and Right.
 * Create a new instance of this class and pass the name of table and name of the 3 fields above
  */
//FIXME: many of these operations should be done in a transaction
class BaseNestedSet implements NestedSetInterface
{
    function __construct($Table,$IDField="ID",$LeftField="Left",$RightField="Right")
    {
        $this->assign($Table,$IDField,$LeftField,$RightField);
    }
    private $Table;
    private $Left,$Right;
    private $ID;
    protected function id()
    {
    	return $this->ID;
    }
    protected function table()
    {
    	return $this->Table;
    }
    protected function left()
    {
    	return $this->Left;
    }
    protected function right()
    {
    	return $this->Right;
    }
    /**
     * Assigns fields of the table
     *
     * @param String $Table
     * @param String $ID
     * @param String $Left
     * @param String $Right
     */
    protected function assign($Table,$ID,$Left,$Right)
    {
        $this->Table=$Table;
        $this->ID=$ID;
        $this->Left=$Left;
        $this->Right=$Right;
    }
    
    /**
     * Returns number of descendants 
     *
     * @param Integer $ID
     * @return Integer Count
     */
    function descendantCount($ID)
    {
        $Res=Jf::sql("SELECT ({$this->right()}-{$this->left()}-1)/2 AS `Count` FROM
        {$this->table()} WHERE {$this->id()}=?",$ID);
        return sprintf("%d",$Res[0]["Count"])*1;
    }
    
    /**
     * Returns the depth of a node in the tree
     * Note: this uses path
     * @param Integer $ID
     * @return Integer Depth from zero upwards
     * @seealso path
     */
    function depth($ID)
    {
        return count($this->path($ID))-1;
    }
    /**
     * Returns a sibling of the current node
     * Note: You can't find siblings of roots 
     * Note: this is a heavy function on nested sets, uses both children (which is quite heavy) and path
     * @param Integer $ID
     * @param Integer $SiblingDistance from current node (negative or positive)
     * @return Array Node on success, null on failure 
     */
    function sibling($ID,$SiblingDistance=1)
    {
        $Parent=$this->parentNode($ID);
        $Siblings=$this->children($Parent[$this->id()]);
        if (!$Siblings) return null;
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
     * @param Integer $ID
     * @return Array parentNode (null on failure)
     * @seealso path
     */
    function parentNode($ID)
    {
        $Path=$this->path($ID);
        if (count($Path)<2) return null;
        else return $Path[count($Path)-2];        
    }
	/**
     * Deletes a node and shifts the children up
     *
     * @param Integer $ID
     */
    function delete($ID)
    {
        $Info=Jf::sql("SELECT {$this->left()} AS `Left`,{$this->right()} AS `Right` 
			FROM {$this->table()}
			WHERE {$this->id()} = ?;
        ",$ID);
        $Info=$Info[0];

        $count=Jf::sql("DELETE FROM {$this->table()} WHERE {$this->left()} = ?",$Info["Left"]);


        Jf::sql("UPDATE {$this->table()} SET {$this->right()} = {$this->right()} - 1, `".
            $this->left."` = {$this->left()} - 1 WHERE {$this->left()} BETWEEN ? AND ?",$Info["Left"],$Info["Right"]);
        Jf::sql("UPDATE {$this->table()} SET {$this->right()} = {$this->right()} - 2 WHERE `".
            $this->Right."` > ?",$Info["Right"]);
        Jf::sql("UPDATE {$this->table()} SET {$this->left()} = {$this->left()} - 2 WHERE `".
            $this->left."` > ?",$Info["Right"]);
        return $count;
    }
    /**
     * Deletes a node and all its descendants
     *
     * @param Integer $ID
     */
    function deleteSubtree($ID)
    {
        $Info=Jf::sql("SELECT {$this->left()} AS `Left`,{$this->right()} AS `Right` ,{$this->right()}-{$this->left()}+ 1 AS Width
			FROM {$this->table()}
			WHERE {$this->id()} = ?;
        ",$ID);
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
        return $count;

    }
    /**
     * Returns all descendants of a node
     *
     * @param Integer $ID
     * @param Boolean $AbsoluteDepths to return Depth of sub-tree from zero or absolutely from the whole tree  
	 * @return Rowset including Depth field
	 * @seealso children
     */
    function descendants($ID,$AbsoluteDepths=false)
    {
           if (!$AbsoluteDepths)
               $DepthConcat="- (sub_tree.depth )";
        $Res=Jf::sql("
            SELECT node.*, (COUNT(parent.{$this->id()})-1 $DepthConcat ) AS Depth
            FROM {$this->table()} AS node,
            	{$this->table()} AS parent,
            	{$this->table()} AS sub_parent,
            	(
            		SELECT node.{$this->id()}, (COUNT(parent.{$this->id()}) - 1) AS depth
            		FROM {$this->table()} AS node,
            		{$this->table()} AS parent
            		WHERE node.{$this->left()} BETWEEN parent.{$this->left()} AND parent.{$this->right()}
            		AND node.{$this->id()} = ?
            		GROUP BY node.{$this->id()}
            		ORDER BY node.{$this->left()}
            	) AS sub_tree
            WHERE node.{$this->left()} BETWEEN parent.{$this->left()} AND parent.{$this->right()}
            	AND node.{$this->left()} BETWEEN sub_parent.{$this->left()} AND sub_parent.{$this->right()}
            	AND sub_parent.{$this->id()} = sub_tree.{$this->id()}
            GROUP BY node.{$this->id()}
            HAVING Depth > 0
            ORDER BY node.{$this->left()}",$ID);
        return $Res;
    }
    /**
     * Returns immediate children of a node
     * Note: this function performs the same as descendants but only returns results with Depth=1
     * @param Integer $ID
     * @return Rowset not including Depth
     * @seealso descendants
     */
    function children($ID)
    {
        $Res=Jf::sql("
            SELECT node.*, (COUNT(parent.{$this->id()})-1 - (sub_tree.depth )) AS Depth
            FROM {$this->table()} AS node,
            	{$this->table()} AS parent,
            	{$this->table()} AS sub_parent,
           	(
            		SELECT node.{$this->id()}, (COUNT(parent.{$this->id()}) - 1) AS depth
            		FROM {$this->table()} AS node,
            		{$this->table()} AS parent
            		WHERE node.{$this->left()} BETWEEN parent.{$this->left()} AND parent.{$this->right()}
            		AND node.{$this->id()} = ?
            		GROUP BY node.{$this->id()}
            		ORDER BY node.{$this->left()}
            ) AS sub_tree
            WHERE node.{$this->left()} BETWEEN parent.{$this->left()} AND parent.{$this->right()}
            	AND node.{$this->left()} BETWEEN sub_parent.{$this->left()} AND sub_parent.{$this->right()}
            	AND sub_parent.{$this->id()} = sub_tree.{$this->id()}
            GROUP BY node.{$this->id()}
            HAVING Depth = 1
            ORDER BY node.{$this->left()};
        ",$ID);
       if ($Res)
       foreach ($Res as &$v)
           unset($v["Depth"]);
        return $Res;
    }    
	/**
     * Returns the path to a node, including the node
     *
     * @param Integer $ID
     * @return Rowset nodes in path
     */
    function path($ID)
    {
        $Res=Jf::sql("
            SELECT parent.* 
            FROM {$this->table()} AS node,
            ".$this->table." AS parent
            WHERE node.{$this->left()} BETWEEN parent.{$this->left()} AND parent.{$this->right()}
            AND node.{$this->id()} = ?
            ORDER BY parent.{$this->left()}",$ID);
        return $Res;
    }
    
    /**
     * Finds all leaves of a parent
     *	Note: if you don' specify $PID, There would be one less AND in the SQL Query
     * @param Integer $PID
     * @return Rowset Leaves
     */
    function leaves($PID=null)
    {
        if ($PID) 
        $Res=Jf::sql("SELECT *
            FROM {$this->table()}
            WHERE {$this->right()} = {$this->left()} + 1 
        	AND {$this->left()} BETWEEN 
            (SELECT {$this->left()} FROM {$this->table()} WHERE {$this->id()}=?)
            	AND 
            (SELECT {$this->right()} FROM {$this->table()} WHERE {$this->id()}=?)",$PID,$PID);
        else
        $Res=Jf::sql("SELECT *
            FROM {$this->table()}
            WHERE {$this->right()} = {$this->left()} + 1");
        return $Res;
    }
    /**
     * Adds a sibling after a node
     *
     * @param Integer $ID
     * @return Integer SiblingID
     */
    function insertSibling($ID=0)
    {
//        $this->DB->AutoQuery("LOCK TABLE {$this->table()} WRITE;");
        //Find the Sibling
        $Sibl=Jf::sql("SELECT {$this->right()} AS `Right`".
        	" FROM {$this->table()} WHERE {$this->id()} = ?",$ID);
        $Sibl=$Sibl[0];
        if ($Sibl==null)
        {
            $Sibl["Right"]=0;
        }
        Jf::sql("UPDATE {$this->table()} SET {$this->right()} = {$this->right()} + 2 WHERE {$this->right()} > ?",$Sibl["Right"]);
        Jf::sql("UPDATE {$this->table()} SET {$this->left()} = {$this->left()} + 2 WHERE {$this->left()} > ?",$Sibl["Right"]);
        $Res= Jf::sql("INSERT INTO {$this->table()} ({$this->left()},{$this->right()}) ".
        	"VALUES(?,?)",$Sibl["Right"]+1,$Sibl["Right"]+2);
//        $this->DB->AutoQuery("UNLOCK TABLES");
        return $Res;
    }
    /**
     * Adds a child to the beginning of a node's children
     *
     * @param Integer $PID
     * @return Integer ChildID
     */
    function insertChild($PID=0)
    {
        //Find the Sibling
        $Sibl=Jf::sql("SELECT {$this->left()} AS `Left`".
        	" FROM {$this->table()} WHERE {$this->id()} = ?",$PID);
        $Sibl=$Sibl[0];
        if ($Sibl==null)
        {
            $Sibl["Left"]=0;
        }
        Jf::sql("UPDATE {$this->table()} SET {$this->right()} = {$this->right()} + 2 WHERE {$this->right()} > ?",$Sibl["Left"]);
        Jf::sql("UPDATE {$this->table()} SET {$this->left()} = {$this->left()} + 2 WHERE {$this->left()} > ?",$Sibl["Left"]);
        $Res=Jf::sql("INSERT INTO {$this->table()} ({$this->left()},{$this->right()}) ".
        	"VALUES(?,?)",$Sibl["Left"]+1,$Sibl["Left"]+2);
        return $Res;
    }
    /**
     * Retrives the full tree including Depth field.
     *
     * @return 2DArray Rowset
     */
    function fullTree()
    {
        $Res=Jf::sql("SELECT node.*, (COUNT(parent.{$this->id()}) - 1) AS Depth
            FROM {$this->table()} AS node,
            {$this->table()} AS parent
            WHERE node.{$this->left()} BETWEEN parent.{$this->left()} AND parent.{$this->right()}
            GROUP BY node.{$this->id()}
            ORDER BY node.{$this->left()}");
        return $Res;
    }
    /**
     * This function converts a 2D array with Depth fields into a multidimensional tree in an associative array
     *
     * @param Array $Result
     * @return Array Tree
     */
    #FIXME: think how to implement this!
    /**
    function Result2Tree($Result)
    {
        $out=array();
        $stack=array();
        $cur=&$out;
        foreach($Result as $R)
        {
            if ($cur[$LastKey]['Depth']==$R['Depth'])
            {
                echo "adding 0 ".$R['Title'].BR;
                $cur[$R[$this->id()]]=$R;
                $LastKey=$R[$this->id()];
            }
            elseif ($cur[$LastKey]['Depth']<$R['Depth'])
            {
                echo "adding 1 ".$R['Title'].BR;
                array_push($stack,$cur);
                $cur=&$cur[$LastKey];
                $cur[$R[$this->id()]]=$R;
                $LastKey=$R[$this->id()];
            }
            elseif ($cur[$LastKey]['Depth']>$R['Depth'])
            {
                echo "adding 2 ".$R['Title'].BR;
                $cur=array_pop($stack);
                $cur[$R[$this->id()]]=$R;
                $LastKey=$R[$this->id()];
            }
            
        }
        return $out;
    }
	/**/
}

?>