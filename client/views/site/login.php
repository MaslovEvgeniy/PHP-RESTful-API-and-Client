<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Simple Blog | Sign In</title>
    <link rel="stylesheet" href=<?= URL . "/vendor/twbs/bootstrap/dist/css/bootstrap.min.css"   ?>>
    <link rel="stylesheet" href=<?= URL . "/web/css/styles.css" ?>>

</head>
<body>
<header>
    <nav class="navbar navbar-default">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand" href=<?= URL . "/"  ?>>Simple Blog</a>
            </div>
            <div>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href=<?= URL . "/register" ?>>Sign Up</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-login">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-12">
                            <h4 id="login-form-link">Sign In</h4>
                        </div>
                    </div>
                    <hr>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">

                            <?= isset($error) ? "<div class=\"bg-danger\">{$error}</div>" : '' ?>

                            <form id="login-form" name="loginForm" action=<?= URL . "/login" ?> method="post">
                                <div class="form-group">
                                    <label for="login">Login</label>
                                    <input type="text" name="login" id="login" class="form-control"
                                           placeholder="Enter your username or email" required>
                                </div>
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" name="password" id="password" class="form-control"
                                           placeholder="Enter your password" value="" required>
                                </div>
                                <button type="submit" name="signin" class="btn btn-success center-block">Sign In
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
