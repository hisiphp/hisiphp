<?php

return [
    // 驱动方式
    'type'     => 'Mysql',
    // 缓存前缀
    'prefix'    => 'hisiphp',
    // 加密算法
    'algos'     => 'sha1',
    // 缓存有效期 0表示永久缓存
    'expire'   => 0,
    // 扩展驱动
    'extend' => ['Mysql'],
];