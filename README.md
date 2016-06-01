	# facebook
Esta modificación le permite al profesor ver que alumnos de su ramo están enlazados con Facebook y avisar por mail a los que no lo están.

Nota 1: Es necesario instalar la modificación al bloque UAI para que funcione correctamente:
https://github.com/matpena/facebook-uai-bloque

Nota 2: En esta versión es necesario crear manualmente una columna en la tabla "mdl_user".
Ejecutar el siguiente código SQL en la base de datos moodle:
ALTER TABLE mdl_user ADD notificado bool;
*Se asume que el prefijo usado para las tablas en la instalacion es "mdl_", en caso contrario cambiar por el prefijo correcto.

Para instalar:
1) Descargar .zip, descomprimir y situar la carpeta descargada en la carpeta "local" dentro de la carpeta de instalación de Moodle.
2) Cambiar el nombre de la carpeta descargada "facebook-uai-master" por "facebook".
3) Al acceder a Moodle le avisará que hay un nuevo plugin disponible, completar la instalación.
4) Una vez que esté instalado el bloque UAI: Ahora el profesor al estar dentro de un curso podrá ver en la barra lateral izquierda la opcion para revisar los alumnos inscritos y enviar invitaciones las invitaciones a quienes no estén.