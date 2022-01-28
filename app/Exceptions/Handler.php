<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    protected function context()
    {
        if (auth()->check() && auth()->user()) {
            return array_merge(parent::context(), [
                'app main url' => config('app.url'),
                'current url' => url()->current(),
                'previous url' => url()->previous(),
                'user name' => auth()->user()->full_name,
                'user email' => auth()->user()->email,
                'request data' => request()->all(),
            ]);
        } else {
            return array_merge(parent::context(), [
                'app main url' => config('app.url'),
                'current url' => url()->current(),
                'previous url' => url()->previous(),
            ]);
        }

        return parent::context();
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // dd(trans('errors.signed'));
        if ($exception instanceof InvalidSignatureException) {
            return response()->view('errors.link-expired', [
                'code' => Response::HTTP_FORBIDDEN,
            ]);
        }

        return parent::render($request, $exception);
    }
}
