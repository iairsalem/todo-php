<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">RTBE: To-do List</a>
    <span class="navbar-brand"></span>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <a class="nav-link" href="/">Manage To-dos<span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/features">Features</a>
            </li>
        </ul>

        <?php
            if($username){
                ?>
                <ul class="navbar-nav d-flex justify-content-end">
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#">Logged in as: <?php echo $username;?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/logout"><small>Log Out</small></a>
                    </li>
                </ul>
                <?php
            } else {
                ?>
                <ul class="navbar-nav d-flex justify-content-end">
                    <li class="nav-item">
                        <a class="nav-link disabled">Log in:</a>
                    </li>
                </ul>
                <form class="form-inline my-2 my-lg-0" method="post">
                    <input name="username" class="form-control mr-sm-2" type="text" placeholder="Username" aria-label="Username">
                    <input name="password" class="form-control mr-sm-2" type="password" placeholder="Password" aria-label="Password">
                    <input name="_token" class="form-control mr-sm-2" type="hidden" value="<?php echo $_SESSION['_token']; ?>">
                    <button class="btn btn-outline-success my-2 my-sm-0" type="submit" name="login">Log in</button>
                </form>
                <ul class="navbar-nav d-flex justify-content-end">
                    <li class="nav-item">
                        <a class="nav-link disabled" href="/signup"><small>Sign Up</small></a>
                    </li>
                </ul>
                <?php
            }
        ?>
    </div>
</nav>