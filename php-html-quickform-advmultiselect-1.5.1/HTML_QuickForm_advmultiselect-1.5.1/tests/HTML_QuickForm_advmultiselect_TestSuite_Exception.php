<?php
/**
 * Test suite for API invalid paremeter call on advmultiselect element
 *
 * PHP version 5
 *
 * @category HTML
 * @package  HTML_QuickForm_advmultiselect
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @version  CVS: $Id: HTML_QuickForm_advmultiselect_TestSuite_Exception.php,v 1.1 2009/02/07 11:58:03 farell Exp $
 * @link     http://pear.php.net/package/HTML_QuickForm_advmultiselect
 * @since    File available since Release 1.5.0
 */

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'PEAR.php';
require_once 'HTML/QuickForm/advmultiselect.php';

/**
 * Test suite class to test API Exceptions
 *
 * @category HTML
 * @package  HTML_QuickForm_advmultiselect
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @version  Release: 1.5.1
 * @link     http://pear.php.net/package/HTML_QuickForm_advmultiselect
 * @since    Class available since Release 1.5.0
 */
class HTML_QuickForm_advmultiselect_TestSuite_Exception extends PHPUnit_Framework_TestCase
{
    /**
     * tests API throws error
     *
     * @param object $error PEAR_Error instance
     * @param int    $code  error code
     * @param string $level error level (exception or error)
     *
     * @return void
     */
    public function catchError($error, $code = null, $level = null)
    {
        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $error);
        if ($error instanceof PEAR_Error) {
            if (isset($code)) {
                $this->assertEquals($error->getCode(), $code);
            }
            if (isset($level)) {
                $user_info = $error->getUserInfo();
                $this->assertEquals($user_info['level'], $level);
            }
        }
    }

    /**
     * Tests to catch exception on invalid parameters, when calling
     * HTML_QuickForm_advmultiselect::setButtonAttributes
     *
     * @return void
     */
    public function testInvalidParametersOnSetButtonAttributes()
    {
        $ams = new HTML_QuickForm_advmultiselect('foo');
        $r   = $ams->setButtonAttributes(array('add'));
        $this->catchError($r, HTML_QUICKFORM_ADVMULTISELECT_ERROR_INVALID_INPUT, 'exception');

        $r   = $ams->setButtonAttributes('delete');
        $this->catchError($r, HTML_QUICKFORM_ADVMULTISELECT_ERROR_INVALID_INPUT, 'error');
    }

    /**
     * Tests to catch exception on invalid parameters, when calling
     * HTML_QuickForm_advmultiselect::loadArray
     *
     * @return void
     */
    public function testInvalidParametersOnLoadArray()
    {
        $ams = new HTML_QuickForm_advmultiselect('foo');
        $r   = $ams->loadArray('apple,orange');
        $this->catchError($r, HTML_QUICKFORM_ADVMULTISELECT_ERROR_INVALID_INPUT, 'exception');
    }

    /**
     * Tests to catch exception on invalid parameters, when calling
     * HTML_QuickForm_advmultiselect::setPersistantOptions
     *
     * @return void
     */
    public function testInvalidParametersOnSetPersistantOptions()
    {
        $ams = new HTML_QuickForm_advmultiselect('foo');
        $r   = $ams->setPersistantOptions(1);
        $this->catchError($r, HTML_QUICKFORM_ADVMULTISELECT_ERROR_INVALID_INPUT, 'exception');

        $r   = $ams->setPersistantOptions('kiwi', 1);
        $this->catchError($r, HTML_QUICKFORM_ADVMULTISELECT_ERROR_INVALID_INPUT, 'exception');
    }
}
?>