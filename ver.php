<?php

require_once (dirname(dirname(dirname(__FILE__)))."/config.php");
require_once ($CFG->dirroot."/local/facebook/locallib.php");
require_once ($CFG->dirroot."/local/facebook/forms.php");


include "app/config.php";
global $DB, $USER, $CFG, $COURSE; 

$idCurso = $_GET['id'];  // id del curso actual
$idProf = $USER->id; // id del usuario actual
$prefix= $CFG->prefix; //prefijo de las tablas de moodle

require_login ();

// URL for current page
$url = new moodle_url("/local/facebook/ver.php");

$context = context_system::instance ();
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout("standard");
$PAGE->set_title('Ver alumnos inscritos');
$PAGE->navbar->add('Ver alumnos inscritos');
echo $OUTPUT->header ();


?>

        
<?php
        
            // Query para mostrar el curso actual
            $results3 = $DB->get_records_sql('SELECT 
	fullname 
FROM 
	 '.$prefix.'course 
WHERE 
	id = ?', array($idCurso));
foreach ($results3  as $element)  {
    $nombreCurso= $element->fullname;
}
   
            // Query para mostrar a los enlazados
$results = $DB->get_records_sql('SELECT
 u.id,u.firstname, u.lastname
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
 u.id = fb.moodleid AND
 ra.roleid = r.id AND
 r.shortname = ?', array($idCurso, 'student'));


// Query para mostrar a los no-enlazados y notificados, es decir, caso omiso
$results2 = $DB->get_records_sql('SELECT
 u.id,u.firstname, u.lastname
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
 u.notificado = TRUE AND
 r.shortname = ?', array($idCurso, 'student'));

//revisar si el usuario conectado es profesor del curso actual

$context = context_course::instance($idCurso);
            if(has_capability('mod/assignment:addinstance', $context)) {
     echo "Invitaciónes a enlazar Facebook aceptadas e invitaciones ignoradas en el curso ".$nombreCurso.".";   
    echo "<br><br>";
?>

<body>

        <table style="display: inline-block;margin-right: 60px" >

            <th>Enlazados</th>
        <th>a Facebook</th>
        <tbody>
<?php
// Tabla para mostrar los enlazados
           foreach ($results  as $element)  {
            ?>

                <tr>
                <td><?php echo $element->firstname?></td>
                    <td><?php echo $element->lastname?></td>
                </tr>
                
            <?php
            }
            ?>
        
            </tbody>
            </table>
    <table style="display: inline-block;"  >
        <th>Invitaciones</th>
        <th>ignoradas</th>
        <tbody>
            <?php
            // Tabla para mostrar los casos omisos
            foreach ($results2  as $element)  {
            ?>

                <tr>
                <td ><?php echo $element->firstname?></td>
                 
                    <td><?php echo $element->lastname?></td>
                </tr>
                
            <?php
            }
            ?>

                           </tbody>
            </table>
    <br>
    <!-- Boton para ir a enviar invitacion paso 1, cid es la id del curso -->
    <form method="post" style ="display: inline;" action="revisar.php?id=<?php echo $idCurso;?>">
    <input type="submit" value="Reenviar invitación">
</form>
                <?php
}
else {
    // Si no era el profesor entonces error
    echo "Error: Página restringida";
    
}
            ?>
        
 
  
    
    </body>
<?php


echo $OUTPUT->footer ();

