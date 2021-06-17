<?php 

error_reporting(0);
include_once('InstagramDownload.php'); 

use Ayesh\InstagramDownload\InstagramDownload;

if (isset($_GET['btngetimage'])) {

        $url = $_GET['txturl'];

        if ($url!=null) 
        {
            //echo $url;

            $client=new InstagramDownload($url);
            $type = $client->getType();
            $url1 = $client->getDownloadUrl();
        }
        else
        {
            header("index.php");
        }

}

?>

<!DOCTYPE html>
<html>
<head>
    <title>InstaZx - Instagram Image Downloader</title>
</head>
<body>
<form method="get">
    <table border="5px" cellpadding="5" cellspacing="5" align="center" width="500px">
        <tr>
            <th colspan="2"><a href="index.php" style="text-decoration:none; ">InstaZx - Instagram Image Downloader</a></th>
        </tr>
        <tr>
            <td colspan="2">
                <h3 align="center">Instagram account must be public</h3>
                Step 1 : Copy post link from Instagram and paste it in Textbot.<br>
                Step 2 : Click "Get Image" Button.<br>
                Step 3 : Then Download Image Link will appear Click on it and download photo.<br>
                Made by - Manish &nbsp&nbsp&nbsp&nbsp&nbsp  Instagram - @vminkook_manish
            </td>
        </tr>
        <tr>
            <td>Enter Link<input type="text" name="txturl" placeholder="Enter Instagram Image(Post) URL"></td>
            <td><button name="btngetimage">Get Image</button></td>
        </tr>
        <tr>
            <?php
            if (isset($_GET['btngetimage'])) {

                if ($type=="image") 
                {
                    echo '<td colspan="2"><a href="'.$url1.'">Download Image</a></td>';
                }
                else
                {
                    echo '<td colspan="2">Paste Link in Textbot</td>';
                }
            }
            ?>
        </tr>
    </table>
</form>
</body>
</html>