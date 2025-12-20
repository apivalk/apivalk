<?php

declare(strict_types=1);

namespace apivalk\apivalk\Http\Request\File;

final class FileBagFactory
{
    public static function create(): FileBag
    {
        $requestFileBag = new FileBag();

        foreach ($_FILES as $file) {
            $fileInformations = self::normalizeUploadedFiles($file);

            foreach ($fileInformations as $fileInformation) {
                $requestFileBag->set(
                    new File(
                        $fileInformation['name'],
                        $fileInformation['type'],
                        $fileInformation['tmp_name'],
                        $fileInformation['error'],
                        $fileInformation['size']
                    )
                );
            }
        }

        return $requestFileBag;
    }

    public static function normalizeUploadedFiles(array $data): array
    {
        $requiredKeys = ['name', 'type', 'tmp_name', 'error', 'size'];
        foreach ($requiredKeys as $key) {
            if (!isset($data[$key])) {
                return [];
            }
        }

        if (!\is_array($data['name'])) {
            return [$data];
        }

        $files = [];

        foreach ($data['name'] as $index => $name) {
            if (!isset(
                $data['type'][$index],
                $data['tmp_name'][$index],
                $data['error'][$index],
                $data['size'][$index]
            )) {
                continue;
            }

            $fileInformation = [
                'name' => $name,
                'type' => $data['type'][$index],
                'tmp_name' => $data['tmp_name'][$index],
                'error' => $data['error'][$index],
                'size' => (int)$data['size'][$index],
            ];

            $files[] = $fileInformation;
        }

        return $files;
    }
}
