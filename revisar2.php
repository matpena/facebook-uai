<?php
$idCurso = $_GET['cid']; // id del curso actual

require_once (dirname(dirname(dirname(__FILE__)))."/config.php");
require_once ($CFG->dirroot."/local/facebook/locallib.php");
require_once ($CFG->dirroot."/local/facebook/forms.php");

global $DB, $USER, $CFG, $COURSE; 

$idProf = $USER->id; // id del usuario
$nomProf = $USER->firstname; // Nombre del profesor, para mandar el mail
$apProf = $USER->lastname; // Apellido del profesor, para mandar el mail
$prefix= $CFG->prefix; //prefijo de las tablas de moodle
require_login ();

// URL for current page
$url = new moodle_url("/local/facebook/revisar2.php");


$context = context_system::instance ();
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout("standard");
$PAGE->set_title('Enviar invitación - Paso 2');
$PAGE->navbar->add('Enviar invitación - Paso 2');
echo $OUTPUT->header ();


// Query para conectarse con la base de datos
  $connect = mysql_connect("localhost","root", "root");
            if (!$connect) {
                die(mysql_error());
            }
            mysql_select_db("moodle");
           // Query para mostrar el curso actual
            $results2 = $DB->get_records_sql('SELECT 
	fullname 
FROM 
	 '.$prefix.'course 
WHERE 
	id = ?', array($idCurso));
foreach ($results2  as $element)  {
    $nombreCurso= $element->fullname;
}
// Query para buscar los no-enlazados, nombre apellido, mail
$results = $DB->get_records_sql('SELECT
 u.firstname, u.lastname, u.email
FROM
 '.$prefix.'user u,
 '.$prefix.'role_assignments ra,
 '.$prefix.'context con,
 '.$prefix.'course c,
 '.$prefix.'role r,
 '.$prefix.'facebook_user fb
 WHERE 
 u.id = ra.userid AND
 ra.contextid = con.id AND
 con.contextlevel = 50 AND
 con.instanceid = c.id AND
 c.id = ? AND
 u.id != fb.moodleid AND
 ra.roleid = r.id AND
 r.shortname = ?', array($idCurso, 'student'));
 
//revisar si el usuario conectado es profesor del curso actual

$context = context_course::instance($idCurso);
 if(has_capability('mod/assignment:addinstance', $context)) {
$emailAlumnos=array();
echo "Usted ha enviado exitosamente la invitacion a las siguientes personas:";
    // Mostrar los resultados, emails, de la query            
               foreach ($results  as $element)  {

            ?>
       
        <table >
        
        <tbody>
                <tr>
                    <td><?php echo $element->firstname?></td>
                    <td><?php echo " "?></td>
                    <td><?php echo $element->lastname?></td>
                <td><?php echo '('.$element->email.')'?></td>
                    
                </tr>
                
            <?php
            }
            ?>
       
            </tbody>
            </table>
<!-- Volver al curso -->
 <form method="post" style ="display: inline;" action="<?php echo $CFG->wwwroot ?>/course/view.php?id=<?php echo $idCurso;?>">
    <input type="submit" value="Volver al curso">
    
<?php
// Query para obtener las ids de los no-enlazados
$resultsIds = $DB->get_records_sql('SELECT
 u.id
FROM
 '.$prefix.'user u,
 '.$prefix.'role_assignments ra,
 '.$prefix.'context con,
 '.$prefix.'course c,
 '.$prefix.'role r,
 '.$prefix.'facebook_user fb
 WHERE 
 u.id = ra.userid AND
 ra.contextid = con.id AND
 con.contextlevel = 50 AND
 con.instanceid = c.id AND
 c.id = ? AND
 u.id != fb.moodleid AND
 ra.roleid = r.id AND
 r.shortname = ?', array($idCurso, 'student'));

$count = 1; // Contador para parar cada 5 mails enviados


// API para mandar mails
$message = new \core\message\message();
$message->component = 'moodle';
$message->name = 'instantmessage';
$message->userfrom = 1; //Se envia desde el usuario id 1, se podría crear un usuario especifico para esto
$message->subject = 'Invitación para enlazar cuenta con Facebook';
$message->fullmessage = 'Usted ha recibido una invitación del curso '.$nomCurso.' para enlazar su cuenta con Facebook, por favor dirigirse al siguiente link: http://localhost:8888/moodle/local/facebook/connect.php';
$message->fullmessageformat = FORMAT_MARKDOWN;
$message->fullmessagehtml = '<p>Usted ha recibido una invitación del curso '.$nomCurso.' para enlazar su cuenta con Facebook, por favor dirigirse al siguiente link: </p><p><a href="http://localhost:8888/moodle/local/facebook/connect.php">http://localhost:8888/moodle/local/facebook/connect.php</a></p>';
$message->smallmessage = 'small message';
$message->notification = '0';
$message->contexturl = '';
$message->contexturlname = 'Context name';
$message->replyto = "noreply@webcursos.uai.cl";
$content = array('*' => array('header' => ' De '.$nomProf.' '.$apProf, 'footer' => '  ')); // Extra content for specific processor
$message->set_additional_content('email', $content);

foreach ($resultsIds  as $element)  {
    
// en este ciclo se van mandando los mails a cada id
$message->userto = $element->id;
$uId=$element->id;
$messageid = message_send($message); 
// a cada id que se le envía un mail se le cambia el valor de la base de datos "notificado" por TRUE
$record = new stdclass;
$record->id = $uId;
$record->notificado = TRUE;
$notificado = $DB->execute("UPDATE 
	 {user}
SET 
	notificado = TRUE 
WHERE 
        id = ? ",array ($uId));
//$notificado = $DB->update_record('user', $record); 

if ($count % 5 == 0) {
      sleep(5); // this will wait 5 secs every 5 emails sent, and then continue the while loop
    }
    $count++;
                
            }
}
// Si no era el profesor entonces error
else {
    echo "Error: Página restringida";
    
    
}


echo $OUTPUT->footer ();