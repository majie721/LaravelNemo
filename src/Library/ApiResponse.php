<?php

namespace LaravelNemo\Library;

use LaravelNemo\Interface\IResponse;
use LaravelNemo\Library\App;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApiResponse implements IResponse
{
    /**
     * @var array 调试信息
     */
    protected array $debug = [];

    /** @var array 响应数据 */
    protected array $data;

    /** @var string 编号 */
    protected string $request_id = '';


    public function setDebug(array $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    public function setRequestId(string $str=''): self
    {
        $this->request_id = $str;
        return $this;
    }

    public function getRequestId(): string
    {
       return Utils::uniqueId();
    }

    /**
     * 返回成功
     *
     * @param string $msg
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function success(string $msg = 'success'): JsonResponse
    {
        return $this->data(null, $msg);
    }

    /**
     * 返回普通数据
     *
     * @param null $data
     * @param string $msg
     * @param int $code
     * @param int $httpCode
     * @return JsonResponse
     */
    public function data(
        $data = null,
        string $msg = 'success',
        int $code = 0,
        int $httpCode = 200
    ): JsonResponse
    {

        $resp = new CommonResp();
        $resp->code = $code;
        $resp->message = $msg;
        $resp->data = $data;
        $resp->request_id = $this->getRequestId();


        //如果不是线上环境, 显示调试信息
        if (App::isDebug()) {
            $resp->debug = $this->debug?:App::debugger();
        }

        return response()->json($resp, $httpCode, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * 返回错误
     *
     * @param string|null $msg
     * @param int $code
     * @param int $httpCode
     * @param null $data
     * @return JsonResponse
     */
    public function error(string $msg = null, int $code = 400, int $httpCode = 400, $data = null): JsonResponse
    {
        return $this->data($data, (string)$msg, $code, $httpCode);
    }

    /**
     * 下载文件内容
     * @param string $filename
     * @param string $content
     * @param array $headers
     * @return StreamedResponse
     */
    public function download(string $filename, string &$content, array $headers = []): StreamedResponse
    {
        return response()->streamDownload(function () use (&$content) {
            echo $content;
        }, $filename, $headers);
    }

    /**
     * @param string $file
     * @param string $filename
     * @param array $headers
     * @return BinaryFileResponse
     */
    public function downloadByFile(string $file, string $filename, array $headers = []): BinaryFileResponse
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $extTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'zip' => 'application/zip',
        ];
        $headers['Content-Type'] = $extTypes[$ext] ?? 'text/html';
        return response()->download($file, $filename, $headers);
    }

    /**
     * 预览文件内容
     * @param string $content
     * @param string $filename
     * @return StreamedResponse
     */
    public function preview(string $filename, string &$content): StreamedResponse
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $extTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'zip' => 'application/zip',
        ];
        $contentType = $extTypes[$ext] ?? 'text/html';
        return response()->streamDownload(function () use (&$content) {
            echo $content;
        }, $filename, ['Content-Type' => $contentType], 'inline');
    }
}
