<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'আপনার সেশনের মেয়াদ শেষ হয়েছিল। অনুগ্রহ করে পেজটি রিফ্রেশ করুন।',
                    'csrf_token' => csrf_token(),
                ], 419);
            }

            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation', '_token'))
                ->with('warning', 'আপনার সেশনের মেয়াদ শেষ হয়েছিল। পেজটি রিফ্রেশ করে পুনরায় চেষ্টা করুন।');
        });

        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'আপনার সেশনের মেয়াদ শেষ হয়েছিল। অনুগ্রহ করে পেজটি রিফ্রেশ করুন।',
                        'csrf_token' => csrf_token(),
                    ], 419);
                }

                return redirect()->back()
                    ->withInput($request->except('password', 'password_confirmation', '_token'))
                    ->with('warning', 'আপনার সেশনের মেয়াদ শেষ হয়েছিল। পেজটি রিফ্রেশ করে পুনরায় চেষ্টা করুন।');
            }
        });
    }
}
