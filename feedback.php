<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
  * Feedback Screen
  *
  */
require_once(dirname(__FILE__).'/../../config.php');
require_login();
global $CFG, $OUTPUT, $USER, $DB;
require_once($CFG->dirroot.'/blocks/questionreport/feedbacklib.php');
require_once($CFG->dirroot.'/blocks/questionreport/locallib.php');
require_once($CFG->dirroot.'/blocks/questionreport/chartlib.php');

$reportnum   = optional_param('reportnum', '0', PARAM_RAW);
$yrnum       = optional_param('yrnum', '0', PARAM_RAW);
$partner     = optional_param('partner', '', PARAM_RAW);
$action      = optional_param('action', '', PARAM_RAW);

if ($action == 'pdf') {
    $content = block_questionreport_genfeedback($reportnum, $yrnum, $partner);
    
    exit();
}
?>
<style>
table {
  border-collapse: collapse;
}

td, th {
  border: 1px solid #999;
  padding: 0.5rem;
  text-align: left;
}
</style>
<?php
$plugin = 'block_questionreport';
$PAGE->set_pagelayout('standard');
$PAGE->set_url('/blocks/questionreport/feedback.php');
$PAGE->set_context(context_system::instance());
$header = get_string('feedbackheader', $plugin);
$PAGE->set_title($header);
$PAGE->set_heading($header);
$PAGE->set_cacheable(true);
$PAGE->navbar->add('Feedback Reports', new moodle_url('/blocks/questionreport/feedback.php'));
?>
<style>
table, th, td {
  border: 1px solid black;
}

table {
  width: 100%;
}
</style>
<?php
$plugin = 'block_questionreport';
$reportlist = array();
$reportlist[0] = get_string('noreport', $plugin);
$reportlist[1] = get_string('report1', $plugin);
$reportlist[2] = get_string('report2', $plugin);
$reportlist[3] = get_string('report3', $plugin);
$yrlist = array();
for ($yr = 2020; $yr < 2030; $yr ++) {
     $yrlist[$yr]  = $yr;
}

echo $OUTPUT->header();
echo html_writer::start_tag('h2');
echo get_string('feedback', $plugin);
echo html_writer::end_tag('h2');
echo "<form class=\"questionreportform\" action=\"$CFG->wwwroot/blocks/questionreport/feedback.php\" method=\"get\">\n";
echo "<input type=\"hidden\" name=\"action\" value=\"pdf\" />\n";
echo html_writer::label(get_string('feedbacktype', $plugin), false, array('class' => 'accesshide'));
echo html_writer::select($reportlist,"reportnum",$reportnum, false);
echo html_writer::label(get_string('year', $plugin), false, array('class' => 'accesshide'));
echo html_writer::select($yrlist,"yrnum",$yrnum, false);
$portfoliolist = block_questionreport_get_portfolio_list();

echo html_writer::label(get_string('portreport', $plugin), false, array('class' => 'accesshide'));
echo html_writer::select($portfoliolist, "partner", $partner, get_string("all", $plugin));
echo '<input type="submit" class="btn btn-primary btn-submit" value="'.get_string('getthereports', $plugin).'" />';
echo '</form>';
//$content = block_questionreport_genfeedback($reportnum, $yrnum, $partner);


echo $OUTPUT->footer();
