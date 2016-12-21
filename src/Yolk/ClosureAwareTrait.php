<?php

namespace Egg\Yolk;

use Closure;

trait ClosureAwareTrait
{
    protected $closure;

    public function setClosure(Closure $closure)
    {
        $this->closure = $closure;
    }

    public function getClosure()
    {
        return $this->closure;
    }

    public function bindTo($newthis, $newscope = 'static')
    {
        $this->closure = $this->closure->bindTo($newthis, $newscope);
        return $this;
    }
}