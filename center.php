<?php
    /* 
    *   Socket 模拟HTTP协议传输文件
    *   Http是应用层协议使用80端口
    */
    $hostname = '127.0.0.1';
    $port = '80';
    $uri = '/fsockopen/server.php';

    function sock_get() {
        
        $filePath = './upload/aaa.txt'; // TODO: 先写死，待验证是否需要传参-->文件大小和文件名
        $fileSize = filesize($filePath);//坑 filesize 如果超过2G 低版本php会返回负数
        $fileName = basename($filePath);

        // 建立连接
        $fp = fsockopen($GLOBALS['hostname'], $GLOBALS['port'], $errno, $errstr, 30);
        stream_set_blocking($fp,true);
        if(!$fp){
            echo "$errno : $errstr<br />";
        }else{
            // 发送一个HTTP请求信息头
            
            $query_str = http_build_query($_GET);
            $request_header = "GET " . $GLOBALS['uri'] . '?' . $query_str . "\r\n";
            $request_header .= "Host: ". $GLOBALS['hostname'] . "\n";
            
            // 再一个回车换行表示头信息结束
            $request_header .= "\n";

            // 发送请求到服务器
            fputs($fp,$request_header);

            // 创建新文件，预备写入
            $fp2 = fopen('./download/' . $fileName,'w');

            // 输入到浏览器，设置头信息
            //声明浏览器输出的是字节流
            header('Content-Type: application/octet-stream');
            // //声明浏览器返回大小是按字节进行计算
            header('Accept-Ranges:bytes');
            //告诉浏览器文件的总大小
            header('Content-Length:' . $fileSize); //注意是'Content-Length:' 非Accept-Length
            //声明下载文件的名称
            header('Content-Disposition:attachment;filename=' . $fileName);//声明作为附件处理和下载后文件的名称


            // 接受响应
            while(!feof($fp)){
                $line = fread($fp, 1024);
                // 写入文件
                fputs($fp2, $line); 
                // 输出到浏览器
                echo $line;
            }

            // 关闭
            fclose($fp2);
            fclose($fp);
        }
    }

    function sock_post(){

        $filePath = './upload/bbb.txt'; // TODO: 先写死，待验证是否需要传参-->文件大小和文件名
        $fileSize = filesize($filePath);//坑 filesize 如果超过2G 低版本php会返回负数
        $fileName = basename($filePath);

        $fp = fsockopen($GLOBALS['hostname'], $GLOBALS['port'], $errno, $errstr, 30);
        $query_str = http_build_query($_POST);
        $head = "POST " . $GLOBALS['uri'] . "?" . $GLOBALS['uri'] . "\r\n";
        $head .= "Host: " . $GLOBALS['hostname'] . "\r\n";
        $head .= "Referer: http://" . $GLOBALS['hostname'] . $GLOBALS['uri'] . "\r\n";
        $head .= "Content-type: application/x-www-form-urlencoded\r\n";
        $head .= "Content-Length: ". strlen(trim($query_str)) . "\r\n";
        $head .= "\r\n";
        $head  .= trim($query_str);
        $write = fputs($fp, $head);

        // 创建新文件，预备写入
        $fp2 = fopen('./download/' . $fileName, 'w');

        // 输入到浏览器，设置头信息
        //声明浏览器输出的是字节流
        header('Content-Type: application/octet-stream');
        // //声明浏览器返回大小是按字节进行计算
        header('Accept-Ranges:bytes');
        //告诉浏览器文件的总大小
        header('Content-Length:' . $fileSize); //注意是'Content-Length:' 非Accept-Length
        //声明下载文件的名称
        header('Content-Disposition:attachment;filename=' . $fileName);//声明作为附件处理和下载后文件的名称

        while(!feof($fp)){
            $line = fread($fp, 1024);
            // 写入文件
            fputs($fp2, $line); 
            // 输出到浏览器
            echo $line;
        }

        fclose($fp2);
        fclose($fp);
    }
 
   if(strtoupper($_SERVER['REQUEST_METHOD']) == 'GET') {
        sock_get();
   } else {
        sock_post();
   }

?>
