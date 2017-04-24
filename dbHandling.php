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
    var $extension;
    var $post_key;
    var $user_id;
    var $username;
    var $likes_id;
    var $favourite_id;




    function __construct($id, $title, $description, $likes, $added, $extension, $post_key, $user_id, $username = 'anon', $liked_id = null, $favourite_id = null) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->likes = $likes;
        $this->added = $added;
        $this->extension = $extension;
        $this->post_key = $post_key;
        $this->user_id = $user_id;
        $this->username = $username;
        $this->liked_id = $liked_id;
        $this->favourite_id = $favourite_id;
    }
}

class Comment{
    var $id;
    var $text;
    var $user_id;
    var $time;
    var $parent_id;
    var $post_key;
    var $children;
    var $username;
    var $avatar;

    function __construct($id, $text, $user_id, $time, $parent_id, $post_key, $username = 'Anonymous', $avatar = null){
        $this->id = $id;
        $this->text = $text;
        $this->user_id = $user_id;
        $this->time = $time;
        $this->parent_id = $parent_id;
        $this->post_key = $post_key;
        $this->children = Array();
        $this->username = $username;
        $this->avatar = $avatar;
    }
}

class Avatar{
    var $id;
    var $key;
    var $extension;
    var $user_id;

    function __construct($id, $key, $extension, $user_id){
        $this->id = $id;
        $this->key = $key;
        $this->extension = $extension;
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

    $smnt = $conn->prepare('SELECT id, email, username, password_hash, avatar FROM users WHERE username = ? or email = ?');
    $smnt->bind_param('ss', $username_or_email, $username_or_email);
    $smnt->execute();

    $smnt->store_result();
    $smnt->bind_result($id, $email, $username, $hash, $avatar);

    if($smnt->fetch()) {
        if(password_verify($password, $hash)){
            session_start();
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['id'] = $id;
            $_SESSION['avatar'] = $avatar;
            return true;
        }else{
            return false;
        }
    } else{
        return false;
    }
}

function insert_new_image($title, $description, $filename, $userid, $tags){
    $conn = get_conn();
    $now = date("Y-m-d H:i:s");

    $name_arr = explode('.', $filename);
    $post_key = $name_arr[0];
    $extension = "." . $name_arr[1];

    /* Prevent sqlinjection */
    $stmt = $conn->prepare('INSERT INTO posts (title, description, added, extension, post_key, user_id) VALUES (?, ?, ?, ?, ?, ?);');
    $stmt->bind_param('sssssi', $title, $description, $now, $extension, $post_key, $userid);

    /* Execute prepared statement */
    $stmt->execute();

    $post_id = $stmt->insert_id; // Do something with this

    $stmt1 = $conn->prepare("INSERT INTO post_tags (tag_id, post_id) VALUES (?,?);");

    foreach($tags as $tag){
        $stmt1->bind_param('ii', $tag, $post_id);
        $stmt1->execute();
    }



    $conn->commit();

    /* Close db connection and statement*/
    $stmt->close();
    $stmt1->close();
    $conn->close();
}


function get_posts($offset, $searchQuery = ''){
    $conn = get_conn();
    $offset = ($offset - 1) * 10;
    $logged_in_user_id = (isset($_SESSION['id']) ? $_SESSION['id'] : '');
    $posts = Array();

    if(!empty($searchQuery)){
        $posts = search($searchQuery);
    }else{
        $smnt = $conn->prepare('SELECT posts.id, posts.title, posts.description, posts.likes, posts.added, posts.extension, posts.post_key, users.username, posts.user_id, likes.user_id, favourites.user_id
                                       FROM posts
                                       JOIN users ON (posts.user_id = users.id ) 
                                       LEFT JOIN likes ON (likes.user_id = ? AND posts.id = likes.post_id)
                                       LEFT JOIN favourites ON (favourites.user_id = ? AND posts.id = favourites.post_id)
                                       ORDER BY posts.id desc LIMIT 10 OFFSET ?;');
        $smnt->bind_param('iii', $logged_in_user_id, $logged_in_user_id ,$offset);
        $smnt->execute();

        $smnt->store_result();
        $smnt->bind_result($id, $title, $description, $likes, $added, $extension, $post_key, $username, $user_id, $liked_id, $favourite_id);



        while($smnt->fetch()){
            $posts[] = new Post($id, $title, $description, $likes, $added, $extension, $post_key, $user_id, $username, $liked_id, $favourite_id);
        }
    }

    $current_rows = count($posts);



    if(empty($searchQuery)){
        $smnt1 = $conn->prepare('SELECT COUNT(id) FROM posts;');
        $smnt1->execute();
        $smnt1->bind_result($id);
        if($smnt1->fetch()){
            $arr = Array();
            $arr['currentResults'] = $current_rows;
            $arr['totalPosts'] = $id;
            $arr['totalPages'] = ceil($id/10);
            $arr['posts'] = $posts;
            return $arr;
        }
    }else{
        $arr = Array();
        $arr['currentResults'] = $current_rows;
        $arr['totalPosts'] = count($posts);
        $arr['totalPages'] = ceil(count($posts)/10);
        $arr['posts'] = $posts;
        return $arr;
    }
}

function echo_posts($posts){
    $logged_in_user_id = (isset($_SESSION['id']) ? $_SESSION['id'] : '');
    foreach($posts as $post){
        $cropped_image = $post->post_key . 'c' . $post->extension;
        echo '<section class="post-wrapper">
                    <h1 class="post-title">'. $post->title .'</h1>
                    <a href="./post.php?key='.$post->post_key.'">
                    <img class="post-image" src="./uploadsfolder/' . $cropped_image . '" onclick="start_gif(this)">' .
            '</a>' .
            '<section class="details">
                            <p class="post-description">' .$post->description . '</p><hr>' .
            '<time class="date">Added:'. date("d/m/Y", strtotime($post->added)).'</time>' .
            '<p class="likes">
                                <i class="fa fa-star' . (isset($post->favourite_id) ? "":"-o") . '" id="'.$post->id.'" onclick="favourite_post(this)"></i>
                                <i class="fa fa-heart'.(isset($post->liked_id) ? "" : "-o") .'" id="'.$post->id.'"  onclick="like_post(this)"></i>
                                <span id="likes_count_'.$post->id.'">'.$post->likes.'</span> likes
                            </p>' .
            '<p class="post-username">Posted by: '.$post->username . '</p>' . ($logged_in_user_id == $post->user_id ? '<a href="./edit_post.php?post='.$post->id.'"><img src="./images/edit.png" class="edit-icon"></a>' : '') .
            '</section>'  .
            '</section>';
    }
}

function get_personal_posts(){
    $conn = get_conn();

    $id = $_SESSION['id'];

    $smnt = $conn->prepare('SELECT id, title, description, likes, added, extension, post_key, user_id FROM posts WHERE user_id = ?');
    $smnt->bind_param('i', $id);

    $smnt->execute();

    $smnt->store_result();
    $smnt->bind_result($id, $title, $description, $likes, $added, $extension, $post_key, $user_id);

    $post_arr = Array();
    while($smnt->fetch()){
        $post = new Post($id, $title, $description, $likes, $added, $extension, $post_key, $user_id);
        array_push($post_arr, $post);
    }
    return $post_arr;
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

function insert_favourite($post_id, $user_id){
    // Get a database connection
    $conn = get_conn();

    /* Prepare statement and prevent sqlinjection */
    $stmt = $conn->prepare('INSERT INTO favourites (post_id, user_id) VALUES (?, ?);');
    $stmt->bind_param('ii', $post_id, $user_id);


    $stmt1 = $conn->prepare('UPDATE posts SET favourites = favourites + 1 WHERE id = ?;');
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

function delete_favourite($post_id, $user_id){
    $conn = get_conn();

    /* Prevent sqlinjection */
    $stmt = $conn->prepare('DELETE FROM favourites WHERE post_id = ? AND user_id = ?;');
    $stmt->bind_param('ii', $post_id, $user_id);

    $stmt1 = $conn->prepare('UPDATE posts SET favourites = favourites - 1 WHERE id = ?;');
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


function get_post_info($post_id_or_key){
    $conn = get_conn();
    $queryParam = null;
    $bindParam = null;
    if(intval($post_id_or_key) != 0){
        $queryParam = 'id';
        $bindParam = 'i';
    }else if(gettype($post_id_or_key) == 'string'){
        $queryParam = 'post_key';
        $bindParam = 's';
    }

    $smnt = $conn->prepare('SELECT id, title, description, likes, added, extension, post_key, user_id
                            FROM posts
                            WHERE posts.'.$queryParam.' = ? limit 1;');
    $smnt->bind_param($bindParam, $post_id_or_key);
    $smnt->execute();

    $smnt->store_result();
    $smnt->bind_result($id, $title, $description, $likes, $added, $extension, $post_key, $user_id);

    $post_array = Array();
    if($smnt->fetch()) {
        $post_array['id'] = $id;
        $post_array['title'] = $title;
        $post_array['description'] = $description;
        $post_array['likes'] = $likes;
        $post_array['added'] = $added;
        $post_array['post_key'] = $post_key;
        $post_array['extension'] = $extension;
        $post_array['user_id'] = $user_id;
        return $post_array;
    }else{
        return false;
    }
}



function delete_post($post_id_or_key){
    $conn = get_conn();

    $queryParam = null;
    if(gettype($post_id_or_key) == "integer" || gettype(intval($post_id_or_key)) == "integer"){
        $queryParam = 'id';
    }else if(gettype($post_id_or_key) == 'string'){
        $queryParam = 'post_key';
    }

    /* Prevent sqlinjection */
    $stmt = $conn->prepare('DELETE FROM posts WHERE '.$queryParam.' = ?;');
    $stmt->bind_param('i', $post_id);

    /* Execute prepared statement */
    $stmt->execute();

    $conn->commit();

    /* Close db connection and statement*/
    $stmt->close();
    $conn->close();
}

function update_post($title, $description, $post_id_or_key, $tags){
    $conn = get_conn();

    $queryParam = null;
    if(gettype($post_id_or_key) == "integer" || gettype(intval($post_id_or_key)) == "integer"){
        $queryParam = 'id';
    }else if(gettype($post_id_or_key) == 'string'){
        $queryParam = 'post_key';
    }

    /* Prevent sqlinjection */
    $stmt = $conn->prepare('UPDATE posts SET title = ?, description = ? WHERE '.$queryParam.' = ?;');
    $stmt->bind_param('ssi', $title, $description, $post_id_or_key);

    /* Execute prepared statement */
    $stmt->execute();



    if($stmt1 = $conn->prepare("SELECT * FROM post_tags WHERE post_id = ?")){
        $stmt1->bind_param('i', $post_id_or_key);
        $stmt1->execute();
        $stmt1->bind_result($id, $tag_id, $post_id);

        $old_tags = Array();
        $tags_to_delete = Array();
        $tags_to_add = Array();

        while($stmt1->fetch()){
            $old_tags[$id] = $tag_id;
        }


        foreach($tags as $tag){
            if(!in_array($tag, $old_tags)){
                $tags_to_add[] = $tag;
            }
        }

        foreach($old_tags as $tag){
            if(!in_array($tag, $tags)){
                $tags_to_delete[] = $tag;
            };
        }

        foreach($tags_to_delete as $t){
            $tag_id_to_delete = array_search($t, $old_tags);
            delete_post_tag($tag_id_to_delete, $conn);
        }

        foreach ($tags_to_add as $t){
            add_post_tag($post_id_or_key, $t, $conn);
        }
    };




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
    if($stmt->fetch()){
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

function get_posts_before_and_after($post_key){
    $conn = get_conn();
    $user_id = (isset($_SESSION['id']) ? $_SESSION['id'] : 0);


    $smnt = $conn->prepare('SELECT posts.id, posts.title, posts.description, posts.likes, posts.favourites, posts.added, posts.extension, posts.post_key, posts.user_id, likes.user_id, favourites.user_id
                            FROM posts
                            LEFT JOIN likes ON (likes.post_id = posts.id AND likes.user_id = ?)
                            LEFT JOIN favourites ON (favourites.post_id = posts.id AND favourites.user_id = ?)
                            WHERE posts.post_key = ? limit 1;');
    $smnt->bind_param('iis', $user_id, $user_id, $post_key);
    $smnt->execute();

    $smnt->bind_result($id, $title, $description, $likes, $favourites, $added, $extension, $post_key, $user_id, $liked, $is_favourite);

    if($smnt->fetch()) {
        $currentId = $id;
        $post_arr = Array();
        $post_arr['id'] = $id;
        $post_arr['title'] = $title;
        $post_arr['description'] = $description;
        $post_arr['likes'] = $likes;
        $post_arr['favourites'] = $favourites;
        $post_arr['added'] = $likes;
        $post_arr['extension'] = $extension;
        $post_arr['post_key'] = $post_key;
        $post_arr['user_id'] = $user_id;
        $post_arr['liked'] = $liked;
        $post_arr['is_favourite'] = $is_favourite;
        $post_arr['current'] = $post_arr;
        $smnt->close();

        //Fetch the post before
        if($smnt1 = $conn->prepare('SELECT post_key FROM posts where id < ? order by id desc limit 1;')) {

            $smnt1->bind_param('i', $currentId);
            $smnt1->store_result();
            $smnt1->execute();
            $smnt1->bind_result($post_key);

            if ($smnt1->fetch()) {
                $post_arr['previous'] = $post_key;
            }else{
                $post_arr['previous'] = 'Empty';
            }
        }


        $smnt1->close();

        //Fetch the post after
        if($smnt2 = $conn->prepare('SELECT post_key FROM posts where id > ? order by id asc limit 1;')){
            $smnt2->bind_param('i', $currentId);
            $smnt2->execute();
            $smnt2->store_result();
            $smnt2->bind_result($post_key);

            if($smnt2->fetch()){
                $post_arr['next'] = $post_key;
            }else{
                $post_arr['next'] = 'Empty';
            }
        }

        $smnt2->close();
        $conn->close();
        return $post_arr;
    }
}

function insert_comment($post_key, $comment, $parent_id){
    $conn = get_conn();
    $now = date("Y-m-d H:i:s");
    $user_id = $_SESSION['id'];
    $username = $_SESSION['username'];
    $avatar = $_SESSION['avatar'];
    $parsed_parent_id = intval($parent_id);
    if($smnt = $conn->prepare('INSERT INTO comments (text, user_id, time, parent_id, post_key) VALUES (?,?,?,?, ?);')){
        $smnt->bind_param('sisis', $comment, $user_id, $now, $parent_id, $post_key);
        if($smnt->execute()){
            $insertedId = $smnt->insert_id;

            return [true, new Comment($insertedId,$comment, $user_id, $now, $parsed_parent_id, $post_key, $username, $avatar)];
        }else{
            var_dump($conn->error);
            return false;
        }
    }else{
        var_dump($conn->error);
        return false;
    }
}

function get_post_comments($post_key){
    $conn = get_conn();
    if($smnt = $conn->prepare('SELECT comments.id, comments.text, comments.user_id, comments.time, comments.parent_id, comments.post_key, users.username, users.avatar
                               FROM comments  
                               JOIN users ON (users.id = comments.user_id)
                               WHERE comments.post_key = ? 
                               ORDER BY comments.id DESC;')){
        $smnt->bind_param('s', $post_key);
        $smnt->execute();

        $smnt->store_result();
        $smnt->bind_result($id, $text, $user_id, $time, $parent_id, $post_key, $username, $avatar);

        $comment_arr = Array();
        while($smnt->fetch()){
            $comment_arr[$id] = new Comment($id, $text, $user_id, $time, $parent_id, $post_key, $username, $avatar);
        }

        foreach ($comment_arr as $com) {
            if ($com->parent_id != 0) {
                $comment_arr[$com->parent_id]->children[] = $com;
            }
        }

        foreach ($comment_arr as $com) {
            if ($com->parent_id != 0) {
                unset($comment_arr[$com->id]);
            }
        }

        return $comment_arr;
    }else{
        return false;
    }
}


function insert_new_avatar($avatar_key, $extension, $user_id){
    $conn = get_conn();

    /* Prevent sqlinjection */
    if($stmt = $conn->prepare('INSERT INTO avatars (avatar_key, extension, user_id) VALUES (?, ?, ?);')){
        $stmt->bind_param('ssi', $avatar_key,$extension, $user_id);

        /* Execute prepared statement */
        if($stmt->execute()){
            $stmt->close();
            $conn->close();
            return true;
        }else{
            $stmt->close();
            $conn->close();
            return false;
        }
    }else{
        return false;
    }



    /* Close db connection and statement*/
}

function get_user_avatars($user_id){
    $conn = get_conn();

    if($stmt = $conn->prepare('SELECT * FROM avatars WHERE user_id = ?;')) {
        $stmt->bind_param('i', $user_id);

        $stmt->store_result();
        $stmt->bind_result($id,$avatar_key, $extension, $user_id);

        $arr = Array();
        if($stmt->execute()){
            while($stmt->fetch()){
                $arr[] = new Avatar($id, $avatar_key, $extension, $user_id);
            }
        }
        return $arr;
    }

}


function update_user_avatar($user_id, $avatar){
    $conn = get_conn();

    /* Prevent sqlinjection */
    if($stmt = $conn->prepare('UPDATE users SET avatar = ? WHERE id = ?;')){
        $stmt->bind_param('si', $avatar, $user_id);

        /* Execute prepared statement */
        if($stmt->execute()){
            $stmt->close();
            return true;
        }else{
            $stmt->close();
            return false;
        }
    }else{
        $stmt->close();
        return false;
    }
}

function echo_user_favourites(){
    $conn = get_conn();
    $user_id = $_SESSION['id'];
    if(isset($user_id)){
        if($smnt = $conn->prepare('SELECT posts.title, posts.post_key 
                                   FROM favourites 
                                   JOIN posts ON posts.id = favourites.post_id
                                   WHERE favourites.user_id = ? limit 10;')){
            $smnt->bind_param('i', $user_id);
            $smnt->execute();
            $smnt->store_result();
            $smnt->bind_result($title, $key);
            if($smnt->num_rows > 0){
                while($smnt->fetch()){
                    echo "<li class='right-list-item'><a href='./post.php?key=$key'>$title</a></li>";
                }
            }else{
                echo "<p>You have no favourites yet. Go ahead and add some by clicking the star icon!</p>";
            };

        }


    }else{
        return false;
    }
}

function get_post_tags($post_id, $ids_only = true, $con = null){
    $conn = (isset($con) ? $con : get_conn());

    $results = Array();
    if($smnt = $conn->prepare("SELECT tags.id, tags.tag_name 
                                        FROM post_tags 
                                        JOIN tags ON (post_tags.tag_id = tags.id)
                                        WHERE post_tags.post_id = ?;")){
        $smnt->bind_param('i', $post_id);
        $smnt->execute();
        $smnt->bind_result($tag_id, $name);

        while($smnt->fetch()){
            if($ids_only) {
                $results[] = $tag_id;
            }else{
                $arr = Array();
                $arr['id'] = $tag_id;
                $arr['tag_name'] = $name;
                $results[] = $arr;
            }
        }
    }
    return $results;
}

function echo_tags($selected = Array()){
    $conn = get_conn();

    if($smnt = $conn->prepare("SELECT * FROM tags;")){
        $smnt->execute();
        $smnt->bind_result($id, $tag_name);

        while($smnt->fetch()){
            $checked = (in_array($id, $selected) ? 'checked' : null);
            echo "<input type='checkbox' name='tags[]' value='$id' id='$id' $checked>
              <label for='$id' class='tag-label'>$tag_name</label>";
        }
    }
}

function echo_post_tags($post_id){
    $tags = get_post_tags($post_id, false);

    foreach($tags as $tag){
        $id = $tag['id'];
        $name = $tag['tag_name'];
        echo "<p id='$id' class='post-tag'>$name</p>";
    }
}

function delete_post_tag($tag_id, $con = null){
    $conn = (isset($con) ? get_conn() : $con);

    /* Prevent sqlinjection */
    $stmt = $conn->prepare('DELETE FROM post_tags WHERE id = ?;');
    $stmt->bind_param('i', $tag_id);

    /* Execute prepared statement */
    $stmt->execute();

    $conn->commit();

    /* Close db connection and statement*/
    $stmt->close();
    $conn->close();
}

function add_post_tag($post_id, $tag_id, $con = null){
    $conn = (isset($con) ? get_conn() : $con);

    /* Prevent sqlinjection */
    $stmt = $conn->prepare('INSERT INTO post_tags (post_id, tag_id) VALUES (?,?);');
    $stmt->bind_param('ii', $post_id, $tag_id);

    /* Execute prepared statement */
    $stmt->execute();

    $conn->commit();

    /* Close db connection and statement*/
    $stmt->close();
    $conn->close();
}

function search($query , $con = null){
    $conn = (isset($con) ? $con : get_conn());
    $logged_in_user_id = (isset($_SESSION['id']) ? $_SESSION['id'] : '');

    $posts = Array();
    $param = "%$query%";
    if($stmt = $conn->prepare("SELECT posts.id, posts.title, posts.description, posts.likes, posts.added, posts.extension, posts.post_key, users.username, posts.user_id, likes.user_id, favourites.user_id 
                                      FROM posts 
                                      JOIN users ON (posts.user_id = users.id ) 
                                      LEFT JOIN likes ON (likes.user_id = ? AND posts.id = likes.post_id)
                                      LEFT JOIN favourites ON (favourites.user_id = ? AND posts.id = favourites.post_id)
                                      WHERE title LIKE ?;")){
        $stmt->bind_param('iis', $logged_in_user_id,$logged_in_user_id,$param);
        $stmt->execute();
        $stmt->bind_result($id, $title, $description, $likes, $added, $extension, $post_key, $username, $user_id, $liked_id, $favourite_id);
        while($stmt->fetch()){
            $posts[$id] = new Post($id, $title, $description, $likes, $added, $extension, $post_key, $user_id, $username, $liked_id, $favourite_id);
        }
    }


    if($stmt1 = $conn->prepare("SELECT posts.id, posts.title, posts.description, posts.likes, posts.added, posts.extension, posts.post_key, users.username, posts.user_id, likes.user_id, favourites.user_id
                                       FROM post_tags
                                       JOIN tags ON  post_tags.tag_id = tags.id
                                       JOIN posts ON  post_tags.post_id = posts.id
                                       JOIN users ON (posts.user_id = users.id ) 
                                       LEFT JOIN likes ON (likes.user_id = ? AND posts.id = likes.post_id)
                                       LEFT JOIN favourites ON (favourites.user_id = ? AND posts.id = favourites.post_id)
                                       WHERE tags.tag_name LIKE ?;")){ //LIMIT 10 OFFSET ? TODO
        $stmt1->bind_param('iis', $logged_in_user_id,$logged_in_user_id,$param);
        $stmt1->execute();
        $stmt1->bind_result($id, $title, $description, $likes, $added, $extension, $post_key, $username, $user_id, $liked_id, $favourite_id);
        while($stmt1->fetch()){
            if(!array_key_exists($id, $posts)) {
                $posts[$id] = new Post($id, $title, $description, $likes, $added, $extension, $post_key, $user_id, $username, $liked_id, $favourite_id);
            }
        }
    }
    return $posts;
}

?>