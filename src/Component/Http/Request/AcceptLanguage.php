<?php

namespace Egg\Component\Http\Request;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;

class AcceptLanguage extends AbstractComponent
{
    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Exception::class,
        ];

        $this->settings = $settings;
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $language = false;
        if ($request->hasHeader('Accept-Language')) {
            $acceptLanguageLine = $request->getHeaderLine('Accept-Language');
            $languages = $this->parseAcceptLanguageLine($acceptLanguageLine);
            $language = $this->findFirstMatchedLanguage($languages);
        }

        if (!$language) {
            throw new \Egg\Http\Exception(406, new \Egg\Http\Error(array(
                'name'          => 'not_acceptable',
                'description'   => sprintf('"Accept-Language" header must be in: %s', implode(', ', $this->settings['languages'])),
            )));
        }
        $response = $response->withHeader('Content-Language', $language);

        $response = $next($request, $response);

        return $response;
    }

    protected function parseAcceptLanguageLine($acceptLanguageLine)
    {
        list($languages) = explode(';', $acceptLanguageLine);

        return explode(',', $languages);
    }

    protected function findFirstMatchedLanguage(array $languages)
    {
        foreach ($languages as $language) {
            if (in_array($language, $this->settings['languages'])) {
                return $language;
            }
        }

        return false;
    }
}