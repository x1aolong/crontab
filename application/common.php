<?php

    /** 此文件放置一些公用的方法 **/
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use think\facade\Config;

    # 1.发送邮件
    function sendEmail($to, $title, $content)
    {
        # Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            # 邮件服务端配置
            $mail->SMTPDebug = 0;                                           # 调试信息, 2=详细 | 1=简单 | 0=不显示错误信息
            $mail->CharSet = 'utf-8';                                       # 字符集设定
            $mail->isSMTP();                                                # 使用smtp协议发送邮件
            $mail->Host = Config::get('emailGeneral.Host');           # 邮箱服务器 smtp类型
            $mail->SMTPAuth = true;                                         # Enable SMTP authentication
            $mail->Username = Config::get('emailGeneral.Username');   # SMTP username
            $mail->Password = Config::get('emailGeneral.Password');   # SMTP password
            $mail->SMTPSecure = 'ssl';                                      # 邮箱发送协议 ssl和tls两种
            $mail->Port = 465;                                              # TCP port to connect to
            # 收件人的相关配置
            $mail->setFrom(Config::get('emailGeneral.Username'), '超级管理员组');
            $mail->addAddress($to);
            # 邮件内容的相关设置
            $mail->isHTML(true);                                    # 以HTML格式进行发送
            $mail->Subject = $title;
            $mail->Body    = $content;
            # 发送成功会返回发送结果
            return $mail->send();

        } catch (Exception $e) {
            # 如邮件发送有误则报出错误详情
            exception($mail->ErrorInfo);
        }
    }

    # 2. 将前端页面的span标签替换成a标签
    function changeTag($data)
    {
        return str_replace('span', 'a', $data);
    }

    # 3. formData上传文件
    function uploadFiles($data, $uploadPath = 'static/admin/avatar', $allowExt = array('jpeg', 'jpg', 'png', 'gif'), $flag = true, $maxSize = 2097152)
    {
        $fileInfo = $data['file'];
        # 检测文件上传错误号
        if ($fileInfo['error'] > 0) {
            # 说明有错误
            switch ($fileInfo['error']) {
                case 1 :
                    $msg = '上传文件超过php.ini中upload_max_fileSize的值';
                    break;
                case 2 :
                    $msg = '超过form表单MAX_FILE_SIZE限制的大小';
                    break;
                case 3 :
                    $msg = '上传文件超过php.ini中upload_max_fileSize的值';
                    break;
                case 4 :
                    $msg = '没选择上传文件';
                    break;
                case 6 :
                    $msg = '没找到临时目录';
                    break;
                case 7 :
                    $msg = '-7';
                    break;
                case 8 :
                    $msg = '系统错误';
                    break;
            }
            # 输出错误信息
            return $msg;
        } else {
            # 检测限定上传文件类型的参数是否有误
            if (!is_array($allowExt))
            {
                return '限定上传文件类型参数错误';
            }
            # 检测文件是否为真实的图片类型
            if ($flag)
            {
                if (!getimagesize($fileInfo['tmp_name']))
                {
                    return '上传文件不是真实的图片';
                }
            }
            # 检测上传文件的类型
            $ext = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
            // $allowExt = array('jpeg', 'jpg', 'png', 'gif');
            if (!in_array($ext, $allowExt))
            {
                return '非法文件类型';
            }
            # 检测上传文件大小
            // $maxSize = 2097152; # 2mb
            if ($fileInfo['size'] > $maxSize)
            {
                return '上传文件需小于2mb';
            }
            # 检测文件是否通过HTTP POST方式上传来的
            if (!is_uploaded_file($fileInfo['tmp_name']))
            {
                return '非法文件上传方式';
            }
            # 检测文件夹是否存在
            // $uploadPath = 'public/static/admin/avatar';
            if (!file_exists($uploadPath))
            {
                mkdir($uploadPath, 0777, true);
                chmod($uploadPath, 0777);
            }
            # 移动文件
            $uniName = md5(uniqid(microtime(true), true)). '.' .$ext;
            $destination = $uploadPath. '/' .$uniName;
            if (!@move_uploaded_file($fileInfo['tmp_name'], $destination))
            {
                return '文件移动失败';
            }
            # 上传成功 返回文件存储路径
            //return $destination;
            return $uniName;
        }
    }

    # 4. 搜索方法
    function searchByKeyword($keyword, $table='', $st_time=null, $ed_time=null, $limit=10)
    {
        if ($table == '') {
            return '缺少目标table';
        }

        if ( ($table && $keyword) || ($table && $st_time!=null && $ed_time!=null) ) {

            if ($table == 'cate') {
                $cateInfo = model($table)->where(['name'=>trim($keyword)])->where(['status' => 1])->order('id', 'desc')->paginate($limit);
                return $cateInfo;
            }

        } else {
            return -1;
        }
    }

    function recurDir($dir, $chmod='') {
        if(is_dir($dir)) {
            if($handle = opendir($dir)) {
                while(false !== ($file = readdir($handle))) {
                    if(is_dir($dir.'/'.$file)) {
                        if($file != '.' && $file != '..') {
                            $path = $dir.'/'.$file;
                            $chmod ? chmod($path,$chmod) : FALSE;
                            echo $path.'<p>';
                            recurDir($path);
                        }
                    }else{
                        $path = $dir.'/'.$file;
                        $chmod ? chmod($path,$chmod) : FALSE;
                        echo $path.'<p>';
                    }
                }
            }
            closedir($handle);
        }
    }