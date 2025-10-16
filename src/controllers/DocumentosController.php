<?php
require_once __DIR__ . '/../models/Documento.php';
require_once __DIR__ . '/../includes/FileUpload.php';

class DocumentosController {
    public function index($module = null) {
        $model = new Documento();
        return $model->all();
    }

    public function show($id) {
        $model = new Documento();
        return $model->find($id);
    }

    public function store($data, $file) {
        $uploader = new FileUpload(__DIR__ . '/../../uploads', ['application/pdf', 'image/jpeg', 'image/png']);

        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $uploadedFilename = $uploader->upload($file);
            if ($uploadedFilename) {
                $data['arquivo'] = $uploadedFilename;
                $model = new Documento();
                return $model->create($data);
            }
        }
        // Se não houver arquivo ou o upload falhar
        return false;
    }

    public function update($id, $data, $file) {
        $model = new Documento();
        $uploader = new FileUpload(__DIR__ . '/../../uploads');

        // Se um novo arquivo foi enviado, processa o upload
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $oldDocument = $model->find($id);
            $uploadedFilename = $uploader->upload($file);

            if ($uploadedFilename) {
                $data['arquivo'] = $uploadedFilename;
                // Exclui o arquivo antigo se o novo foi salvo com sucesso
                if ($oldDocument && !empty($oldDocument['arquivo'])) {
                    $uploader->deleteFile($oldDocument['arquivo']);
                }
            } else {
                return false; // Falha no upload do novo arquivo
            }
        }

        return $model->update($id, $data);
    }

    public function destroy($id) {
        $model = new Documento();
        $documento = $model->find($id);

        if ($documento && $model->delete($id)) {
            $uploader = new FileUpload(__DIR__ . '/../../uploads');
            return $uploader->deleteFile($documento['arquivo']);
        }
        return false;
    }
}
