<?php

namespace sek;

/**
 * Class HttpCode
 * @package sek
 * @since 2019/9/23
 * @author shotgun8767
 */
class HttpCode
{
    /**
     * [100, 200): 指定客户端相应的某些动作
     * [200, 300): 表示请求成功
     * [300, 400): 已经移动的文件/指定新的文件地址信息
     * [400, 500): 指出客户端的错误
     * [500, 600): 指出服务器错误
     * */

    // 同意客户端在后续请求中发送附件
    const SC_CONTINUE = 100;

    // 服务器将按照其上的头信息变为一个不同的协议
    const SC_SWITCHING_PROTOCOLS = 101;

    // 一切正常
    const SC_OK = 200;

    // 服务器在请求的响应中建立了新文档，在头信息中给出文档的URL
    const SC_CREATED = 201;

    // 告诉客户端请求正在被执行，但还没有处理完
    const SC_ACCEPTED = 202;

    // 文档被正常地返回
    const SC_NON_AUTHORITATIVE_INFORMATION = 203;

    // 无内容返回
    const SC_NO_CONTENT = 204;

    // 重置内容，用于强迫浏览器清除表单域
    const SC_RESET_CONTENT = 205;

    // 服务器完成一个包含Range头信息的局部请求时发送
    const SC_PARTIAL_CONTENT = 206;

    // 被请求的文档可以在多个地方找到
    const SC_MULTIPLE_CHOICES = 300;

    // 指所请求的文档在别的地方永久存放，文档新的URL会在定位响应头信息中给出
    const SC_MOVED_PERMANENTLY = 301;

    // 指所请求的文档在别的地方暂时存放，文档新的URL会在定位响应头信息中给出
    const SC_MOVED_TEMPORARILY = 302;

    // 与301相似。当请求为POST时，新文档应用GET找回
    const SC_SEE_OTHER = 303;

    // 指缓冲的版本已经被更新并且客户端应刷新文档
    const SC_NOT_MODIFIED = 304;

    // 表示所请求的文档要通过定位头信息中的代理服务器获得
    const SC_USE_PROXY = 305;

    // 与302相同
    const SC_TEMPORARY_REDIRECT = 307;

    // 指出客户端请求中的语法错误
    const SC_BAD_REQUEST = 400;

    // 表示客户端在授权头信息中没有有效的身份信息时访问受到密码保护的页面。
    const SC_UNAUTHORIZED = 401;

    // 服务器拒绝提供所请求的资源
    const SC_FORBIDDEN = 403;

    // 服务器无法找到资源
    const SC_NOT_FOUND = 404;

    // 方法指出请求方法(GET, POST, HEAD, PUT, DELETE等)对某些特定的资源不允许使用
    const SC_METHOD_NOT_ALLOWED = 405;

    // 请求资源的MIME类型与客户端中Accept头信息中指定的类型不一致
    const SC_NOT_ACCEPTABLE = 406;

    // 指出客户端必须通过代理服务器的认证
    const SC_PROXY_AUTHENTICATION_REQUIRED = 407;

    // 指服务端等待客户端发送请求的时间过长
    const SC_REQUEST_TIMEOUT = 408;

    // 指出试图上传文件版本不正确
    const SC_CONFLICT = 409;

    // 指出所请求的文档已经不存在并且没有更新的地址
    const SC_GONE = 410;

    // 表示服务器不能处理请求，除非客户端发送头信息指出发送给服务器的数据的大小
    const SC_LENGTH_REQUIRED = 411;

    // 指出请求头信息中的某些先决条件是错误的
    const SC_PRECONDITION_FAILED = 412;

    // 指出现在所请求的文档比服务器现在想要处理的要大
    const SC_REQUEST_ENTITY_TOO_LARGE = 413;

    // 指出URI过长（URI指的是URL中主机、域名、端口号后的内容）
    const SC_REQUEST_TOO_LONG = 414;

    // 指出请求所带的附件的格式类型服务器无法处理
    const SC_UNSUPPORTED_MEDIA_TYPE = 415;

    // 表示客户端包含了一个服务器无法满足的Range头信息的请求
    const SC_REQUESTED_RANGE_NOT_SATISFIABLE = 416;

    // 服务器拒绝在后面的请求中接受附件
    const SC_EXCEPTION_FAILED = 417;

    // 请求格式正确，但是由于含有语义错误，无法响应。
    const SC_UNPROCESSABLE_ENTITY = 422;

    // 服务器内部错误
    const SC_INTERNAL_SERVER_ERROR = 500;

    // 指出服务器不支持请求中的功能
    const SC_NOT_IMPLEMENTED = 501;

    // 指出接收服务器接收到远端服务器的错误响应
    const SC_BAD_GATEWAY = 502;

    // 表示服务器由于在维护或已经超载而无法响应
    const SC_SERVICE_UNAVAILABLE = 503;

    // 指出接收服务器没有从远端服务器得到及时的响应（响应超时）
    const SC_GATEWAY_TIMEOUT = 504;

    // 指出服务器并不支持在请求中所标明 HTTP 版本
    const SC_HTTP_VERSION_NOT_SUPPORTED = 505;
}