<?php

namespace App\Services;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Http\Message\Response;
use React\Filesystem\Filesystem;

final class ImageUploadServices{
    private const UPLOADS_DIR = 'uploads';

    private $projectRoot;
    private $filesystem;

    public function __construct(string $projectRoot, \React\Filesystem\Filesystem $filesystem){
        $this->projectRoot = $projectRoot;
        $this->filesystem = $filesystem;
    }

    public function uploadFile($base64_image_file, string $file_name): PromiseInterface{
        $uploadPath = $this->makeFilePath($file_name);
        $fullPath = $this->projectRoot . '/' . $uploadPath;

		if(empty($base64_image_file)){
            return (new \App\Utils\PromiseResponse())::rejectPromise("No image selected");
        }
        $image_file = \base64_decode($base64_image_file);

        //Upload to the server asynchronously
        $file = $this->filesystem->file($fullPath);
        return $file->putContents((string)$image_file)
            ->then(function () use ($uploadPath, $file) {
                //Get Image Stats
                return $file->stat()->then(function ($stat) use ($uploadPath) {
                    return [
                        "image_path" => $uploadPath,
                        "image_size_byte"=>$stat["size"]
                    ];
                },
                function ($error) {
                    return [
                        "image_path" => $uploadPath
                    ];
                });
            },
            function ($error) {
                return "Error: $error";
            });
    }


    public function delete(string $imgPath): PromiseInterface{
        if (empty($imgPath)) {
            return \App\Utils\PromiseResponse::rejectPromise([]);
        }elseif (strpos($imgPath, 'uploads/') !== false) {
            $uploadPath = $imgPath;
        }else{
            $uploadPath = self::UPLOADS_DIR.'/'.$imgPath;
        }

        $fullPath = $this->projectRoot . '/' . $uploadPath;
        $file = $this->filesystem->file($fullPath);
        return $file->exists()->then(function () use ($file) {
            return $file->remove()->then(function () {
                echo 'File was removed';
                return [];
            });
        }, function () {
            echo 'File not found';
            return [];//''
        });
    }

    private function makeFilePath(string $fileName): string{
        preg_match('/^.*\.(.+)$/', $fileName, $filenameParsed);
		// $filenameExt         =strtolower(pathinfo($file_name, \PATHINFO_EXTENSION));

        return implode(
            '',
            [
                self::UPLOADS_DIR,
                '/',
                'sxc',
                md5(time().mt_rand(100,999)),
                '.',
                $filenameParsed[1],
            ]
        );
    }
}


                    