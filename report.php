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
// You shou3ld have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
  * Block questionreport Report File.
  *
  * @package    block_questionreport
  */
require_once(dirname(__FILE__).'/../../config.php');
$cid          = optional_param('cid', '0', PARAM_RAW);// Course ID.
$action       = optional_param('action', 'view', PARAM_ALPHAEXT);
$start_date   = optional_param('start_date', '0', PARAM_RAW);
$end_date     = optional_param('end_date', '0', PARAM_RAW);
$partner      = optional_param('partner', '', PARAM_RAW);
$questionid   = optional_param('question', 0, PARAM_INT);
$surveyid   = optional_param('surveyid', 0, PARAM_INT);
$coursefilter = optional_param('coursefilter', '0', PARAM_RAW);
$portfolio    = optional_param('portfolio', '', PARAM_RAW);
$teacher      = optional_param('teacher', '', PARAM_RAW); //Teacher id.

$plugin = 'block_questionreport';
// Require the javascript for wordcloud.
$PAGE->requires->js('/blocks/questionreport/js/wordCloud2.js');
$PAGE->set_pagelayout('standard');
$PAGE->set_url('/blocks/questionreport/report.php');
$PAGE->set_context(context_system::instance());
$header = 'Lead Facilitator Report';
//exit();
$PAGE->set_title($header);
$PAGE->set_heading($header);
$PAGE->set_cacheable(true);
$PAGE->navbar->add($header, new moodle_url('/blocks/questionreport/report.php'));

global $CFG, $OUTPUT, $USER, $DB;
require_once($CFG->dirroot.'/blocks/questionreport/locallib.php');
require_once($CFG->dirroot.'/blocks/questionreport/chartlib.php');

$ctype = substr($cid, 0, 1);
$courseid = substr($cid, 2);
if ($ctype == "M") {
    require_login($courseid);
    global $COURSE;
}
if ($action == 'pdf') {
    block_questionreport_get_essay_results($ctype, $questionid, $start_date, $end_date, $coursefilter, $surveyid, $action, $portfolio, $teacher, $courseid);
    exit();
}
echo $OUTPUT->header();
// Build up the filters
$courselist = block_questionreport_get_courses();
$portfieldid = get_config($plugin, 'portfoliofield');
echo html_writer::start_tag('h2');
echo get_string('filters', $plugin);
echo html_writer::end_tag('h2');
echo "<form class=\"questionreportform\" action=\"$CFG->wwwroot/blocks/questionreport/report.php\" method=\"get\">\n";
echo "<input type=\"hidden\" name=\"action\" value=\"view\" />\n";
echo "<input type=\"hidden\" name=\"cid\" value=\"$cid\" />\n";

echo html_writer::label(get_string('courseonly', $plugin), false, array('class' => 'accesshide'));
echo html_writer::select($courselist, "coursefilter", $coursefilter, false);

$partnerlist = block_questionreport_get_partners_list();

echo html_writer::label(get_string('partnerfilter', $plugin), false, array('class' => 'accesshide'));
echo html_writer::select($partnerlist, "partner", $partner, get_string("all", $plugin));
// See if they are an admin.
$adminvalue = get_config($plugin, 'adminroles');
// Array of roles that can be a local admin.
$adminarray = explode(',', $adminvalue);
// check to see if they are an admin.
$adminuser = false;
$is_admin = block_questionreport_is_admin();
// echo '$is_admin = '.$is_admin;
if (!!$is_admin) {
    $adminuser = true;
}

// Adds extra controls for admin user filtering.
if ($adminuser) {
    $portfoliolist = block_questionreport_get_portfolio_list();
    echo html_writer::label(get_string('portfoliofilteronly', $plugin), false, array('class' => 'accesshide'));
    echo html_writer::select($portfoliolist, "portfolio", $portfolio, get_string("all", $plugin));
    $teacherlist = block_questionreport_get_teachers_list();

    echo html_writer::label(get_string('teacherfilteronly', $plugin), false, array('class' => 'accesshide'));
    echo html_writer::select($teacherlist, "teacher", $teacher, get_string("all", $plugin));
} else {
    $def = 0;
    echo "<input type=\"hidden\" name=\"portfolio\" value=\"$def\" />\n";
    echo "<input type=\"hidden\" name=\"teacher\" value=\"$teacher\" />\n";
}

// Date select. Both plugin admin and non-admin views.
echo html_writer::start_tag('div', array('class' => 'date-input-group'));
echo html_writer::label(get_string('datefilter', $plugin), false, array('class' => 'accesshide'));
echo '<input type="date" id="start-date" name="start_date" value="'.$start_date.'"/>';
echo html_writer::label(get_string('to'), false, array('class' => 'inline'));
echo '<input type="date" id="end-date" name="end_date" value="'.$end_date .'"/>';
echo html_writer::end_tag('div');
echo '<input type="submit" class="btn btn-primary btn-submit" value="'.get_string('getthesurveys', $plugin).'" />';
echo '</form>';


$tagvalue = get_config($plugin, 'tag_value');
$tagid = $DB->get_field('tag', 'id', array('name' => $tagvalue));
$moduleid = $DB->get_field('modules', 'id', array('name' => 'questionnaire'));

if ($coursefilter > '0') {
    $filtertype = substr($coursefilter, 0, 1);
    $filterid = substr($coursefilter, 2);
} else {
    $filtertype = 'All';
}
$content = '';
$cname = '';
if ($ctype == "M") {
  // echo 'line 130<br />';
    // Is a moodle course.
    $content = '';
    // Get teachers separated by roles.
    $context = context_course::instance($COURSE->id);
    $cname = $COURSE->fullname;
    $roles = get_config('block_questionreport', 'roles');

    // Get array of teachers.
    if (!empty($roles)) {
        $teacherroles = explode(',', $roles);
        $teachers = get_role_users(
            $teacherroles,
            $context,
            true,
            'ra.id AS raid, r.id AS roleid, r.sortorder, u.id, u.lastname, u.firstname, u.firstnamephonetic,
                            u.lastnamephonetic, u.middlename, u.alternatename, u.picture, u.imagealt, u.email',
            'r.sortorder ASC, u.lastname ASC, u.firstname ASC'
        );
    } else {
        $teachers = array();
    }
    // Get role names / aliases in course context.
    $rolenames = role_get_names($context, ROLENAME_ALIAS, true);

    // Get multiple roles config.
    // What the fuck is this even used for?!
    // TODO: Remove this if it's not used.
    $multipleroles = get_config($plugin, 'multipleroles');

    // Get the tags list.
    $sqlcourse = "SELECT m.course, m.id, m.instance
                    FROM {course_modules} m
                    JOIN {tag_instance} ti on ti.itemid = m.id
                   WHERE m.module = ".$moduleid. "
                     AND ti.tagid = ".$tagid . "
                     AND m.course = ".$courseid . "
                     AND m.deletioninprogress = 0";

    $surveys = $DB->get_record_sql($sqlcourse);
    // echo 'line 170<br />';
    if (!$surveys) {
        echo '<p>No valid survey in course.</p>';
        echo $OUTPUT->footer();
        exit();
    }
    $surveyid = $surveys->instance;

    // Get the survey results from this course.
    $displayedteachers = array();
    $sqlresp = "SELECT COUNT(r.id) crid FROM {questionnaire_response} r
                 WHERE r.questionnaireid = ".$surveyid." AND r.complete = 'y'";

    $paramscourse = array();
    if ($start_date > 0) {
        $std = strtotime($start_date);
        $sqlresp = $sqlresp . " AND submitted >= :std";
        $paramscourse['std'] = $std;
    }

    if ($end_date > 0) {
        $endtd = strtotime($end_date);
        $sqlresp = $sqlresp. " AND submitted <= :endtd";
        $paramscourse['endtd'] = $endtd;
    }

    $resp = $DB->get_record_sql($sqlresp, $paramscourse);

    $totrespcourse = $resp->crid;

    // Get the total responses.
    $partnersql = '';
    if ($partner > '') {
        $comparevalue = $DB->sql_compare_text($partner);
        $comparevalue = $comparevalue + 1;
        $partnerid = get_config($plugin, 'partnerfield');
        $partnersql = 'JOIN {customfield_data} cd ON cd.instanceid = m.course AND cd.fieldid = '.$partnerid .' AND cd.value = '.$comparevalue;
    }

    $totresp = 0;
    $sqlcourses = "SELECT m.course, m.id, m.instance
                      FROM {course_modules} m
                      JOIN {tag_instance} ti on ti.itemid = m.id ".$partnersql. "
                     WHERE m.module = ".$moduleid. "
                       AND ti.tagid = ".$tagid . "
                       AND m.deletioninprogress = 0";
    if ($filtertype == 'M') {
        $sqlcourses = $sqlcourses .' AND m.course ='.$filterid;
    }

    if ($filtertype == 'A') {
        $sqlcourses = $sqlcourses .' AND m.course = -1';
    }
    $sqltot = "SELECT COUNT(r.id) crid ";
    $fromtot = " FROM {questionnaire_response} r ";
    $wheretot = "WHERE r.questionnaireid = :questionnaireid AND r.complete = 'y' ";
    $paramstot = array();
    if ($start_date > 0) {
        $std = strtotime($start_date);
        $wheretot = $wheretot . " AND submitted >= :std";
        $paramstot['std'] = $std;
    }

    if ($end_date > 0) {
        $endtd = strtotime($end_date);
        $wheretot = $wheretot . " AND submitted <= :endtd";
        $paramstot['endtd'] = $endtd;
    }
    $slf = strlen($teacher);
    if ($slf == 0) {
        $lf = block_questionreport_checklf();
        if ($lf) {
            $teacher = $USER->id;
        }
    }
    $surveys = $DB->get_records_sql($sqlcourses);
    // echo 'line 246<br />';

    // echo '$surveys = '.print_r($surveys);
    // $is_teacher = $teacher !== ''.'<br/>';
    // echo 'is_teacher = '.!!$is_teacher.'<br/>';

    foreach ($surveys as $survey) {
        $valid = false;
        if ($is_admin || $teacher !== '') {
            $valid = true;
        }
        if ($valid && $portfolio > "" && $portfolio > '0') {
            $courseport = $DB->get_field('customfield_data', 'intvalue', array('instanceid' => $survey->course,
                                         'fieldid' => $portfieldid));
            if ($courseport != $portfolio) {
                $valid = false;
            }
        }

        if ($valid) {
            $sid = $survey->instance;
            $paramstot['questionnaireid'] = $sid;
            $sqlquestion = $sqltot . $fromtot . $wheretot;
            $respsql = $DB->get_record_sql($sqlquestion, $paramstot);
            $totresp = $respsql->crid + $totresp;
            // echo '$sqlquestion = '.$sqlquestion.'<br />';
            // echo '$respsql = '.print_r($respsql).'<br />';
            // echo '$totresp = '.$totresp.'<br />';
        }
    }

    // Add in the non moodle courses.
    $sqlext = "SELECT COUNT(ts.courseid) cdtot
                  FROM {local_teaching_survey} ts";
    $whereext = "WHERE 1 = 1";
    $paramsext = array();
    if ($start_date > 0) {
        $std = strtotime($start_date);
        $whereext = $whereext . " AND coursedate >= :std";
        $paramsext['std'] = $std;
    }

    if ($end_date > 0) {
        $endtd = strtotime($end_date);
        $whereext = $whereext . " AND coursedate <= :endtd";
        $paramsext['endtd'] = $endtd;
    }
    if ($portfolio > "") {
        $whereext = $whereext . " AND (port1id = ".$portfolio. " or port2id = ".$portfolio ." )" ;
    }
    if ($teacher > " ") {
        $whereext = $whereext . " AND (teacher1id = ".$teacher. " or teacher2id = ".$teacher ." )" ;
    }

    $sqlext = $sqlext .' '.$whereext;

    if ($filtertype == 'A') {
        $sqlext = $sqlext .' AND ts.courseid ='.$filterid;
    }

    if ($filtertype == 'M') {
        $sqlext = $sqlext .' AND ts.courseid = -1';
    }

    $respext = $DB->get_record_sql($sqlext, $paramsext);
    // echo 'line 311<br />';

    $totresp = $totresp + $respext->cdtot;
} else {
    // Not a moodle course.
    $cname = $DB->get_field('local_teaching_course', 'coursename', array('id' => $courseid));
    $sqlext = "SELECT COUNT(ts.courseid) cdtot
                  FROM {local_teaching_survey} ts";
    $whereext = "WHERE courseid = ".$courseid;
    $paramsext = array();
    if ($start_date > 0) {
        $std = strtotime($start_date);
        $whereext = $whereext . " AND coursedate >= :std";
        $paramsext['std'] = $std;
    }

    if ($end_date > 0) {
        $endtd = strtotime($end_date);
        $whereext = $whereext . " AND coursedate <= :endtd";
        $paramsext['endtd'] = $endtd;
    }
    $sqlext = $sqlext .' '.$whereext;
    // echo 'line 333<br />';
    $respext = $DB->get_record_sql($sqlext, $paramsext);
    // echo 'line 335<br />';
    $totrespcourse = $respext->cdtot;
    $sqlext = "SELECT COUNT(ts.courseid) cdtot
                  FROM {local_teaching_survey} ts";
    $whereext = "WHERE 1 = 1";
    if ($filtertype == 'A') {
        $sqlext = $sqlext .' AND ts.courseid ='.$filterid;
    }

    if ($filtertype == 'M') {
        $sqlext = $sqlext .' AND ts.courseid = -1';
    }


    $paramsext = array();
    if ($start_date > 0) {
        $std = strtotime($start_date);
        $whereext = $whereext . " AND coursedate >= :std";
        $paramsext['std'] = $std;
    }

    if ($end_date > 0) {
        $endtd = strtotime($end_date);
        $whereext = $whereext . " AND coursedate <= :endtd";
        $paramsext['endtd'] = $endtd;
    }
    $sqlext = $sqlext .' '.$whereext;
    // echo 'line 361<br />';
    $respext = $DB->get_record_sql($sqlext, $paramsext);
    // echo 'line 362<br />';
    $totresp = $respext->cdtot;
    $sqlcourses = "SELECT m.course, m.id, m.instance
                      FROM {course_modules} m
                      JOIN {tag_instance} ti on ti.itemid = m.id
                     WHERE m.module = ".$moduleid. "
                       AND ti.tagid = ".$tagid . "
                       AND m.deletioninprogress = 0";

    $sqltot = "SELECT COUNT(r.id) crid ";
    $fromtot = " FROM {questionnaire_response} r ";
    $wheretot = "WHERE r.questionnaireid = :questionnaireid AND r.complete = 'y' ";
    $paramstot = array();
    if ($start_date > 0) {
        $std = strtotime($start_date);
        $wheretot = $wheretot . " AND submitted >= :std";
        $paramstot['std'] = $std;
    }

    if ($end_date > 0) {
        $endtd = strtotime($end_date);
        $wheretot = $wheretot . " AND submitted <= :endtd";
        $paramstot['endtd'] = $endtd;
    }
    if ($filtertype == 'M') {
        $sqlcourses = $sqlcourses .' AND m.course ='.$filterid;
    }

    if ($filtertype == 'A') {
        $sqlcourses = $sqlcourses .' AND m.course = -1';
    }
    // echo 'line 392<br />';
    $surveys = $DB->get_records_sql($sqlcourses);
    // echo 'line 394<br />';
    foreach ($surveys as $survey) {
        $sid = $survey->instance;
        $paramstot['questionnaireid'] = $sid;
        $sqlquestion = $sqltot . $fromtot . $wheretot;
        $respsql = $DB->get_record_sql($sqlquestion, $paramstot);
        $totresp = $respsql->crid + $totresp;
    }
}

// Assembled data for lead facilitator table.
$data = new stdClass();
// What kind of user is it?
// $data->is_admin = $is_admin;
$data->fac_questions_info = $is_admin ? get_string('fac_questions_info_admin', $plugin) : get_string('fac_questions_info_teacher', $plugin);
$data->rate_questions_info = $is_admin ? get_string('rate_questions_info_admin', $plugin) : get_string('rate_questions_info_teacher', $plugin);
// Response data.
$data->responses = new stdClass();
$data->responses->this_course = $totrespcourse;
$data->responses->all_courses = $totresp;
echo '<br><b>Selected Course : </b><i>'. $cname. '</i>';
// Facilitator data container.
$data->facilitator = [];
// echo 'line 420<br />';
if ($ctype == 'M') {
  // echo 'line 422<br />';
    $params = array();
    // $sql = 'select min(position) mp from {questionnaire_question} where surveyid = '.$surveyid .' and type_id = 11 order by position desc';
    // $records = $DB->get_record_sql($sql, $params);
    // $stp = $records->mp;
    for ($x = 0; $x <= 1; $x++) {
        //   $pnum = $stp + $x;
        // Question
        if ($x == 0) {
            $qname = 'facilitator_rate_content';
        } else {
            $qname = 'facilitator_rate_community';
        }
        $qcontent = $DB->get_field('questionnaire_question', 'content', array('name' => $qname, 'surveyid' => $surveyid, 'type_id' => '11'));
        // Course
        // echo 'line 437<br />';
        $course = block_questionreport_get_question_results($ctype, $x, $courseid, $surveyid, $moduleid, $tagid, $start_date, $end_date, $partner, $portfolio, $teacher);
        // echo 'line 439<br />';
        $all = block_questionreport_get_question_results($ctype, $x, $coursefilter, 0, $moduleid, $tagid, $start_date, $end_date, $partner, $portfolio, $teacher);
        // echo 'line 441<br />';
        // Build object from data and assign it to the $data object passed to the template.
        $obj = new stdClass();
        $obj->question = str_replace("&nbsp;", ' ', trim(strip_tags($qcontent)));
        $obj->course = $course;
        $obj->all = $all;
        array_push($data->facilitator, $obj);
    }
} else {
    for ($x =0; $x <=1; $x++) {
        // Course
        $course = block_questionreport_get_question_results($ctype, $x, $courseid, 1, $moduleid, $tagid, $start_date, $end_date, $partner, $portfolio, $teacher);
        $all = block_questionreport_get_question_results($ctype, $x, $coursefilter, 0, $moduleid, $tagid, $start_date, $end_date, $partner, $portfolio, $teacher);
        // Build object from data and assign it to the $data object passed to the template.
        if ($x == 0) {
            $qcontent = "Please rate the following statement for each of your course facilitators: He/she/they facilitated the content clearly. ";
        } else {
            $qcontent = "Please rate the following statement for each of your course facilitators: He/she/they effectively built a community of learners. ";
        }
        $obj = new stdClass();
        $obj->question = str_replace("&nbsp;", ' ', trim(strip_tags($qcontent)));
        $obj->course = $course;
        $obj->all = $all;
        array_push($data->facilitator, $obj);
    }
}
// Container for session survey questions passed to template.
// Check to see if the user is an admin.
// $adminvalue = get_config($plugin, 'adminroles');
// $adminarray = explode(',', $adminvalue);
// check to see if they are an admin.
$adminuser = block_questionreport_is_admin(); // false;
// if (!!$is_admin) {
//     $adminuser = true;
// } else {
//     $context = context_course::instance($COURSE->id);
//     $roles = get_user_roles($context, $USER->id, true);
//     foreach ($adminarray as $val) {
//         $sql = "SELECT * FROM {role_assignments}
//        	            AS ra LEFT JOIN {user_enrolments}
//        	            AS ue ON ra.userid = ue.userid
//         	            LEFT JOIN {role} AS r ON ra.roleid = r.id
//         	            LEFT JOIN {context} AS c ON c.id = ra.contextid
//         	            LEFT JOIN {enrol} AS e ON e.courseid = c.instanceid AND ue.enrolid = e.id
//         	            WHERE r.id= ".$val." AND ue.userid = ".$USER->id. " AND e.courseid = ".$COURSE->id;
//         $result = $DB->get_records_sql($sql, array( ''));
//         if ($result) {
//             $adminuser = true;
//         }
//     }
//     // check the system roles.
//     if (!$adminuser) {
//         $systemcontext = context_system::instance();
//         $roles = get_user_roles($systemcontext, $USER->id, true);
//         foreach ($adminarray as $val) {
//             foreach ($roles as $rl) {
//                 if ($rl->roleid == $val) {
//                     $adminuser = true;
//                 }
//             }
//         }
//     }
// }

$data->session = [];
if ($ctype == 'M') {
    $qid = $DB->get_field('questionnaire_question', 'id', array('position' => '1', 'surveyid' => $surveyid, 'type_id' => '8'));
    $qcontent = $DB->get_field('questionnaire_question', 'content', array('position' => '1', 'surveyid' => $surveyid, 'type_id' => '8'));
    $qname = $DB->get_field('questionnaire_question', 'name', array('id' => $qid));

    $choices = $DB->get_records('questionnaire_quest_choice', array('question_id' => $qid));
    $choicecnt = 0;

    foreach ($choices as $choice) {
        $obj = new stdClass;
        $obj->question = $choice->content;
        $choiceid = $choice->id;
        $choicecnt = $choicecnt + 1;
        $course = block_questionreport_get_question_results_rank(
            $ctype,
            $qid,
            $choiceid,
            $courseid,
            $surveyid,
            $moduleid,
            $tagid,
            $start_date,
            $end_date,
            $partner,
            $portfolio,
            $teacher,
            $qname
        );
        $all = block_questionreport_get_question_results_rank(
            $ctype,
            $qid,
            $choicecnt,
            $coursefilter,
            0,
            $moduleid,
            $tagid,
            $start_date,
            $end_date,
            $partner,
            $portfolio,
            $teacher,
            $qname
        );
        $obj->course = $course; // TODO: Derek: Pass the actual choice values for course and all here.
        $obj->all = $all;
        array_push($data->session, $obj);
    }
    // Only display NPS if the user is an admin.
    if ($adminuser) {
        $qid = $DB->get_field('questionnaire_question', 'id', array('name' => 'NPS', 'surveyid' => $surveyid));
        $qcontent = $DB->get_field('questionnaire_question', 'content', array('name' => 'NPS', 'surveyid' => $surveyid, 'type_id' => '8'));
        $choices = $DB->get_records('questionnaire_quest_choice', array('question_id' => $qid));
        $choicecnt = 0;
        // echo "Processing NPS question. ID is {$qid}.".'<br/>';
        foreach ($choices as $choice) {
            $obj = new stdClass;
            $obj->question = "Net Promoter Score (NPS): {$choice->content}";
            $choiceid = $choice->id;
            $choicecnt = $choicecnt + 1;
            $course = block_questionreport_get_question_results_rank(
                $ctype,
                $qid,
                $choiceid,
                $courseid,
                $surveyid,
                $moduleid,
                $tagid,
                $start_date,
                $end_date,
                $partner,
                $portfolio,
                $teacher,
                'NPS'
            );
            $all = block_questionreport_get_question_results_rank(
                $ctype,
                $qid,
                $choicecnt,
                $coursefilter,
                0,
                $moduleid,
                $tagid,
                $start_date,
                $end_date,
                $partner,
                $portfolio,
                $teacher,
                'NPS'
            );
            $obj->course = $course; // TODO: Derek: Pass the actual choice values for course and all here.
            $obj->all = $all;
            array_push($data->session, $obj);
        }
    }
} else {
    $endloop = 8;
    if ($adminuser) {
        $endloop = 9;
    }
    for ($x=1; $x< $endloop; $x++) {
        $obj = new stdClass;
        switch ($x) {
            case "1":
             $quest = "I am satisfied with the overall quality of this course.";
             break;
          case "2":
             $quest = "The topics for this course were relevant for my role. ";
             break;
          case "3":
             $quest = "The independent online work activities were well-designed to help me meet the learning targets. ";
             break;
          case "4":
             $quest = "The Zoom meeting activities were well-designed to help me meet the learning targets.";
             break;
          case "5":
             $quest = "I felt a sense of community with the other participants in this course even though we were meeting virtually. ";
             break;
          case "6":
             $quest = "This course helped me navigate remote and/or hybrid learning during COVID-19. ";
             break;
          case "7":
             $quest = "I will apply my learning from this course to my practice in the next 4-6 weeks. ";
             break;
          case "8":
            $quest = "How likely are you to recommend this professional learning to a colleague or friend? ";
            break;
          }
        $obj->question = $quest;
        $course = block_questionreport_get_question_results_rank($ctype, $x, $x, $courseid, 1, $moduleid, $tagid, $start_date, $end_date, $partner);
        $all = block_questionreport_get_question_results_rank($ctype, $x, $x, $coursefilter, 0, $moduleid, $tagid, $start_date, $end_date, $partner);
        $obj->course = $course; // TODO: Derek: Pass the actual choice values for course and all here.
        $obj->all = $all;
        array_push($data->session, $obj);
    }
}
if ($ctype <> 'M') {
    $surveyid = $courseid;
}
// Return rendered template.
$content .= $OUTPUT->render_from_template('block_questionreport/report_tables', $data);
$content .= "<form class=\"questionreportform\" action=\"$CFG->wwwroot/blocks/questionreport/report.php\" method=\"get\">\n";
$content .= "<input type=\"hidden\" name=\"action\" value=\"pdf\" />\n";
$content .= "<input type=\"hidden\" name=\"cid\" value=\"$cid\" />\n";
$content .= "<input type=\"hidden\" name=\"partner\" value=\"$partner\" />\n";
$content .= "<input type=\"hidden\" name=\"start_date\" value=\"$start_date\" />\n";
$content .= "<input type=\"hidden\" name=\"end_date\" value=\"$end_date\" />\n";
$content .= "<input type=\"hidden\" name=\"question\" value=\"$questionid\" />\n";
$content .= "<input type=\"hidden\" name=\"surveyid\" value=\"$surveyid\" />\n";
$content .= "<input type=\"hidden\" name=\"coursefilter\" value=\"$coursefilter\" />\n";
$content .= "<input type=\"hidden\" name=\"teacher\" value=\"$teacher\" />\n";
$content .= '<input class="btn btn-primary btn-submit" type="submit" value="'.get_string('pdfquestion', $plugin).'" />';
$content .= '</form>';

$questionlist = block_questionreport_get_essay($ctype, $surveyid);
$removed = array_pop($questionlist); // Remove all from the array.
$content .= "<form class=\"questionreportform\" action=\"$CFG->wwwroot/blocks/questionreport/report.php\" method=\"get\">\n";
$content .= "<input type=\"hidden\" name=\"action\" value=\"view\" />\n";
$content .= "<input type=\"hidden\" name=\"cid\" value=\"$cid\" />\n";
$content .= "<input type=\"hidden\" name=\"partner\" value=\"$partner\" />\n";
$content .= "<input type=\"hidden\" name=\"start_date\" value=\"$start_date\" />\n";
$content .= "<input type=\"hidden\" name=\"end_date\" value=\"$end_date\" />\n";
$content .= html_writer::label(get_string('questionlist', $plugin), false, array('class' => 'accesshide'));
$content .= html_writer::select($questionlist, "question", $questionid, false);
$content .= '<input class="btn btn-primary btn-submit" type="submit" value="'.get_string('getthequestion', $plugin).'" />';
$content .= '</form>';



// Assemble data for word cloud.
$word_cloud = new stdClass();
// wordcount is an array.
// Array should be in the list form stipulated here:
// https://github.com/timdream/wordcloud2.js/
// [ [ "word", size], ["word", size], ... ]

if ($questionid > 0) {
    if ($ctype <> 'M') {
        $surveyid = $courseid;
    }
    $wordcount = block_questionreport_get_words($ctype, $surveyid, $questionid, $start_date, $end_date, $action, $portfolio, $teacher, $courseid);
    $default_font_size = 20; // Adjust for more words.
    $words = [];
    foreach ($wordcount as $wd) {
        $word = [];
        array_push($word, $wd->word);
        array_push($word, $wd->percent * $default_font_size);
        array_push($words, $word);
    }

    // Print wordCloud array to the page.
    $content .= '<script>';
    $content .= 'var wordCloud = '.json_encode($words).';';
    $content .= '</script>';
    // Return rendered word cloud.
    $content .= $OUTPUT->render_from_template('block_questionreport/word_cloud', $word_cloud);
}
// Build data object for text question quotes.
$quote_data = new stdClass();
// Array of text responses to render.
if ($questionid > 0) {
    $quote_data->quotes = block_questionreport_get_essay_results($ctype, $questionid, $start_date, $end_date, 0, $surveyid, $action, $portfolio, $teacher, $courseid);
}

// Return rendered quote list.
$content .= $OUTPUT->render_from_template('block_questionreport/custom_quotes', $quote_data);

echo $content;
echo $OUTPUT->footer();
