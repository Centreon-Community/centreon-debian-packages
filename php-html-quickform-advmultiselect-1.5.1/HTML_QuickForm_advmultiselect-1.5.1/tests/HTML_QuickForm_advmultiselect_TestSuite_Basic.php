<?php
/**
 * Test suite for basic advmultiselect element
 *
 * PHP version 5
 *
 * @category HTML
 * @package  HTML_QuickForm_advmultiselect
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @version  CVS: $Id: HTML_QuickForm_advmultiselect_TestSuite_Basic.php,v 1.2 2009/02/06 14:24:52 farell Exp $
 * @link     http://pear.php.net/package/HTML_QuickForm_advmultiselect
 * @since    File available since Release 1.5.0
 */

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'HTML/QuickForm/advmultiselect.php';

/**
 * Test suite class to test basic HTML_QuickForm_advmultiselect API.
 *
 * @category HTML
 * @package  HTML_QuickForm_advmultiselect
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @version  Release: 1.5.1
 * @link     http://pear.php.net/package/HTML_QuickForm_advmultiselect
 * @since    Class available since Release 1.5.0
 */
class HTML_QuickForm_advmultiselect_TestSuite_Basic extends PHPUnit_Framework_TestCase
{
    /**
     * POST data
     * @var  array
     */
    protected $post;

    /**
     * GET data
     * @var  array
     */
    protected $get;

    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->post = $_POST;
        $this->get  = $_GET;

        $_POST = array();
        $_GET  = array();
    }

    /**
     * Tears down the fixture.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
        $_POST = $this->post;
        $_GET  = $this->get;
    }

    /**
     * Tests advmultiselect element without options and selection
     *
     * @return void
     */
    public function testAmsIsEmptyByDefault()
    {
        $ams = new HTML_QuickForm_advmultiselect('foo');

        $this->assertTrue($ams->getMultiple());
        $this->assertEquals(10, $ams->getSize());
        $this->assertNull($ams->getSelected());
    }

    /**
     * Tests presence of standard Add + Remove buttons
     *
     * @return void
     */
    public function testAmsHasAddRemoveButtons()
    {
        $ams = new HTML_QuickForm_advmultiselect('foo');

        $htmlAms = $ams->toHtml();

        $this->assertRegExp('/name="foo\\[\\]"/', $htmlAms);
        $this->assertRegExp('/name="foo-f\\[\\]"/', $htmlAms);
        $this->assertRegExp('/name="foo-t\\[\\]"/', $htmlAms);

        $this->assertRegExp('/name="add"/', $htmlAms);
        $this->assertRegExp('/value="'.htmlspecialchars(' >> ').'"/', $htmlAms);
        $this->assertRegExp('/name="remove"/', $htmlAms);
        $this->assertRegExp('/value="'.htmlspecialchars(' << ').'"/', $htmlAms);
    }

    /**
     * Tests advmultiselect element set with a default selection
     *
     * @return void
     */
    public function testAmsWithDefaultSelection()
    {
        $options = array('dodge' =>  'Dodge',
                         'chevy' =>  'Chevy',
                         'bmw'   =>  'BMW');
        $ams = new HTML_QuickForm_advmultiselect('foo', null, $options);
        $ams->setSelected('bmw');

        $this->assertRegExp(
            '!<option[^>]+selected="selected"[^>]*>BMW</option>!',
            $ams->toHtml()
        );
    }

    /**
     * Tests advmultiselect element load options (with default values)
     *
     * @return void
     */
    public function testAmsLoadOptions()
    {
        $options = array('dodge' =>  'Dodge',
                         'chevy' =>  'Chevy',
                         'bmw'   =>  'BMW');
        $ams = new HTML_QuickForm_advmultiselect('foo');
        $ams->load($options, 'bmw,chevy');

        $this->assertRegExp(
            '!<option[^>]+selected="selected"[^>]*>BMW</option>!',
            $ams->toHtml()
        );
        $this->assertRegExp(
            '!<option[^>]+selected="selected"[^>]*>Chevy</option>!',
            $ams->toHtml()
        );
    }

    /**
     * Tests advmultiselect element setting new html template
     *
     * @return void
     */
    public function testAmsDefineDefaultHtmlTemplateWithoutJavascript()
    {
        $ams  = new HTML_QuickForm_advmultiselect('foo');
        // set apply the default html template without javascript
        $tpl_default   = $ams->setElementTemplate(null, false);
        $tpl_withoutJS = $ams->setElementTemplate(null, true);

        $this->assertTrue(strpos('{javascript}', $tpl_default) >= 0);
        $this->assertFalse(strpos('{javascript}', $tpl_withoutJS));
    }
}
?>