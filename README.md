# php fsockopen demo

upload：主服务器文件存储目录
server.php：主服务器下载文件接口

download：存储从主服务器下载的文件
center.php：中继器向主服务器下载 --> 缓存到本地并同时输出(客户端：浏览器)

client.html：向中继器下载文件


测试步骤：
1、将文件A放在upload目录中
2、访问client.html--填写相关信息，点击GET下载