<?php

namespace App\Console\Commands;

use App\Http\Controllers\CloneImgController;
use Illuminate\Console\Command;

class clone_img extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'img:clone_img {id} {--wide=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command to clone img from google ';

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
    public function handle()
    {
        $id = $this->argument('id');
        $wide = $this->option('wide');
        $result = app()->call("App\Http\Controllers\CloneImgController@index",
            ['id' =>$id, 'wide' =>$wide]);
        ;

        //1RQBwzwT13brqASLC42rpoR4QZn2KWbuV
        echo $result;

    }
}
