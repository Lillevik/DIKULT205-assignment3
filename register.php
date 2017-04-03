<?php
if($_SERVER["REQUEST_METHOD"] == "POST") {
    include_once 'dbHandling.php';

    $errUsername = $errEmail = $errPassword = $errMatch = null;
    $errors = false;

    $email = (isset($_POST['emailinput']) ? $_POST['emailinput'] : '');
    $username = (isset($_POST['usernameinput']) ? $_POST['usernameinput'] : '');
    $password = (isset($_POST['passwordinput']) ? $_POST['passwordinput'] : '');
    $repeat = (isset($_POST['repeatpassword']) ? $_POST['repeatpassword'] : '');
    $terms = (isset($_POST['terms']) ? $_POST['terms'] : '');

    if (empty($email)) {
        $errors = true;
        $errEmail = 'Email is required.';
    }else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors = true;
        $errEmail = 'Email format must be "mail@example.com".';
    }


    if (empty($username)) {
        $errUsername = 'Username is required.';
        $errors = true;
    }else if(strlen($username) < 6){
        $errUsername = 'Username must be atleast 6 characters.';
        $errors = true;
    }

    if(empty($password)){
        $errPassword = 'Password is required.';
        $errors = true;
    }else if(strlen($password) < 6) {
        $errPassword = 'Password must be longer than 6 characters.';
        $errors = true;
    }

    if ($password != $repeat) {
        $errMatch = '<p class="error-message">Passwords are not matching.</p>';
        $errors = true;
    }

    if (!$terms) {
        $errTerms = '<p class="error-message">Terms must be accepted.</p>';
        $errors = true;
    }



    /* If there are no errors, the new user is created */
    if (!$errors) {

        /* Checks is a record exists with either the email or username*/
        $duplicates = user_exists($username, $email);

        if (empty($duplicates)) {
            /* Inserts a new user to the database */
            insert_new_user($email, $username, $password);
            /* redirects the users to a success page */
            header('Location: success.php');
            exit();
        }
    }
}

?>
<head>
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
    <header>
        <nav>

        </nav>
    </header>

    <main>
        <a href="./" id="backtofrontpage">To the frontpage</a>
        <form id="registerform" action="register.php" method="POST">

            <label for="emailinput" class="inputlabel">Email address<span class="required">*</span></label>
            <p class="error-message" id="error-emailinput"><?php echo (isset($errEmail) ? $errEmail : '')?></p>
            <input type="email" class="writteninput <?php echo (isset($errEmail) ? 'requiredInfo' : '');?>" id="emailinput" name="emailinput" placeholder="Email address" title="Enter email address here" value="<?php echo (isset($email) ? $email : '');?>">

            <label for="usernameinput" class="inputlabel">Username<span class="required">*</span></label>
            <p class="error-message" id="error-usernameinput"><?php echo (isset($errUsername) ? $errUsername : '')?></p>
            <input type="text" class="writteninput <?php echo (isset($errUsername) ? 'requiredInfo' : '');?>" id="usernameinput" name="usernameinput" placeholder="Username" title="Enter username here" value="<?php echo (isset($username) ? $username : '');?>">

            <label for="passwordinput" class="inputlabel">Password<span class="required">*</span></label>
            <p class="error-message" id="error-passwordinput"><?php echo (isset($errPassword) ? $errPassword : '')?></p>
            <input type="password" class="writteninput <?php echo (isset($errPassword) ? 'requiredInfo' : '');?>" id="passwordinput" name="passwordinput" placeholder="Password" title="Enter password here">

            <label for="repeatpassword" class="inputlabel">Repeat password<span class="required">*</span></label>
            <p class="error-message" id="error-repeatpassword"><?php echo (isset($errMatch) ? $errMatch : '')?></p>
            <input type="password" class="writteninput <?php echo (isset($errMatch) ? 'requiredInfo' : '');?>" id="repeatpassword" name="repeatpassword" placeholder="Repeat password" title="Repeat password here">

            <ul>
                <li><label class="genderlabel" for="gendermale">Male<input type="radio" name="gender" class="gender" id="gendermale" value="male"></label></li>
                <li><label class="genderlabel" for="genderfemale">Female<input type="radio" name="gender" class="gender" id="genderfemale" value="female"></label></li>
                <li><label class="genderlabel" for="genderother">Other<input type="radio" name="gender" class="gender" id="genderother" value="other"><br></label></li>
            </ul>

            <?php echo (isset($errTerms) ? $errTerms : '')?>
            <label for="terms">Accept terms of service<input type="checkbox" class="gender" id="terms" name="terms" value="terms"><span class="required">*</span></label>
            <input type="submit" class="writteninput" value="Register">

            <?php

            /*if(isset($errors) and !empty($errors)){
                $errorString = 'The following is required: ';
                $current = 0;
                $numberOfErrors = count($errors) - 1;
                foreach ($errors as $s){
                    if($numberOfErrors == 0){
                        $errorString = $errorString . $s . '.';
                    }
                    else if($current < $numberOfErrors){
                        $errorString = $errorString . $s . ', ';
                        $current += 1;
                    } else {
                        $errorString = $errorString . 'and ' . $s . '.';
                    }
                }
                echo '<p id="errormessage">' . $errorString . '</p>';
            }*/

            if(isset($duplicates)){
                if(count($duplicates) == 1){
                    echo '<p id="errormessage">The ' . $duplicates[0] . ' already exists.</p>';
                }else if(count($duplicates) == 2){
                    echo '<p id="errormessage">The ' . $duplicates[0] . ' and '. $duplicates[1] . ' already exists.</p>';
                }
            }
            ?>

            <p class="info">Already registered? Click <a href="./login.php">here</a> to login.</p>
        </form>
    </main>

    <footer>

    </footer>
    <script type="text/javascript" src="./js/register.js"></script>
</body>







