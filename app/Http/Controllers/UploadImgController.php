<?php

namespace App\Http\Controllers;

use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class UploadImgController extends Controller
{
    public  function image($id, Request $request){

        $client = app()->call("App\Http\Controllers\Controller@getClient");
        $service = new Google_Service_Drive($client);
        $validator = Validator::make($request->all(),[
            'fname' => 'required'
        ],[
            'fname.required' => 'Image storage directory name cannot be empty',
        ]);
        $param=[
          'folderName' => $request->input('fname'),
        ];
        $items = $this->getFilesFromDirectory($param['folderName']);
        $tmpFiles = $this->FilesExitDrive($id, $service, $items);
        $errors = $validator->errors();
        $total = 0;
        $result = [];
        if(count($errors) >0){
            return  response()->json([
                'message'=> 'upload img from drive is not successful',
                'data'=>[
                    'errors'=>$errors
                ]
            ],400);

        }
        else{
            foreach ($items as $item){
                if(File::isFile($item) && !in_array(File::basename($item), $tmpFiles)){
                    $itemPath = $this->upload($id, $service, $item);
                    $total++;
                    $itemData = [
                        'filePath'=>$itemPath,
                        'fileSize'=>File::size($itemPath),
                        'mimeType'=>File::mimeType($itemPath),
                    ];
                    array_push($result, $itemData);
                }
            }
            return response()->json([
                'data'=>[
                    'message'=>'upload img success',
                    'totalUploadImg'=>$total,
                    'data'=>$result
                ]
            ], 200);
        }

    }

    private function getFilesFromDirectory($folderName){
        $dirPath = "D:\dev\api_img/image/{$folderName}";
        if(!file_exists($dirPath)){
            mkdir($dirPath,'0777', true);
        }
        $result = [];
        $files = File::allFiles($dirPath);
        foreach ($files as $file){
            if(File::mimeType($file->getRealPath()) == 'image/jpeg'){
                array_push($result, $file);
            }
        }
        return $result;

    }
    public  function  FilesExitDrive($id, $service, $items){
        $fileDrive = [];
        $result = [];
        $listFile = app()->call("App\Http\Controllers\CloneImgController@getAllFiles",[
            'id'=>$id,
            'service'=>$service,
        ]);
        foreach ($listFile as $file){
           array_push($fileDrive, $file->getName());
        }

        foreach ($items as $item){
            if(in_array(File::basename($item), $fileDrive)){
                array_push($result,File::basename($item));
            }
        }
        return $result;
    }

    private function upload($id, $service, $item){
        try {
            $imgName = File::basename($item);
            $fileMetaData = new Google_Service_Drive_DriveFile();
            $fileMetaData->setName($imgName);
            $fileMetaData->setParents(array($id));
            $content = file_get_contents($item->getRealPath());
            $file = $service->files->create($fileMetaData,[
                'data'  =>  $content,
                'mimeType'  =>  mime_content_type($item->getRealPath()),
                'uploadType'    =>  'multipart',
                'fields'    => 'id'
            ]);
            return $item->getRealPath();
        }
        catch (\Exception $e){
            echo "err: {$e->getMessage()} ". "\n";
        }
    }

}
