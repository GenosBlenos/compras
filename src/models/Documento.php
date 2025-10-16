<?php
require_once __DIR__ . '/../includes/Model.php';
class Documento extends Model {
    protected $table = 'documentos';
    protected $fillable = ['titulo', 'arquivo'];
    protected $orderBy = 'id';
}
