<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class upload_img extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'img:upload_img {id} {--fName=} {--format=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command to up load file GGdrive ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
        $id = $this->argument('id');
        $folderName = $this->option('fName');
        $formatFile = $this->option('format');
        $result = app()->call("App\Http\Controllers\UploadImgController@{$formatFile}",[
            'id'=>$id,
            'param' => [
                'folderName' => $folderName,
                'formatFile' => $formatFile,
            ],
        ]);
        echo $result;
    }
}
