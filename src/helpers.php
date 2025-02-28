<?php

/**
    * Return a random state parameter for authorization
    *
    * @return string
*/
if (!function_exists('generate_random_state')) {
    function generate_random_state()
    {
        return bin2hex(random_bytes(16));
    }
}

/**
    * Build a URL with params
    *
    * @param  string $url
    * @param  array $params
    * @return string
*/
if (!function_exists('build_url')) {
    function build_url($url, $params)
    {
        $parsedUrl = parse_url($url);
        if (empty($parsedUrl['host'])) {
            return trim($url, '?') . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        }

        if (! empty($parsedUrl['port'])) {
            $parsedUrl['host'] .= ':' . $parsedUrl['port'];
        }

        $parsedUrl['scheme'] = (empty($parsedUrl['scheme'])) ? 'https' : $parsedUrl['scheme'];
        $parsedUrl['path'] = (empty($parsedUrl['path'])) ? '' : $parsedUrl['path'];

        $url = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
        $query = [];

        if (! empty($parsedUrl['query'])) {
            $parsedUrl['query'] = explode('&', $parsedUrl['query']);

            foreach ($parsedUrl['query'] as $value) {
                $value = explode('=', $value);

                if (count($value) < 2) {
                    continue;
                }

                $key = array_shift($value);
                $value = implode('=', $value);

                $query[$key] = urldecode($value);
            }
        }

        $query = array_merge($query, $params);

        return $url . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }
}