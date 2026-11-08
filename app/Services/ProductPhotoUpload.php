<?php

namespace App\Services;

use App\Models\ProductPhoto;
use Core\Constants\Constants;

class ProductPhotoUpload
{
    public const MAX_SIZE = 2 * 1024 * 1024;

    // O tipo é lido do conteúdo do arquivo, não da extensão que o usuário mandou
    public const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    public function __construct(private ProductPhoto $photo)
    {
    }

    /**
     * @param array<string, mixed> $file um item de $_FILES
     */
    public function save(array $file): bool
    {
        if (!$this->validates($file)) {
            return false;
        }

        $type = mime_content_type($file['tmp_name']);
        $extension = self::ALLOWED_TYPES[$type];
        $this->photo->file_name = 'product_' . $this->photo->product_id . '_' . uniqid() . '.' . $extension;

        if (!$this->photo->isValid()) {
            $this->photo->addError('file', 'produto não encontrado!');
            return false;
        }

        if (!$this->moveFile($file['tmp_name'], $this->filePath())) {
            $this->photo->addError('file', 'não foi possível salvar a imagem!');
            return false;
        }

        return $this->photo->save();
    }

    public function remove(): bool
    {
        if (is_file($this->filePath())) {
            unlink($this->filePath());
        }

        return $this->photo->destroy();
    }

    public function filePath(): string
    {
        return self::directory() . '/' . $this->photo->file_name;
    }

    public static function directory(): string
    {
        return (string) Constants::rootPath()->join('public/assets/uploads');
    }

    /**
     * @param array<string, mixed> $file
     */
    private function validates(array $file): bool
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $this->photo->addError('file', 'selecione uma imagem!');
            return false;
        }

        if ($file['size'] > self::MAX_SIZE) {
            $this->photo->addError('file', 'a imagem deve ter no máximo 2 MB!');
            return false;
        }

        $type = mime_content_type($file['tmp_name']);
        if (!array_key_exists($type, self::ALLOWED_TYPES)) {
            $this->photo->addError('file', 'a imagem deve ser JPEG ou PNG!');
            return false;
        }

        return true;
    }

    protected function moveFile(string $from, string $to): bool
    {
        return move_uploaded_file($from, $to);
    }
}
