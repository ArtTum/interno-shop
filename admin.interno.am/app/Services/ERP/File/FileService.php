<?php

namespace App\Services\ERP\File;


use App\Services\ERP\File\Interfaces\FileServiceInterface;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class FileService implements FileServiceInterface
{
    const DISK = 'public';

    public function create(array $data, $folder, $fileName, $name): string
    {
        $file = $data[$name];
        if (!is_null($fileName)) {
            $uniqueName = $fileName . '.' . $file->getClientOriginalExtension();
        } else {
            $uniqueName = $file->getClientOriginalName();
        }

        $filePath = $file->storeAs('uploads/' . $folder, $uniqueName, 'public');

        return '/' . $filePath;
    }

    public function save($file, array $dimensions, string $originalName, string $path, string $fileType, string $extension = 'webp', int $quality = 80): string
    {
        $filename = $originalName . '.' . $extension;

        if ($this->imagesSupportedTypes($fileType)) {
            $image = Image::make($file)->orientate();
            $image->resize($dimensions[0], $dimensions[1], function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->encode($extension, $quality);

            $this->put((string)$image, $path . $filename);
        } else {
            $this->put(file_get_contents($file), $path . $filename);
        }

        return $path;
    }

    private function put($fileContents, string $path): void
    {
        Storage::disk(self::DISK)->put($path, $fileContents);
    }

    public function imagesSupportedTypes ($fileType): bool
    {
        return  in_array($fileType, ['image/jpeg', 'image/png', 'image/webp']);
    }
}
