<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

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
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof TokenMismatchException) {
            return $this->renderTokenMismatch($request);
        }

        if ($this->isHttpException($exception)) {
            if ($exception->getStatusCode() == 404) {
                return response()->view('front.page.' . '404', [], 404);
            }
        }

        return parent::render($request, $exception);
    }

    /**
     * 419 (CSRF): записуємо в лог, що саме прийшло на сервер, і замість
     * сторінки «Page Expired» повертаємо на форму зі збереженим введенням.
     */
    protected function renderTokenMismatch($request)
    {
        $sessionCookie = config('session.cookie');

        Log::warning('CSRF token mismatch (419)', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'is_secure' => $request->isSecure(),
            'has_token_input' => $request->has('_token'),
            'token_input_length' => strlen((string) $request->input('_token')),
            'has_session_cookie' => $request->hasCookie($sessionCookie),
            'session_has_token' => $request->hasSession() && (bool) $request->session()->token(),
            'post_fields' => count($request->request->all()),
            'content_type' => $request->header('Content-Type'),
            'content_length' => $request->header('Content-Length'),
            'post_max_size' => ini_get('post_max_size'),
            'referer' => $request->header('Referer'),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Сесія застаріла. Оновіть сторінку й спробуйте ще раз.'], 419);
        }

        $back = redirect()->back()
            ->withInput($request->except(['_token', 'password', 'password_confirmation', 'new_password', 'new_password_confirmation']))
            ->with('error', 'Сесія застаріла — дані збережено, перевірте їх і надішліть ще раз.');

        // Лист кандидату: відкрити ту саму форму після повернення
        $route = $request->route();
        if ($route && $route->getName() === 'admin.shops.leads.send') {
            $back->with('reopen_lead', (int) $route->parameter('id'));
        }

        return $back;
    }
}
