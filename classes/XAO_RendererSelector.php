<?php

use XAO\Renderers\RendererInterface;
use XAO\Renderers\JsonRenderer;
use XAO\Renderers\HtmxRenderer;
use XAO\Renderers\XsltRenderer;

/**
 * Very small helper to choose a renderer based on env, query or default.
 */
class XaoRendererSelector
{
    public static function fromEnvOrQuery(string $default = 'json'): RendererInterface
    {
        $mode = $default;
        if (isset($_GET['render'])) {
            $mode = (string)$_GET['render'];
        } elseif ($env = getenv('XAO_RENDER_MODE')) {
            $mode = (string)$env;
        }

        switch (strtolower($mode)) {
            case 'xslt':
                return new XsltRenderer();
            case 'htmx':
            case 'html':
                return new HtmxRenderer();
            case 'json':
            default:
                return new JsonRenderer();
        }
    }
}
