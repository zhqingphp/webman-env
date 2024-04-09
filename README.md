```
composer require zhqingphp/webman-env
```

* 配置文件app.php中可以设置关闭分组，大小写设置

#### env文件中如要使用true,false,null,要使用单/双引号

```
WEB_PORT=8787

[APP]
VERSION = 1.0.0
DEBUG = 'true'

[MYSQL.ADMIN]
DB_HOST = 127.0.0.1
DB_PORT = 3306
DB_USER = root
```

#### 获取 `env_get(string|int|null $key = null, mixed $default = '');`

* $key null=获取全部

```php
print_r(env_get('WEB_PORT'))
print_r(env_get('APP.VERSION'))
print_r(env_get('MYSQL.ADMIN.DB_HOST'))
```

#### 设置 `env_set(array|string|int $key, mixed $val = "");`

* $key 支持array多个设置

```php
env_set('WEB_PORT',8080);
env_set('MYSQL.ADMIN.DB_USER','admin');
env_set([
     'WEB_PORT'=>8080,
     'MYSQL.ADMIN.DB_USER'=>'root',
]);
```

#### 删除 `env_del(array|string|int $key, bool $del_group = true);`

* $key 支持array多个删除
* $del_group true=支持删除分组

```php
env_del('WEB_PORT');
env_del('MYSQL.ADMIN.DB_HOST');
env_del(['WEB_PORT','MYSQL.ADMIN.DB_USER']);
```