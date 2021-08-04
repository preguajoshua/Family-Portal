<?php

if (! function_exists('success')) {
    /**
     * Success response.
     *
     * @param   string  $message
     * @return  Response
     */
    function success($message = null)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message
        ]);
    }
}

if (! function_exists('payload')) {
    /**
     * Payload response.
     *
     * @param   array   $data
     * @param   Object  $transformer
     * @return  Response
     */
    function payload($data = [], $transformer = null)
    {
        if (!is_null($transformer)) {
            return response()->json($transformer->transform($data));
        }

        return response()->json($data);
    }
}

if (! function_exists('error')) {
    /**
     * Error response.
     *
     * @param   string   $message
     * @param   integer  $code
     * @return  Response
     */
    function error($message = null, $code = 500)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $code);
    }
}

if (! function_exists('xmlStringToArray')) {
    /**
     * XML string to array.
     *
     * @param   string  $str
     * @return  array
     */
    function xmlStringToArray($str)
    {
        $xml = new SimpleXMLElement($str, LIBXML_NOEMPTYTAG);

        return simpleXmlToArray($xml);
    }
}

if (! function_exists('simpleXmlToArray')) {
    /**
     * Simple XML to array.
     *
     * @param   Object   $xml
     * @param   integer  $loop
     * @return  array
     */
    function simpleXmlToArray($xml, $loop = 1)
    {
        $array = [];

        $xml = (array) $xml;

        foreach ($xml as $key => $value) {
            if (is_array($value)) {
                $array[$key] = simpleXmlToArray($value, 2);
            } else if ($value instanceof SimpleXMLElement) {
                $attributes = count($value->attributes()) ? $value->attributes() : $value;

                if (!is_array($attributes) && !is_object($attributes)) {
                    $array[$key] = trim($attributes);
                    continue;
                } else if (!isset($array[$key])) {
                    $array[$key] = new \stdClass;
                }

                foreach ($attributes as $k => $v) {
                    $array[$key]->{$k} = (string) $v;
                }
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }
}

if (! function_exists('formatPhone')) {
    /**
     * Format a phone number.
     *
     * @param   string  $phone
     * @return  string
     */
    function formatPhone($phone)
    {
        if (!isset($phone[3])) {
            return '';
        }

        // Strip out everything but numbers
        $phone  = preg_replace('/[^0-9]/', '', $phone);
        $length = strlen($phone);

        switch ($length) {
            case 7:
                return preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $phone);
                break;
            case 10:
                return preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $phone);
                break;
            case 11:
                return preg_replace('/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/', '$1($2) $3-$4', $phone);
                break;
            default:
                return $phone;
                break;
        }
    }
}

if (! function_exists('removeUtf8Bom')) {
    /**
     * Remove UTF8 BOM
     *
     * Some API services (mostly .NET services) include a BOM in their response body.
     * The BOM is 2-4 bytes that indicate what character encoding the response is in (e.g. UTF8).
     * The problem is that PHP's json_decode() function fail when trying to parse strings that include a BOM.
     *
     * @param   string  $text
     * @return  string
     */
    function removeUtf8Bom($text)
    {
        $bom = pack('H*', 'EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);

        return $text;
    }
}
