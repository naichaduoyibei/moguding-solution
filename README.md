# 🍄蘑菇丁/自动签到解决方案

- [交流](#comminicate)
- [依赖](#depend)
- [功能](#feature)
- [特性](#peculiarity)
- [用法](#usage)
    - [GitHub Actions 部署（推荐）](#github-actions)
    - [本地部署](#local)
    - [宝塔计划任务部署](#bt-task)
- [消息推送](#message-push)
    - [Server 酱](#server-sct)
- [常见问题](#faq)

<a name="comminicate"></a>
## 交流

[交流群](https://qm.qq.com/cgi-bin/qm/qr?k=zbymM6W3sUh11Sjx9ZVDo8vbwL2YpWkL&jump_from=webapi) ID: 253228619

<a name="depend"></a>
## 依赖

- [php-moguding-sdk](https://github.com/laradocs/php-moguding-sdk)

<a name="feature"></a>
## 功能

- ✅ 上班（08:30）
- ✅ 下班（17:30）

<a name="peculiarity"></a>
## 特性

- ✅ 消息推送

<a name="usage"></a>
## 用法

<a name="github-actions"></a>
### 使用 GitHub Actions 部署（推荐）

> Note: 推荐使用 `Ctrl` + `F` 进行搜索

1. 点击 `fork` 将此项目克隆到你自己的仓库
2. 点击 `Settings` -> `Secrets` -> `Actions` -> `New repository secret`
3. 填写 `Name` 和 `Value`（见下方 参数列表）
4. 点击 `Add secret`

参数列表：

| Name         | Value             | 备注                                                   |
|--------------|-------------------|------------------------------------------------------|
| DEVICE       | `android` 或 `ios` | 系统名称                                                 |
| PHONE        | 手机号码              | 登录蘑菇丁的手机号码                                           |
| PASSWORD     | 密码                | 登录蘑菇丁的密码                                             |
| PROVINCE     | 省份                | 例如：`江西省` 或 `上海市`                                     |
| CITY         | 城市                | 例如：`南昌市`，注：如果在直辖市就不要填                                |
| ADDRESS      | 详细地址              | 可以登录蘑菇丁查看定位，把 `省份` 和 `城市` 去掉                         |
| LONGITUDE    | 经度                | 可以在这里查看：[经纬度查询 - 坐标拾取系统](https://jingweidu.bmcx.com) |
| LATITUDE     | 纬度                | 可以在这里查看：[经纬度查询 - 坐标拾取系统](https://jingweidu.bmcx.com) |
| DESCRIPTION  | 备注                | 非必填：意思是你想填就填，不想填就不填                                  |

5. 点击 `Actions`
6. 点击 `上班` 或 `下班`
7. 点击 `Run workflow`
8. 点击 `Run workflow`（绿色）

刷新网页之后你会看见 (棕色图标)上班 或 (棕色图标)下班

喝杯咖啡等待一会，直到 (棕色图标) 变成 (绿色图标)

如果你看见的是 (红色图标)

请点进去查看原因：

1. 点击 `上班` 或 `下班`
2. 点击 `build`
3. 点击 `(红色图标)Run sign`

然后再转到 [常见问题](#faq) 查找原因。

<a name="local"></a>
### 本地部署

1. 运行以下命令将此项目 `clone` 到本地
```bash
git clone git@github.com:laradocs/moguding-solution.git moguding
```

2. 使用以下命令进入 moguding 文件夹
```bash
cd moguding
```

3. 执行以下命令安装需要的依赖
```bash
composer install
```

4. 使用以下命令复制 .env.example 文件

Linux / MacOS:
```bash
cp .env.example .env
```

Windows:
```bash
copy .env.example .env
```

5. 打开 `.env` 文件填写相关配置

6. 最后运行以下命令即可
```bash
php artisan moguding
```

<a name="bt-task"></a>
### 宝塔计划任务部署

> 推荐使用: CentOS 7.x

1. 运行以下命令将此项目 `clone` 到本地
```
git clone git@github.com:laradocs/moguding-solution.git moguding
```
2. 把 moguding 文件夹 进行压缩

3. 把 `压缩包` 上传到服务器 `根目录`（你也可以上传到你能找到的目录）
4. 将 `压缩包` 解压至当前目录（我这里是 `根目录`）
5. 使用以下命令切换到 `moguding` 目录
```
cd moguding
```
6. 执行以下命令安装需要的依赖
```
composer install
```

7. 执行以下命令生成配置文件
```
cp .env.example .env
```
8. 打开 `.env` 文件填写相关配置
9. 点击 `计划任务`
10. 填写 `任务名称` -> `执行周期` -> `脚本内容`

脚本内容（以 `根目录` 为例）：
```
cd moguding && php artisan moguding
```

11. 点击 `添加任务`

<a name="message-push"></a>
## 消息推送

<a name="server-sct"></a>
### Server 酱

使用 `Server 酱` 进行消息推送：

1. 打开 [Server 酱](https://sct.ftqq.com) 官网
2. 点击 `SendKey`（第一次需要登录） -> `复制`
3. 回到 `moguding-solution` 项目
4. 点击 `Settings` -> `Secrets` -> `Actions` -> `New repository secret`
5. 填写 `Name` 和 `Value`（见下方 参数列表）
6. 点击 `Add secret`

参数列表：

| Name    | Value           | 备注             |
|---------|-----------------|----------------|
| SENDKEY | 刚才复制的 `SendKey` | 非必填：不填代表不使用此功能 |

<a name="faq"></a>
## 常见问题

1. Error: Process completed with exit code 1.

```
In CurlFactory.php line 210:
                                                                               
  cURL error 28: Connection timed out after 1502 milliseconds (see https://cu  
  rl.haxx.se/libcurl/c/libcurl-errors.html) for https://api.moguding.net:9000  
  /session/user/v1/login                                                       
                                                                               

Error: Process completed with exit code 1.
```

如果出现了上面的字样，重新执行 `workflow` 即可。

如果有其他问题，请及时在 [Issues](https://github.com/laradocs/moguding-solution/issues) 提出。
