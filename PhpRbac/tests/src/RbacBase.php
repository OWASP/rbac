<?php

use PhpRbac\Rbac;

/**
 * @file
 * Unit Tests for PhpRbac PSR Wrapper
 *
 * @defgroup phprbac_unit_test_wrapper_base Unit Tests for BaseRbac Functionality
 * @ingroup phprbac_unit_tests
 * @{
 * Documentation for all Unit Tests regarding BaseRbac functionality.
 */

class RbacBase extends \RbacSetup
{
    /*
     * Tests for $this->Instance()->add()
     */

    public function testAddNullTitle()
    {
        $dataSet = $this->getConnection()->createDataSet();

        $type_id = $this->Instance()->add(null, $this->type() . ' Description');

        $this->assertSame(0, $type_id);
    }

    public function testAddNullDescription()
    {
        $dataSet = $this->getConnection()->createDataSet();

        $type_id = $this->Instance()->add($this->type() . '_title', null);

        $this->assertSame(0, $type_id);
    }

    public function testAddSequential()
    {
        $dataSet = $this->getConnection()->createDataSet();

        $this->Instance()->add($this->type() . '_title_1', $this->type() . ' Description 1');
        $this->Instance()->add($this->type() . '_title_2', $this->type() . ' Description 2');
        $this->Instance()->add($this->type() . '_title_3', $this->type() . ' Description 3');

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type()
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_add_' . $this->type() . '_sequential.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddHierarchy()
    {
        $dataSet = $this->getConnection()->createDataSet();

        $type_1 = $this->Instance()->add('blog', 'Define ' . $this->type() . ' for the Blog');
        $this->Instance()->add($this->type() . '_title_1', $this->type() . ' Description 1', $type_1);
        $this->Instance()->add($this->type() . '_title_2', $this->type() . ' Description 2', $type_1);
        $this->Instance()->add($this->type() . '_title_3', $this->type() . ' Description 3', $type_1);

        $type_2 = $this->Instance()->add('forum', 'Define ' . $this->type() . ' for the Forums');
        $this->Instance()->add($this->type() . '_title_1', $this->type() . ' Description 1', $type_2);
        $this->Instance()->add($this->type() . '_title_2', $this->type() . ' Description 2', $type_2);
        $this->Instance()->add($this->type() . '_title_3', $this->type() . ' Description 3', $type_2);

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type()
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_add_' . $this->type() . '_hierarchy.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    /*
     * Tests for $this->Instance()->count()
     */

    public function testCount()
    {
        $this->Instance()->add($this->type() . '_title_1', $this->type() . ' Description 1');
        $this->Instance()->add($this->type() . '_title_2', $this->type() . ' Description 2');
        $this->Instance()->add($this->type() . '_title_3', $this->type() . ' Description 3');

        $type_count = $this->Instance()->count();

        $this->assertSame(4, $type_count);
    }

    /*
     * Tests for $this->Instance()->returnId()
     */

    public function testReturnIdTitle()
    {
        $this->Instance()->addPath('/'. $this->type() . '_1/'. $this->type() . '_2');

        $entityId = $this->Instance()->returnId($this->type() . '_2');

        $this->assertEquals('3', $entityId);
    }

    public function testReturnIdPath()
    {
        $this->Instance()->addPath('/'. $this->type() . '_1/'. $this->type() . '_2');

        $entityId = $this->Instance()->returnId('/'. $this->type() . '_1/'. $this->type() . '_2');

        $this->assertEquals('3', $entityId);
    }

    public function testReturnIdNullBadParameters()
    {
        $entityId = $this->Instance()->returnId($this->type() . '_2');

        $this->assertSame(null, $entityId);
    }

    public function testReturnIdNullNoParameters()
    {
        $entityId = $this->Instance()->returnId();

        $this->assertSame(null, $entityId);
    }

    /*
     * Tests for $this->Instance()->titleId()
     */

    public function testGetTitleId()
    {
        $this->Instance()->add($this->type() . '_title', $this->type() . ' Description');
        $title_id = $this->Instance()->titleId($this->type() . '_title');

        $this->assertSame('2', $title_id);
    }

    public function testGetTitleIdNull()
    {
        $title_id = $this->Instance()->titleId($this->type() . '_title');

        $this->assertNull($title_id);
    }

    /*
     * Tests for $this->Instance()->getTitle()
     */

    public function testGetTitle()
    {
        $type_id = $this->Instance()->add($this->type() . '_title', $this->type() . ' Description');
        $type_title = $this->Instance()->getTitle($type_id);

        $this->assertSame($this->type() . '_title', $type_title);
    }

    public function testGetTitleNull()
    {
        $type_title = $this->Instance()->getTitle(intval(3));

        $this->assertNull($type_title);
    }

    /*
     * Tests for $this->Instance()->getDescription()
     */

    public function testGetDescription()
    {
        $type_description = $this->Instance()->getDescription(intval(1));

        $this->assertSame('root', $type_description);
    }

    public function testGetDescriptionNull()
    {
        $type_description = $this->Instance()->getDescription(intval(2));

        $this->assertNull($type_description);
    }

    /*
     * Tests for $this->Instance()->edit()
     */

    public function testEditTitle()
    {
        $type_id = $this->Instance()->add($this->type() . '_title', $this->type() . ' Description');
        $this->Instance()->edit($type_id, $this->type() . '_title_edited');

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type() . ' WHERE ID=2'
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_edit_' . $this->type() . '_title.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testEditDescription()
    {
        $type_id = $this->Instance()->add($this->type() . '_title', $this->type() . ' Description');
        $this->Instance()->edit($type_id, null, $this->type() . ' Description edited');

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type() . ' WHERE ID=2'
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_edit_' . $this->type() . '_description.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testEditAll()
    {
        $type_id = $this->Instance()->add($this->type() . '_title', $this->type() . ' Description');
        $this->Instance()->edit($type_id, $this->type() . '_title_edited', $this->type() . ' Description edited');

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type() . ' WHERE ID=2'
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_edit_' . $this->type() . '_all.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testEditNullId()
    {
        $type_id = $this->Instance()->add($this->type() . '_title', $this->type() . ' Description');
        $result = $this->Instance()->edit(intval(3), $this->type() . '_title', $this->type() . ' Description');

        $this->assertFalse($result);
    }

    public function testEditNullParameters()
    {
        $type_id = $this->Instance()->add($this->type() . '_title', $this->type() . ' Description');
        $result = $this->Instance()->edit($type_id);

        $this->assertFalse($result);
    }

    /*
     * Tests for $this->Instance()->addPath()
     */

    public function testAddPathSingle()
    {
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type()
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_add_path_' . $this->type() . '_single.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddPathSingleDescription()
    {
        $descriptions = array(
            $this->type() . ' Description 1',
            $this->type() . ' Description 2',
            $this->type() . ' Description 3',
        );

        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3', $descriptions);

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type()
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_add_path_' . $this->type() . '_single_description.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddPathSequential()
    {
        $this->Instance()->addPath('/' . $this->type() . '_1/');
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/');
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type()
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_add_path_' . $this->type() . '_sequential.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddPathSequentialDescription()
    {
        $descriptions_1 = array(
            $this->type() . ' Description 1',
        );

        $this->Instance()->addPath('/' . $this->type() . '_1/', $descriptions_1);

        $descriptions_2 = array(
            null,
            $this->type() . ' Description 2',
        );

        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/', $descriptions_2);

        $descriptions_3 = array(
            null,
            null,
            $this->type() . ' Description 3',
        );

        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3', $descriptions_3);

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type()
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_add_path_' . $this->type() . '_sequential_description.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddPathHierarchy()
    {

        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_4');

        $this->Instance()->addPath('/' . $this->type() . '_12/' . $this->type() . '_13/' . $this->type() . '_14');
        $this->Instance()->addPath('/' . $this->type() . '_12/' . $this->type() . '_15/' . $this->type() . '_11');

        $this->Instance()->addPath('/' . $this->type() . '_23/' . $this->type() . '_24/' . $this->type() . '_25');
        $this->Instance()->addPath('/' . $this->type() . '_33/' . $this->type() . '_34/' . $this->type() . '_35');

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type()
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_add_path_' . $this->type() . '_hierarchy.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddPathHierarchyDescription()
    {
        $descriptions_1 = array(
            $this->type() . ' Description 1',
            $this->type() . ' Description 2',
            $this->type() . ' Description 3',
        );

        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3', $descriptions_1);

        $descriptions_2 = array(
            null,
            null,
            $this->type() . ' Description 4',
        );

        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_4', $descriptions_2);

        $descriptions_3 = array(
            $this->type() . ' Description 12',
            $this->type() . ' Description 13',
            $this->type() . ' Description 14',
        );

        $this->Instance()->addPath('/' . $this->type() . '_12/' . $this->type() . '_13/' . $this->type() . '_14', $descriptions_3);

        $descriptions_4 = array(
            null,
            $this->type() . ' Description 15',
            $this->type() . ' Description 11',
        );

        $this->Instance()->addPath('/' . $this->type() . '_12/' . $this->type() . '_15/' . $this->type() . '_11', $descriptions_4);

        $descriptions_5 = array(
            $this->type() . ' Description 23',
            $this->type() . ' Description 24',
            $this->type() . ' Description 25',
        );

        $this->Instance()->addPath('/' . $this->type() . '_23/' . $this->type() . '_24/' . $this->type() . '_25', $descriptions_5);

        $descriptions_6 = array(
            $this->type() . ' Description 33',
            $this->type() . ' Description 34',
            $this->type() . ' Description 35',
        );

        $this->Instance()->addPath('/' . $this->type() . '_33/' . $this->type() . '_34/' . $this->type() . '_35', $descriptions_6);

        $queryTable = $this->getConnection()->createQueryTable(
            $this->Instance()->tablePrefix() . $this->type(), 'SELECT * FROM ' . $this->Instance()->tablePrefix() . $this->type()
        );

        $expectedTable = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_add_path_' . $this->type() . '_hierarchy_description.xml')
        ->getTable($this->Instance()->tablePrefix() . $this->type());

        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testAddPathReturnNodesCreatedCountTwoCreated()
    {
        $this->Instance()->addPath('/' . $this->type() . '_1/');
        $nodes_created = $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $this->assertSame(2, $nodes_created);
    }

    public function testAddPathReturnNodesCreatedCountNoneCreated()
    {
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');
        $nodes_created = $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $this->assertSame(0, $nodes_created);
    }


    /**
     * @expectedException Exception
     */

    public function testAddPathBadPath()
    {
        $this->Instance()->addPath('permissions_1/permissions_2//permissions_3');
    }

    /*
     * Tests for $this->Instance()->pathId()
     */

    public function testPathID()
    {
        $this->Instance()->addPath('/' . $this->type() . '_1/');
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/');
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $path_id = $this->Instance()->pathId('/' . $this->type() . '_1/' . $this->type() . '_2');

        $this->assertSame('3', $path_id);
    }

    public function testPathIDNullBadPath()
    {
        $this->Instance()->addPath('/' . $this->type() . '_1/');
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/');
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $path_id = $this->Instance()->pathId($this->type() . '_2');

        $this->assertNull($path_id);
    }

    public function testPathIDGroupConcatMaxCharCountShortCount()
    {
        $this->Instance()->addPath('/first_depth0/first_depth1/first_depth2/first_depth3/first_depth4/first_depth5/first_depth6/first_depth7/first_depth8/first_depth9/first_depth10/final_109/first_depth11');
        $this->Instance()->addPath('/second_depth0/second_depth1/second_depth2/second_depth3/second_depth4/second_depth5/second_depth6/second_depth7/second_depth8/second_depth9/second_depth10/second_depth11/second_depth12/second_depth13/second_depth14/second_depth15/second_depth16/second_depth17/second_depth18/second_depth19/second_depth20/second_depth21/second_depth22/second_depth23/second_depth24/second_depth25/second_depth26/second_depth27/second_depth28/second_depth29/second_depth30/second_depth31/second_depth32/second_depth33/second_depth34/second_depth35/second_depth36/second_depth37/second_depth38/second_depth39/second_depth40/second_depth41/second_depth42/second_depth43/second_depth44/second_depth45/second_depth46/second_depth47/second_depth48/second_depth49/second_depth50/second_depth51/second_depth52/second_depth53/second_depth54/second_depth55/second_depth56/second_depth57/second_depth58/second_depth59/second_depth60/second_depth61/second_depth62/second_depth63/second_depth64/second_depth65/second_depth66/second_depth67/second_depth68/second_depth69/second_depth70/second_depth71/second_depth72/second_depth73/second_depth74/second_depth75/second_depth76/second_depth77/second_depth78/second_depth79/second_depth80/second_depth81/second_depth82/second_depth83/second_depth84/second_depth85/second_depth86/second_depth87/second_depth88/second_depth89/second_depth90/second_depth91/second_depth92/second_depth93/second_depth94/second_depth95/second_depth96/second_depth97/second_depth98/second_depth99/second_depth100/second_depth101/second_depth102/second_depth103/second_depth104/second_depth105/second_depth106/second_depth107/second_depth108/second_depth109/final_109');

        $path_id = $this->Instance()->pathId("/first_depth0/first_depth1/first_depth2/first_depth3/first_depth4/first_depth5/first_depth6/first_depth7/first_depth8/first_depth9/first_depth10/final_109");

        $this->assertSame('13', $path_id);
    }

    public function testPathIDGroupConcatMaxCharCountLongCount()
    {
        $this->Instance()->addPath('/first_depth0/first_depth1/first_depth2/first_depth3/first_depth4/first_depth5/first_depth6/first_depth7/first_depth8/first_depth9/first_depth10/first_depth11');
        $this->Instance()->addPath('/second_depth0/second_depth1/second_depth2/second_depth3/second_depth4/second_depth5/second_depth6/second_depth7/second_depth8/second_depth9/second_depth10/second_depth11/second_depth12/second_depth13/second_depth14/second_depth15/second_depth16/second_depth17/second_depth18/second_depth19/second_depth20/second_depth21/second_depth22/second_depth23/second_depth24/second_depth25/second_depth26/second_depth27/second_depth28/second_depth29/second_depth30/second_depth31/second_depth32/second_depth33/second_depth34/second_depth35/second_depth36/second_depth37/second_depth38/second_depth39/second_depth40/second_depth41/second_depth42/second_depth43/second_depth44/second_depth45/second_depth46/second_depth47/second_depth48/second_depth49/second_depth50/second_depth51/second_depth52/second_depth53/second_depth54/second_depth55/second_depth56/second_depth57/second_depth58/second_depth59/second_depth60/second_depth61/second_depth62/second_depth63/second_depth64/second_depth65/second_depth66/second_depth67/second_depth68/second_depth69/second_depth70/second_depth71/second_depth72/second_depth73/second_depth74/second_depth75/second_depth76/second_depth77/second_depth78/second_depth79/second_depth80/second_depth81/second_depth82/second_depth83/second_depth84/second_depth85/second_depth86/second_depth87/second_depth88/second_depth89/second_depth90/second_depth91/second_depth92/second_depth93/second_depth94/second_depth95/second_depth96/second_depth97/second_depth98/second_depth99/second_depth100/second_depth101/second_depth102/second_depth103/second_depth104/second_depth105/second_depth106/second_depth107/second_depth108/second_depth109/final_109');

        $path_id = $this->Instance()->pathId("/second_depth0/second_depth1/second_depth2/second_depth3/second_depth4/second_depth5/second_depth6/second_depth7/second_depth8/second_depth9/second_depth10/second_depth11/second_depth12/second_depth13/second_depth14/second_depth15/second_depth16/second_depth17/second_depth18/second_depth19/second_depth20/second_depth21/second_depth22/second_depth23/second_depth24/second_depth25/second_depth26/second_depth27/second_depth28/second_depth29/second_depth30/second_depth31/second_depth32/second_depth33/second_depth34/second_depth35/second_depth36/second_depth37/second_depth38/second_depth39/second_depth40/second_depth41/second_depth42/second_depth43/second_depth44/second_depth45/second_depth46/second_depth47/second_depth48/second_depth49/second_depth50/second_depth51/second_depth52/second_depth53/second_depth54/second_depth55/second_depth56/second_depth57/second_depth58/second_depth59/second_depth60/second_depth61/second_depth62/second_depth63/second_depth64/second_depth65/second_depth66/second_depth67/second_depth68/second_depth69/second_depth70/second_depth71/second_depth72/second_depth73/second_depth74/second_depth75/second_depth76/second_depth77/second_depth78/second_depth79/second_depth80/second_depth81/second_depth82/second_depth83/second_depth84/second_depth85/second_depth86/second_depth87/second_depth88/second_depth89/second_depth90/second_depth91/second_depth92/second_depth93/second_depth94/second_depth95/second_depth96/second_depth97/second_depth98/second_depth99/second_depth100/second_depth101/second_depth102/second_depth103/second_depth104/second_depth105/second_depth106/second_depth107/second_depth108/second_depth109/final_109");

        $this->assertSame('124', $path_id);
    }

    /*
     * Tests for $this->Instance()->getPath()
     */

    public function testPath()
    {
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $path_returned = $this->Instance()->getPath(intval(3));

        $this->assertSame('/' . $this->type() . '_1/' . $this->type() . '_2', $path_returned);
    }

    public function testgetPathNull()
    {
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $path_returned = $this->Instance()->getPath(intval(5));

        $this->assertNull($path_returned);
    }

    /*
     * Tests for $this->Instance()->children()
     */

    public function testChildren()
    {
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3/' . $this->type() . '_4/' . $this->type() . '_5');
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3/' . $this->type() . '_6/' . $this->type() . '_7');
        $path_id = $this->Instance()->pathId('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $children = $this->Instance()->children($path_id);

        $expected = array(
            array(
            	   'ID' => '5',
            	   'Lft' => '4',
            	   'Rght' => '7',
            	   'Title' => $this->type() . '_4',
            	   'Description' => '',
            ),
            array(
            	   'ID' => '7',
            	   'Lft' => '8',
            	   'Rght' => '11',
            	   'Title' => $this->type() . '_6',
            	   'Description' => '',
            )
        );

        $this->assertSame($expected, $children);
    }

    public function testChildrenNullBadID()
    {
        $children = $this->Instance()->children(20);

        $this->assertNull($children);
    }

    /*
     * Tests for $this->Instance()->descendants()
     */

    public function testDescendants()
    {
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3/' . $this->type() . '_4/' . $this->type() . '_5');
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3/' . $this->type() . '_6/' . $this->type() . '_7');
        $path_id = $this->Instance()->pathId('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $descendants = $this->Instance()->descendants($path_id);

        $expected = array(
            $this->type() . '_4' => array(
            	   'ID' => '5',
            	   'Lft' => '4',
            	   'Rght' => '7',
            	   'Title' => $this->type() . '_4',
            	   'Description' => '',
            	   'Depth' => '1',
            ),
            $this->type() . '_5' => array(
            	   'ID' => '6',
            	   'Lft' => '5',
            	   'Rght' => '6',
            	   'Title' => $this->type() . '_5',
            	   'Description' => '',
            	   'Depth' => '2',
            ),
            $this->type() . '_6' => array(
            	   'ID' => '7',
            	   'Lft' => '8',
            	   'Rght' => '11',
            	   'Title' => $this->type() . '_6',
            	   'Description' => '',
        	       'Depth' => '1',
            ),
            $this->type() . '_7' => array(
            	   'ID' => '8',
            	   'Lft' => '9',
            	   'Rght' => '10',
            	   'Title' => $this->type() . '_7',
            	   'Description' => '',
            	   'Depth' => '2',
            ),
        );

        $this->assertSame($expected, $descendants);
    }

    public function testDescendantsEmptyBadID()
    {
        $descendants = $this->Instance()->descendants(20);

        $this->assertEmpty($descendants);
    }

    /*
     * Tests for $this->Instance()->depth()
     */

    public function testDepth()
    {
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3/' . $this->type() . '_4/' . $this->type() . '_5');
        $path_id = $this->Instance()->pathId('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $depth = $this->Instance()->depth($path_id);

        $this->assertSame(3, $depth);
    }

    public function testDepthBadID()
    {
        $depth = $this->Instance()->depth(20);

        $this->assertSame(-1, $depth);
    }

    /*
     * Tests for $this->Instance()->parentNode()
     */

    public function testParentNode()
    {
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3/' . $this->type() . '_4/' . $this->type() . '_5');
        $this->Instance()->addPath('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3/' . $this->type() . '_6/' . $this->type() . '_7');
        $path_id = $this->Instance()->pathId('/' . $this->type() . '_1/' . $this->type() . '_2/' . $this->type() . '_3');

        $parent_node = $this->Instance()->parentNode($path_id);

        $expected = array(
            'ID' => '3',
            'Lft' => '2',
            'Rght' => '13',
            'Title' => $this->type() . '_2',
            'Description' => '',
        );

        $this->assertSame($expected, $parent_node);
    }

    public function testParentNodeNullBadID()
    {
        $parent_node = $this->Instance()->parentNode(20);

        $this->assertNull($parent_node);
    }

    /*
     * Tests for $this->Instance()->assign()
     */

    public function testAssignWithId()
    {
        $perm_id = self::$rbac->Permissions->add('permissions_1', 'permissions Description 1');
        $role_id = self::$rbac->Roles->add('roles_1', 'roles Description 1');

        $this->Instance()->assign($role_id, $perm_id);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addExcludeTables(array($this->Instance()->tablePrefix() . 'userroles'));

        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_assign_' . $this->type() . '.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    public function testAssignWithTitle()
    {
        self::$rbac->Permissions->add('permissions_1', 'permissions Description 1');
        self::$rbac->Roles->add('roles_1', 'roles Description 1');

        $this->Instance()->assign('roles_1', 'permissions_1');

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addExcludeTables(array($this->Instance()->tablePrefix() . 'userroles'));

        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_assign_' . $this->type() . '.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    public function testAssignWithPath()
    {
        self::$rbac->Permissions->add('permissions_1', 'permissions Description 1');
        self::$rbac->Roles->add('roles_1', 'roles Description 1');

        $this->Instance()->assign('/roles_1', '/permissions_1');

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addExcludeTables(array($this->Instance()->tablePrefix() . 'userroles'));

        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_assign_' . $this->type() . '.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    /*
     * Tests for $this->Instance()->unassign()
     */

    public function testUnassignId()
    {
        $perm_id = self::$rbac->Permissions->add('permissions_1', 'permissions Description 1');
        $role_id = self::$rbac->Roles->add('roles_1', 'roles Description 1');

        $this->Instance()->assign($role_id, $perm_id);
        $this->Instance()->unassign($role_id, $perm_id);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            self::$rbac->Users->tablePrefix() . 'rolepermissions',
        ));

        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_unassign_' . $this->type() . '.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    public function testUnassignTitle()
    {
        $perm_id = self::$rbac->Permissions->add('permissions_1', 'permissions Description 1');
        $role_id = self::$rbac->Roles->add('roles_1', 'roles Description 1');

        $this->Instance()->assign($role_id, $perm_id);
        $this->Instance()->unassign('roles_1', 'permissions_1');

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            self::$rbac->Users->tablePrefix() . 'rolepermissions',
        ));

        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_unassign_' . $this->type() . '.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    public function testUnassignPath()
    {
        $perm_id = self::$rbac->Permissions->add('permissions_1', 'permissions Description 1');
        $role_id = self::$rbac->Roles->add('roles_1', 'roles Description 1');

        $this->Instance()->assign($role_id, $perm_id);
        $this->Instance()->unassign('/roles_1', '/permissions_1');

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            self::$rbac->Users->tablePrefix() . 'rolepermissions',
        ));

        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_unassign_' . $this->type() . '.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    /*
     * Tests for $this->Instance()->resetAssignments()
     */

    public function testResetPermRoleAssignments()
    {
        $perm_id_1 = self::$rbac->Permissions->add('permissions_1', 'permissions Description 1');
        $perm_id_2 = self::$rbac->Permissions->add('permissions_2', 'permissions Description 2');
        $perm_id_3 = self::$rbac->Permissions->add('permissions_3', 'permissions Description 3');

        $role_id_1 = self::$rbac->Roles->add('roles_1', 'roles Description 1');
        $role_id_2 = self::$rbac->Roles->add('roles_2', 'roles Description 2');
        $role_id_3 = self::$rbac->Roles->add('roles_3', 'roles Description 3');

        $this->Instance()->assign($role_id_1, $perm_id_1);
        $this->Instance()->assign($role_id_2, $perm_id_2);
        $this->Instance()->assign($role_id_3, $perm_id_3);

        $this->Instance()->resetAssignments(true);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            self::$rbac->Users->tablePrefix() . 'rolepermissions',
        ));

        $filterDataSet->setExcludeColumnsForTable(
            $this->Instance()->tablePrefix() . 'rolepermissions',
            array('AssignmentDate')
        );

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_reset_assignments_' . $this->type() . '.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    /**
     * @expectedException Exception
     */

    public function testResetPermRoleAssignmentsException()
    {
        $this->Instance()->resetAssignments();
    }

    /*
     * Tests for $this->Instance()->reset()
     */

    public function testReset()
    {
        $this->Instance()->add($this->type() . '_title_1', $this->type() . ' Description 1');
        $this->Instance()->add($this->type() . '_title_2', $this->type() . ' Description 2');
        $this->Instance()->add($this->type() . '_title_3', $this->type() . ' Description 3');

        $this->Instance()->reset(true);

        $dataSet = $this->getConnection()->createDataSet();

        $filterDataSet = new \PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
        $filterDataSet->addIncludeTables(array(
            $this->Instance()->tablePrefix() . $this->type(),
        ));

        $expectedDataSet = $this->createFlatXmlDataSet(dirname(__FILE__) . '/datasets/base/expected_reset_' . $this->type() . '.xml');

        $this->assertDataSetsEqual($expectedDataSet, $filterDataSet);
    }

    /**
     * @expectedException Exception
     */

    public function testResetException()
    {
        $this->Instance()->reset();
    }
}

/** @} */ // End group phprbac_unit_test_wrapper_base */
