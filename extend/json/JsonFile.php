<?php

namespace json;

use SplFileInfo;

/**
 * Class JsonFile
 * @package json
 * @since 2019/9/25
 * @author shotgun8767
 */
class JsonFile extends SplFileInfo
{
    /**
     * 合法的JSON文件后缀名
     */
    const JSON_FILE_EXTENSION = ['json', 'json5'];

    /**
     * 文件内容
     * @var array
     */
    protected $content;

    /**
     * JsonFile constructor.
     * @param $file_name
     * @throws JsonFileException
     */
    public function __construct($file_name)
    {
        parent::__construct($file_name);
        if (!in_array($this->getExtension(), self::JSON_FILE_EXTENSION)) {
            throw new JsonFileException('object file is not a valid json file');
        }
    }

    /**
     * 获取Json文件内容，以数组的方式返回
     * @return array|null
     * @throws \Exception
     */
    public function getContent() : ?array
    {
        if (is_null($this->content)) {
            $content = file_get_contents($this->getPathname());

            try {
                return json_decode($content, true);
            } catch (\Exception $e) {
                throw $e;
            }
        } else {
            return $this->content;
        }
    }
}