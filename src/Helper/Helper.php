<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Helper;

use Ramsey\Uuid\Uuid;
use Overtrue\Http\Client;

class Helper
{

    /**
     * 获取uuid4
     *
     * @return string
     */
    public static function uuid4():string
    {
        return Uuid::uuid4()->getHex()->toString();
    }

    /**
     * 下载文件
     *
     * @param string $remotePath 远程地址
     * @param string $filePath
     * @return string|false
     */
    public static function downloadFile(string $remotePath, string $filePath)
    {
        $response = Client::create([
            'response_type' => 'raw',
        ])->request($remotePath, 'GET', [
            'verify' => false,
            'http_errors' => false,
        ]);

        if (@file_put_contents($filePath, $response->getBody()->getContents())) {
            return $filePath;
        }
        return false;
    }

    /**
     * 获取临时目录
     *
     * @return string
     */
    public static function getTempDir(): string
    {
        return sys_get_temp_dir();
    }

    /**
     * 申请临时文件
     *
     * @param string $prefix
     * @param string $dir
     * @return false|string
     */
    public static function getTempFileName(string $dir, string $prefix = ''): false|string
    {
        return tempnam($dir, $prefix);
    }

    /**
     * 是否是远程地址
     *
     * @param $url
     * @return false|int
     */
    public static function isUrl($url)
    {
        return preg_match('/^http[s]?:\/\//', $url);
    }

    /**
     * 获取文件mime类型
     *
     * @param $filePath
     * @return false|string
     */
    public static function getMimeType($filePath)
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($filePath);
    }

    /**
     * 删除文件
     *
     * @param string $filePath
     * @return bool
     */
    public static function deleteFile(string $filePath)
    {
        return @unlink($filePath);
    }

}