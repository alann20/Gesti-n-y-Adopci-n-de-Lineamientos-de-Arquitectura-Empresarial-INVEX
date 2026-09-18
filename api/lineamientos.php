<?php
declare(strict_types=1);
require __DIR__ . '/db.php';
allowCors();
$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $codigo = $_GET['codigo'] ?? '';
    $sql = 'SELECT l.*, COALESCE(JSON_ARRAYAGG(JSON_OBJECT("id",r.id,"name",r.nombre,"email",r.correo,"order",r.orden)), JSON_ARRAY()) AS responsables_json FROM lineamientos l LEFT JOIN responsables r ON r.lineamiento_id=l.id';
    $params = [];
    if ($codigo !== '') { $sql .= ' WHERE l.codigo=?'; $params[]=$codigo; }
    $sql .= ' GROUP BY l.id ORDER BY l.id';
    $st=$pdo->prepare($sql); $st->execute($params);
    $rows=$st->fetchAll();
    foreach($rows as &$row){
        $row['adopted']=(bool)$row['adoptado'];
        $row['owners']=json_decode($row['responsables_json'] ?? '[]', true) ?: [];
        unset($row['responsables_json']);
    }
    jsonResponse(['ok'=>true,'data'=>$rows]);
}

if ($method === 'PUT') {
    $data=requestJson();
    $codigo=trim((string)($data['code'] ?? $data['codigo'] ?? ''));
    if($codigo==='') jsonResponse(['ok'=>false,'error'=>'Falta code/codigo'],400);
    $st=$pdo->prepare('SELECT id FROM lineamientos WHERE codigo=?'); $st->execute([$codigo]); $id=$st->fetchColumn();
    if(!$id) jsonResponse(['ok'=>false,'error'=>'Lineamiento no encontrado'],404);

    $allowed=['adopted','etapa','status','estatus_aplicable','date','fecha_declaracion','observation','observacion'];
    $map=['adopted'=>'adoptado','etapa'=>'etapa','status'=>'etapa','estatus_aplicable'=>'estatus_aplicable','date'=>'fecha_declaracion','fecha_declaracion'=>'fecha_declaracion','observation'=>'observacion','observacion'=>'observacion'];
    $sets=[];$params=[];
    if(array_key_exists('adopted',$data)){ $sets[]='adoptado=?'; $params[]=$data['adopted']?1:0; }
    $etapa=$data['etapa'] ?? $data['status'] ?? null;
    $estatus=$data['estatus_aplicable'] ?? null;
    if($etapa!==null){ $sets[]='etapa=?'; $params[]=$etapa; }
    if($estatus!==null){
        $effectiveEtapa=$etapa;
        if($effectiveEtapa===null){$q=$pdo->prepare('SELECT etapa FROM lineamientos WHERE id=?');$q->execute([$id]);$effectiveEtapa=$q->fetchColumn();}
        if(!validateLifecycle($pdo,(string)$effectiveEtapa,(string)$estatus)) jsonResponse(['ok'=>false,'error'=>'El estatus aplicable no corresponde a la etapa seleccionada'],422);
        $sets[]='estatus_aplicable=?'; $params[]=$estatus;
    }
    $fecha=$data['fecha_declaracion'] ?? $data['date'] ?? null;
    if(array_key_exists('fecha_declaracion',$data)||array_key_exists('date',$data)){ $sets[]='fecha_declaracion=?'; $params[]=$fecha ?: null; }
    $obs=$data['observacion'] ?? $data['observation'] ?? null;
    if(array_key_exists('observacion',$data)||array_key_exists('observation',$data)){ $sets[]='observacion=?'; $params[]=$obs; }
    if(!$sets) jsonResponse(['ok'=>true,'message'=>'Sin cambios']);
    $params[]=$id; $pdo->prepare('UPDATE lineamientos SET '.implode(',',$sets).' WHERE id=?')->execute($params);
    jsonResponse(['ok'=>true,'message'=>'Lineamiento actualizado']);
}

jsonResponse(['ok'=>false,'error'=>'Método no permitido'],405);
