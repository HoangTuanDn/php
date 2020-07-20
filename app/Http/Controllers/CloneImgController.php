<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Google_Service_Drive;
use Exception;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Exception\ImageException;
use Intervention\Image\ImageManager;
use Illuminate\Http\Request;

class CloneImgController extends Controller
{
    public function index($id,Request $request){
        $client = app()->call("App\Http\Controllers\Controller@getClient");
        $service = new Google_Service_Drive($client);
        $files = $this->getAllFiles($id,$service);
        $wide = $request->input('wide');
        $validator = Validator::make($request->all(),[
            'wide' => 'required|numeric'
        ],[
            'wide.required' => 'width of image is not null',
            'wide.numeric' => 'width of image must be number'
        ]);
//        if(!in_array($wide,[354,1000])){
//            $wide = 1000;
//        }
        $errors = $validator->errors();
        $totalImg = 0;
        $results = [];
        if(count($errors) >0){
            return  response()->json([
                'message'=> 'clone img from drive is not successful',
                'data'=>[
                    'errors'=>$errors
                ]
            ],400);

        }
        else{
            if(!empty($files) && isset($wide)){
                foreach ($files as $file){

                    $fileContent = $this->getFileContent($file, $service);
                    $filePath = $this->run($file, $fileContent, $wide);
                    $ok = $this->resizeImg($filePath,$wide);
                    $item = [
                        'path' => $filePath,
                        'imgName'=> File::basename($filePath),
                        'mimeType'=> File::mimeType($filePath),
                        'size'=>File::size($filePath)
                    ];
                    $totalImg++;
                    array_push($results,$item);
                }
            }

            if($totalImg>0){
                return  response()->json([
                    'message'=> 'clone img from drive is successful',
                    'data'=>[
                        'totalImg'=>$totalImg,
                        'img'=> $results,
                    ]
                ],200);
            }

        }
    }

    public function getAllFiles($id, $service){
        $results = [];
        $pageToken = null;
        do{
            try {
                $optParams = array(
                    'pageSize' => 20,
                    'fields' => 'nextPageToken, files(id, name, properties, createdTime, imageMediaMetadata, mimeType)',
                    'q' => "'{$id}' in parents and trashed=false and (mimeType = 'image/jpeg') "
                );
                $optParams['pageToken'] = $pageToken? $pageToken : null;
                $results = $service->files->listFiles($optParams);
                if(!empty($results) && count($results->getFiles()) != 0){
                    $pageToken = $results->getNextPageToken();
                }
                else{
                    $pageToken = null;
                }

            }
            catch (Exception $e){
                $pageToken = null;
                echo "err : {$e->getMessage()} "."\n";
            }

        }while($pageToken);
        return $results;
    }

    private function getFileContent($file, $service){

        try {
            $response = $service->files->get($file->getId(), array(
                'alt' => 'media'));
            $content = $response->getBody()->getContents();
            return $content;

        }
        catch (Exception $e ){
            echo "err {$e->getMessage()}" ."\n";
        }

    }

    private function run($file,$fileContent, $wide){
        $filePath = storage_path("app/store_img_{$wide}/{$file->getName()}");
        try{
            if(!file_exists(dirname($filePath))){
                mkdir(dirname($filePath),'0777',true);
            }
            if(!file_exists($filePath) && $fileContent){
                file_put_contents($filePath,$fileContent);
            }

            return $filePath;
        }
        catch (Exception $e){
            echo "err{$e->getMessage()}" ."\n";
        }
    }
    private function resizeImg($filePath, $wide){
        // create an image manager instance with favored driver
        $manager = new ImageManager(array('driver' => 'imagick'));
        $success = false;
        try{
            if(file_exists($filePath)){
                $img = $manager->make($filePath);
                if($img->getWidth() != $wide){
                    $img->resize($wide, null, function ($constraint){
                        $constraint->aspectRatio();
                    });
                    $img->save($filePath);
                    $success = true;
                }
            }
            return $success;
        }
        catch (ImageException $e){
            echo "err {$e->getMessage()}";
        }
    }
}
