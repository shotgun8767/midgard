<?php


if (!function_exists('is_debug')) {
    /**
     * 是否开启测试模式
     * @return bool
     */
    function is_debug() : bool
    {
        return env('app.debug') ? true : false;
    };
}

if (!function_exists('scene')) {
    /**
     * 获取场景
     * @return string
     */
    function scene() : string
    {
        return env('app.scene');
    };
}

if (!function_exists('begin')) {
    /**
     * 开始计时
     * @param string $scene
     */
    function begin(string $scene = 'default_scene') : void
    {
        $GLOBALS['time'][$scene] = microtime();
    }
}

if (!function_exists('stop')) {
    /**
     * 计时结束
     * @param string $scene
     */
    function stop(string $scene = 'default_scene') : void
    {
        if (is_debug()) {
            $now = explode(' ', microtime());
            $begin = explode(' ', $GLOBALS['time'][$scene]);
            $s = $now[1] - $begin[1];
            if ($s < 0) $s += 1.0;
            $ms = str_split(substr($now[0] - $begin[0], 0, 5));
            foreach ($ms as $key => $char) {
                if ($char !== '0' && $char !== '.') {
                    break;
                } else {
                    unset($ms[$key]);
                }
            }
            $ms = implode($ms);

            echo
                "<div style='font-family: Consolas,serif;'>" .
                "[<span style='color: red'>$scene</span>] used time: " .
                "<span style='color: blue'>$s</span>" .
                " second(s) <span style='color: green'>$ms</span>" .
                " milliseconds. </div><br>";
        }
    }
}