<?php

namespace Egg\Component\Http;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Egg\Component\AbstractComponent;
use Egg\Interfaces\ComponentInterface as Component;
use Neomerx\Cors\Analyzer;
use Neomerx\Cors\Contracts\AnalysisResultInterface;
use Neomerx\Cors\Strategies\Settings as SettingsStrategy;

class Cors extends AbstractComponent
{
    use \Psr\Log\LoggerAwareTrait;

    protected $settingsStrategy;

    public function __construct(array $settings = [])
    {
        $this->dependencies = [
            \Egg\Component\Http\Exception::class,
        ];

        $this->settingsStrategy = new SettingsStrategy();
        $this->settings = array_merge([
            'origin' => '*',
            'methods' => [],
            'headers.allow' => [],
            'headers.expose' => [],
            'credentials' => false,
            'cache' => 0,
        ], $settings);
    }

    public function run(Request $request, Response $response, Component $next)
    {
        $analyzer = Analyzer::instance($this->buildsettingsStrategy());
        if ($this->logger) {
            $analyzer->setLogger($this->logger);
        }
        $cors = $analyzer->analyze($request);
        switch ($cors->getRequestType()) {
            case AnalysisResultInterface::ERR_ORIGIN_NOT_ALLOWED:
                throw new \Egg\Http\Exception($response, 401, new \Egg\Http\Error(array(
                    'name'          => 'not_allowed_cors_origin',
                    'description'   => 'CORS request origin is not allowed.',
                )));

            case AnalysisResultInterface::ERR_METHOD_NOT_SUPPORTED:
                throw new \Egg\Http\Exception($response, 401, new \Egg\Http\Error(array(
                    'name'          => 'unsupported_cors_method',
                    'description'   => 'CORS request method is not supported.',
                )));

            case AnalysisResultInterface::ERR_HEADERS_NOT_SUPPORTED:
                throw new \Egg\Http\Exception($response, 401, new \Egg\Http\Error(array(
                    'name'          => 'unsupported_cors_headers',
                    'description'   => 'CORS request headers are not supported.',
                )));

            case AnalysisResultInterface::TYPE_PRE_FLIGHT_REQUEST:
                $cors_headers = $cors->getResponseHeaders();
                foreach ($cors_headers as $header => $value) {
                    if (false === is_array($value)) {
                        $value = (string)$value;
                    }
                    $response = $response->withHeader($header, $value);
                }
                return $response->withStatus(200);

            case AnalysisResultInterface::TYPE_REQUEST_OUT_OF_CORS_SCOPE:
                return $next($request, $response);

            default:
                /* Actual CORS request. */
                $cors_headers = $cors->getResponseHeaders();
                foreach ($cors_headers as $header => $value) {
                    if (false === is_array($value)) {
                        $value = (string)$value;
                    }
                    $response = $response->withHeader($header, $value);
                }
                $this->container['response'] = $response;
                return $next($request, $response);
        }
    }

    protected function buildsettingsStrategy()
    {
        $origin = array_fill_keys((array) $this->settings['origin'], true);
        $this->settingsStrategy->setRequestAllowedOrigins($origin);

        $methods = array_fill_keys($this->settings['methods'], true);
        $this->settingsStrategy->setRequestAllowedMethods($methods);

        $headers = array_fill_keys($this->settings['headers.allow'], true);
        $headers = array_change_key_case($headers, CASE_LOWER);
        $this->settingsStrategy->setRequestAllowedHeaders($headers);

        $headers = array_fill_keys($this->settings['headers.expose'], true);
        $this->settingsStrategy->setResponseExposedHeaders($headers);

        $this->settingsStrategy->setRequestCredentialsSupported($this->settings['credentials']);

        $this->settingsStrategy->setPreFlightCacheMaxAge($this->settings['cache']);

        return $this->settingsStrategy;
    }
}