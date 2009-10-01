<?php
/**
 * Test suite for customized advmultiselect element
 *
 * PHP version 5
 *
 * @category HTML
 * @package  HTML_QuickForm_advmultiselect
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @version  CVS: $Id: HTML_QuickForm_advmultiselect_TestSuite_Custom.php,v 1.7 2009/04/04 15:02:52 farell Exp $
 * @link     http://pear.php.net/package/HTML_QuickForm_advmultiselect
 * @since    File available since Release 1.5.0
 */

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'HTML/QuickForm/advmultiselect.php';

/**
 * Test suite class to test advanced HTML_QuickForm_advmultiselect API.
 *
 * @category HTML
 * @package  HTML_QuickForm_advmultiselect
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD
 * @version  Release: 1.5.1
 * @link     http://pear.php.net/package/HTML_QuickForm_advmultiselect
 * @since    Class available since Release 1.5.0
 */
class HTML_QuickForm_advmultiselect_TestSuite_Custom extends PHPUnit_Framework_TestCase
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
     * Tests dual advmultiselect element with limited size
     *
     * @return void
     */
    public function testAms2WithSizeLimit()
    {
        $ams = new HTML_QuickForm_advmultiselect('foo', null, null,
                                                 array('size' => 5,
                                                       'class'=> 'pool',
                                                       'style' => 'width:300px;'));

        $this->assertEquals(5, $ams->getSize());
        $this->assertRegExp(
            '!<select[^>]*style="width:300px;"[^>]*>!',
            $ams->toHtml()
        );
    }

    /**
     * Tests dual advmultiselect element with 2 additional labels
     *
     * @return void
     */
    public function testAms2WithLabels()
    {
        $ams = new HTML_QuickForm_advmultiselect('foo');
        $label2 = 'Left list';
        $label3 = 'Right list';
        $ams->setLabel(array('foo:', $label2, $label3));

        $this->assertRegExp(
            '!<th>'.$label2.'</th>\\s*<th>&nbsp;</th>\\s*<th>'.$label3.'</th>!',
            $ams->toHtml()
        );
    }

    /**
     * Tests dual advmultiselect element with new text buttons
     *
     * @return void
     */
    public function testAms2WithTextButtons()
    {
        $add_text_button    = 'Add >>';
        $remove_text_button = '<< Remove';
        $all_text_button    = 'Add all >>';
        $none_text_button   = '<< Remove all';
        $toggle_text_button = '<< Toggle >>';
        $up_text_button     = '! Up !';
        $down_text_button   = '! Down !';
        $top_text_button    = '! Top !';
        $bottom_text_button = '! Bottom !';
        $class_button       = 'inputCommand';

        $ams = new HTML_QuickForm_advmultiselect('foo');

        $amsDualTemplate = '
<table{class}>
<!-- BEGIN label_2 --><tr><th>{label_2}</th><!-- END label_2 -->
<!-- BEGIN label_3 --><th>&nbsp;</th><th>{label_3}</th></tr><!-- END label_3 -->
<tr>
  <td valign="top">{unselected}</td>
  <td align="center">
    {add}{remove}<br/>
    {all}{none}{toggle}<br/>
    {moveup}{movedown}{movetop}{movebottom}
  </td>
  <td valign="top">{selected}</td>
</tr>
</table>
';
        $ams->setElementTemplate($amsDualTemplate);

        $ams->setButtonAttributes('add',    array('value' => $add_text_button,
                                                  'class' => $class_button
        ));
        $ams->setButtonAttributes('remove', array('value' => $remove_text_button,
                                                  'class' => $class_button
        ));
        $ams->setButtonAttributes('all', array('value' => $all_text_button,
                                               'class' => $class_button
        ));
        $ams->setButtonAttributes('none', array('value' => $none_text_button,
                                                'class' => $class_button
        ));
        $ams->setButtonAttributes('toggle', array('value' => $toggle_text_button,
                                                  'class' => $class_button
        ));
        $ams->setButtonAttributes('moveup', array('value' => $up_text_button,
                                                  'class' => $class_button
        ));
        $ams->setButtonAttributes('movedown', array('value' => $down_text_button,
                                                    'class' => $class_button
        ));
        $ams->setButtonAttributes('movetop', array('value' => $top_text_button,
                                                   'class' => $class_button
        ));
        $ams->setButtonAttributes('movebottom', array('value' => $bottom_text_button,
                                                      'class' => $class_button
        ));

        preg_match_all('!<input([^>]+)/>!', $ams->toHtml(), $matches, PREG_SET_ORDER);
        $this->assertEquals(
            array('name' => 'add',
                  'value' => htmlspecialchars($add_text_button),
                  'type' => 'button',
                  'class' => $class_button,
                  'onclick' => 'QFAMS.moveSelection(\'foo\', this.form.elements[\'foo-f[]\'], this.form.elements[\'foo-t[]\'], this.form.elements[\'foo[]\'], \'add\', \'none\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[0][1])
        );
        $this->assertEquals(
            array('name' => 'remove',
                  'value' => htmlspecialchars($remove_text_button),
                  'type' => 'button',
                  'class' => $class_button,
                  'onclick' => 'QFAMS.moveSelection(\'foo\', this.form.elements[\'foo-f[]\'], this.form.elements[\'foo-t[]\'], this.form.elements[\'foo[]\'], \'remove\', \'none\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[1][1])
        );
        $this->assertEquals(
            array('name' => 'all',
                  'value' => htmlspecialchars($all_text_button),
                  'type' => 'button',
                  'class' => $class_button,
                  'onclick' => 'QFAMS.moveSelection(\'foo\', this.form.elements[\'foo-f[]\'], this.form.elements[\'foo-t[]\'], this.form.elements[\'foo[]\'], \'all\', \'none\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[2][1])
        );
        $this->assertEquals(
            array('name' => 'none',
                  'value' => htmlspecialchars($none_text_button),
                  'type' => 'button',
                  'class' => $class_button,
                  'onclick' => 'QFAMS.moveSelection(\'foo\', this.form.elements[\'foo-f[]\'], this.form.elements[\'foo-t[]\'], this.form.elements[\'foo[]\'], \'none\', \'none\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[3][1])
        );
        $this->assertEquals(
            array('name' => 'toggle',
                  'value' => htmlspecialchars($toggle_text_button),
                  'type' => 'button',
                  'class' => $class_button,
                  'onclick' => 'QFAMS.moveSelection(\'foo\', this.form.elements[\'foo-f[]\'], this.form.elements[\'foo-t[]\'], this.form.elements[\'foo[]\'], \'toggle\', \'none\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[4][1])
        );
        $this->assertEquals(
            array('name' => 'up',
                  'value' => htmlspecialchars($up_text_button),
                  'type' => 'button',
                  'class' => $class_button,
                  'onclick' => 'QFAMS.moveUp(this.form.elements[\'foo-t[]\'], this.form.elements[\'foo[]\']); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[5][1])
        );
        $this->assertEquals(
            array('name' => 'down',
                  'value' => htmlspecialchars($down_text_button),
                  'type' => 'button',
                  'class' => $class_button,
                  'onclick' => 'QFAMS.moveDown(this.form.elements[\'foo-t[]\'], this.form.elements[\'foo[]\']); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[6][1])
        );
        $this->assertEquals(
            array('name' => 'top',
                  'value' => htmlspecialchars($top_text_button),
                  'type' => 'button',
                  'class' => $class_button,
                  'onclick' => 'QFAMS.moveTop(this.form.elements[\'foo-t[]\'], this.form.elements[\'foo[]\']); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[7][1])
        );
        $this->assertEquals(
            array('name' => 'bottom',
                  'value' => htmlspecialchars($bottom_text_button),
                  'type' => 'button',
                  'class' => $class_button,
                  'onclick' => 'QFAMS.moveBottom(this.form.elements[\'foo-t[]\'], this.form.elements[\'foo[]\']); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[8][1])
        );
    }

    /**
     * Tests dual advmultiselect element with image buttons
     *
     * @return void
     */
    public function testAms2WithImageButtons()
    {
        $add_text_button    = ' >> ';
        $add_image_src      = '/img/qfams/down.png';
        $remove_text_button = ' << ';
        $remove_image_src   = '/img/qfams/up.png';

        $ams = new HTML_QuickForm_advmultiselect('foo');
        $ams->setButtonAttributes('add',    array('type' => 'image',
                                                  'src'  => $add_image_src));
        $ams->setButtonAttributes('remove', array('type' => 'image',
                                                  'src'  => $remove_image_src));

        preg_match_all('!<input([^>]+)/>!', $ams->toHtml(), $matches, PREG_SET_ORDER);
        $this->assertEquals(
            array('name' => 'add',
                  'value' => htmlspecialchars($add_text_button),
                  'type' => 'image',
                  'src' => $add_image_src,
                  'onclick' => 'QFAMS.moveSelection(\'foo\', this.form.elements[\'foo-f[]\'], this.form.elements[\'foo-t[]\'], this.form.elements[\'foo[]\'], \'add\', \'none\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[0][1])
        );
        $this->assertEquals(
            array('name' => 'remove',
                  'value' => htmlspecialchars($remove_text_button),
                  'type' => 'image',
                  'src' => $remove_image_src,
                  'onclick' => 'QFAMS.moveSelection(\'foo\', this.form.elements[\'foo-f[]\'], this.form.elements[\'foo-t[]\'], this.form.elements[\'foo[]\'], \'remove\', \'none\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[1][1])
        );
    }

    /**
     * Tests dual advmultiselect element with fancy attributes (disabled, ...)
     *
     * @return void
     */
    public function testAms2DisabledOptionsProduceValues()
    {
        $fruit_opt = array('apple'  =>  'Apple',
                           'orange' =>  'Orange',
                           'pear'   =>  array('Pear', array('disabled' => 'disabled'))
                           );

        $ams = new HTML_QuickForm_advmultiselect('fruit', null, $fruit_opt);
        $ams->setSelected('pear');

        $this->assertEquals(array('pear'), $ams->getSelected());
    }

    /**
     * Tests dual advmultiselect element with persistant options sets
     *
     * @return void
     */
    public function testAms2WithPersistantOptions()
    {
        $fruit_opt = array('apple'  =>  'Apple',
                           'orange' =>  array('Orange', array('disabled' => 'disabled')),
                           'pear'   =>  'Pear'
                           );

        $ams = new HTML_QuickForm_advmultiselect('fruit', null, $fruit_opt);
        $ams->setPersistantOptions(array('orange'), false);
        $ams->setPersistantOptions('pear');

        $this->assertEquals(array('pear'), $ams->getPersistantOptions());
    }

    /**
     * Tests single advmultiselect element with persistant options sets
     *
     * @return void
     */
    public function testAms1WithPersistantOptions()
    {
        $fruits = array('apple'  =>  'Apple',
                        'orange' =>  'Orange',
                        'pear'   =>  'Pear');

        $ams = new HTML_QuickForm_advmultiselect('fruit', null, $fruits);
        $ams->setPersistantOptions(array('orange'));

        $amsSingleTemplate = '
<table{class}>
<!-- BEGIN label_3 --><tr><th>{label_3}</th></tr><!-- END label_3 -->
<tr>
  <td>{selected}</td>
</tr>
</table>
';
        $ams->setElementTemplate($amsSingleTemplate);

        $this->assertRegExp(
            '!<label><input[^>]+disabled="disabled"[^>]*>Orange</label>!',
            $ams->toHtml()
        );
    }

    /**
     * Tests single advmultiselect element with selected options sets
     *
     * @return void
     */
    public function testAms1WithSelectedOptions()
    {
        $fruits = array('apple'  =>  'Apple',
                        'orange' =>  'Orange',
                        'kiwi'   =>  'Kiwi',
                        'pear'   =>  'Pear');

        $ams = new HTML_QuickForm_advmultiselect('fruit', null, $fruits);
        $ams->setSelected('pear,apple');

        $amsSingleTemplate = '
<table{class}>
<!-- BEGIN label_3 --><tr><th>{label_3}</th></tr><!-- END label_3 -->
<tr>
  <td>{selected}</td>
</tr>
</table>
';
        $ams->setElementTemplate($amsSingleTemplate);

        $this->assertRegExp(
            '!<label><input[^>]+checked="checked"[^>]*>Apple</label>!',
            $ams->toHtml()
        );
    }

    /**
     * Tests dual advmultiselect element with html comment and compressed javascript
     *
     * @return void
     */
    public function testAms2MinimizedWithAnnotation()
    {
        $fruit_opt = array('apple'  =>  'Apple',
                           'orange' =>  'Orange',
                           'pear'   =>  'Pear'
                           );

        $ams = new HTML_QuickForm_advmultiselect('fruit', null, $fruit_opt);
        $rem = 'my first QFAMS element with auto-arrange feature';
        $ams->setComment($rem);

        $js = $ams->getElementJs(true, true);
        $this->assertFalse(empty($js));

        $this->assertRegExp('!'.$rem.'!', $ams->toHtml());
    }

    /**
     * Tests dual advmultiselect frozen element
     *
     * @return void
     */
    public function testFrozenAms2()
    {
        $fruit_opt = array('apple'  =>  'Apple',
                           'orange' =>  'Orange',
                           'pear'   =>  'Pear'
                           );

        $ams = new HTML_QuickForm_advmultiselect('fruit', null, $fruit_opt);
        $ams->setSelected('orange');
        $ams->freeze();

        $this->assertRegExp(
            '!Orange<input type="hidden" name="fruit\[\]" value="orange" />!',
            $ams->toHtml()
        );
    }

    /**
     * Tests dual advmultiselect element with auto-arrange feature (sort ascending option)
     *
     * @return void
     */
    public function testAms2WithSortAscOption()
    {
        $fruit_opt = array('orange' =>  array('Orange', array('disabled' => 'disabled')),
                           'pear'   =>  'Pear',
                           'apple'  =>  'Apple',
                           'lemon'  =>  'Lemon'
                           );

        $ams = new HTML_QuickForm_advmultiselect('fruit', null, $fruit_opt,
                                                 array('style' => 'width:200px;'),
                                                 SORT_ASC);
        $ams->setSelected('orange');

        $amsDualTemplate = '
<table{class}>
<!-- BEGIN label_2 --><tr><th>{label_2}</th><!-- END label_2 -->
<!-- BEGIN label_3 --><th>&nbsp;</th><th>{label_3}</th></tr><!-- END label_3 -->
<tr>
  <td valign="top">{unselected}</td>
  <td align="center">
    {add}{remove}<br/>
    {all}{none}{toggle}
  </td>
  <td valign="top">{selected}</td>
</tr>
</table>
';
        $ams->setElementTemplate($amsDualTemplate);

        $add_text_button    = ' >> ';
        $remove_text_button = ' << ';
        $all_text_button    = ' Select All ';
        $none_text_button   = ' Select None ';
        $toggle_text_button = ' Toggle Selection ';

        preg_match_all('!<input([^>]+)/>!', $ams->toHtml(), $matches, PREG_SET_ORDER);
        $this->assertEquals(
            array('name' => 'add',
                  'value' => htmlspecialchars($add_text_button),
                  'type' => 'button',
                  'onclick' => 'QFAMS.moveSelection(\'fruit\', this.form.elements[\'fruit-f[]\'], this.form.elements[\'fruit-t[]\'], this.form.elements[\'fruit[]\'], \'add\', \'asc\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[0][1])
        );
        $this->assertEquals(
            array('name' => 'remove',
                  'value' => htmlspecialchars($remove_text_button),
                  'type' => 'button',
                  'onclick' => 'QFAMS.moveSelection(\'fruit\', this.form.elements[\'fruit-f[]\'], this.form.elements[\'fruit-t[]\'], this.form.elements[\'fruit[]\'], \'remove\', \'asc\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[1][1])
        );
        $this->assertEquals(
            array('name' => 'all',
                  'value' => htmlspecialchars($all_text_button),
                  'type' => 'button',
                  'onclick' => 'QFAMS.moveSelection(\'fruit\', this.form.elements[\'fruit-f[]\'], this.form.elements[\'fruit-t[]\'], this.form.elements[\'fruit[]\'], \'all\', \'asc\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[2][1])
        );
        $this->assertEquals(
            array('name' => 'none',
                  'value' => htmlspecialchars($none_text_button),
                  'type' => 'button',
                  'onclick' => 'QFAMS.moveSelection(\'fruit\', this.form.elements[\'fruit-f[]\'], this.form.elements[\'fruit-t[]\'], this.form.elements[\'fruit[]\'], \'none\', \'asc\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[3][1])
        );
        $this->assertEquals(
            array('name' => 'toggle',
                  'value' => htmlspecialchars($toggle_text_button),
                  'type' => 'button',
                  'onclick' => 'QFAMS.moveSelection(\'fruit\', this.form.elements[\'fruit-f[]\'], this.form.elements[\'fruit-t[]\'], this.form.elements[\'fruit[]\'], \'toggle\', \'asc\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[4][1])
        );
    }

    /**
     * Tests dual advmultiselect element with auto-arrange feature (sort descending option)
     *
     * @return void
     */
    public function testAms2WithSortDescOption()
    {
        $fruit_opt = array('orange' =>  'Orange',
                           'pear'   =>  'Pear',
                           'apple'  =>  'Apple',
                           'lemon'  =>  'Lemon'
                           );

        $ams = new HTML_QuickForm_advmultiselect('fruit', null, $fruit_opt,
                                                 array('style' => 'width:200px;'),
                                                 SORT_DESC);

        $amsDualTemplate = '
<table{class}>
<!-- BEGIN label_2 --><tr><th>{label_2}</th><!-- END label_2 -->
<!-- BEGIN label_3 --><th>&nbsp;</th><th>{label_3}</th></tr><!-- END label_3 -->
<tr>
  <td valign="top">{unselected}</td>
  <td align="center">
    {add}{remove}<br/>
    {all}{none}{toggle}
  </td>
  <td valign="top">{selected}</td>
</tr>
</table>
';
        $ams->setElementTemplate($amsDualTemplate);

        $add_text_button    = ' >> ';
        $remove_text_button = ' << ';
        $all_text_button    = ' Select All ';
        $none_text_button   = ' Select None ';
        $toggle_text_button = ' Toggle Selection ';

        preg_match_all('!<input([^>]+)/>!', $ams->toHtml(), $matches, PREG_SET_ORDER);
        $this->assertEquals(
            array('name' => 'add',
                  'value' => htmlspecialchars($add_text_button),
                  'type' => 'button',
                  'onclick' => 'QFAMS.moveSelection(\'fruit\', this.form.elements[\'fruit-f[]\'], this.form.elements[\'fruit-t[]\'], this.form.elements[\'fruit[]\'], \'add\', \'desc\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[0][1])
        );
        $this->assertEquals(
            array('name' => 'remove',
                  'value' => htmlspecialchars($remove_text_button),
                  'type' => 'button',
                  'onclick' => 'QFAMS.moveSelection(\'fruit\', this.form.elements[\'fruit-f[]\'], this.form.elements[\'fruit-t[]\'], this.form.elements[\'fruit[]\'], \'remove\', \'desc\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[1][1])
        );
        $this->assertEquals(
            array('name' => 'all',
                  'value' => htmlspecialchars($all_text_button),
                  'type' => 'button',
                  'onclick' => 'QFAMS.moveSelection(\'fruit\', this.form.elements[\'fruit-f[]\'], this.form.elements[\'fruit-t[]\'], this.form.elements[\'fruit[]\'], \'all\', \'desc\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[2][1])
        );
        $this->assertEquals(
            array('name' => 'none',
                  'value' => htmlspecialchars($none_text_button),
                  'type' => 'button',
                  'onclick' => 'QFAMS.moveSelection(\'fruit\', this.form.elements[\'fruit-f[]\'], this.form.elements[\'fruit-t[]\'], this.form.elements[\'fruit[]\'], \'none\', \'desc\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[3][1])
        );
        $this->assertEquals(
            array('name' => 'toggle',
                  'value' => htmlspecialchars($toggle_text_button),
                  'type' => 'button',
                  'onclick' => 'QFAMS.moveSelection(\'fruit\', this.form.elements[\'fruit-f[]\'], this.form.elements[\'fruit-t[]\'], this.form.elements[\'fruit[]\'], \'toggle\', \'desc\'); return false;'
                  ),
            HTML_Common::_parseAttributes($matches[4][1])
        );
    }

    /**
     * Tests single advmultiselect element with fancy attributes
     *
     * @return void
     */
    public function testAms1WithFancyOptions()
    {
        $fruit_opt = array('apple'  =>  'Apple',
                           'orange' =>  'Orange',
                           'pear'   =>  'Pear',
                           'lemon'  =>  array('Lemon', array('style' => 'color:yellow'))
                           );

        $ams = new HTML_QuickForm_advmultiselect('fruit', null, $fruit_opt);

        $template1 = '
<table{class}>
<!-- BEGIN label_3 --><tr><th>{label_3}</th><th>&nbsp;</th></tr><!-- END label_3 -->
<tr>
 <td>{selected}</td>
 <td>{all}<br />{none}<br />{toggle}</td>
</tr>
</table>
';

        $ams->setElementTemplate($template1);

        $this->assertRegExp(
            '!<label style="color:yellow"><input[^>]*/>Lemon</label>!',
            $ams->toHtml()
        );
    }
}
?>