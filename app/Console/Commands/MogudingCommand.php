<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laradocs\Moguding\Client;
use Laradocs\Moguding\Exceptions\RequestTimeoutException;
use Laradocs\Moguding\Exceptions\SendKeyInvalidException;
use Laradocs\Moguding\Exceptions\UnauthenticatedException;
use Laradocs\Moguding\Extensions\Notification;
use Exception;

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

    protected Client $factory;

    protected Notification $notification;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Client $factory, Notification $notification)
    {
        $this->factory = $factory;
        $this->notification = $notification;
        parent::__construct();
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
        $parameters = [
            config('moguding.sct.key'),
            "[{$data['createTime']}] 打卡成功",
            config('moguding.country') . config('moguding.province') . config('moguding.city') . config('moguding.address')
        ];
        $this->sct(...$parameters);
        unset($parameters);
        $this->info("[{$data['createTime']}] 签到成功！");
    }

    protected function login(string $device, string $phone, string $password): array
    {
        try {
            $user = $this->factory->login($device, $phone, $password);
        } catch (RequestTimeoutException) {
            $this->login($device, $phone, $password);
        } catch (UnauthenticatedException $e) {
            throw new Exception($e->getMessage());
        }

        return $user;
    }

    protected function plans(string $token, string $userType, int $userId): array
    {
        try {
            $plans = $this->factory->getPlan($token, $userType, $userId);
        } catch (RequestTimeoutException) {
            $this->plans($token, $userType, $userId);
        } catch (UnauthenticatedException $e) {
            throw new Exception($e->getMessage());
        }

        return $plans;
    }

    protected function save(string $token, int $userId, string $province, ?string $city, string $address, float $longitude, float $latitude, string $type, string $device, string $planId, ?string $description = null, string $country = '中国'): array
    {
        try {
            $data = $this->factory->save($token, $userId, $province, $city, $address, $longitude, $latitude, $type, $device, $planId, $description, $country);
        } catch (RequestTimeoutException) {
            $this->save($token, $userId, $province, $city, $address, $longitude, $latitude, $type, $device, $planId, $description, $country);
        } catch (UnauthenticatedException $e) {
            throw new Exception($e->getMessage());
        }

        return $data;
    }

    protected function sct(?string $sendKey = null, ?string $title = null, ?string $desp = null): void
    {
        try {
            $this->notification->sct($sendKey, $title, $desp);
        } catch (SendKeyInvalidException) {
            $this->warn('SendKey 配置错误。');
        }
    }
}
