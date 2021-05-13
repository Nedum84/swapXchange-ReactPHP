<?php

namespace App\Services;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Http\Message\Response;
use React\Filesystem\Filesystem;

final class ImageUploadServices{
    private $dir;
    private $filesystem;

    public function __construct(string $dir,\React\Filesystem\Filesystem $filesystem){
        $this->dir = $dir;
        $this->filesystem = $filesystem;
    }

    public function uploadFile(string $image_file, string $file_name = "swapxchange.jpg"): PromiseInterface{
        echo "dfdfdf";

		// $ext         =strtolower(pathinfo($file_name, \PATHINFO_EXTENSION));
        
        // $hash        =md5(time().mt_rand(100,999));
		// $product_url_path = 'SXC'."-".$hash.".".$ext;
		// $image_path  = $this->dir.$product_url_path;

        // $image_file = base64_decode($image_file);

		// if(empty($image_file)){
        //     return (new \App\Utils\PromiseResponse())::rejectPromise("No image selected");
        // }elseif(!file_put_contents($image_path, $image_file)){
        //     return (new \App\Utils\PromiseResponse())::rejectPromise("Photo couldn\'t be uploaded");
		// }else{
        //     return (new \App\Utils\PromiseResponse())::resolvePromise([
        //         "img_url" => $product_url_path
        //     ]);
		// }

        // $file = $this->filesystem->file($product_url_path);
        // $file->touch()->then(function () {
        //     echo 'File created or exists' . PHP_EOL;
        //     $file->putContents($image_file)->then(function () {
        //         echo "Data was written\n";
        //         return [
        //             "img_url" => $product_url_path
        //         ];
        //     },
        //     function (\Exception $error) {
        //         return "Error: $error";
        //     });
        // },
        // function (\Exception $error) {
        //     return "Error: $error";
        // });
    }


    public function delete(string $url): PromiseInterface{

        $file = $this->filesystem->file($url);
        return $file->remove()->then(function () {
            echo 'File was removed' . PHP_EOL;
            return "Image successfully removed";
        },
        function (\Exception $error) {
            return "Error: $error";
        });
    }

}


                    