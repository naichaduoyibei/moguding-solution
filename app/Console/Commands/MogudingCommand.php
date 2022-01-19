<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laradocs\Moguding\Client;

class MogudingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moguding';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '蘑菇丁自动签到';

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
     * @return int
     */
    public function handle()
    {
        $factory = new Client();
        $user = $factory->login ( config ( 'moguding.device' ), config ( 'moguding.phone' ), config ( 'moguding.password' ) );
        if ( empty ( $user ) ) {
            return;
        }
        $plans = $factory->getPlan ( $user [ 'token' ], $user [ 'userType' ], $user [ 'userId' ] );
        if ( empty ( $plans ) ) {
            return;
        }
        foreach ( $plans as $plan ) {
            $factory->save ( $user [ 'token' ], $user [ 'userId' ], config ( 'moguding.province' ), config ( 'moguding.city' ), config ( 'moguding.address' ), config ( 'moguding.longitude' ), config ( 'moguding.latitude' ), config ( 'moguding.type' ), config ( 'moguding.device' ), $plan [ 'planId' ], config ( 'moguding.description' ) );
            echo '签到成功！' . PHP_EOL;
        }
    }
}
