<?php
/**
 * Created by PhpStorm.
 * User: Denson
 * Date: 2017/3/31
 * Time: 23:08
 */
$url = "http://xxxxx.com/index.php?r=pay/notify";
$post_data = $_POST;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
$out = curl_exec($ch);
curl_close($ch);
echo $out;