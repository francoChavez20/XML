<?php
$conexion = new mysqli("localhost", "root", "root", "xml");
if ($conexion->connect_errno) {
    echo "Fallo al conectar a MySQL: (" . $conexion->connect_errno . ") " . $conexion->connect_error;
}
$xml = new DOMDocument('1.0', 'UTF-8');
$xml->formatOutput = true;

$et1 = $xml->createElement('programas_estudio');
$xml->appendChild($et1);

$consulta = "SELECT * FROM sigi_programa_estudios";
$resultado = $conexion->query($consulta);
while ($pe = mysqli_fetch_assoc($resultado)){
    echo $pe['nombre']."<br>";
    $num_pe = $xml->createElement("pe".$pe['id']);
    $codigo_pe = $xml->createElement('codigo', $pe['codigo']);
    $num_pe->appendChild($codigo_pe);
    $tipo_pe = $xml->createElement('tipo', $pe['tipo']);
    $num_pe->appendChild($tipo_pe);
    $nombre_pe = $xml->createElement('nombre', $pe['nombre']);
    $num_pe->appendChild($nombre_pe);

    $et_plan = $xml->createElement('planes_estudio');
    $xml->appendChild($et_plan);

    $consulta_plan = "SELECT * FROM sigi_planes_estudio WHERE id_programa_estudios=".$pe['id'];
    $resultado_plan = $conexion->query($consulta_plan);
    while ($plan = mysqli_fetch_assoc($resultado_plan)){
        echo $plan['nombre']."<br>";
        $num_plan = $xml->createElement("plan".$plan['id']);
        $et_plan->appendChild($num_plan);
        $nombre_plan = $xml->createElement('nombre', $plan['nombre']);
        $num_plan->appendChild($nombre_plan);
        $rolucion_plan = $xml->createElement('resolucion', $plan['resolucion']);
        $num_plan->appendChild($rolucion_plan);
        $fecha_plan = $xml->createElement('fecha_registro', $plan['fecha_registro']);
        $num_plan->appendChild($fecha_plan);

        $et_modulos = $xml->createElement('modulos_formativos');
        $xml->appendChild($et_modulos);
        $consulta_modulo = "SELECT * FROM sigi_modulo_formativo " .
                            "WHERE id_plan_estudio=".$plan['id'];
        $resultado_modulo = $conexion->query($consulta_modulo);
        while ($modulo = mysqli_fetch_assoc($resultado_modulo)){
            echo $modulo['descripcion']."<br>";
            $num_mod = $xml->createElement("mod".$modulo['id']);
            $et_modulos->appendChild($num_mod);
            $descripcion_mod = $xml->createElement('descripcion', $modulo['descripcion']);
            $num_mod->appendChild($descripcion_mod);
            $numero_mod = $xml->createElement('nro_modulo', $modulo['nro_modulo']);

        }
        
    }
    $et_plan ->appendChild($et_modulos);
    $num_pe->appendChild($et_plan);
    $et1->appendChild($num_pe);
}



$archivo = "ies_db.xml";
$xml->save($archivo);
?>