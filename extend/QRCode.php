<?php

use Endroid\QrCode\{ErrorCorrectionLevel, QrCode as core};
use Endroid\QrCode\Response\QrCodeResponse;
use Endroid\QrCode\Exception\InvalidPathException;

/**
 * Class QRCode
 * @mixin core
 * @package utility\plugin
 */
class QRCode
{
    /**
     * ErrorCorrectionLevels 二维码容错度
     */
    const ECL_LOW = 'low';
    const ECL_MEDIUM = 'medium';
    const ECL_QUARTILE = 'quartile';
    const ECL_HIGH = 'high';

    /**
     * QrCode实例
     * @var core
     */
    protected $QRCode;

    /**
     * 文件类型
     * @var string
     */
    protected $ext = 'png';

    /**
     * 文件名
     * @var string
     */
    protected $filename = '';

    /**
     * QRCode constructor.
     * @param string $text  二维码内文（即扫二维码得到的文字）
     * @param int $size     二维码大小
     * @param int $margin   二维码边距
     */
    public function __construct(string $text = '', int $size = 200, int $margin = 0)
    {
        $this->QRCode = new core($text);
        $this->QRCode->setSize($size);
        $this->QRCode->setMargin($margin);
    }

    /**
     * 静态方法创建实例
     * @param string $text  二维码内文（即扫二维码得到的文字）
     * @param int $size     二维码大小
     * @param int $margin   二维码边距
     * @return QRCode
     */
    public static function new(string $text = '', int $size = 200, int $margin = 0)
    {
        return new self($text, $size, $margin);
    }

    /**
     * 工厂模式
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        call_user_func_array([$this->QRCode, $name], $arguments);
    }

    /**
     * 设置二维码图片后缀名
     * @param $ext
     * @return QRCode
     */
    public function setExt($ext) : self
    {
        $this->ext = $ext;
        return $this;
    }

    /**
     * 保存二维码图片
     * @param string $dir
     * @param string $filename
     * @return string 二维码路径
     */
    public function saveTo(string $dir, string $filename) : string
    {
        $this->filename = $filename;
        $path = $dir . '/'. $this->getBaseName();
        $this->writeFile($path);

        return $path;
    }

    /**
     * 获取文件名（带有后缀名）
     * @return string
     */
    public function getBaseName() : string
    {
        return $this->filename . '.' . $this->ext;
    }

    /**
     * 设置二维码容错度
     * @param string $ECL ErrorCorrectionLevels
     * @return QRCode
     */
    public function setECL(string $ECL) : self
    {
        $this->setErrorCorrectionLevel(new ErrorCorrectionLevel($ECL));

        return $this;
    }

    /**
     * 设置商标
     * @param string $path  商标图片地址
     * @param int $width    商标图片宽
     * @param int $height   商标图片高
     * @return QRCode
     * @throws InvalidPathException
     */
    public function setLogo(string $path, int $width, int $height) : self
    {
        $this->setLogoPath($path);
        $this->setLogoSize($width, $height);

        return $this;
    }

    /**
     * 获取响应实例
     * @return QrCodeResponse
     */
    public function getResponse() : QrCodeResponse
    {
        return new QrCodeResponse($this->QRCode);
    }

    /**
     * 设置二维码底部说明文字
     * @param string $text      文字内容
     * @param int $fontSize     文字大小
     * @return QRCode
     */
    public function setLabel(string $text, int $fontSize) : self
    {
        $this->QRCode->setLabel($text, $fontSize, null, true);
        return $this;
    }

    /**
     * 设置二维码底部说明文字边距
     * @param int|null $top     上边距
     * @param int|null $right   右边距
     * @param int|null $bottom  下边距
     * @param int|null $left    左边距
     * @return QRCode
     */
    public function setLabelMargin(?int $top = null, ?int $right = null, ?int $bottom = null, int $left = null) : self
    {
        $margin = [];
        if (is_int($top))    $margin['t'] = $top;
        if (is_int($right))  $margin['r'] = $right;
        if (is_int($bottom)) $margin['b'] = $bottom;
        if (is_int($left))   $margin['l'] = $left;

        $this->QRCode->setLabelMargin($margin);
        return $this;
    }
}