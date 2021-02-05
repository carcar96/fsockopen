<?php

    /* 
    *   Socket 模拟HTTP协议传输文件
    *   Http是应用层协议端口
    */
    $hostname = '127.0.0.1';
    $port = '8090';
    $path = '';
    
    function sock_get($fileName) {
        
        /*$len = $file_size = 51917560;
        $fileName = 'get.exe';*/
        
        // 建立连接
        $fp = fsockopen($GLOBALS['hostname'], $GLOBALS['port'], $errno, $errstr, 30);
        stream_set_blocking($fp,true);
        if(!$fp){
            echo "$errno : $errstr<br />";
        }else{    
            // 断点续传
            /*$start = 0;
            $end = $len - 1;
            if (isset($_SERVER['HTTP_RANGE'])) {
                $range = explode("=", $_SERVER['HTTP_RANGE'])[1];
                $start = explode("-", $range)[0];
                $len = $len - $start;
                header("HTTP/1.1 206 Partial Content");
            }*/
            
            // 发送一个HTTP请求信息头
            $query_str = http_build_query($_GET);
            $request_header = "GET " . $GLOBALS['path'] . '?' . $query_str . "\r\n";
            $request_header .= "Host: " . $GLOBALS['hostname'] . ':' . $GLOBALS['port'] . "\r\n";;
            $request_header .= "Connection: Close\r\n";
            //$request_header .= "Range: $start-$end\n";
            
            // 再一个回车换行表示头信息结束
            $request_header .= "\n";

            // 发送请求到服务器
            $write = fputs($fp,$request_header);

            // 创建新文件，预备写入
            $fp2 = fopen('./download/' . $fileName,'w');

            // 输入文件标签
            Header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            //header("Content-Range: bytes $start-$end/$file_size");
            //Header("Content-Length: " . $len);
            Header('Pragma: public');

            $ua = $_SERVER["HTTP_USER_AGENT"];
            if (preg_match("/MSIE/", $ua)) {
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
            } else if (preg_match("/Firefox/", $ua)) {
                header('Content-Disposition: attachment; filename*="utf-8\'\'' . $fileName . '"');
            } else if (preg_match("/Chrome/", $ua)) {
                header('Content-Disposition: attachment; filename=' . $fileName);
            } else {
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
            }

            ob_clean();
            ob_end_clean();
            ini_set('memory_limit', -1);
            set_time_limit(0);

            // 接受响应
            $buffer = 1024;
            while(!feof($fp)){
                $line = fread($fp, $buffer);
                // 写入文件
                fputs($fp2, $line); 
                // 输出到浏览器
                echo $line;
                flush();
            }

            // 关闭
            fclose($fp2);
            fclose($fp);
        }
    }
    
    function sock_post($fileName){

        $fp = fsockopen($GLOBALS['hostname'], $GLOBALS['port'], $errno, $errstr, 30);
        $query_str = http_build_query($_POST);
        $head = "POST " . $GLOBALS['path'] . " HTTP/1.1\r\n";
        $head .= "Host: " . $GLOBALS['hostname'] . ':' . $GLOBALS['port'] . "\r\n";
        $head .= "Referer: http://" . $GLOBALS['hostname'] . ':' . $GLOBALS['port'] . $GLOBALS['path'] . "\r\n";
        $head .= "Content-type: application/x-www-form-urlencoded\r\n";
        $head .= "Content-Length: ". strlen(trim($query_str)) . "\r\n";
        $head .= "\r\n";
        $head .= trim($query_str);
        $write = fputs($fp, $head);

        // $resp = '';
        // while (!feof($fp)) {
        //     $line = fread($fp, 1024);
        //     // echo $line;
        //     $resp .= $line;
        // }
        // echo $resp;

        $respHeader = '';
        $respBody = '';
        while (!feof($fp)) {
            $line = fread($fp, 1024);
            if(!$respHeader) {
                $respInfo = explode("\n\n", str_replace("\r", "", $line));
                $respHeader = $respInfo[0];
                $respBody .= $respInfo[1];
            } else {
                $respBody .= $line;
            }
        }

        if (!strstr($respHeader, "HTTP/1.1 200 OK"))
        {
            echo $respHeader.$respBody;
        }
        else
        {
            $respHeader = explode("\n", $respInfo[0]);
            $fileSize = '';
            $fileName = '';
            foreach($respHeader as $val)
            {
                if(strstr($val, "Content-Length"))
                {
                    $fileSize = (int)trim(explode(":", $val)[1]);
                }
                if(strstr($val, "Content-Disposition"))
                {
                    $cd = explode(":", $val);
                    $fnstr = explode(";", $cd[1]);
                    $fileName = trim(explode("=", $fnstr[1])[1], '"');
                }
            }
            // var_dump($header);
            // echo $fileSize;
            // echo $fileName;

            $len = $fileSize;
            // 输入到浏览器，设置头信息                
            // 断点续传
            $start = 0;
            $end = $len - 1;
            if (isset($_SERVER['HTTP_RANGE'])) {
                $range = explode("=", $_SERVER['HTTP_RANGE'])[1];
                $start = explode("-", $range)[0];
                $len = $len - $start;
                header("HTTP/1.1 206 Partial Content");
            }
            // 范围
            header("Content-Range: bytes $start-$end/$fileSize");
            //告诉浏览器文件的总大小
            header('Content-Length:' . $len);
            Header('Pragma: public');
            //声明下载文件的名称
            $ua = $_SERVER["HTTP_USER_AGENT"];
            if (preg_match("/MSIE/", $ua)) {
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
            } else if (preg_match("/Firefox/", $ua)) {
                header('Content-Disposition: attachment; filename*="utf-8\'\'' . $fileName . '"');
            } else if (preg_match("/Chrome/", $ua)) {
                header('Content-Disposition: attachment; filename=' . $fileName);
            } else {
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
            }

            ob_clean();
            ob_end_clean();
            ini_set('memory_limit', -1);
            set_time_limit(0);

            $body = $respInfo[1].$respBody;
            // 创建新文件
            $fp2 = fopen('./download/' . $fileName, 'w');
            // 写入文件
            fputs($fp2, $body);
            // 关闭
            fclose($fp2);

            // 输出到浏览器
            echo $body;
            flush();
        }

        // 关闭
        fclose($fp);
    }

    function get_server_data($msurl) {
        $msInfo = parse_url($msurl);
        $GLOBALS['hostname'] = $msInfo['host'];
        $GLOBALS['port'] = $msInfo['port'];
        $GLOBALS['path'] = $msInfo['path'];
    }
 
   if(strtoupper($_SERVER['REQUEST_METHOD']) == 'GET') {
        get_server_data($_GET['msurl']);
        sock_get($_GET['filename']);
   } else {
        get_server_data($_POST['msurl']);
        sock_post($_POST['filename']);
   }
?>
