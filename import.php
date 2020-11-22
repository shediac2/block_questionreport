<?php
require_once(dirname(__FILE__).'/../../config.php');
global  $DB;
$url = '/var/www/html/m39/blocks/questionreport/sys20.csv';
if (($handle = fopen($url, "r")) !== FALSE) {
     fgetcsv($handle, ",");
     fgetcsv($handle, ",");
     $imports = array();
     while (($data = fgetcsv($handle, ",")) !== FALSE) {
 //           var_dump($data);
            $rec = new stdClass();
            $dt = strtotime($data[1]);
            $rec->coursedate = $dt;
            $rec->coursename = $data[2];
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
            $rec->teacher1 = $data[14];
            $rec->content1 = $data[15];
            $rec->community1 = $data[16];
            $rec->teacher2 = $data[18];
            $rec->content2 = $data[19];
            $rec->community2 = $data[20];
            $rec->overall = $data[21];
            $rec->improved = $data[23];
            $rec->reccomend = $data[24];
            $rec->choose = $data[25];
            $rec->comment = $data[26];
            $lastrecord = $DB->insert_record('local_teaching_survey', $rec);
      }
  }
echo 'done';

