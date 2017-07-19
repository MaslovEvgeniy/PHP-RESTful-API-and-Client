<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Simple Blog | Post</title>
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
                        <li><a href=<?= URL . "/logout" ?>>Logout</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<div class="container">
    <div class="row">
        <div class="col-lg-12">

            <?php if (!empty($errors)): ?>
                <p class="bg-danger">
                    <?php foreach ($errors as $error): ?>
                        <?= $error . "<br>" ?>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>

            <?php $name = isset($post['id']) ? 'edit' : 'create';
            $link = isset($post['id']) ? '/edit/' . $post['id'] : '/create';
            $title = isset($post['title']) ? $post['title'] : '';
            $content = isset($post['content']) ? $post['content'] : '';
            ?>

            <form id="form" name="<?= $name  ?>" action=<?= URL . $link ?> method="post">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" class="form-control" value="<?= $title  ?>" required>
                </div>
                <div class="form-group">
                    <label for="content">Content</label>
                    <input type="text" name="content" id="content" class="form-control"
                           value="<?= $content  ?>" required>
                </div>
                <button type="submit" name="<?= $name  ?>" class="btn btn-success center-block">Apply
                </button>
            </form>
        </div>
    </div>

</div>
</body>
</html>