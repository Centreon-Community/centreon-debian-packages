<?php
require_once('HTML/QuickForm.php');
require_once('HTML/QuickForm/ElementGrid.php');

if (isset($_REQUEST['actAsGroup'])) {
    echo '<a href="?">Click here to make the value exporting act normally</a>';
} else {
    echo '<a href="?actAsGroup=1">Click here to make the value exporting act like a group</a>';
}

$form = new HTML_QuickForm();
$elementGrid =& $form->addElement('elementGrid', 'elementGrid', 'Element Grid', array('actAsGroup' => isset($_REQUEST['actAsGroup'])));
for ($c = 0; $c < 4; ++$c) {
    $elementGrid->addColumnName('Col '.($c + 1));
}
$rows = array();
for ($r = 0; $r < 4; ++$r) {
    unset($row);
    $row = array();
    for ($c = 0; $c < 4; ++$c) {
        $row[] = HTML_QuickForm::createElement((($c + $r) % 2) ? 'checkbox' : 'text',
                                               'r'.$r.'c'.$c,
                                               'Row '.($r + 1).' Col '.($c + 1));
    }
    //$elementGrid->addRow($row, 'Row '.($r + 1));
    $elementGrid->addRowName('Row '.($r + 1));
    $rows[] =& $row;
}
$elementGrid->setRows($rows);
$form->setDefaults(array('r1c0' => true,
                         'r2c2' => 'TEST',
                         'r3c1' => 'Value',
                         'r0c3' => true));
if (isset($_REQUEST['actAsGroup'])) {
    $form->addElement('hidden', 'actAsGroup', true);
}
$form->addElement('submit', 'submit', 'Submit');
if ($form->validate()) {
    $form->freeze();
    echo '<h2>$form->exportValues()</h2>
<pre>';
    print_r($form->exportValues());
    echo '</pre>
<h2>$elementGrid->getValue()</h2>
<pre>';
    print_r($elementGrid->getValue());
    echo '</pre>';
}
$form->display();

highlight_file(__FILE__);
?>