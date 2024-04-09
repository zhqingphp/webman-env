<?php
return [
    'enable' => true,
    'key_type' => true, //key设置:null=原样(区分大小写);true=全大写,false=全小写(全大/小写时读取时不区分)
    'on_group' => true,//分组:true=开启,false=关闭
    'read_type' => true //读取文件方式:true=parse_ini_file  false=file_get_contents
];