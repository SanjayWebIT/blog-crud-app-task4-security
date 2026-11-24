<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_login();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(400); exit('Invalid post ID.'); }
$stmt = $pdo->prepare('SELECT id, user_id, title, content FROM posts WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$post = $stmt->fetch();
if (!$post) { http_response_code(404); exit('Post not found.'); }
if (!can_manage_post($pdo, (int) $post['user_id'])) { http_response_code(403); exit('You do not have permission to manage this post.'); }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (isset($_POST['delete'])) {
        $delete = $pdo->prepare('DELETE FROM posts WHERE id = :id');
        $delete->execute([':id' => $id]);
        redirect('index.php');
    }
    $title = trim((string) ($_POST['title'] ?? ''));
    $content = trim((string) ($_POST['content'] ?? ''));
    $errors = validate_post_input($title, $content);
    if (!$errors) {
        $update = $pdo->prepare('UPDATE posts SET title = :title, content = :content WHERE id = :id');
        $update->execute([':title' => $title, ':content' => $content, ':id' => $id]);
        redirect('single.php?id=' . $id);
    }
    $post['title'] = $title; $post['content'] = $content;
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Edit post</title><link rel="stylesheet" href="style.css"></head>
<body><main class="container narrow"><a href="single.php?id=<?= (int) $id ?>">← Back to article</a><h1>Edit post</h1>
<?php foreach ($errors as $error): ?><p class="error"><?= e($error) ?></p><?php endforeach; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label for="title">Title</label><input id="title" name="title" maxlength="200" required value="<?= e($post['title']) ?>">
<label for="content">Content</label><textarea id="content" name="content" rows="10" maxlength="50000" required><?= e($post['content']) ?></textarea>
<button type="submit">Save changes</button></form>
<form method="post" onsubmit="return confirm('Delete this post permanently?');" class="delete-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="danger" type="submit" name="delete" value="1">Delete post</button></form>
</main></body></html>
