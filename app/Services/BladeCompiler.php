<?php

namespace App\Services;

use Illuminate\View\Compilers\BladeCompiler AS IlluminateBladeCompiler;

class BladeCompiler extends IlluminateBladeCompiler
{
    /**
     * Array of opening and closing tags for regular echos.
     *
     * @var array
     */
    protected $contentTags = ['{{%', '%}}'];

    /**
     * Array of opening and closing tags for escaped echos.
     *
     * @var array
     */
    protected $escapedTags = ['{%', '%}'];
}
