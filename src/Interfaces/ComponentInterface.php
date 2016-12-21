<?php

namespace Egg\Interfaces;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

interface ComponentInterface
{
    public function init();
    public function __invoke(Request $request, Response $response, callable $next = null);
    public function run(Request $request, Response $response, ComponentInterface $next);
}