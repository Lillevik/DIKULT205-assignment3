<?php
/**
 * This function echo's some general metadata tags.
 * charset, viewport and icon
 */
function echo_metadata($title = "Picstr"){
    echo
    "<meta charset=\"utf-8\">
     <meta name='viewport' content='width=device-width, initial-scale=1.0'>
     <link rel='icon' href='./images/logo.png'>
     <link href=\"https://use.fontawesome.com/db87107c26.css\" media=\"all\" rel=\"stylesheet\">
     <link rel='stylesheet' href='./css/menu.css'>
     <title>$title</title>";
}

/**
 * This functions echo's the navigation markup.
 * It should be placed within a <header> element.
 */
function get_navigation(){
    //Gets the session variables, username and avatar
    $username = (isset($_SESSION['username']) ? $_SESSION['username'] : null);
    $avatar = (!empty($_SESSION['avatar']) ? './avatars/' . $_SESSION['avatar'] : './images/profile.png');
    echo
        "<nav>
            <ul class='menulist'>
                <li class='menuitem'><a href='./' class=''><img src='./images/logo.png' id='menu-logo'></a></li>
                <li class='menuitem'><a href='./new_post.php' class='menulink newpost'>New post<i class='fa fa-plus-square-o' aria-hidden='true'></i></a></li>
           "
                . (isset($_SESSION['logged_in']) ?


                "<li class='rightmenuitem' id='dropdown'>
                    <div class='menulink' id='profile-list-element'>
                        <img id='profile-avatar' src='$avatar'>
                        <span id='username'>$username</span>
                    </div>
                    <ul class='droplist'>
                        <li class='droplist-item'><a href='./profile.php' class='menulink-dropdown'>Profile</a></li>
                        <li class='droplist-item'><a href='./logout.php' class='menulink-dropdown'>Logout</a></li>
                    </ul>
                </li>
                <li class='rightmenuitem'>
                    <form id='search-form' name='search-form' action='./index.php' method='post'>
                        <input type='search' name='search-input' id='search-input' placeholder='Titles and tags..'>
                        <label for='submit-search'><i class=\"fa fa-search\" aria-hidden=\"true\"></i></label>
                        <input type='submit' value='search' id='submit-search'>
                    </form>
                </li>" :

               "<li class='rightmenuitem'><a href='./login.php' class='menulink'>Login</a></li>" .
               "<li class='rightmenuitem'><a href='./register.php' class='menulink'>Register</a></li>
                <li class='rightmenuitem'>
                    <form id='search-form' name='search-form' action='./index.php' method='post'>
                        <input type='search' name='search-input' id='search-input' placeholder='Titles and tags..'>
                        <label for='submit-search'><i class=\"fa fa-search\" aria-hidden=\"true\"></i></label>
                        <input type='submit' value='search' id='submit-search'>
                    </form>
                </li>") .
           "</ul>
        </nav>";
}

function echo_footer(){
    echo
    "<footer>
        <p>Continue the journey on social media!</p>
        <section id='social-media'>
            <a href=''><img src='./images/facebook.svg' class='social-image'></a>
            <a href=''><img src='./images/twitter.svg' class='social-image'></a>
            <a href=''><img src='./images/instagram.svg' class='social-image'></a>
            <a href=''><img src='./images/google.svg' class='social-image'></a>
            <a href=''><img src='./images/youtube.svg' class='social-image'></a>
        </section>
     </footer>";
}


/**
 * This function checks if a users is logged
 * in and returns them to the login page if
 * they are not.
 * @param $returnUrl
 */
function check_user_logged_in($returnUrl = ""){
    if(!isset($_SESSION['logged_in'])){
        $url = (!empty($returnUrl)? "&returnUrl=$returnUrl":"");
        header("Location: ./login.php?access=denied$url");
    }
}

function user_is_logged_in(){
    if(isset($_SESSION['logged_in'])){
        return true;
    }else{
        return false;
    }
}

function random_string($len){
    $result = "";
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTVWXYZ";
    $charArray = str_split($chars);
    for($i = 0; $i < $len; $i++){
        $randItem = array_rand($charArray);
        $result .= "".$charArray[$randItem];
    }
    return $result;
}

/**
 * Indents a flat JSON string to make it more human-readable.
 *
 * @param string $json The original JSON string to process.
 *
 * @return string Indented version of the original JSON string.
 */
function indent($json) {

    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;

            // If this character is the end of an element,
            // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }

        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element,
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }

            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }

        $prevChar = $char;
    }

    return $result;
}

function echo_comment_tree($comments){
    foreach($comments as $com){
        $avatar = ($com->avatar != null ? './avatars/' . $com->avatar: './images/profile.png');
        echo "<li class='comment' id='$com->id'><div class='profile-info'><div class='profile-pic-wrapper'><img src='$avatar' class='profile-pic'></div><a class='profile-link' href='#'>$com->username</a></div> <p class='comment-text'>$com->text</p><input type='button' value='reply' class='reply-button'></li>";
        if(count($com->children)){
            echo "<details>";
            echo "<summary><span class='children-count'>".count($com->children)."</span> replies </summary>";
            echo "<ul class='comment-parent-list' id='parent_$com->id'>";
            echo_comment_tree($com->children);
            echo "</ul>";
            echo "</details>";
        }
    }
}

?>