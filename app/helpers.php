<?php

if (!function_exists('monitoringCurlCall')) {
    function monitoringCurlCall($postRequestUrl, $params = null) {
        try {
            $handle = curl_init();
            curl_setopt($handle, CURLOPT_URL, $postRequestUrl);
            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($handle, CURLOPT_HTTPHEADER,
                array('Content-Type:application/json')
            );
            //curl_setopt($handle, CURLOPT_HTTPHEADER, $header);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);

            $content = curl_exec($handle);


            $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            $error = curl_errno($handle);


            curl_close($handle);
            if ($code == 200 && !($error)) {

                return $content;
            } else {
                return false;
            }
        }catch (\Exception $exception) {
            return false;
        }

    }
}

