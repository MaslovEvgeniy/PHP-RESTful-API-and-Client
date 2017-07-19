<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Simple Blog | Home</title>
    <link rel="stylesheet" href=<?= URL . "/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" ?>>
    <link rel="stylesheet" href=<?= URL . "/web/css/styles.css" ?>>

</head>
<body>
<header>
    <nav class="navbar navbar-default">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand" href=<?= URL . "/" ?>>Simple Blog</a>
            </div>
            <div>
                <ul class="nav navbar-nav navbar-right">
                    <?php if ($isGuest): ?>
                        <li><a href=<?= URL . "/login" ?>>Sign In</a></li>
                        <li><a href=<?= URL . "/register" ?>>Sign Up</a></li>
                    <?php else: ?>
                        <li><a href=<?= URL . "/create" ?>>Create post</a></li>
                        <li><a href=<?= URL . "/logout" ?>>Logout</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<div class="container">
    <?php if (!empty($errors)): ?>
        <p class="bg-danger">
            <?php foreach ($errors as $error): ?>
                <?= $error . "<br>" ?>
            <?php endforeach; ?>
        </p>
    <?php endif; ?>
    <?php if (!$isGuest) echo "<h3>Hello, {$userData['username']}!</h3>"?>

    <?php if (!empty($posts)): ?>
        <h2 class="text-center">Posts</h2>
        <?php foreach ($posts as $post): ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?= $post['title'] ?>
                    <?php if (!$isGuest && $userData['id'] === $post['author_id']): ?>
                        <div class="pull-right">
                            <a href=<?= URL . '/edit/' . $post['id'] ?>>
                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                            </a>
                            <a href=<?= URL . '/remove/' . $post['id'] ?>>
                                <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="panel-body"><?= $post['content'] ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <h2>No Posts yet</h2>
    <?php endif; ?>
</div>
</body>
</html>