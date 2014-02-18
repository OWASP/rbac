<?php
/**
 * @defgroup phprbac_unit_tests Rbac Unit Test Functionality
 * @{
 * Documentation for all PhpRbac Unit Test related functionality.
 * @}
 */

/**
 * @file
 * Unit Tests for PhpRbac PSR Wrapper
 *
 * @defgroup phprbac_unit_test_wrapper_generic Unit Tests for Generic_Tests_DatabaseTestCase Functionality
 * @ingroup phprbac_unit_tests
 * @{
 * Documentation for all Unit Tests regarding Generic_Tests_DatabaseTestCase functionality.
 */

abstract class Generic_Tests_DatabaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    // only instantiate pdo once for test clean-up/fixture load
    static private $pdo = null;

    // only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
    private $conn = null;

    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                if ((string) $GLOBALS['DB_ADAPTER'] === 'pdo_sqlite') {
                    self::$pdo = new PDO('sqlite:' . dirname(dirname(__FILE__)) . '/database/' . $GLOBALS['DB_DBNAME']);

                    $sql = file_get_contents(dirname(dirname(__DIR__))."/database/sqlite.sql");
                    $sql = str_replace("PREFIX_", $GLOBALS['DB_PREFIX'], $sql);
                    $statements = explode(";", $sql);

                    if (is_array($statements))
                    foreach ($statements as $query)
                        self::$pdo->query($query);

                } else {
                    self::$pdo = new PDO( $GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );
                }
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
        }

        return $this->conn;
    }
}

/** @} */ // End group phprbac_unit_test_wrapper_generic */
