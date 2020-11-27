<?php
require_once(dirname(__FILE__).'/../../config.php');
global  $DB;
$url = '/var/www/html/m39/blocks/questionreport/sys20.csv';
if (($handle = fopen($url, "r")) !== FALSE) {
     fgetcsv($handle, ",");
     fgetcsv($handle, ",");
     $imports = array();
     while (($data = fgetcsv($handle, ",")) !== FALSE) {
            $rec = new stdClass();
            $dt = strtotime($data[1]);
            $rec->coursedate = $dt;
            // check the course id.
            if ($DB->record_exists('local_teaching_course', array ('coursename'=> $data[2]))) {
                $courseid = $DB->get_field('local_teaching_course', 'id', array ('coursename'=> $data[2]));            
            } else {
                $crec = new stdClass();
                $crec->coursename = $data[2];
                $courseid = $DB->insert_record('local_teaching_course', $crec);            
            }
           // $rec->courseid = intval($courseid);
            
            //$rec->coursename = $data[2];
            $rec->district = $data[3];
            $rec->roledesc = $data[4];
            $rec->satisfied = $data[5];
            $rec->topics = $data[6];
            $rec->online = $data[7];
            $rec->zoom = $data[8];
            $rec->community = $data[9];
            $rec->covid = $data[10];
            $rec->navigate = $data[11];
            $rec->learning = $data[12];
            $rec->practice = $data[13];
            // check the teacher
            $tname = trim($data[14]);
//            echo 'teacher name '.$tname;
            $tlen = strlen($tname);
            $teacherid = 0;
//echo ' covid '.$data[10];
//echo ' nav '.$data[11];
//exit();            
            if ($tlen > 4) {
//echo 'looking '; 
                if ($DB->record_exists('local_teaching_teacher', array ('teachername'=> $tname))) {
                    $teacherid = $DB->get_field('local_teaching_teacher', 'id', array ('teachername'=> $tname));
 //                   echo ' found '.$teacherid;            
                } else {
                    $trec = new stdClass();
                    $trec->teachername = $tname;
                    $teacherid = $DB->insert_record('local_teaching_teacher', $trec);
   //                 echo ' doing insert ';
                }            
            }

    //        $rec->teacher1id = $teacherid;
            // $rec->teacher1 = $data[14];
            $rec->content1 = $data[15];
            $rec->community1 = $data[16];
            $tname = trim($data[18]);
            $tlen = strlen($tname);
            $teacher2id = 0;
            if ($tlen > 4) {
                if ($DB->record_exists('local_teaching_teacher', array ('teachername'=> $tname))) {
                    $teacher2id = $DB->get_field('local_teaching_teacher', 'id', array ('teachername'=> $tname));            
                } else {
                    $trec = new stdClass();
                    $trec->teachername = $tname;
                    $teacher2id = $DB->insert_record('local_teaching_teacher', $trec);
                }            
            }
      //      $rec->teacher2id = $teacherid;

            //$rec->teacher2 = $data[18];
            $rec->content2 = $data[19];
            $rec->community2 = $data[20];
            $rec->overall = $data[21];
            $rec->improved = $data[23];
            $rec->reccomend = $data[24];
            $rec->choose = $data[25];
            $rec->comment = $data[26];
            //$rec->courseid = intval($courseid);
            $pname = trim($data[28]);
            $plen = strlen($pname);
            $portid = 0;
            if ($plen > 4) {
                if ($DB->record_exists('local_teaching_port', array ('portname'=> $tname))) {
                    $portid = $DB->get_field('local_teaching_port', 'id', array ('portname'=> $tname));            
                } else {
                    $prec = new stdClass();
                    $prec->portname = $tname;
                    $portid = $DB->insert_record('local_teaching_port', $prec);
                }            
            }
  //          $rec->port1id = $portid;

            $pname = trim($data[29]);
            $plen = strlen($pname);
            $port2id = 0;
            if ($plen > 4) {
                if ($DB->record_exists('local_teaching_port', array ('portname'=> $tname))) {
                    $port2id = $DB->get_field('local_teaching_port', 'id', array ('portname'=> $tname));            
                } else {
                    $prec = new stdClass();
                    $prec->portname = $tname;
                    $port2id = $DB->insert_record('local_teaching_port', $prec);
                }            
            }
//            $rec->port2id = $portid;
//            var_dump($rec);
//            exit();
//            $rec->port1 = $data[28];
           // $rec->port2 = $data[29];
              $lastrecord = $DB->insert_record('local_teaching_survey', $rec);
              $DB->set_field('local_teaching_survey', 'courseid', $courseid, array('uidsurvey' => $lastrecord));
              $DB->set_field('local_teaching_survey', 'port1id', $portid, array('uidsurvey' => $lastrecord));
              $DB->set_field('local_teaching_survey', 'port2id', $port2id, array('uidsurvey' => $lastrecord));
              $DB->set_field('local_teaching_survey', 'teacher1id', $teacherid, array('uidsurvey' => $lastrecord));
              $DB->set_field('local_teaching_survey', 'teacher2id', $teacher2id, array('uidsurvey' => $lastrecord));

//exit();
      }
  }
echo 'done';


