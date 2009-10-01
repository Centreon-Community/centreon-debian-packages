<?php
/**
 * Test suite for HTML_QuickForm_advmultiselect
 *
 * PHP version 5
 *
 * @category HTML
 * @package  HTML_QuickForm_advmultiselect
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @version  CVS: $Id: AllTests.php,v 1.2 2009/02/07 11:58:03 farell Exp $
 * @link     http://pear.php.net/package/HTML_QuickForm_advmultiselect
 * @since    File available since Release 1.5.0
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'HTML_QuickForm_advmultiselect_AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

chdir(dirname(__FILE__));

require_once 'HTML_QuickForm_advmultiselect_TestSuite_Exception.php';
require_once 'HTML_QuickForm_advmultiselect_TestSuite_Basic.php';
require_once 'HTML_QuickForm_advmultiselect_TestSuite_Custom.php';

/**
 * Class for running all test suites for HTML_QuickForm_advmultiselect package.
 *
 * @category HTML
 * @package  HTML_QuickForm_advmultiselect
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @version  Release: 1.5.1
 * @link     http://pear.php.net/package/HTML_QuickForm_advmultiselect
 * @since    Class available since Release 1.5.0
 */

class HTML_QuickForm_advmultiselect_AllTests
{
    /**
     * Runs the test suite.
     *
     * @return void
     * @static
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * Adds the HTML_QuickForm_advmultiselect test suite.
     *
     * @return object the PHPUnit_Framework_TestSuite object
     * @static
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('HTML_QuickForm_advmultiselect Test Suite');
        $suite->addTestSuite('HTML_QuickForm_advmultiselect_TestSuite_Exception');
        $suite->addTestSuite('HTML_QuickForm_advmultiselect_TestSuite_Basic');
        $suite->addTestSuite('HTML_QuickForm_advmultiselect_TestSuite_Custom');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'HTML_QuickForm_advmultiselect_AllTests::main') {
    HTML_QuickForm_advmultiselect_AllTests::main();
}
?>