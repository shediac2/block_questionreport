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
 * Block "questionreport" - Local library
 *
 * @package    block_people
 * @copyright  2017 Kathrin Osswald, Ulm University <kathrin.osswald@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Function to generate the feedback reports.
function block_questionreport_genfeedback($reportnum, $yrnum, $port) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/pdflib.php');
    $content = '';
    $plugin = 'block_questionreport';
    if ($reportnum == '0') {
        return $content;
        exit();
    }
    // Generate the feedback reports
    // report1 = 'Participant feedback, by month';
    // report2 = 'Participant feedback, by portfolio';
    // report3 = 'Participant feedback, by partner site';
     $qlist = array();
     $qlist[1] = 'I am satisfied with the overall quality of this course';
     $qlist[2] = 'The topics for this course were relevant for my role.';
     $qlist[3] = 'The independent online work activities were well-designed to help me meet the learning targets';
     $qlist[4] = 'The Zoom meeting activities were well-designed to help me meet the learning targets';
     $qlist[5] = 'I felt a sense of community with the other participants in this course even though we were meeting virtually.';
     $qlist[6] = 'This course helped me navigate remote and/or hybrid learning during COVID-19.';
     $qlist[7] = 'I will apply my learning from this course to my practice in the next 4-6 weeks';
     $qlist[8] = 'Recommend this course to a colleague or friend';
     $qlist[100] = 'He/she/they facilitated the content clearly. ';
     $qlist[101] = 'He/she/they effectively built a community of learners.';
     switch($reportnum) { 
        case "1":
          $val = '0';
          $yr2 = $yrnum + 1;
          $header = get_string('report1', $plugin);
          $content = '<h1><p>'.$header.'</p></h1><br>';
          $content .= '<table style="width:100%;" border="1" cellspacing="0" cellpadding="4"><tr><th></th>';
          $tablestart = '<th style="background-color:#ccf5ff">01_JUN'.$yrnum.'</th><th style="background-color:#ccf5ff">02_JUL'.$yrnum.'</th>'.
                                 '<th style="background-color:#ccf5ff">03_AUG'.$yrnum.'</th><th style="background-color:#ccf5ff">04_SEP'.$yrnum.'</th>'.
                                 '<th style="background-color:#ccf5ff">05_OCT'.$yrnum.'</th><th style="background-color:#ccf5ff">06_NOV'.$yrnum.'</th>'.
                                 '<th style="background-color:#ccf5ff">07_DEC'.$yrnum.'</th><th style="background-color:#ccf5ff">08_JAN'.$yr2.'</th>'.
                                 '<th style="background-color:#ccf5ff">09_FEB'.$yr2.'</th><th style="background-color:#ccf5ff">03_MAR'.$yr2.'</th>'.
                                 '<th style="background-color:#ccf5ff">10_APR'.$yr2.'</th><th style="background-color:#ccf5ff">11_MAY'.$yr2.'</th>
                                 <th style="background-color:#ccf5ff">Grand Total</th></tr>';
          $content = $content.$tablestart;
          $line1 = '<tr><td>Number of Survey Responses</td>';
          for ($mnlist = 6; $mnlist < 13; $mnlist ++) {
          	   $stdate = '01-'.$mnlist.'-'.$yrnum;
          	   if ($mnlist < 12) {
          	   	 $nm2 = $mnlist + 1; 
                   $nddate = '01-'.$nm2.'-'.$yrnum;
               } else {
                   $nddate = '01-01-'.$yr2;
               }
               $mn1 = block_questionreport_choicequestion(0, $stdate, $nddate, 0, $val, $port);
               $line1 .= '<td>'.$mn1.'</td>';
          }
          for ($mnlist = 1; $mnlist < 6; $mnlist ++) {
          	   $stdate = '01-'.$mnlist.'-'.$yr2;
          	   $nm2 = $mnlist + 1;
               $nddate = '01-'.$nm2.'-'.$yr2;
               $mn1 = block_questionreport_choicequestion(0, $stdate, $nddate, 0, $val, $port) ;
               $line1 .= '<td>'.$mn1.'</td>';
               
          }
          $stdate = '01-06-'.$yrnum;
          $nddate = '01-06-'.$yr2;
          $mn1 = block_questionreport_choicequestion(0, $stdate, $nddate, 0, $val, $port);
          $line1 .= '<td>'.$mn1.'</td>';

          $content = $content .$line1.'</tr><tr><td colspan = "14">&nbsp;</td></tr>';
          $content = $content.'<tr><th><b>Session Summary (% Agree and Strongly Agree)</b></th>'.$tablestart;
          for ($ql = 1; $ql < 9; $ql++) {
               $line1 = '<tr><td>'.$qlist[$ql].'</td>';
               for ($mnlist = 6; $mnlist < 13; $mnlist ++) {
          	        $stdate = '01-'.$mnlist.'-'.$yrnum;
          	        if ($mnlist < 12) {
                   	   $nm2 = $mnlist + 1;          	        	
                        $nddate = '01-'.$nm2.'-'.$yrnum;
                    } else {
                        $nddate = '01-01-'.$yr2;
                    }
                    $mn1 = block_questionreport_choicequestion($ql, $stdate, $nddate, 0, $val, $port);
                    $line1 .= '<td>'.$mn1.'</td>';
               }
               for ($mnlist = 1; $mnlist < 6; $mnlist ++) {
          	        $stdate = '01-'.$mnlist.'-'.$yr2;
              	     $nm2 = $mnlist + 1;
                    $nddate = '01-'.$nm2.'-'.$yr2;
                    $mn1 = block_questionreport_choicequestion($ql, $stdate, $nddate, 0, $val, $port);
                    $line1 .= '<td>'.$mn1.'</td>';
               }
               $stdate = '01-06-'.$yrnum;
               $nddate = '01-06-'.$yr2;
               $mn1 = block_questionreport_choicequestion($ql, $stdate, $nddate, 0, $val, $port);
               $line1 .= '<td>'.$mn1.'</td>';
               
               $content = $content .$line1.'</tr>';            
          }
          $content = $content .'<tr><td colspan = "14">&nbsp;</td></tr>';
          $content = $content.'<tr><th><b>Facilitation Summary (% Agree and Strongly Agree)</b></th>'.$tablestart;
          for ($ql = 100; $ql < 102; $ql++) {
               $line1 = '<tr><td>'.$qlist[$ql].'</td>';
               for ($mnlist = 6; $mnlist < 13; $mnlist ++) {
          	        $stdate = '01-'.$mnlist.'-'.$yrnum;
          	        if ($mnlist < 12) {
                   	   $nm2 = $mnlist + 1;          	        	
                        $nddate = '01-'.$nm2.'-'.$yrnum;
                    } else {
                        $nddate = '01-01-'.$yr2;
                    }
                    $mn1 = block_questionreport_choicequestion($ql, $stdate, $nddate, 0, $val, $port);
                    $line1 .= '<td>'.$mn1.'</td>';
               }
               for ($mnlist = 1; $mnlist < 6; $mnlist ++) {
          	        $stdate = '01-'.$mnlist.'-'.$yr2;
              	     $nm2 = $mnlist + 1;
                    $nddate = '01-'.$nm2.'-'.$yr2;
                    $mn1 = block_questionreport_choicequestion($ql, $stdate, $nddate, 0, $val, $port);
                    $line1 .= '<td>'.$mn1.'</td>';
               }
               $stdate = '01-06-'.$yrnum;
               $nddate = '01-06-'.$yr2;
               $mn1 = block_questionreport_choicequestion($ql, $stdate, $nddate, 0, $val, $port);
               $line1 .= '<td>'.$mn1.'</td>';

               $content = $content .$line1.'</tr>';            
          }
          $content = $content.'</table>';
        	 $doc = new pdf;
          $doc->setPrintFooter(false);
          $doc->setFont('helvetica',' ', '4');
          $doc->SetFillColor(0,255,0);
          $doc->AddPage();
          $doc->writeHTML($content, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = '');
          $name = 'Participant feedback, by portfolio_for_year'.$yrnum.'.pdf';
          $doc->Output($name);
          exit();                                      
          break;
        case "2":
          $header = get_string('report2', $plugin);
          $portfieldid = get_config($plugin, 'portfoliofield');
          $data = $DB->get_field('customfield_field', 'configdata', array('id' => $portfieldid));
          $content = '<h1><p>'.$header.'</p></h1><br>';
          $x = json_decode($data);
          $opts = $x->options;
          $options_old = preg_split("/\s*\n\s*/", $opts);
          $x = 1;
          $content .= '<table style="width:100%;" border="1" cellspacing="0" cellpadding="4"><tr><th></th>';
          $tablestart = '';
          foreach($options_old as $val) {
          	 $tablestart .='<th style="background-color:#ccf5ff">'.$val.'</th>';
          }

       	 $tablestart .='<th style="background-color:#ccf5ff">Grand Total</th>';
          $content .= $tablestart.'</tr>';
          $line1 = '<tr><td>Number of Survey Responses</td>';
          $yr2 = $yrnum + 1;
          $stdate = '01-06-'.$yrnum;
          $nddate = '01-06-'.$yr2;
          $x = 0;
          $rows = 0;
          foreach($options_old as $val) {
          	   $x = $x + 1;
               $mn1 = block_questionreport_choicequestion(0, $stdate, $nddate, $x, $val, $port);
               $line1 .= '<td>'.$mn1.'</td>';
          }
          $rows = $x;
          $x = '0';
          $mn1 = block_questionreport_choicequestion(0, $stdate, $nddate, $x, '', $port);
          $line1 .= '<td>'.$mn1.'</td></tr>';
          $content .= $line1;
          $content .= '<tr><td colspan = "'.$rows.'">&nbsp;</td></tr>';
          $content = $content.'<tr><th><b>Session Summary (% Agree and Strongly Agree)</b></th>'.$tablestart.'</tr>';
          for ($ql = 1; $ql < 9; $ql++) {
               $line1 = '<tr><td>'.$qlist[$ql].'</td>';
               $x = 0;
               foreach($options_old as $val) {
          	  $x = $x + 1;
                  $mn1 = block_questionreport_choicequestion($ql, $stdate, $nddate, $x, $val, $port);
                  $line1 .= '<td>'.$mn1.'</td>';
               }
               $mn1 = block_questionreport_choicequestion($ql, $stdate, $nddate, 0, $val, $port);
               $line1 .= '<td>'.$mn1.'</td>';
               $content = $content .$line1.'</tr>';
          }
          $content = $content .'<tr><td colspan = "'.$rows.'">&nbsp;</td></tr>';

          $content = $content.'<tr><th><b>Facilitation Summary (% Agree and Strongly Agree)</b></th>'.$tablestart.'</tr>';
          for ($ql = 100; $ql < 102; $ql++) {
               $x = 0;
               $line1 = '<tr><td>'.$qlist[$ql].'</td>';
               foreach($options_old as $val) {
          	      $x = $x + 1;
                  $mn1 = block_questionreport_choicequestion($ql, $stdate, $nddate, $x, $val, $port);
                  $line1 .= '<td>'.$mn1.'</td>';
               }
               $mn1 = block_questionreport_choicequestion($ql, $stdate, $nddate, 0, $val, $port);
               $line1 .= '<td>'.$mn1.'</td>';

               $content = $content .$line1.'</tr>';            
          }
          $content = $content.'</table>';
       	  $doc = new pdf;
          $doc->setPrintFooter(false);
          $doc->setFont('helvetica',' ', '4');
          $doc->SetFillColor(0,255,0);
          $doc->AddPage();
          $doc->writeHTML($content, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = '');
          $name = 'Feedback_by_month_'.$yrnum.'.pdf';
          $doc->Output($name);
          exit();                                      
                
          echo $content;          
          break;
       case "3":
          $header = get_string('report3', $plugin);
          $portfieldid = get_config($plugin, 'portfoliofield');
          $data = $DB->get_field('customfield_field', 'configdata', array('id' => $portfieldid));
          $content = '<h1><p>'.$header.'</p></h1><br>';
          $fieldid = get_config($plugin, 'partnerfield');
          $data = $DB->get_field('customfield_field', 'configdata', array('id' => $fieldid));
          $options = array();
          $x = json_decode($data);
          $opts = $x->options;
          $options = preg_split("/\s*\n\s*/", $opts);
          $content .= '<table style="width:100%;" border="1" cellspacing="0" cellpadding="4"><tr><th></th>';
          $tablestart = '';
          foreach($options as $val) {
          	 $tablestart .='<th style="background-color:#ccf5ff">'.$val.'</th>';
          }
          $tablestart .= '</tr>';
          $content .= $tablestart;
          $line1 = '<tr><td>Number of Survey Responses</td>';
          $yr2 = $yrnum + 1;
          $stdate = '01-06-'.$yrnum;
          $nddate = '01-06-'.$yr2;
          $x = 0;
          $rows = 0;
          foreach($options as $val) {
       	       $x = $x + 1;
               $mn1 = block_questionreport_choicequestion(0, $stdate, $nddate, $port, $val, $x);
               $line1 .= '<td>'.$mn1.'</td>';
          }
          $rows = $x;
          $x = '0';
          $mn1 = block_questionreport_choicequestion(0, $stdate, $nddate, $port, '', $x);
          $line1 .= '<td>'.$mn1.'</td></tr>';
          $content .= $line1;
          $content .= '<tr><td colspan = "'.$rows.'">&nbsp;</td></tr>';

         // $content = $content.'<tr><th><b>Session Summary (% Agree and Strongly Agree)</b></th></tr>';

          for ($ql = 1; $ql < 9; $ql++) {
               $line1 = '<tr><td>'.$qlist[$ql].'</td>';
               $x = 0;
               foreach($options as $val) {
       	          $x = $x + 1;
                  $mn1 = block_questionreport_choicequestion($ql, $stdate, $nddate, $port, $val, $x);
                  $line1 .= '<td>'.$mn1.'</td>';
               }
               $mn1 = block_questionreport_choicequestion($ql, $stdate, $nddate, $port, '', $x);
               $line1 .= '<td>'.$mn1.'</td>';
               $content = $content .$line1.'</tr>';
          }

          $content = $content .'<tr><td colspan = "'.$rows.'">&nbsp;</td></tr>';

         // $content = $content.'<tr><th><b>Facilitation Summary (% Agree and Strongly Agree)</b></th>'.$tablestart.'</tr>';

          for ($ql = 100; $ql < 102; $ql++) {
               $x = 0;
               $line1 = '<tr><td>'.$qlist[$ql].'</td>';
               foreach($options as $val) {
          	  $x = $x + 1;
                  $mn1 = block_questionreport_choicequestion($ql, $stdate, $nddate, $port, $val, $x);
                  $line1 .= '<td>'.$mn1.'</td>';
               }
               $mn1 = block_questionreport_choicequestion($ql, $stdate, $nddate, $port, '', $x);
               $line1 .= '<td>'.$mn1.'</td>';

               $content = $content .$line1.'</tr>';
          }

          $content = $content.'</table>';
       	   $doc = new pdf;
          $doc->setPrintFooter(false);
          $doc->setFont('helvetica',' ', '4');
          $doc->SetFillColor(0,255,0);
          $doc->AddPage();
          $doc->writeHTML($content, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = '');
          $name = 'Participant Feedback by partner site for'.$yrnum.'.pdf';
          $doc->Output($name);
          exit();
          break;
     }
}
// Function to return the % of question

function block_questionreport_choicequestion($qnum, $stdate, $nddate, $portfolio, $portdisplay, $partner) {
   global $DB;
   $paramsql = array();
   $paramsext = array();
   $fromressql = "" ;
   $whereressql = " ";
   $where1 = " ";
   $whereext = " ";
   $plugin = 'block_questionreport';
   if ($partner > 0 and $portfolio > '') {
   	 // get the name we need.
       $plugin = 'block_questionreport';
       $portfieldid = get_config($plugin, 'portfoliofield');
       $data = $DB->get_field('customfield_field', 'configdata', array('id' => $portfieldid));
       $x = json_decode($data);
       $opts = $x->options;
       $options_old = preg_split("/\s*\n\s*/", $opts);
       $x = 0;
       foreach($options_old as $val) {
     	   $x = $x + 1;
           if ($x == $partner) {
               $portval = $val;
           }
       }
   }
   switch ($qnum) {
      case "0" :
        $mdlsql = "WHERE content = 'I am satisfied with the overall quality of this course.'";
        $nonmdl = "WHERE  1 = 1";
        break;
      case "1":
        $mdlsql = "AND content = 'I am satisfied with the overall quality of this course.'";
        $nonmdl = "WHERE satisfied >=4";
        break;
      case "2":
        $mdlsql = "AND content = 'The topics for this course were relevant for my role.'";
        $nonmdl = "WHERE topics >=4";
        break;
      case "3":
        $mdlsql = "AND content = 'The independent online work activities were well-designed to help me meet the learning targets.'";
        $nonmdl = "WHERE online >=4";
        break;
      case "4":
        $mdlsql = "AND content = 'The Zoom meeting activities were well-designed to help me meet the learning targets.'";
        $nonmdl = "WHERE zoom >=4";
        break;
      case "5":
        $mdlsql = "AND content = 'I felt a sense of community with the other participants in this course even though we were meeting virtually.'";
        $nonmdl = "WHERE community >=4";
        break;
      case "6":
        $mdlsql = "AND content = 'This course helped me navigate remote and/or hybrid learning during COVID-19.'";
        $nonmdl = "WHERE covid >=4";
        break;
      case "7":
        $mdlsql = "AND content = 'I will apply my learning from this course to my practice in the next 4-6 weeks.'";
        $nonmdl = "WHERE practice >=4";
        break;
      case "8":
        $mdlsql = "AND content = 'Recommend this course to a colleague or friend.'";
        $nonmdl = "WHERE reccomend >=9";
        $nonmdl1 = " WHERE reccomend <=6";
        break;
     case "100" :
        $qname = "facilitator_rate_content";  
        $mdlsql = " AND 1 = 1";
        $nonmdl = " where (content1 >=4 or content2 >=4) ";
        break;
     case "101" :
        $mdlsql = " AND 1 = 1";
        $qname = "facilitator_rate_community";
        $nonmdl = " where (community1 >=4 or community2 >=4) ";
        break;   
   }
  
   if ($stdate > 0) {
       $fromressql = $fromressql .' JOIN {questionnaire_response} qr on qr.id = mr.response_id';
       $whereressql = $whereressql . ' AND qr.submitted >= :stdate';
       $std = strtotime($stdate);
       $whereext = $whereext . " AND coursedate >= :std";
       $where1 = $where1 . " AND coursedate >= :std";
       $paramsext['std'] = $std;
       $paramsql['stdate'] = $std;
   }
   if ($nddate > 0) {
       $fromressql = $fromressql .' JOIN {questionnaire_response} qr2 on qr2.id = mr.response_id';
       $whereressql = $whereressql . ' AND qr2.submitted <= :nddate';
       $ndt = strtotime($nddate);
       $paramsql['nddate'] = $ndt;
       $whereext = $whereext . " AND coursedate < :endtd";
       $where1 = $where1 . " AND coursedate < :endtd";
       $paramsext['endtd'] = $ndt;
   }
   if ($partner > 0 ) {
       $whereext = $whereext . " AND ( district = :district )";
       $paramsext['district'] = $portdisplay;
       if ($portfolio > '') {
           $whereext = $whereext . " AND ( port1name = :port1name or port2name = :port2name)";
           $paramsext['port1name'] = $portval;
           $paramsext['port2name'] = $portval;
       }
   } else {
       if ($portfolio > '0') {
           $whereext = $whereext . " AND ( port1name = :port1name or port2name = :port2name)";
           $paramsext['port1name'] = $portdisplay;
           $paramsext['port2name'] = $portdisplay;
       }
   }
   // Get the total responses.
   $sqlext = "SELECT COUNT(ts.courseid) cdtot
              FROM {local_teaching_survey} ts ";
   $sqlext = $sqlext .$nonmdl.$whereext;
   $sqlnonmoodle = $DB->get_record_sql($sqlext, $paramsext);
   $cntnonmoodle = $sqlnonmoodle->cdtot;
   $sqlmoodle = " SELECT COUNT(qr.id) crid 
                    FROM {questionnaire_response} qr 
                    JOIN {questionnaire} q on q.id = qr.questionnaireid ";
   if ($partner > 0) {
       $fieldid = get_config($plugin, 'partnerfield');
       $partnerid = $DB->get_field('customfield_field', 'configdata', array('id' => $fieldid));
       $sqlmoodle = $sqlmoodle. " JOIN {customfield_data} cd on cd.instanceid = q.course 
                                  AND cd.fieldid = ".$fieldid . "
                                  AND cd.intvalue = ".$partner;
       if ($portfolio > '0') {
           $portfieldid = get_config($plugin, 'portfoliofield');
           $sqlmoodle = $sqlmoodle. " JOIN {customfield_data} cd1 on cd1.instanceid = q.course 
                                  AND cd1.fieldid = ".$portfieldid . "
                                  AND cd1.intvalue = ".$portfolio;
       }
  } else {
       if ($portfolio > '0') {
           $sqlmoodle = $sqlmoodle ." JOIN {customfield_data} cd on cd.instanceid = q.course and cd.value=".$portfolio;     
       }
   }
   $sqlmoodle = $sqlmoodle. " WHERE q.name = 'End-of-Course Survey' 
                     AND qr.complete = 'y'
                     AND qr.submitted >= :stdate 
                     AND qr.submitted < :nddate";
   $sqlrecmoodle = $DB->get_record_sql($sqlmoodle, $paramsql);
   $cntmoodle = $sqlrecmoodle->crid;
   if ($qnum == 0) {
       $val = $cntmoodle + $cntnonmoodle;
       return $val;   
   } else {
       $ans1 = 0;
       $ans1a = 0;        
       $val = $cntmoodle + $cntnonmoodle;
       if ($cntmoodle > 0 ) {
           if ($qnum < 100 )  {  	
               $sqlmoodle = " select count(rankvalue) crid
                              from {questionnaire_quest_choice} qc
                              join {questionnaire_response_rank} qr on qr.question_id = qc.question_id
                              join {questionnaire_response} q on q.id = qr.response_id";
               if ($partner > 0) {
                   $fieldid = get_config($plugin, 'partnerfield');
                   $partnerid = $DB->get_field('customfield_field', 'configdata', array('id' => $fieldid));
                   $sqlmoodle = $sqlmoodle. " 
                                  JOIN {questionnaire} qm on qm.id = q.questionnaireid
                                  JOIN {customfield_data} cd on cd.instanceid = qm.course 
                                  AND cd.fieldid = ".$fieldid . "
                                  AND cd.intvalue = ".$partner;
                  if ($portfolio > '0') {
                      $portfieldid = get_config($plugin, 'portfoliofield');
                      $sqlmoodle = $sqlmoodle. " JOIN {customfield_data} cd1 on cd1.instanceid = qm.course 
                                                   AND cd1.fieldid = ".$portfieldid . "
                                                   AND cd1.intvalue = ".$portfolio;
                  } 
               } else {                
                    if ($portfolio > '0') {
                       $sqlmoodle = $sqlmoodle ." JOIN {questionnaire} qu on qu.id = q.questionnaireid 
                                                  JOIN {customfield_data} cd on cd.instanceid = qu.course
                                                  AND cd.value=".$portfolio;
                    }
               }
               $sqlmoodle = $sqlmoodle ."               
                          and qc.id = qr.choice_id
                          AND q.submitted >= :stdate 
                          AND q.submitted < :nddate";
                if ($qnum != 8) {
                    $sqlmoodle = $sqlmoodle . " AND (rankvalue = 4 or rankvalue = 5) ";          
                } else {
                     $sqlmoodle2 = $sqlmoodle . " AND (rankvalue <= 6 ) ".$mdlsql;                  
                     $sqlmoodle = $sqlmoodle . " AND (rankvalue = 9 or rankvalue = 10) ";                  
               }
           }  else {
            	  $sqlmoodle = "SELECT distinct(qr.id) 
                                 FROM {questionnaire_response_rank} mr 
                                 JOIN {questionnaire_question} qu 
                                  ON qu.id = mr.question_id                                        
                                JOIN {questionnaire_response} qr on qr.id = mr.response_id";
                  $wheresql = "  WHERE qu.name = '".$qname. "'
                                 AND (rankvalue = 4 or rankvalue = 5)
                                 AND qr.submitted >= :stdate 
                                 AND qr.submitted < :nddate";
                  if ($partner > 0) {
                      $fieldid = get_config($plugin, 'partnerfield');
                      $partnerid = $DB->get_field('customfield_field', 'configdata', array('id' => $fieldid));
                      $sqlmoodle = $sqlmoodle. " 
                                     JOIN {questionnaire} qm on qm.id = qr.questionnaireid
                                     JOIN {customfield_data} cd on cd.instanceid = qm.course 
                                     AND cd.fieldid = ".$fieldid . "
                                     AND cd.intvalue = ".$partner;
                      if ($portfolio > '0') {
                          $portfieldid = get_config($plugin, 'portfoliofield');
                          $sqlmoodle = $sqlmoodle. " JOIN {customfield_data} cd1 on cd1.instanceid = qm.course 
                                                      AND cd1.fieldid = ".$portfieldid . "
                                                      AND cd1.intvalue = ".$portfolio;
                      } 
                  }
                  $sqlmoodle = $sqlmoodle.$wheresql;
//                  echo $sqlmoodle;
  //                exit();        
               } 
             if ($qnum < 100) {                           
                 $sqlmoodle = $sqlmoodle ." ".$mdlsql;
                 $sqlrecans = $DB->get_record_sql($sqlmoodle, $paramsql);
             }
//             $sqlrecans = $DB->get_record_sql($sqlmoodle, $paramsql);
             if ($qnum == 8) {
                 $sqlrecans2 = $DB->get_record_sql($sqlmoodle2, $paramsql);
                 $ans1a = $sqlrecans2->crid;        
             }
             if ($qnum < 100) {
                 $sql2 = $DB->get_record_sql($sqlmoodle, $paramsql);
                 $ans1 = $sql2->crid;
             } else {
                  $recs = $DB->get_records_sql($sqlmoodle, $paramsql);
                  $ans1 = count($recs);
             }
        }
     }
        $sqlext = "SELECT COUNT(ts.courseid) cdtot
                   FROM {local_teaching_survey} ts ";
        if ($qnum == 8) {
            $sqlext2 = $sqlext .$nonmdl1. $whereext; 
            $sqlrecans2a = $DB->get_record_sql($sqlext2, $paramsext);
            $ans2a = $sqlrecans2a->cdtot;
        }
        
        $sqlext = $sqlext .$nonmdl.$whereext;
        $sqlrecans2 = $DB->get_record_sql($sqlext, $paramsext);
        $ans2 = $sqlrecans2->cdtot;

 //       if ($qnum == 8) {
//        	echo '<br> sqlans2a '.$sqlext2;
        	
//echo '<br> ans2 '.$sqlext; 
//var_dump($paramsext);       	
//            echo ' ans2 '.$ans2 . ' ans2a '.$ans2a . 'ans1 '.$ans1 . 'ans1a'. $ans1a;
//            echo '<br> val '.$val;
 //           exit();                  
  //      }              
//        $ans2 = $sqlrecans2->cdtot;
        if ($val > 0) {
            if ($qnum <> 8 ) {
                $totgood = $ans1 + $ans2;
                $val = ($totgood / $val) * 100;
                $ret = round($val, 0)."(%)";
            } else {
                $ans1 = $ans1 + $ans2;
                $ans1a = $ans1a + $ans2a;
                $ans1 = ($ans1 / $val) * 100;
                $ans1a = ($ans1a / $val) * 100;
                $ret = $ans1 - $ans1a;
                $ret = round($ret, 0);   
            }
            return $ret;
        } else {
           return '-';
        }
   }
