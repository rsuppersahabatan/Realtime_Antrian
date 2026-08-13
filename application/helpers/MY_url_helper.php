<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('is_url_exist'))
{
    function is_url_exist($url = NULL)
    {
        if ( ! empty($url) && filter_var($url, FILTER_VALIDATE_URL))
        {
            if (function_exists('curl_version'))
            {
                $ch = curl_init($url);    

                curl_setopt($ch, CURLOPT_NOBODY, TRUE);
                curl_exec($ch);

                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($code == 200)
                {
                    $status = TRUE;
                }
                else
                {
                    $status = FALSE;
                }

                // FIX: Only close if it's a resource (PHP 7.x and older). 
                // In PHP 8+, it's a CurlHandle object and will self-destruct automatically.
                if (is_resource($ch))
                {
                    curl_close($ch);
                }

                return $status;
            }
            else
            {
                $headers = @get_headers($url);

                return stripos($headers[0], '200 OK') ? TRUE : FALSE;
            }
        }
        else
        {
            return FALSE;
        }
    }
}