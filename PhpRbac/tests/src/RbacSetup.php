<?php
//namespace PhpRbac;

use PhpRbac\Rbac;

/**
 * @file
 * Unit Tests for PhpRbac PSR Wrapper
 *
 * @defgroup phprbac_unit_test_wrapper_setup Unit Tests for Rbac Functionality
 * @ingroup phprbac_unit_tests
 * @{
 * Documentation for all Unit Tests regarding RbacSetup functionality.
 */

class RbacSetup extends \Generic_Tests_DatabaseTestCase
{
    /*
     * Test Setup and Fixture
     */

	public static $rbac;

    public static function setUpBeforeClass()
    {
    	self::$rbac = new Rbac('unit_test');

    	if ((string) $GLOBALS['DB_ADAPTER'] === 'pdo_sqlite') {
    	    self::$rbac->reset(true);
    	}
    }

    protected function tearDown()
    {
        if ((string) $GLOBALS['DB_ADAPTER'] === 'pdo_sqlite') {
            self::$rbac->reset(true);
        }
    }

    public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__) . '/datasets/database-seed.xml');
    }

    /*
     * Tests for proper object instantiation
     */

    public function testRbacInstance() {
        $this->assertInstanceOf('PhpRbac\Rbac', self::$rbac);
    }
}

/** @} */ // End group phprbac_unit_test_wrapper_setup */
