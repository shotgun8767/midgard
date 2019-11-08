<?php
namespace app;

use sek\BaseException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\Request;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 添加自定义异常处理机制
        if ($e instanceof BaseException) {
            // sek异常
            $exception = [
                'request_url' => Request::url(),
                'message' => $e->getMessage(),
                'data' => $e->getData()
            ];

            if ($errcode = $e->getErrcode()) {
                $exception['errcode'] = $errcode;
            }

            return json($exception, $e->getCode());
        } else {
            if (!is_debug() && config('app.error_log')) {
                // 记录错误日志
                $this->report($e);
            }
        }

        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
}
