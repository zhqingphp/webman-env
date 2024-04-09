<?php

use zhqingphp\env\EnvHelper;

if (!function_exists('new_env_helper')) {
    /**
     * @return EnvHelper
     */
    function new_env_helper(): EnvHelper {
        global $EnvHelperClass, $argv;
        if (empty($EnvHelperClass) || !empty($argv)) {
            $key_type = config('plugin.zhqingphp.env.app.key_type');
            $on_group = config('plugin.zhqingphp.env.app.on_group');
            $read_type = config('plugin.zhqingphp.env.app.read_type');
            $EnvHelperClass = new EnvHelper(base_path('.env'), $key_type, $on_group, $read_type);
        }
        return $EnvHelperClass;
    }
}

if (!function_exists('env_set')) {
    /**
     * 设置env
     * @param array|string|int $key
     * @param mixed $val
     * @return int
     */
    function env_set(array|string|int $key, mixed $val = ""): int {
        return new_env_helper()->set($key, $val);
    }
}

if (!function_exists('env_get')) {
    /**
     * 获取env
     * @param string|int|null $key
     * @param mixed $default
     * @return mixed
     */
    function env_get(string|int|null $key = null, mixed $default = ''): mixed {
        return new_env_helper()->get($key, $default);
    }
}

if (!function_exists('env_del')) {
    /**
     * 删除env
     * @param array|string|int $key
     * @param bool $del_group
     * @return int
     */
    function env_del(array|string|int $key, bool $del_group = true): int {
        return new_env_helper()->del($key, $del_group);
    }
}