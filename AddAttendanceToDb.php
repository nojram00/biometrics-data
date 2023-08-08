<?php
require_once '../Databases/CustomQuery.php';
require_once '../Models/TimeIn.php';
require_once '../Models/TimeOut.php';

require_once '../Models/NewAttendance.php';
require_once 'NewRecordsHandler.php';

use Db\CustomQuery;
use BiometricsData\NewRecords;

date_default_timezone_set('Asia/Manila');

$records = NewRecords::NewFindRecords('12345', ip:'http://192.168.0.143:8090');

$timeIn = new TimeIn();
$timeOut = new TimeOut();

$all = $records->All();
// var_dump($all);
foreach($all as $r){
    $date = date('Y-m-d', $r['time']/1000);
    $recs = NewRecords::NewFindRecords('12345', $r['personId'], ip:'192.168.0.143:8090');
    $time_in = $recs->GetTimeInWithinThisDate($date);
    $time_out = $recs->GetTimeOutWithinThisDate($date);

    if($r['attendance']['attendanceStatus'] == 'Time In'){
        $c = CustomQuery::SelectAll('attendance_time_in')
            ->Where('time_in_personId', $r['personId'])
            ->Where('date_time_in', $date)
            ->Execute();
        if($c['rowCount'] <= 0){
            $timeIn->create([
                'time_in_personId' => $r['personId'],
                'date_time_in' => $date,
                'time_in' => $time_in
            ]);
        }
    }
    if($r['attendance']['attendanceStatus'] == 'Time Out'){
        $c = CustomQuery::SelectAll('attendance_time_out')
            ->Where('time_out_personId', $r['personId'])
            ->Where('date_time_out', $date)
            ->Execute();
        if($c['rowCount'] <= 0){
            $timeOut->create([
                'time_out_personId' => $r['personId'],
                'date_time_out' => $date,
                'time_out' => $time_out
            ]);
        }
    }
}
