<?php

return new \Phalcon\Config([
    'database' => [
        'adapter'  => 'Mysql',
        'host'     => getenv('DB_HOST'),
        'username' => getenv('DB_USERNAME'),
        'password' => getenv('DB_PASSWORD'),
        'dbname'   => getenv('DB_DATABASE'),
        'charset'  => 'utf8',
    ],

    'application' => [
        'controllersDir' => __DIR__ . '/../controllers/',
        'modelsDir'      => __DIR__ . '/../models/',
        'baseUri'        => '/'
    ],

    //七牛
    'qiniu' => [
        'accessKey' => getenv('QINIU_ACCESSKEY'),
        'secretKey' => getenv('QINIU_SECRETKEY'),
        'bucket'    => getenv('QINIU_BUCKET'),
        'img_host'  => '//img.sherman.com/',
    ],

    //wechat
    'wechat' => [
        /**
         * Debug 模式，bool 值：true/false 当值为 false 时，所有的日志都不会记录
         */
        'debug'  => getenv('WE_DEBUG'),
        /**
         * wechat账号基本信息，请从微信公众平台/开放平台获取
         */
        'app_id'  => getenv('WE_APPID'),         // AppID
        'secret'  => getenv('WE_AppSecret'),     // AppSecret
        'token'   => getenv('WE_TOKEN'),          // Token
        'aes_key' => getenv('WE_EncodingAESKey'),                    // EncodingAESKey，安全模式下请一定要填写
        /**
         * 日志配置
         * level: 日志级别, 可选为： debug/info/notice/warning/error/critical/alert/emergency
         * file：日志文件位置(绝对路径)，可写权限
         */
        'log' => [
            'level' => 'debug',
            'file'  => APP_PATH.'/logs/'.getenv('WE_LOG_PATH'),
        ],
        /**
         * OAuth 配置
         * scopes：公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login
         * callback：OAuth授权完成后的回调页地址
         */
        'oauth' => [
            'scopes'   => [getenv('WE_OAUTH_SCOPES')],
            'callback' => getenv('WE_OAUTH_CALLBACK'),
        ],
        /**
         * 微信支付
         */
        'payment' => [
            'merchant_id'        => getenv('WEPAY_MERCHANT_ID'),
            'key'                => getenv('WEPAY_MERCHANT_KEY'),
            'cert_path'          => APP_PATH.'/config/cer/'.getenv('WEPAY_CERT'), // XXX: 绝对路径
            'key_path'           => APP_PATH.'/config/cer/'.getenv('WEPAY_KEY'),      // XXX: 绝对路径
            // 'device_info'     => '',
            // 'sub_app_id'      => '',
            // 'sub_merchant_id' => '',
        ],
        'guzzle' => [
            'timeout' => 2.0, // 超时时间（秒）
            'verify' => false, // 关掉 SSL 认证
        ],
        /**
         * 摇周边
         */
        'around' => [
            // 开发者自定义的key，用来生成活动抽奖接口的签名参数。长度必须为32位
            'lottery_key' => '',
        ],
    ],

    //Hashids
    'hashids' => [
        'salt' => 'SHERMAN',
        'length' => '6',
        'alphabet' => 'asIBFOiuGphWbJTCRgZPAfQLMnY4lvEd5z61NkKcStjDXm327ryHx0ewqU8o9V',
    ],

    //sms
    'sms' => [
        'smsType' => 'mq',//启用短信服务商： ali:阿里大于；cl:创蓝；mq:MQ发送短信

        //阿里大于配置
        'ali' => [
            'format' => 'json',
            'appkey' => '',
            'secretKey' => '',
            //短信签名
            'smsFreeSignName' => '',
            //短信模板ID
            'smsTemplateCode' => '',
            //短信内容
            //您好，您的验证码是${code}
        ],

        //创蓝配置
        'cl' => [
            'apiSendUrl' => '',//创蓝发送短信接口URL
            'apiBalanceQueryUrl' => '',//创蓝短信余额查询接口URL
            'apiAccount' => '',//创蓝账号
            'apiPassword' => '',
            'context' => '您好，您的验证码是{code}',
        ],
    ],

    //ali mq
    'mq' => [
        //短信接口
        'sms' => [
            'producer' => 'PID-sms_001',
            'topic' => 'sherman-sms',
            'consumer' => 'CID-sms_001',
        ],
    ],

    'cacheKey' => [
        'key_sms' => ['key' => 'data_sms_%s', 'expire' => 600],
        'key_qiniu' => ['key' => 'data_qiniu_token', 'expire' => 3500],
        'key_users' => ['key' => 'data_member_%s', 'expire' => 600],
        'key_jsApiTicket' => ['key' => 'data_JsapiTicket', 'expire' => 7000],
        'key_wechatAccessToken' => ['key' => 'data_WechatAccessToken', 'expire' => 7000],
    ],
]);