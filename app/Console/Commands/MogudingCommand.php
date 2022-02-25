<?php

namespace App\Console\Commands;

use Exception;
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
        $user = $factory->login (
            config ( 'moguding.device' ),
            config ( 'moguding.phone' ),
            config ( 'moguding.password' )
        );
        if ( empty ( $user ) ) {
            throw new Exception ( '签到失败，用户名或密码错误。' );
        }
        $plans = $factory->getPlan (
            $user [ 'token' ],
            $user [ 'userType' ],
            $user [ 'userId' ]
        );
        if ( empty ( $plans ) ) {
            throw new Exception ( '签到失败，你没有签到计划。' );
        }
        foreach ( $plans as $plan ) {
            $data = $factory->save (
                $user [ 'token' ],
                $user [ 'userId' ],
                config ( 'moguding.province' ),
                config ( 'moguding.city' ),
                config ( 'moguding.address' ),
                config ( 'moguding.longitude' ),
                config ( 'moguding.latitude' ),
                config ( 'moguding.type' ),
                config ( 'moguding.device' ),
                $plan [ 'planId' ],
                config ( 'moguding.description' )
            );
            if ( empty ( $data ) ) {
                throw new Exception ( '考勤失败。如果你看见了这个，一定要及时提 Issues，这意味着代码需要更新了。' );
            }
            $factory->sctSend (
                config ( 'moguding.sct.key' ),
                sprintf ( '%s %s %s', '蘑菇丁', ( ( config ( 'moguding.type' ) === 'START' ) ? '上班' : '下班' ), '打卡成功！' ),
                sprintf ( '%s%s%s', config ( 'moguding.province' ), ( config ( 'moguding.city' ) ?? '' ), config ( 'moguding.address' ) )
            );
            $this->info ( '打卡成功！' );
        }
    }
}
