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
$url = new moodle_url("/local/facebook/revisar.php");

$context = context_system::instance ();
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout("standard");
$PAGE->set_title('Enviar invitación - Paso 1');
$PAGE->navbar->add('Enviar invitación - Paso 1');
echo $OUTPUT->header ();
?>

        
<?php
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

             // Query para buscar los no-enlazados, nombre y apellido
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
 u.id != fb.moodleid AND
 ra.roleid = r.id AND
 r.shortname = ?', array($idCurso, 'student'));
//revisar si el usuario conectado es profesor del curso actual

$context = context_course::instance($idCurso);
            if(has_capability('mod/assignment:addinstance', $context)) {
     echo "Usted enviará una invitación para enlazar Webcursos con Facebook a los siguientes alumnos, pertenecientes al curso de ".$nombreCurso."." ;   
    echo "<br><br>";
?>


<body>
        <table >
        
        <tbody>
<?php
// Se van creando filas que se llenan con los datos de la query
            foreach ($results  as $element)  {
            ?>

                <tr>
                <td><?php echo $element->firstname?></td>
                 <td><?php echo " "?></td>
                    <td><?php echo $element->lastname?></td>
                </tr>
                
            <?php
            }
            ?>
        
            </tbody>
            </table>
 
    <br>
    <!-- Redirigir a paso 2 o volver al curso, cid es la id del curso -->
    <form method="post" style ="display: inline;" action="revisar2.php?cid=<?php echo $idCurso;?>">
    <input type="submit">
</form>
    <form method="post" style ="display: inline;" action="<?php echo $CFG->wwwroot ?>/course/view.php?id=<?php echo $idCurso;?>">
    <input type="submit" value="Cancelar">
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

