<?php
require_once __DIR__ . '/../admin/config.php';

echo "--- TBL_CATEGORY_PHOTO ---\n";
$st = $pdo->query("SELECT * FROM tbl_category_photo");
while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
    print_r($r);
}

echo "\n--- TBL_PHOTO ---\n";
$st2 = $pdo->query("SELECT p.*, c.p_category_name FROM tbl_photo p LEFT JOIN tbl_category_photo c ON p.p_category_id = c.p_category_id ORDER BY p.photo_id DESC");
while ($r = $st2->fetch(PDO::FETCH_ASSOC)) {
    print_r($r);
}
