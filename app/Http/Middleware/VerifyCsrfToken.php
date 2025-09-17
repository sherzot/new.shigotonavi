<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * CSRF チェックから除外されるルート。
     *
     * @var array<int, string>
     */
    protected $except = [
        'matchings/filterJobs',
        'resume/skip',
        'resume/continue',
    ];


    /**
     * 除外ルートを決定する方法。
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
