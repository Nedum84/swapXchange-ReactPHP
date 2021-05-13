<?php

declare(strict_types=1);

namespace App\Controller\StaticFiles;

use App\JsonResponse;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Promise\PromiseInterface;
use React\Filesystem\FilesystemInterface;
use React\Filesystem\Node\FileInterface;
use Narrowspark\MimeType\MimeTypeFileExtensionGuesser;

final class StaticFilesController{
    private $filesystem;
    private $projectRoot;

    public function __construct(FilesystemInterface $filesystem, string $projectRoot){
        $this->filesystem = $filesystem;
        $this->projectRoot = $projectRoot;
    }

    public function __invoke(ServerRequestInterface $request): PromiseInterface{
        return $this->file($request->getUri()->getPath())
            ->then(
                function (File $file) {
                    return new Response(200, ['Content-Type' => $file->mimeType], $file->contents);
                }
            )
            ->otherwise(
                function (FileNotFound $exception) {
                    return JsonResponse::notFound();
                }
            )
            ->otherwise(
                function (Exception $exception) {
                    return JsonResponse::internalServerError($exception->getMessage());
                }
            );
    }

    public function file(string $path): PromiseInterface{
        $file = $this->filesystem->file($this->projectRoot . $path);

        return $file
            ->exists()
            ->then(
                function () use ($file) {
                    return $this->readFile($file);
                },
                function () {
                    throw new FileNotFound();
                }
            );
    } 

    private function readFile(FileInterface $file): PromiseInterface{
        return $file->getContents()
            ->then(
                function ($contents) use ($file) {
                    $mimeType = MimeTypeFileExtensionGuesser::guess($file->getPath());
                    return new File($contents, $mimeType);
                }
            );
    }
}
