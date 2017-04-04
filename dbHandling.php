<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 13/01/17
 * Time: 21:40
 */
require 'password.php';
include_once 'functions.php';
include 'config.php';


/**
 * @return mysqli connection
 */
function get_conn(){
    $dbInfo = getDbCredentials();

    if (!defined('servername')) define('servername',  $dbInfo['serverName']);
    if (!defined('database')) define('database', $dbInfo['database']);
    if (!defined('dbusername')) define('dbusername', $dbInfo['dbusername']);
    if (!defined('password')) define('password', $dbInfo['password']);
    if (!defined('port')) define('port', $dbInfo['port']);

    $conn = mysqli_connect(servername, dbusername, password, database);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $conn;
}

class Post{
    var $id;
    var $title;
    var $description;
    var $likes;
    var $added;
    var $filename;
    var $user_id;

    function __construct($id, $title, $description, $likes, $added, $filename, $user_id) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->likes = $likes;
        $this->added = $added;
        $this->filename = $filename;
        $this->user_id = $user_id;
    }
}

/**
 * @param $email
 * @param $username
 * @param $password
 */
function insert_new_user($email, $username, $password){
    $conn = get_conn();
    $pwHash = password_hash($password, PASSWORD_BCRYPT);

    /* Prevent sqlinjection */
    $stmt = $conn->prepare('INSERT INTO users (email, username, password_hash) VALUES (?, ?, ?);');
    $stmt->bind_param('sss', $email, $username, $pwHash);

    /* Execute prepared statement */
    $stmt->execute();

    $conn->commit();

    /* Close db connection and statement*/
    $stmt->close();
    $conn->close();
}

/**
 * @param $username_to_check
 * @param $email_to_check
 * @return array
 * @internal param $username
 * @internal param $email
 */
function user_exists($username_to_check, $email_to_check){
    $duplicates = Array();

    $conn = get_conn();

    $smnt = $conn->prepare('SELECT username, email FROM users WHERE username = ? or email = ?;');
    $smnt->bind_param('ss', $username_to_check, $email_to_check);

    $smnt->execute();
    $smnt->store_result();
    $smnt->bind_result($username, $email);

    if($smnt->fetch()) {
        if($username_to_check == $username){
            $duplicates[] = 'username';
        }

        if($email_to_check == $email){
            $duplicates[] = 'email';
        }
    }
    return $duplicates;
}

function validate_login($username_or_email, $password){
    $conn = get_conn();

    $smnt = $conn->prepare('SELECT id, email, username, password_hash FROM users WHERE username = ? or email = ?');
    $smnt->bind_param('ss', $username_or_email, $username_or_email);
    $smnt->execute();

    $smnt->store_result();
    $smnt->bind_result($id, $email, $username, $hash);

    if($smnt->fetch()) {
        if(password_verify($password, $hash)){
            session_start();
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['id'] = $id;
            return true;
        }else{
            return false;
        }
    } else{
        return false;
    }
}

function insert_new_image($title, $description, $filename, $userid){
    $conn = get_conn();
    $now = date("Y-m-d H:i:s");

    /* Prevent sqlinjection */
    $stmt = $conn->prepare('INSERT INTO posts (title, description, added, filename, user_id) VALUES (?, ?, ?, ?, ?);');
    $stmt->bind_param('ssssi', $title, $description, $now, $filename, $userid);

    /* Execute prepared statement */
    $stmt->execute();

    $inertedId = $stmt->insert_id; // Do something with this

    $conn->commit();

    /* Close db connection and statement*/
    $stmt->close();
    $conn->close();
}


function echo_posts($limit){
    $conn = get_conn();
    $logged_in_user_id = (isset($_SESSION['id']) ? $_SESSION['id'] : '');

    //
    $smnt = $conn->prepare('SELECT posts.id, posts.title, posts.description, posts.likes, posts.added, posts.filename, users.username, users.id, likes.user_id
                            FROM posts
                            JOIN users ON (posts.user_id = users.id ) 
                            LEFT JOIN likes ON (likes.user_id = ? AND posts.id = likes.post_id)
                            ORDER BY posts.id desc LIMIT ?;');
    $smnt->bind_param('ii', $logged_in_user_id, $limit);
    $smnt->execute();

    $smnt->store_result();
    $smnt->bind_result($id, $title, $description, $likes, $added, $filename, $username, $user_id, $liked_id);

    $liked = "liked.png";
    $not_liked = "like.png";
    while($smnt->fetch()) {
        $arr = explode("." , $filename);
        $cropped_image = $arr[0] . 'c.' . $arr[1];
        echo '<section class="post-wrapper">
                    <h1 class="post-title">'. $title .'</h1>
                    <img class="post-image" src="./uploadsfolder/' . $cropped_image . '" onclick="start_gif(this)">' .
                    '<section class="details">
                            <p class="post-description">' .$description . '</p><hr>' .
                           '<time class="date">Added:'. date("d/m/Y", strtotime($added)).'</time>' .
                           '<p class="likes"><img src="./images/'.((isset($liked_id)) ? $liked : $not_liked) .'" id="'.$id.'" class="like-button" onclick="like_post(this)"><span class="'.$id.' likes_number">'.$likes.'</span> likes</p>' .
                           '<p class="post-username">Posted by: '.$username . '</p>' . ($logged_in_user_id == $user_id ? '<a href="./edit_post.php?post='.$id.'"><img src="./images/edit.png" class="edit-icon"></a>' : '') .
                    '</section>'  .
              '</section>';
    }
}

function insert_like($post_id, $user_id){
    // Get a database connection
    $conn = get_conn();

    /* Prepare statement and prevent sqlinjection */
    $stmt = $conn->prepare('INSERT INTO likes (post_id, user_id) VALUES (?, ?);');
    $stmt->bind_param('ii', $post_id, $user_id);


    $stmt1 = $conn->prepare('UPDATE posts SET likes = likes + 1 WHERE id = ?;');
    $stmt1->bind_param('i', $post_id);

    /* Execute prepared statement */
    $stmt->execute();
    $stmt1->execute();
    $conn->commit();

    /* Close db connection and statement*/
    $stmt->close();
    $stmt1->close();
    $conn->close();

}


function delete_like($post_id, $user_id){
    $conn = get_conn();

    /* Prevent sqlinjection */
    $stmt = $conn->prepare('DELETE FROM likes WHERE post_id = ? AND user_id = ?;');
    $stmt->bind_param('ii', $post_id, $user_id);

    $stmt1 = $conn->prepare('UPDATE posts SET likes = likes - 1 WHERE id = ?;');
    $stmt1->bind_param('i', $post_id);

    /* Execute prepared statement */
    $stmt->execute();
    $stmt1->execute();

    $conn->commit();

    /* Close db connection and statement*/
    $stmt->close();
    $stmt1->close();
    $conn->close();
}


function get_post_info($post_id){
    $conn = get_conn();

    $smnt = $conn->prepare('SELECT * FROM posts WHERE id = ?;');
    $smnt->bind_param('i', $post_id);
    $smnt->execute();

    $smnt->store_result();
    $smnt->bind_result($id, $title, $description, $likes, $added, $filename, $user_id);

    $post_array = Array();
    if($smnt->fetch()) {
        $post_array['id'] = $id;
        $post_array['title'] = $title;
        $post_array['description'] = $description;
        $post_array['likes'] = $likes;
        $post_array['added'] = $added;
        $post_array['filename'] = $filename;
        $post_array['user_id'] = $user_id;
        return $post_array;
    }else{
        return false;
    }
}

function delete_post($post_id){
    $conn = get_conn();

    /* Prevent sqlinjection */
    $stmt = $conn->prepare('DELETE FROM posts WHERE id = ?;');
    $stmt->bind_param('i', $post_id);

    /* Execute prepared statement */
    $stmt->execute();

    $conn->commit();

    /* Close db connection and statement*/
    $stmt->close();
    $conn->close();
}

function update_post($title, $description, $post_id){
    $conn = get_conn();

    /* Prevent sqlinjection */
    $stmt = $conn->prepare('UPDATE posts SET title = ?, description = ? WHERE id = ?;');
    $stmt->bind_param('ssi', $title, $description, $post_id);

    /* Execute prepared statement */
    $stmt->execute();

    $conn->commit();

    /* Close db connection and statement*/
    $stmt->close();
    $conn->close();
}

/**
 * This function fetches the
 * @param $post_id
 */
function get_post_likes($post_id){
    //Get a database connection
    $conn = get_conn();

    $smnt = $conn->prepare('SELECT users.username FROM likes JOIN users ON (likes.user_id = users.id) WHERE likes.post_id = ?;');
    $smnt->bind_param('i', $post_id);
    $smnt->execute();


    $smnt->store_result();
    $smnt->bind_result($username);
    $arr = Array();
    $arr ['usernames'] = Array();

    while($smnt->fetch()) {
        array_push($arr['usernames'], $username);
    }

    $json = json_encode($arr, JSON_UNESCAPED_UNICODE);
    echo indent($json);
}

/**
 * This function fetches the latest posts from the database
 * and returns them as json to the browser.
 */
function get_fresh_posts(){
    //Get a database connection
    $conn = get_conn();

    $smnt = $conn->prepare('SELECT id, title, description, likes, added, filename, user_id FROM posts;');


    //Execute the statement
    $smnt->execute();

    //Bind and store the result in the statement
    $smnt->bind_result($id, $title, $description, $likes, $added, $filename, $user_id);
    $smnt->store_result();

    //Create an array for the posts with an
    $postArr = Array();
    $postArr['posts'] = Array();

    //Loop through the results and add them to the array.
    while($smnt->fetch()){
        $post = new Post($id, $title, $description, $likes, $added, $filename, $user_id);
        array_push($postArr['posts'], $post);
    }

    //Encode the array containing the posts
    $json = json_encode($postArr);

    //Echo an indented json string.
    echo indent($json);
}

function check_account_information($email){
    $conn = get_conn();

    $stmt = $conn->prepare('SELECT id, email, username FROM USERS WHERE email = ? limit 1;');
    $stmt->bind_param('s', $email);

    $stmt->store_result();
    $stmt->bind_result($id, $email, $username);

    $arr = Array();
    while($stmt->fetch()){
        $arr['id'] = $id;
        $arr['email'] = $email;
        $arr['username'] = $username;
    }
    return $arr;
}

function insert_recovery_token($email){
    $conn = get_conn();
    $dateString = strtotime("+1 day");
    $date = date("Y-m-d H:i:s", $dateString);
    $token = random_string(50);

    /* Prevent sqlinjection using prepared statement */
    $stmt1 = $conn->prepare('SELECT email, username FROM users WHERE email = ? limit 1;');
    $stmt1->bind_param('s', $email);
    $stmt1->execute();
    $stmt1->store_result();
    $stmt1->bind_result($dbemail, $username);

    if($stmt1->fetch()){
        $unique = false;

        while(!$unique){
            if(!token_exists($token, $conn)){
                //Add reset token
                $stmt = $conn->prepare('INSERT INTO password_reset (email, token, expiration_date) VALUES (?, ?, ?);');
                $stmt->bind_param('sss', $email, $token, $date);
                $stmt->execute();

                //Setup the email.
                $topic = "Password reset";
                $message =
                '<DOCTYPE html>
                <html>
                    <head>
                        <meta charset="utf-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    </head>
                    <body>
                        <main style="border:1px solid #333; border-radius: 2px; background:lightcyan;width: 500px; max-width: 100%; margin:0 auto; padding:20px;">
                            <p>Hello, ' . $username . '.</p>
                            <p>Here is a <a href="https://dikult205.k.uib.no/NSJ17/assignment3/forgotPassword.php?token='.$token.'">link</a> to <b>reset</b> your password.</p>
                        </main>
                    </body>
                </html>';
                $header = "Content-Type: text/html; charset=UTF-8\r\n";

                mail($dbemail,$topic,$message,$header);
                $stmt->close();


                $unique = true;
            }
        }
    }

    $conn->commit();
    /* Close db connection and statement*/
    $stmt1->close();
    $conn->close();
}

function token_exists($token, $conn = null){
    if(!isset($conn)){
        $conn = get_conn();
    }
    $smnt = $conn->prepare('SELECT token FROM password_reset where token = ? limit 1;');
    $smnt->bind_param('s', $token);

    $smnt->execute();
    $smnt->store_result();
    $smnt->bind_result($token);

    if($smnt->fetch()) {
        return true;
    }else{
        return false;
    }
}

function reset_password($newPass, $token){
    $conn = get_conn();
    $smnt = $conn->prepare('SELECT email, token FROM password_reset WHERE token = ? limit 1');
    $smnt->bind_param('s', $token);

    $smnt->execute();

    $smnt->store_result();
    $smnt->bind_result($email, $token);
    if($smnt->fetch()){
        $pwhash = password_hash($newPass, PASSWORD_BCRYPT);
        $smnt = $conn->prepare('UPDATE users SET password_hash = ? WHERE email = ?;');
        $smnt->bind_param('ss', $pwhash, $email);
        $smnt->execute();

        $smnt = $conn->prepare('DELETE FROM password_reset WHERE token = ?');
        $smnt->bind_param('s', $token);
        $smnt->execute();

        $smnt->close();
    }
}




?>