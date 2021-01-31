<?php

function once($filePath) {
    
    //以只读和二进制模式打开文件  
    $file = fopen ( $filePath, "rb" );
    
    
    //告诉浏览器这是一个文件流格式的文件   
    Header ( "Content-type: application/octet-stream" );
    
    //请求范围的度量单位 
    Header ( "Accept-Ranges: bytes" ); 
    
    //Content-Length是指定包含于请求或响应中数据的字节长度  
    $fileSize = filesize($filePath);//坑 filesize 如果超过2G 低版本php会返回负数 
    Header ( "Accept-Length: " . $fileSize ); 
    
    //用来告诉浏览器，文件是可以当做附件被下载，下载后的文件名称为$file_name该变量的值。
    Header ( "Content-Disposition: attachment; filename=" . basename($filePath) );   
    
    //读取文件内容并直接输出到浏览器   
    echo fread ( $file, $fileSize );   
    
    fclose ( $file );   
    exit ();
}

function more($filePath) {
    $readBuffer = 1024;

    //设置头信息
    //声明浏览器输出的是字节流
    header('Content-Type: application/octet-stream');
    //声明浏览器返回大小是按字节进行计算
    header('Accept-Ranges:bytes');
    //告诉浏览器文件的总大小
    $fileSize = filesize($filePath);//坑 filesize 如果超过2G 低版本php会返回负数
    header('Content-Length:' . $fileSize); //注意是'Content-Length:' 非Accept-Length
    //声明下载文件的名称
    header('Content-Disposition:attachment;filename=' . basename($filePath));//声明作为附件处理和下载后文件的名称
    //获取文件内容
    $handle = fopen($filePath, 'rb');//二进制文件用‘rb’模式读取
    while (!feof($handle) ) { //循环到文件末尾 规定每次读取（向浏览器输出为$readBuffer设置的字节数）
        echo fread($handle, $readBuffer);
    }
    fclose($handle);//关闭文件句柄
    exit;
}

if(strtoupper($_SERVER['REQUEST_METHOD']) == 'GET') {
    more('./upload/aaa.txt');
} else {
    more('./upload/bbb.txt');
}