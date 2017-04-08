<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 14/01/17
 * Time: 16:16
 */


function echo_metadata(){
    echo
    "<meta charset=\"utf-8\">
     <meta name='viewport' content='width=device-width, initial-scale=1.0'>
     <link rel='icon' href='./images/logo.svg'>";
}

function get_navigation(){
    $domain = get_domain();
    $username = (isset($_SESSION['username']) ? $_SESSION['username'] : null);
    echo
        "<nav>
            <ul class='menulist'>
                <li class='menuitem'><a href='$domain/' class='menulink'>Home</a></li>
                <li class='menuitem'><a href='$domain/new_post.php' class='menulink'>New post</a></li>"
                . (isset($_SESSION['logged_in']) ?

               "<li class='rightmenuitem'><a href='$domain/logout.php' class='menulink'>Logout</a></li>
                <li class='rightmenuitem'><a href='$domain/profile.php' class='menulink'>$username</a></li>" :

               "<li class='rightmenuitem'><a href='$domain/login.php' class='menulink'>Login</a></li>" .
               "<li class='rightmenuitem'><a href='$domain/register.php' class='menulink'>Register</a></li>") .
           "</ul>
        </nav>";
}


function check_user_logged_in(){
    if(!isset($_SESSION['logged_in'])){
        header('Location: ./login.php?access=denied');
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
        $avatar = (isset($com->avatar) ? './avatars/' . $com->avatar: './images/profile.png');
        echo "<li class='comment' id='$com->id'><div class='profile-info'><img src='$avatar' class='profile-pic'><a class='profile-link' href='#'>$com->username</a></div> <p class='comment-text'>$com->text</p><input type='button' value='reply' class='reply-button'></li>";
        if(count($com->children)){
            echo "<details>";
            echo "<summary>".count($com->children)." replies </summary>";
            echo "<ul class='comment-parent-list' id='parent_$com->id'>";
            echo_comment_tree($com->children);
            echo "</ul>";
            echo "</details>";
        }
    }
}

?>