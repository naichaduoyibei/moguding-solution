<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laradocs\Moguding\Extensions\Notification;
use Exception;
use Laradocs\Moguding\MogudingManager;

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
    protected $description = '蘑菇丁自动签到';

    protected MogudingManager $manager;

    protected Notification $notification;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MogudingManager $manager, Notification $notification)
    {
        parent::__construct();

        $this->manager = $manager;
        $this->notification = $notification;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $parameters = [
            config('moguding.device'),
            config('moguding.phone'),
            config('moguding.password'),
        ];
        $user = $this->login(...$parameters);
        unset($parameters);

        $parameters = [
            $user['token'],
            $user['userType'],
            $user['userId'],
        ];
        $plans = $this->plans(...$parameters);
        unset($parameters);

        foreach ($plans as $plan) {
            $parameters = [
                $user['token'],
                $user['userId'],
                config('moguding.province'),
                config('moguding.city'),
                config('moguding.address'),
                config('moguding.longitude'),
                config('moguding.latitude'),
                $this->argument('type') ?? config('moguding.type'),
                config('moguding.device'),
                $plan['planId'],
                config('moguding.description'),
                config('moguding.country'),
            ];
            $data = $this->save(...$parameters);
            unset($parameters);
        }
        $this->info("[{$data['createTime']}] 签到成功！");
        $parameters = [
            config('moguding.sct.key'),
            "[{$data['createTime']}] 签到成功",
            sprintf(
                '%s%s%s%s',
                config('moguding.country'),
                config('moguding.province'),
                config('moguding.city'),
                config('moguding.address')
            )
        ];
        try {
            $this->notification->sct(...$parameters);
        } catch (Exception $e) {
            $this->warn('[Server 酱] 超过当天的发送次数限制 或 SendKey 配置无效。');
        }
        unset($parameters);
    }

    protected function login(string $device, string $phone, string $password): array
    {
        try {
            $user = $this->manager->login($device, $phone, $password);
        } catch (Exception $e) {
            $message = '请求超时！';
            if ($getMessage = $e->getMessage()) {
                $message = $getMessage;
            }

            throw new Exception($message);
        }

        return $user;
    }

    protected function plans(string $token, string $userType, int $userId): array
    {
        try {
            $plans = $this->manager->getPlan($token, $userType, $userId);
        } catch (Exception $e) {
            $message = '请求超时！';
            if ($getMessage = $e->getMessage()) {
                $message = $getMessage;
            }

            throw new Exception($message);
        }

        return $plans;
    }

    protected function save(string $token, int $userId, string $province, ?string $city, string $address, float $longitude, float $latitude, string $type, string $device, string $planId, ?string $description = null, string $country = '中国'): array
    {
        try {
            $data = $this->manager->save($token, $userId, $province, $city, $address, $longitude, $latitude, $type, $device, $planId, $description, $country);
        } catch (Exception $e) {
            $message = '请求超时！';
            if ($getMessage = $e->getMessage()) {
                $message = $getMessage;
            }

            throw new Exception($message);
        }

        return $data;
    }
}
