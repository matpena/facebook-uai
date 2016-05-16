<?php

require_once (dirname(dirname(dirname(__FILE__)))."/config.php");
require_once ($CFG->dirroot."/local/facebook/locallib.php");
require_once ($CFG->dirroot."/local/facebook/forms.php");


include "app/config.php";
use Facebook\FacebookResponse;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequire;
global $DB, $USER, $CFG, $COURSE; 

$idcurso = $_GET['id'];
$idProf = $USER->id;
$connect = optional_param("code", null, PARAM_RAW);
//$connect = $_GET["code"];
$disconnect = optional_param ("disconnect", null, PARAM_TEXT );

require_login ();

// URL for current page
$url = new moodle_url("/local/facebook/revisar.php");

$context = context_system::instance ();
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout("standard");
$PAGE->set_title(get_string("connecttitle", "local_facebook"));
$PAGE->navbar->add(get_string("facebook", "local_facebook"));
echo $OUTPUT->header ();
$facebook = new Facebook\Facebook($config);

$helper = $facebook->getRedirectLoginHelper();
$appname = $CFG->fbkAppNAME;
$apptoken = $CFG->fbkTkn;
$appid = $CFG->fbkAppID;
$secretid = $CFG->fbkScrID;

// Search if the user have linked with facebook
$userinfo = $DB->get_record ( 'facebook_user', array (
		'moodleid' => $USER->id,
		'status' => FACEBOOK_STATUS_LINKED
) );

$time = time ();
?>

        
        <?php
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
	id = $idcurso");
            $row2 = mysql_fetch_assoc($results2); ?>
 <body>
    Usted desea enviar una invitación a los siguientes alumnos, pertenecientes al curso de <?php echo $row2['fullname'];?>:
    
    <br><br>
<?php           
$results = mysql_query("SELECT
 u.firstname, u.lastname, u.id
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
 c.id = $idcurso AND
u.id != fb.moodleid AND
 ra.roleid = r.id AND
 r.shortname = 'student'");
$idAlumnos=array();
            while($row = mysql_fetch_array($results)) {
                $idAlumnos[]=$row['id'];
            ?>
       
        <table >
        
        <tbody>
                <tr>
                <td><?php echo $row['firstname']?></td>
                    <td><?php echo $row['lastname']?></td>
                </tr>
                
            <?php
            }
            ?>
        
            </tbody>
            </table>
    <br>
    <!-- cid es la id del curso -->
    <form method="post" style ="display: inline;" action="revisar2.php?cid=<?php echo $idcurso;?>">
    <input type="submit">
</form>
    <form method="post" style ="display: inline;" action="http://localhost:8888/moodle/course/view.php?id=<?php echo $idcurso;?>">
    <input type="submit" value="Cancelar">
</form>
    </body>
<?php


echo $OUTPUT->footer ();

