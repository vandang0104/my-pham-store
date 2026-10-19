<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Test trong thư mục Feature dùng TestCase của Laravel (có app, HTTP, DB).
|
*/

pest()->extend(TestCase::class)
    ->in('Feature');
