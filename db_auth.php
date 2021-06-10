<?php
use devCoder\DotEnv;
(new DotEnv(__DIR__ . '/.env'))->load();
echo getenv('DATABASE_DNS')
?>