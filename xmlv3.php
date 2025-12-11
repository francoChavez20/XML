<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "root", "insert");

// Verificar la conexión
if ($conexion->connect_errno) {
    echo "Fallo al conectar a MySQL: (" . $conexion->connect_errno . ") " . $conexion->connect_error;
}

// Cargar el archivo XML
$xml = simplexml_load_file('ies_db.xml') or die("Error: no se cargó correctamente el archivo XML");

// Recorrer y extraer los datos del XML y luego insertarlos en la base de datos
foreach ($xml as $i_pe => $pe) {
    echo 'Código: ' . $pe->codigo . "<br>";
    echo 'Tipo: ' . $pe->tipo . "<br>";
    echo 'Nombre: ' . $pe->nombre . "<br>";

    // Insertar en la tabla sigi_programa_estudios
    $stmt_pe = $conexion->prepare("INSERT INTO sigi_programa_estudios (codigo, tipo, nombre) VALUES (?, ?, ?)");
    $stmt_pe->bind_param("sss", $pe->codigo, $pe->tipo, $pe->nombre);
    $stmt_pe->execute();
    $programa_id = $conexion->insert_id; // Obtener el id del programa insertado

    foreach ($pe->planes_estudio[0] as $i_ple => $plan) {
        echo '--> Plan de estudio: ' . $plan->nombre . "<br>";
        echo '--> Resolución: ' . $plan->resolucion . "<br>";
        echo '--> Fecha de registro: ' . $plan->fecha_registro . "<br>";
        echo '--> Perfil egresado: ' . $plan->perfil_egresado . "<br>";

        // Insertar en la tabla sigi_planes_estudio
        // Notar que ahora no incluimos `fecha_registro`, la cual será manejada por MySQL automáticamente
        $stmt_plan = $conexion->prepare("INSERT INTO sigi_planes_estudio (id_programa_estudios, nombre, resolucion, perfil_egresado) VALUES (?, ?, ?, ?)");
        $stmt_plan->bind_param("isss", $programa_id, $plan->nombre, $plan->resolucion, $plan->perfil_egresado);
        $stmt_plan->execute();
        $plan_id = $conexion->insert_id; // Obtener el id del plan insertado

        foreach ($plan->modulos_formativos[0] as $id_mod => $modulo) {
            echo '----> Módulo: ' . $modulo->descripcion . "<br>";
            echo '----> Número de módulo: ' . $modulo->nro_modulo . "<br>";

            // Insertar en la tabla sigi_modulo_formativo
            $stmt_modulo = $conexion->prepare("INSERT INTO sigi_modulo_formativo (id_plan_estudio, descripcion, nro_modulo) VALUES (?, ?, ?)");
            $stmt_modulo->bind_param("isi", $plan_id, $modulo->descripcion, $modulo->nro_modulo);
            $stmt_modulo->execute();
            $modulo_id = $conexion->insert_id; // Obtener el id del módulo insertado

            foreach ($modulo->periodos[0] as $id_per => $periodo) {
                echo '------> Periodo: ' . $periodo->descripcion . "<br>";

                // Insertar en la tabla sigi_semestre
                $stmt_periodo = $conexion->prepare("INSERT INTO sigi_semestre (id_modulo_formativo, descripcion) VALUES (?, ?)");
                $stmt_periodo->bind_param("is", $modulo_id, $periodo->descripcion);
                $stmt_periodo->execute();
                $periodo_id = $conexion->insert_id; // Obtener el id del periodo insertado

                foreach ($periodo->unidades_didacticas[0] as $id_ud => $unidad) {
                    echo '--------> Unidad Didáctica: ' . $unidad->nombre . "<br>";
                    echo '--------> Créditos teóricos: ' . $unidad->creditos_teorico . "<br>";
                    echo '--------> Créditos prácticos: ' . $unidad->creditos_practico . "<br>";
                    echo '--------> Tipo: ' . $unidad->tipo . "<br>";
                    echo '--------> Horas semanales: ' . $unidad->horas_semanal . "<br>";
                    echo '--------> Horas semestrales: ' . $unidad->horas_semestral . "<br>";

                    // Insertar en la tabla sigi_unidad_didactica
                    $stmt_ud = $conexion->prepare("INSERT INTO sigi_unidad_didactica (id_semestre, nombre, creditos_teorico, creditos_practico, tipo, horas_semanal, horas_semestral) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt_ud->bind_param("ssiiisi", $periodo_id, $unidad->nombre, $unidad->creditos_teorico, $unidad->creditos_practico, $unidad->tipo, $unidad->horas_semanal, $unidad->horas_semestral);
                    $stmt_ud->execute();
                }
            }
        }
    }
}

echo "Datos insertados correctamente.";

$conexion->close();
?>
