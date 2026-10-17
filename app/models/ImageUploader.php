<?php
declare(strict_types=1);

/**
 * Upload ảnh sản phẩm: chỉ JPG/PNG/WEBP, tối đa 2 MB, kiểm tra nội dung thật của file
 * (không tin phần mở rộng do người dùng gửi) và đổi tên ngẫu nhiên khi lưu.
 */
final class ImageUploader
{
    public static function dir(): string
    {
        return PUBLIC_PATH . '/' . config('upload.dir');
    }

    /** Lưu một file trong $_FILES (dạng đã tách từng file). Trả về tên file đã lưu. */
    public static function save(array $f): string
    {
        $err = $f['error'] ?? UPLOAD_ERR_NO_FILE;
        $name = (string)($f['name'] ?? 'ảnh');
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
            throw new AppException("Ảnh «$name» vượt quá dung lượng cho phép (tối đa 2 MB).");
        }
        if ($err !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
            throw new AppException("Không tải được ảnh «$name». Vui lòng thử lại.");
        }
        if ($f['size'] > config('upload.max_bytes')) {
            throw new AppException("Ảnh «$name» vượt quá 2 MB.");
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        $ext = config('upload.mimes')[$mime] ?? null;
        if (!$ext || @getimagesize($f['tmp_name']) === false) {
            throw new AppException("Ảnh «$name» không hợp lệ. Chỉ chấp nhận JPG, PNG hoặc WEBP.");
        }
        $dir = self::dir();
        if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
            throw new AppException('Không tạo được thư mục lưu ảnh.');
        }
        $file = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
        if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $file)) {
            throw new AppException('Không lưu được ảnh lên máy chủ (kiểm tra quyền ghi thư mục public/uploads).');
        }
        return $file;
    }

    /** Chuẩn hóa $_FILES['images'] (nhiều file) thành danh sách từng file, bỏ ô trống. */
    public static function normalize(?array $files): array
    {
        if (!$files || !isset($files['name'])) {
            return [];
        }
        $out = [];
        $names = (array)$files['name'];
        foreach ($names as $i => $n) {
            if ((is_array($files['error']) ? $files['error'][$i] : $files['error']) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $out[] = [
                'name' => is_array($files['name']) ? $files['name'][$i] : $files['name'],
                'tmp_name' => is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'],
                'size' => is_array($files['size']) ? $files['size'][$i] : $files['size'],
                'error' => is_array($files['error']) ? $files['error'][$i] : $files['error'],
            ];
        }
        return $out;
    }

    /** Xóa file ảnh trên đĩa (an toàn: chỉ trong thư mục upload, bỏ qua ảnh mẫu của hệ thống). */
    public static function remove(string $file): void
    {
        if (preg_match('/^[\w\-]+\.(jpg|png|webp)$/', $file)) {   // ảnh mẫu .svg không bị xóa
            $path = self::dir() . '/' . $file;
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
