<?php

namespace zhqingphp\env;
class EnvHelper {
    protected string $env_file;
    protected array $env_arr = [];
    private array $env_array = [];
    protected bool|null $key_type = true;

    /**
     * @param string $env_file
     * @param bool|null $key_type
     * @param bool $on_group
     * @param bool $read_type
     */
    public function __construct(
        string    $env_file,
        bool|null $key_type = true,
        bool      $on_group = true,
        bool      $read_type = true
    ) {
        $this->env_file = $env_file;
        $this->key_type = $key_type;
        $this->array($on_group, $read_type);
    }

    /**
     * @param array|string|int $key
     * @param mixed $val
     * @return int
     */
    public function set(
        array|string|int $key,
        mixed            $val = ""
    ): int {
        $i = 0;
        $array = is_array($key) ? $key : [$key => $val];
        $rep = function ($val) {
            return ($val === null ? "'null'" : ($val === true ? "'true'" : ($val === false ? "'false'" : $val)));
        };
        foreach ($array as $k => $v) {
            ++$i;
            $keys = $this->convert(trim($k));
            $arr = explode(".", $keys);
            if (!empty($one = ($arr[0] ?? "")) && !empty($two = ($arr[1] ?? ""))) {
                if (
                    empty(isset($this->env_array[$one]))
                    || is_array($this->env_array[$one])
                ) {
                    if (count($arr) > 2) {
                        $one = join(".", array_slice($arr, 0, -1));
                        $two = join(".", array_slice($arr, -1));
                    }
                    $this->env_array[$one][$two] = $rep($v);
                    $this->env_arr[$one][$two] = $v;
                } else {
                    $this->env_array[$keys] = $rep($v);
                    $this->env_arr[$keys] = $v;
                }
            } else {
                $this->env_array[$keys] = $rep($v);
                $this->env_arr[$keys] = $v;
            }
        }
        return $this->save($i);
    }

    /**
     * @param string|int|null $key
     * @param mixed $default
     * @return mixed
     */
    public function get(
        string|int|null $key = null,
        mixed           $default = ""
    ): mixed {
        if (isset($key)) {
            $keys = $this->convert($key);
            if (empty($data = $this->env_arr[$keys] ?? "")) {
                $arr = explode(".", $keys);
                if (
                    !empty($one = ($arr[0] ?? ""))
                    && !empty($two = ($arr[1] ?? ""))
                ) {
                    if (count($arr) > 2) {
                        $one = join(".", array_slice($arr, 0, -1));
                        $two = join(".", array_slice($arr, -1));
                    }
                    $data = (!empty($data = $this->env_arr[$one][$two] ?? "")) ? $data : "";
                }
            }
            return $data ?: $default;

        }
        return $this->env_arr;
    }

    /**
     * @param array|string|int $key
     * @param bool $del_group
     * @param int $i
     * @return int
     */
    public function del(
        array|string|int $key,
        bool             $del_group = true,
        int              $i = 0
    ): int {
        $array = is_array($key) ? $key : [$key];
        foreach ($array as $k) {
            $keys = $this->convert(trim($k));
            if (
                isset($this->env_array[$keys])
                && (
                    $del_group === true
                    || empty(is_array($this->env_array[$keys]))
                    || empty($this->env_array[$keys] ?? "")
                )
            ) {
                ++$i;
                unset($this->env_array[$keys]);
                unset($this->env_arr[$keys]);
            } else {
                $arr = explode(".", $keys);
                if (
                    !empty($one = ($arr[0] ?? ""))
                    && !empty($two = ($arr[1] ?? ""))
                ) {
                    if (count($arr) > 2) {
                        $one = join(".", array_slice($arr, 0, -1));
                        $two = join(".", array_slice($arr, -1));
                    }
                    if (
                        isset($this->env_array[$one][$two])
                        && (
                            empty(is_array($this->env_array[$one][$two]))
                            || empty($this->env_array[$one][$two] ?? "")
                        )
                    ) {
                        unset($this->env_array[$one][$two]);
                        unset($this->env_arr[$one][$two]);
                        if (
                            $del_group === true
                            && isset($this->env_array[$one])
                            && is_array($this->env_array[$one])
                            && count($this->env_array[$one]) == 0
                        ) {
                            unset($this->env_array[$one]);
                            unset($this->env_arr[$one]);
                        }
                        ++$i;
                    }
                }
            }
        }
        return $this->save($i);
    }

    /**
     * @param int $i
     * @param string $ini
     * @return int
     */
    protected function save(int $i = 0, string $ini = ""): int {
        if ($i > 0) {
            $rep = function ($val) {
                $val = is_callable($val) ? $val() : $val;
                $val = is_array($val) ? json_encode($val) : $val;
                return (
                is_string($val)
                    ? (in_array(strtolower($val), ["null", "true", "false"])
                    ? ("'" . $val . "'")
                    : $val
                ) : $val
                );
            };
            $env = "";
            foreach ($this->env_array as $key => $val) {
                if (is_array($val)) {
                    $ini .= PHP_EOL . "[" . $key . "]" . PHP_EOL;
                    foreach ($val as $k => $v) {
                        $ini .= $k . " = " . $rep($v) . PHP_EOL;
                    }
                } else {
                    $env .= $key . " = " . $rep($val) . PHP_EOL;
                }
            }
            @file_put_contents($this->env_file, trim($env . $ini, PHP_EOL));
        }
        return $i;
    }

    /**
     * @param bool $on_group
     * @param bool $read_type
     * @return $this
     */
    protected function array(
        bool $on_group = true,
        bool $read_type = true
    ): static {
        $array = (
        $read_type
            ? @parse_ini_file($this->env_file, $on_group)
            : (@parse_ini_string(@file_get_contents($this->env_file), $on_group))
        );
        if (!empty($array) && is_array($array)) {
            $rep = function ($val) {
                if (is_string($val)) {
                    $v = strtolower($val);
                    $val = ($v == 'null' ? null : ($v == 'false' ? false : ($v == 'true' ? true : $val)));
                }
                return $val;
            };
            foreach ($array as $key => $val) {
                $keys = $this->convert($key);
                if (!empty($val) && is_array($val)) {
                    foreach ($val as $k => $v) {
                        $ks = $this->convert($k);
                        $this->env_array[$keys][$ks] = $v;
                        $this->env_arr[$keys][$ks] = $rep($v);
                    }
                } else {
                    $this->env_array[$keys] = $val;
                    $this->env_arr[$keys] = $rep($val);
                }
            }
        }
        return $this;
    }

    /**
     * @param string|int $key
     * @return string|int
     */
    protected function convert(string|int $key): string|int {
        return ($this->key_type === true)
            ? strtoupper($key)
            : ($this->key_type === false
                ? strtolower($key)
                : $key
            );
    }
}