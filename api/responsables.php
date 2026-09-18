<?php
declare(strict_types=1);
require __DIR__ . '/db.php';
allowCors();
$pdo=db(); $method=$_SERVER['REQUEST_METHOD'];

if($method==='GET'){
  $codigo=trim((string)($_GET['codigo'] ?? ''));
  if($codigo==='') jsonResponse(['ok'=>false,'error'=>'Falta codigo'],400);
  $st=$pdo->prepare('SELECT r.id,r.lineamiento_id,r.nombre AS name,r.correo AS email,r.orden FROM responsables r INNER JOIN lineamientos l ON l.id=r.lineamiento_id WHERE l.codigo=? ORDER BY r.orden,r.id');
  $st->execute([$codigo]); jsonResponse(['ok'=>true,'data'=>$st->fetchAll()]);
}

if($method==='POST'){
  $d=requestJson(); $codigo=trim((string)($d['code']??$d['codigo']??''));
  $nombre=trim((string)($d['name']??$d['nombre']??'')); $correo=trim((string)($d['email']??$d['correo']??''));
  if($codigo==='') jsonResponse(['ok'=>false,'error'=>'Falta codigo'],400);
  $st=$pdo->prepare('SELECT id FROM lineamientos WHERE codigo=?');$st->execute([$codigo]);$lid=$st->fetchColumn();
  if(!$lid) jsonResponse(['ok'=>false,'error'=>'Lineamiento no encontrado'],404);
  $q=$pdo->prepare('SELECT COALESCE(MAX(orden),0)+1 FROM responsables WHERE lineamiento_id=?');$q->execute([$lid]);$orden=(int)$q->fetchColumn();
  $st=$pdo->prepare('INSERT INTO responsables(lineamiento_id,nombre,correo,orden) VALUES(?,?,?,?)');$st->execute([$lid,$nombre,$correo,$orden]);
  jsonResponse(['ok'=>true,'id'=>(int)$pdo->lastInsertId(),'order'=>$orden],201);
}

if($method==='PUT'){
  $d=requestJson(); $id=(int)($d['id']??0);
  if(!$id) jsonResponse(['ok'=>false,'error'=>'Falta id'],400);
  $sets=[];$p=[];
  if(array_key_exists('name',$d)||array_key_exists('nombre',$d)){ $sets[]='nombre=?';$p[]=$d['name']??$d['nombre']??''; }
  if(array_key_exists('email',$d)||array_key_exists('correo',$d)){ $sets[]='correo=?';$p[]=$d['email']??$d['correo']??''; }
  if(array_key_exists('order',$d)||array_key_exists('orden',$d)){ $sets[]='orden=?';$p[]=(int)($d['order']??$d['orden']); }
  if(!$sets) jsonResponse(['ok'=>true]);
  $p[]=$id;$pdo->prepare('UPDATE responsables SET '.implode(',',$sets).' WHERE id=?')->execute($p);
  jsonResponse(['ok'=>true]);
}

if($method==='DELETE'){
  $id=(int)($_GET['id']??0);
  if(!$id){$d=requestJson();$id=(int)($d['id']??0);}
  if(!$id) jsonResponse(['ok'=>false,'error'=>'Falta id'],400);
  $pdo->prepare('DELETE FROM responsables WHERE id=?')->execute([$id]);
  jsonResponse(['ok'=>true]);
}
jsonResponse(['ok'=>false,'error'=>'Método no permitido'],405);
