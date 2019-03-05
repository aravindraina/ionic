<?php
namespace App\Controllers;
use Psr\Container\ContainerInterface;
session_start();

class CourseController
{
   protected $container;
   public function __construct(ContainerInterface $container) {
     $this->container = $container;
   }
   public function listcourse($request, $response, $args) {
    if( $_SESSION['type'] == 1){
      $result = $this->container->db->query("SELECT * FROM ioniccloud.course;");
      $results = [];
      $schoolid = $_SESSION['school_id'];
      $batchresult = $this->container->db->query("SELECT * FROM ioniccloud.batch where school = '$schoolid';");
      $batchresults = [];

      $lessonresult = $this->container->db->query("SELECT * FROM ioniccloud.lesson;");
      $lessonresults = [];
      $noOfLessons = [];
      while($lessonrow = mysqli_fetch_array($lessonresult)) {
        array_push($lessonresults,$lessonrow);
      }

      while($batchrow = mysqli_fetch_array($batchresult)) {
        $studentids = json_decode($batchrow['student'], true);
        foreach ($studentids as $studentid){   
              if($studentid === $_SESSION['student_id']){
                array_push($batchresults,$batchrow);
            }
        } 
      }

      while($row = mysqli_fetch_array($result)) {
        foreach($batchresults as $batch){
          if($row['id']===$batch['course_id']){
            $count = 0;
            foreach($lessonresults as $lesson){
              if($row['id']===$lesson['course_id']){
                $count = $count + 1;
              }
            }
            $row['nooflesson'] = $count;
            array_push($results,$row);
          }
        }
      }
    }else{
      $result = $this->container->db->query("SELECT * FROM ioniccloud.course;");
      $results = [];
      while($row = mysqli_fetch_array($result)) {
        array_push($results,$row);
      }
    }
    return json_encode($results);
  }

  
  public function addcourse($request, $response, $args) 
  {
    $data = $request->getParsedBody();
    $name = filter_var($data['cname'], FILTER_SANITIZE_STRING);
    $type = filter_var($data['ctype'], FILTER_SANITIZE_STRING);
    $duration = filter_var($data['duration'], FILTER_SANITIZE_STRING);
    $value = $data['frequency'];
    $sessions = filter_var($data['sessions'], FILTER_SANITIZE_STRING);
    $sqli = $this->container->db;
    $result = $sqli->query("insert into ioniccloud.course (name, type, duration, printing, session ) 
    VALUES ('$name','$type','$duration','$value','$sessions')");
    if (mysqli_affected_rows($sqli)==1) {
      return $this->container->renderer->render($response, 'index.php', array('redirect'=>'manage-course'));
    }
    
    return $this->container->renderer->render($response, 'index.php', array('redirect'=>'add-course'));
    }
}
?>