<?php

require_once (dirname(dirname(dirname(__FILE__)))."/config.php");
require_once ($CFG->dirroot."/local/facebook/locallib.php");
require_once ($CFG->dirroot."/local/facebook/forms.php");


include "app/config.php";
global $DB, $USER, $CFG, $COURSE; 

$idCurso = $_GET['id'];  // id del curso actual
$idProf = $USER->id; // id del usuario actual

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
        // Query para conectarse con la base de datos
         $connect = mysql_connect("localhost","root", "root");
            if (!$connect) {
                die(mysql_error());
            }
            mysql_select_db("moodle");
            // Query para mostrar el curso actual
            $results2 = mysql_query("SELECT 
	fullname 
FROM 
	`mdl_course` 
WHERE 
	id = $idCurso");
            $row2 = mysql_fetch_assoc($results2); 
            // Query para mostrar a los enlazados
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
 c.id = $idCurso AND
 u.id = fb.moodleid AND
 ra.roleid = r.id AND
 r.shortname = 'student'");
// Query para mostrar a los no-enlazados y notificados, es decir, caso omiso
$results2 = mysql_query("SELECT
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
 c.id = $idCurso AND
 u.id != fb.moodleid AND
 ra.roleid = r.id AND
 u.notificado = TRUE AND
 r.shortname = 'student'");
 // Query para revisar si el usuario conectado es profesor del curso actual
$results5 = mysql_query("SELECT 
	c.id, 
	c.shortname, 
	u.id, 
	u.username, 
	CONCAT(u.firstname, ' ', u.lastname) AS name 
FROM 
	mdl_course c 
	LEFT OUTER JOIN mdl_context cx ON c.id = cx.instanceid 
	LEFT OUTER JOIN mdl_role_assignments ra ON cx.id = ra.contextid 
	AND ra.roleid = '3' 
	LEFT OUTER JOIN mdl_user u ON ra.userid = u.id 
WHERE 
	cx.contextlevel = '50' 
	AND c.id = $idCurso
        AND u.id =$idProf");

// Si el usuario es profesor entonces devuelve 1 fila
if (mysql_num_rows($results5)==1 ) {
     echo "Invitaciónes a enlazar Facebook aceptadas y casos omisos en el curso ".$row2['fullname'] ;   
    echo "<br><br>";
$idAlumnos=array();
$idAlumnos2=array();
?>

<body>

        <table style="display: inline-block;margin-right: 60px" >

            <th>Enlazados</th>
        <th>a Facebook</th>
        <tbody>
<?php
// Tabla para mostrar los enlazados
            while($row = mysql_fetch_array($results)) {
                $idAlumnos[]=$row['id'];
            ?>

                <tr>
                <td><?php echo $row['firstname']?></td>
                    <td><?php echo $row['lastname']?></td>
                </tr>
                
            <?php
            }
            ?>
        
            </tbody>
            </table>
    <table style="display: inline-block;" >
        <th>Caso</th>
        <th>omiso</th>
        <tbody>
            <?php
            // Tabla para mostrar los casos omisos
            while($row = mysql_fetch_array($results2)) {
                $idAlumnos2[]=$row['id'];
            ?>

                <tr>
                <td ><?php echo $row['firstname']?></td>
                 <td><?php echo " "?></td>
                    <td><?php echo $row['lastname']?></td>
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
