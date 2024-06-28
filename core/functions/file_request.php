<?php
define("MAX_FILE_SIZE", 10 * 1024 * 1024);

abstract class FileRequest
{
    
    static function uploadeFile($fileRequest)
    {
        $fileName = basename($_FILES[$fileRequest]['name']);
        $fileTmpName = $_FILES[$fileRequest]['tmp_name'];
        $fileSize = $_FILES[$fileRequest]['size'];
        $targetFilePath = "../uploads/" . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowTypes = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'mp3', 'svg');

        // Check if file is empty
        if ($fileSize == 0) {
            echo "Sorry, the file is empty.";
            exit;
        }

        // Check file size
        if ($fileSize > MAX_FILE_SIZE) {
            echo "Sorry, the file exceeds the maximum allowed size of " .
                (MAX_FILE_SIZE / 1024 / 1024) . " MB.";
            exit;
        }

        // Check file type
        if (in_array($fileType, $allowTypes)) {
            // Upload file to server
            if (move_uploaded_file($fileTmpName, $targetFilePath)) {
                echo "The file " . $fileName . " has been uploaded.";
                return $fileName;
            } else {
                echo "Sorry, there was an error uploading your file.";
                return "fail";
            }
        } else {
            echo "Sorry, only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }


    static function deleteFile($path, $fileName)
    {
        if (file_exists($path . '/' . $fileName)) {
            unlink($path . '/' . $fileName);
        }
    }
}
