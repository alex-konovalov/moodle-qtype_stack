<?php 


require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');

require_once(dirname(__FILE__) . '/../locallib.php');
require_once('stringutil.class.php');
require_once('options.class.php');
require_once('cas/castext.class.php');
require_once('cas/casstring.class.php');
require_once('cas/cassession.class.php');

require_login();

$context = context_system::instance();
require_capability('moodle/site:config', $context);
$PAGE->set_context($context);
$PAGE->set_url('/question/type/stack/stack/test.php');

// Enable testing of CAStext in with a background of a non-trivial session.
$a1=array('a:x^2','b:(x+1)^2');
$s1=array();
foreach($a1 as $s) {
    $s1[] = new STACK_CAS_CasString($s);
}
$cs1 = new STACK_CAS_CasSession($s1);


$string = optional_param('cas', '', PARAM_RAW);

if ($string) {
    $ct           = new STACK_CAS_CasText($string);
    $displayText  = $ct->Get_display_castext();
    $errs         = $ct->Get_errors();
}

echo $OUTPUT->header();

if ($string) {
    echo '<p>', $displayText, '</p>';
    echo $errs;
}

?>
<form action="test.php" method="POST">
    <!--<textarea cols="80" rows="5" name="cas">@ diff(x^5, x) @ \[ @diff(x^3, x)@ \]</textarea><br /><br /> -->
    <textarea cols="80" rows="5" name="cas"><?php echo $string;?></textarea><br /><br />
    <input type="submit" value="Chat" />
</form>
<?php

echo $OUTPUT->footer();
