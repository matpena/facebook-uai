<?php
$idCurso = $_GET['cid'];

require_once (dirname(dirname(dirname(__FILE__)))."/config.php");
require_once ($CFG->dirroot."/local/facebook/locallib.php");
require_once ($CFG->dirroot."/local/facebook/forms.php");


include "app/config.php";
use Facebook\FacebookResponse;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequire;
global $DB, $USER, $CFG, $COURSE; 

$idProf = $USER->id; // id del curso
$nomProf = $USER->firstname; // Nombre del profesor
$apProf = $USER->lastname; // Apellido del profesor
$connect = optional_param("code", null, PARAM_RAW);
//$connect = $_GET["code"];
$disconnect = optional_param ("disconnect", null, PARAM_TEXT );

require_login ();

// URL for current page
$url = new moodle_url("/local/facebook/revisar2.php");


$context = context_system::instance ();
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout("standard");
$PAGE->set_title(get_string("connecttitle", "local_facebook"));
$PAGE->navbar->add(get_string("facebook", "local_facebook"));
echo $OUTPUT->header ();

// Get current day, month and year for current user.
$date = usergetdate(time());
list($d, $m, $y) = array($date['mday'], $date['mon'], $date['year']);
// Print formatted date in user time.
$date2 = userdate(make_timestamp($y, $m));

$time = time ();
// enviar las id de los alumnos, en revisar2 se hace un foreach para el arreglo pasado, se usa https://docs.moodle.org/dev/Message_API para mandar emails a cada id
  $connect = mysql_connect("localhost","root", "root");
            if (!$connect) {
                die(mysql_error());
            }
            mysql_select_db("moodle");
$results2 = mysql_query("SELECT 
	fullname 
FROM 
	`mdl_course` 
WHERE 
	id = $idCurso");
            $row2 = mysql_fetch_assoc($results2);
$nomCurso = $row2['fullname'];
$results = mysql_query("SELECT
 u.email
FROM
 mdl_user u,
 mdl_role_assignments ra,
 mdl_context con,
 mdl_course c,
 mdl_role r,
mdl_facebook_user fb
WHERE
 u.id = ra.userid AND
 ra.contextid = con.id AND
 con.contextlevel = 50 AND
 con.instanceid = c.id AND
 c.id = $idCurso AND
u.id != fb.moodleid AND
 ra.roleid = r.id AND
 r.shortname = 'student'");
$emailAlumnos=array();
            while($row = mysql_fetch_array($results)) {
                $emailAlumnos[]=$row['email'];
            ?>
       
        <table >
        
        <tbody>
                <tr>
                <td><?php echo $row['email']?></td>
                    
                </tr>
                
            <?php
            }
            ?>
        
            </tbody>
            </table>
<?php

$resultsIds = mysql_query("SELECT
 u.id
FROM
 mdl_user u,
 mdl_role_assignments ra,
 mdl_context con,
 mdl_course c,
 mdl_role r,
mdl_facebook_user fb
WHERE
 u.id = ra.userid AND
 ra.contextid = con.id AND
 con.contextlevel = 50 AND
 con.instanceid = c.id AND
 c.id = $idCurso AND
u.id != fb.moodleid AND
 ra.roleid = r.id AND
 r.shortname = 'student'");

$count = 1; // Contador para parar cada 5 mails enviados

$message = new \core\message\message();
$message->component = 'moodle';
$message->name = 'instantmessage';
$message->userfrom = 1;
$message->subject = 'Invitación para enlazar cuenta con Facebook';
$message->fullmessage = 'Usted ha recibido una invitación del curso '.$nomCurso.' para enlazar su cuenta con Facebook, por favor dirigirse al siguiente link: http://localhost:8888/moodle/local/facebook/connect.php';
$message->fullmessageformat = FORMAT_MARKDOWN;
$message->fullmessagehtml = '<p>Usted ha recibido una invitación del curso '.$nomCurso.' para enlazar su cuenta con Facebook, por favor dirigirse al siguiente link: </p><p><a href="http://localhost:8888/moodle/local/facebook/connect.php">http://localhost:8888/moodle/local/facebook/connect.php</a></p>';
$message->smallmessage = 'small message';
$message->notification = '0';
$message->contexturl = 'http://GalaxyFarFarAway.com';
$message->contexturlname = 'Context name';
$message->replyto = "random@example.com";
$content = array('*' => array('header' => ' de '.$nomProf.' '.$apProf, 'footer' => ' test ')); // Extra content for specific processor
$message->set_additional_content('email', $content);
while($row = mysql_fetch_array($resultsIds)) {


$message->userto = $row['id'];

$messageid = message_send($message); 
if ($count % 5 == 0) {
      sleep(5); // this will wait 5 secs every 5 emails sent, and then continue the while loop
    }
    $count++;
                
            }

//foreach ($idAlumnos as $value) {

//}










echo $OUTPUT->footer ();