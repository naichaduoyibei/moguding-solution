<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Exception;
use Laradocs\Moguding\Exceptions\SendKeyInvalidException;
use Laradocs\Moguding\Moguding;
use Laradocs\Moguding\Params\Address;
use Laradocs\Moguding\Params\Login;
use Laradocs\Moguding\Params\LoginParam;
use Laradocs\Moguding\Params\Save;
use Laradocs\Moguding\Params\SaveParam;
use Laradocs\Moguding\Params\User;
use Laradocs\Moguding\Params\UserParam;
use Laradocs\Moguding\Plugins\ServerChan;
use Laradocs\Moguding\Utils\Json;

class MogudingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moguding {type?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '蘑菇丁自动打卡';

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
        $moguding = new Moguding();

        $this->info('正在登录...');
        try {
            $data = (new Client())
                ->post('https://api.moguding.net:9000/session/user/v3/login', [
                    'json' => [
                        't' => bin2hex(openssl_encrypt((int)(microtime(true) * 1000), 'AES-128-ECB', '23DbtQHR2UMbH6mJ', OPENSSL_PKCS1_PADDING)),
                        'loginType' => config('moguding.device'),
                        'phone' => bin2hex(openssl_encrypt(config('moguding.phone'), 'AES-128-ECB', '23DbtQHR2UMbH6mJ', OPENSSL_PKCS1_PADDING)),
                        'password' => bin2hex(openssl_encrypt(config('moguding.password'), 'AES-128-ECB', '23DbtQHR2UMbH6mJ', OPENSSL_PKCS1_PADDING)),
                    ]
                ])
                ->getBody()
                ->getContents();
            $user = Json::decode($data)['data'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage() ?? '请求超时', $e->getCode(), $e);
        }

        $this->info('登录成功！');

        $this->info('正在获取用户信息...');

        $this->info('获取用户信息成功！');

        $this->info('正在获取计划列表...');
        // 获取计划列表
        try {
            $plans = $moguding->getPlanList(new UserParam(
                new User(
                    $user['token'],
                    $user['userId'],
                    $user['userType']
                )
            ));
        } catch (Exception $e) {
            throw new Exception($e->getMessage() ?? '请求超时', $e->getCode(), $e);
        }
        $this->info('获取用户信息成功！');

        $this->info('正在打卡中...');

        $bar = $this->output->createProgressBar(count($plans));
        $bar->start();

        // 打卡并推送通知
        $success = 0;
        $server = new ServerChan(config('moguding.sct.key'));
        foreach ($plans as $plan) {
            try {
                $save = $moguding->getSaveInfo(new SaveParam(
                    new Save(
                        new User(
                            $user['token'],
                            $user['userId'],
                            $user['userType'],
                        ),
                        new Address(
                            config('moguding.province'),
                            config('moguding.city'),
                            config('moguding.address'),
                            config('moguding.longitude'),
                            config('moguding.latitude'),
                            config('moguding.country')
                        ),
                        $plan['planId'],
                        config('moguding.device'),
                        config('moguding.type') ?? $this->argument('type'),
                        config('moguding.description'),
                    )
                ));
                ++$success;
                try {
                    $server->title("[{$save['createTime']}] 签到成功！")
                        ->desp(
                            sprintf(
                                '%s%s%s%s',
                                config('moguding.country'),
                                config('moguding.province'),
                                config('moguding.city'),
                                config('moguding.address')
                            )
                        )
                        ->send();
                } catch (SendKeyInvalidException $e) {
                    continue;
                }
            } catch (Exception $e) {
                continue;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info('打卡完成！成功：' . $success . ' 失败：' . (count($plans) - $success));

        return 0;
    }
}
