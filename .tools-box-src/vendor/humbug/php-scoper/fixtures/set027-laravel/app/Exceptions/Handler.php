<?php

namespace _HumbugBoxb47773b41c19\App\Exceptions;

use Exception;
use _HumbugBoxb47773b41c19\Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
class Handler extends ExceptionHandler
{
    protected $dontReport = [];
    protected $dontFlash = ['password', 'password_confirmation'];
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }
    public function render($request, Throwable $exception)
    {
        return parent::render($request, $exception);
    }
}
