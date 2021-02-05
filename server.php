<?php

    function download($filePath)
    {
        $file_name = basename($filePath);
        $len = $file_size = filesize($filePath);
        
        // 分段读取文件
        $fp = fopen($filePath, "rb");
        $buffer = 1024 * 4;

        // 断点续传
        $start = 0;
        $end = $len - 1;
        if (isset($_SERVER['HTTP_RANGE'])) {
            $range = explode("=", $_SERVER['HTTP_RANGE'])[1];
            $start = explode("-", $range)[0];
            $len = $len - $start;
            header("HTTP/1.1 206 Partial Content");
            fseek($fp, $start);
        }

        // 输入文件标签
        Header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        header("Content-Range: bytes $start-$end/$file_size");
        Header("Content-Length: " . $len);
        Header('Pragma: public');

        $ua = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "";
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf-8\'\'' . $file_name . '"');
        } else if (preg_match("/Chrome/", $ua)) {
            header('Content-Disposition: attachment; filename=' . $file_name);
        } else {
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
        }

        ob_clean();
        ob_end_clean();
        ini_set('memory_limit', -1);
        set_time_limit(0);

        while (!feof($fp)) {
            print fread($fp, $buffer);
            flush();
        }
        fclose($fp);
    }

    if(strtoupper($_SERVER['REQUEST_METHOD']) == 'GET') {
        download('./upload/' . $_GET['filename']);
    } else {
        download('./upload/' . $_POST['filename']);
    }