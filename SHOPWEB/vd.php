            <div class="col-md-6">
                <?php
                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];
                    $user_name = $_SESSION['user_name'];
                    echo "
                    <div class='col-md-10 text-end'>
                        <h4>Xin chào, $user_name (ID: $user_id)</h4>
                    </div>
                    <div class='col-md-2 text-end'>
                     <a href='/PHP/WEB/SHOPWEB/authentication/logout.php' class='btn btn-danger w-100'>LogOut</a>
                     </div>";
                } else {
                    echo"
                    <div class='col-md-6'>
                        <a href='/PHP/WEB/SHOPWEB/authentication/register.php' class='btn btn-success w-100'>Register</a> 
                    </div>
                    <div class='col-md-6'>
                        <a href='/PHP/WEB/SHOPWEB/authentication/login.php' class='btn btn-outline-primary w-100'>LogIn</a>
                    </div>";
                }
                ?>
            </div>