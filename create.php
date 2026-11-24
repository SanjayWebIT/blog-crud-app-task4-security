<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_login();
$errors = []; $title = ''; $content = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim((string) ($_POST['title'] ?? ''));
    $content = trim((string) ($_POST['content'] ?? ''));
    $errors = validate_post_input($title, $content);
    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO posts (user_id, title, content) VALUES (:user_id, :title, :content)');
        $stmt->execute([':user_id' => (int) $_SESSION['user_id'], ':title' => $title, ':content' => $content]);
        redirect('index.php');
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>New post</title><link rel="stylesheet" href="style.css"></head>
<body><main class="container narrow"><a href="index.php">← Blog home</a><h1>Create post</h1>
<?php foreach ($errors as $error): ?><p class="error"><?= e($error) ?></p><?php endforeach; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label for="title">Title</label><input id="title" name="title" maxlength="200" required value="<?= e($title) ?>">
<label for="content">Content</label><textarea id="content" name="content" rows="10" maxlength="50000" required><?= e($content) ?></textarea>
<button type="submit">Publish post</button></form></main></body></html>
