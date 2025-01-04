<?php
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function uploadImage($file, $target_dir) {
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Cek apakah file adalah gambar
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return "Error: File bukan gambar.";
    }
    
    // Cek ukuran file (maksimal 5MB)
    if ($file["size"] > 5000000) {
        return "Error: File terlalu besar.";
    }
    
    // Izinkan format tertentu
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        return "Error: Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
    }
    
    // Buat nama file unik
    $new_filename = $target_dir . uniqid() . '.' . $imageFileType;
    
    // Resize gambar
    $new_width = 120;
    $new_height = 183;
    
    $new_image = imagecreatetruecolor($new_width, $new_height);
    
    switch($imageFileType) {
        case 'jpg':
        case 'jpeg':
            $source = imagecreatefromjpeg($file["tmp_name"]);
            break;
        case 'png':
            $source = imagecreatefrompng($file["tmp_name"]);
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            break;
        case 'gif':
            $source = imagecreatefromgif($file["tmp_name"]);
            break;
        default:
            return "Error: Format gambar tidak didukung.";
    }
    
    // Hitung posisi untuk cropping
    $width = imagesx($source);
    $height = imagesy($source);
    $ratio = max($new_width/$width, $new_height/$height);
    $w = $new_width / $ratio;
    $h = $new_height / $ratio;
    $x = ($width - $w) / 2;
    $y = ($height - $h) / 2;
    
    // Crop dan resize gambar
    imagecopyresampled($new_image, $source, 0, 0, $x, $y, $new_width, $new_height, $w, $h);
    
    switch($imageFileType) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($new_image, $new_filename, 90);
            break;
        case 'png':
            imagepng($new_image, $new_filename, 9);
            break;
        case 'gif':
            imagegif($new_image, $new_filename);
            break;
    }
    
    imagedestroy($source);
    imagedestroy($new_image);
    
    return $new_filename;
}
?>