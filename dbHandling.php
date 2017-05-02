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
include __dir__ . "/" .get_connect_path();


/**
 * Creates and returns a mysqli connection
 * using the credentials found in the config file
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

/**
 * Class Post
 * Used for creating objects post object
 * in order to return them in a list.
 */
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


    /**
     * Post constructor.
     * @param $id
     * @param $title
     * @param $description
     * @param $likes
     * @param $added
     * @param $extension
     * @param $post_key
     * @param $user_id
     * @param string $username
     * @param null $liked_id
     * @param null $favourite_id
     */
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

/**
 * Class Comment
 * Used for creating objects post object
 * in order to return them in a list.
 */
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

    /**
     * Comment constructor.
     * @param $id
     * @param $text
     * @param $user_id
     * @param $time
     * @param $parent_id
     * @param $post_key
     * @param string $username
     * @param null $avatar
     */
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

/**
 * Class Avatar
 * Used for creating objects post object
 * in order to return them in a list.
 */
class Avatar{
    var $id;
    var $key;
    var $extension;
    var $user_id;

    /**
     * Avatar constructor.
     * @param $id
     * @param $key
     * @param $extension
     * @param $user_id
     */
    function __construct($id, $key, $extension, $user_id){
        $this->id = $id;
        $this->key = $key;
        $this->extension = $extension;
        $this->user_id = $user_id;
    }
}

/**
 *
 * This function inserts a new user into the database.
 * The password is converted into a hash for better security.
 *
 * @param $email
 * @param $username
 * @param $password
 * @return bool
 */
function insert_new_user($email, $username, $password){
    $conn = get_conn();
    $pwHash = password_hash($password, PASSWORD_BCRYPT);

    /* Prevent sqlinjection */
    if($stmt = $conn->prepare('INSERT INTO users (email, username, password_hash) VALUES (?, ?, ?);')){
        $stmt->bind_param('sss', $email, $username, $pwHash);

        /* Execute prepared statement */
        if($stmt->execute()){
            $conn->commit();

            /* Close db connection and statement*/
            $stmt->close();
            $conn->close();
            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }
}

/**
 *
 * This functions checks the database for an existing user
 * and returns an array with the already exsisting columns values.
 * E.g username, email
 *
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

/**
 * This function checks the given users inputs against
 * the database and logs the user in if an account exists
 * and the passwords matches the hash stored in the database.
 * If the user is validated, some session variables are stored
 * to later recognize the user throughout the website.
 *
 *
 * @param $username_or_email
 * @param $password
 * @return bool
 */
function validate_login($username_or_email, $password){
    $conn = get_conn();

    $smnt = $conn->prepare('SELECT id, email, username, password_hash, avatar, rank FROM users WHERE username = ? or email = ?');
    $smnt->bind_param('ss', $username_or_email, $username_or_email);
    $smnt->execute();

    $smnt->store_result();
    $smnt->bind_result($id, $email, $username, $hash, $avatar, $rank);

    if($smnt->fetch()) {
        if(password_verify($password, $hash)){
            session_start();
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['id'] = $id;
            $_SESSION['avatar'] = $avatar;
            $_SESSION['rank'] = $rank;
            return true;
        }else{
            return false;
        }
    } else{
        return false;
    }
}

/**
 * Adds a new post into the database using
 * the given parameters
 *
 * @param $title - post title
 * @param $description - post description
 * @param $filename - The filename of the posts image
 * @param $userid - the post authors user's id
 * @param $tags - An array of tags for the post
 */
function insert_new_post($title, $description, $filename, $userid, $tags){
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


/**
 * Fetches up to 10 posts from the database after a given offset.
 * If the query parameter is given, the search function is executed and
 * returns a list of posts that matches the search query.
 *
 * @param $offset
 * @param string $searchQuery
 * @param string $tagSearch
 * @return array
 */
function get_posts($offset, $searchQuery = '', $tagSearch = ''){
    $conn = get_conn();
    $offset = ($offset - 1) * 10;
    $logged_in_user_id = (isset($_SESSION['id']) ? $_SESSION['id'] : '');
    $posts = Array();

    /* Search for posts if query is give or fetches after offset if not */
    if(!empty($searchQuery)){
        $posts = search($searchQuery, $conn);
    }else if(!empty($tagSearch)){
        $posts = get_posts_by_tag_name($tagSearch, $conn);
    }else{
        /* Prepare statement to prevent sqlinjection */
        $smnt = $conn->prepare('SELECT posts.id, posts.title, posts.description, posts.likes, posts.added, posts.extension, posts.post_key, users.username, posts.user_id, likes.user_id, favourites.user_id
                                       FROM posts
                                       JOIN users ON (posts.user_id = users.id ) 
                                       LEFT JOIN likes ON (likes.user_id = ? AND posts.id = likes.post_id)
                                       LEFT JOIN favourites ON (favourites.user_id = ? AND posts.id = favourites.post_id)
                                       ORDER BY posts.id desc LIMIT 10 OFFSET ?;');
        /* Bind parameters */
        $smnt->bind_param('iii', $logged_in_user_id, $logged_in_user_id ,$offset);
        $smnt->execute();

        $smnt->store_result();
        $smnt->bind_result($id, $title, $description, $likes, $added, $extension, $post_key, $username, $user_id, $liked_id, $favourite_id);


        /* Adds post objects to a list */
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

/**
 * This function echo's posts from a given
 * array of posts objects.
 *
 * @param $posts - array of post objects
 */
function echo_posts($posts){
    $logged_in_user_id = (isset($_SESSION['id']) ? $_SESSION['id'] : '');
    foreach($posts as $post){
        $cropped_image = $post->post_key . 'c' . $post->extension;
        echo '<section class="post-wrapper">
                    <h1 class="post-title">'. $post->title .'</h1>
                    <a href="./post.php?key='.$post->post_key.'">
                    <img class="post-image" src="./uploadsfolder/' . $cropped_image . '">' .
                    '</a>' .
                    '<section class="details">
                                    <p class="post-description">' .nl2br($post->description) . '</p><hr>' .
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

/**
 * Gets all the posts for the logged in user.
 * Used in the profile page.
 *
 * @return array - An array of post objects
 */
function get_personal_posts(){
    $conn = get_conn();

    $id = $_SESSION['id'];

    /* Prepare statement to prevent sqlinjection */
    $smnt = $conn->prepare('SELECT id, title, description, likes, added, extension, post_key, user_id FROM posts WHERE user_id = ?');
    $smnt->bind_param('i', $id);

    $smnt->execute();

    $smnt->store_result();
    $smnt->bind_result($id, $title, $description, $likes, $added, $extension, $post_key, $user_id);

    $post_arr = Array();
    while($smnt->fetch()){
        $post_arr[] = new Post($id, $title, $description, $likes, $added, $extension, $post_key, $user_id);
    }
    //Return array of post objects
    return $post_arr;
}


/**
 * This function deletes a favourite or like from the given table
 * in the database using the given post_id and user_id and updates the
 * total count for the posts favourites or likes.
 *
 * @param $post_id
 * @param $user_id
 * @param $favourite_or_like_table
 */
function delete_favourite_or_like($post_id, $user_id, $favourite_or_like_table){
    $conn = get_conn();

    /* Prepare statement to prevent sqlinjection */
    $stmt = $conn->prepare('DELETE FROM '.$favourite_or_like_table.' WHERE post_id = ? AND user_id = ?;');
    $stmt->bind_param('ii', $post_id, $user_id);

    $stmt1 = $conn->prepare('UPDATE posts SET '.$favourite_or_like_table.' = '.$favourite_or_like_table.' - 1 WHERE id = ?;');
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

/**
 * This function deletes a favourite or like from the given table
 * in the database using the given post_id and user_id and updates the
 * total count for the posts favourites or likes.
 *
 * @param $post_id
 * @param $user_id
 * @param $favourite_or_like_table
 */
function insert_favourite_or_like($post_id, $user_id, $favourite_or_like_table){
    // Get a database connection
    $conn = get_conn();

    /* Prepare statement and prevent sqlinjection */
    if($stmt = $conn->prepare('INSERT INTO '.$favourite_or_like_table.' (post_id, user_id) VALUES (?, ?);')){
        $stmt->bind_param('ii', $post_id, $user_id);


        $stmt1 = $conn->prepare('UPDATE posts SET '.$favourite_or_like_table.' = '.$favourite_or_like_table.' + 1 WHERE id = ?;');
        $stmt1->bind_param('i', $post_id);

        /* Execute prepared statement */
        $stmt->execute();
        $stmt1->execute();
        $conn->commit();

        /* Close db connection and statements*/
        $stmt->close();
        $stmt1->close();
        $conn->close();
    }
}

/**
 * This function fetches a single post from the database using either
 * a post_id or a post_key (both unique). If a post is found, an array containing all
 * the post information is returned. If not, a false boolean is returned.
 *
 * @param $post_id_or_key
 * @return array|bool
 */
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
    /* Prepare statement and prevent sqlinjection */
    if($smnt = $conn->prepare('SELECT id, title, description, likes, added, extension, post_key, user_id
                                   FROM posts
                                   WHERE posts.'.$queryParam.' = ? limit 1;')){
        //Bind query parameters and execute query
        $smnt->bind_param($bindParam, $post_id_or_key);
        $smnt->execute();

        //Store and bind result
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
}


/**
 * This function deletes a post record from the database
 * using either a post_id or a post_key(both unique).
 *
 * @param $post_id_or_key
 */
function delete_post($post_id_or_key){
    $conn = get_conn();

    $queryParam = null;
    if(gettype($post_id_or_key) == "integer" || gettype(intval($post_id_or_key)) == "integer"){
        $queryParam = 'id';
    }else if(gettype($post_id_or_key) == 'string'){
        $queryParam = 'post_key';
    }

    /* Prepare statement and prevent sqlinjection */
    if($stmt = $conn->prepare('DELETE FROM posts WHERE '.$queryParam.' = ?;')){
        $stmt->bind_param('i', $post_id_or_key);

        /* Execute prepared statement */
        $stmt->execute();

        $conn->commit();

        /* Close db connection and statement*/
        $stmt->close();
        $conn->close();
    }
}

/**
 * This function updates a post with the given post_id or post_key(both unique)
 * and sets the title, description and tags for the updated post.
 *
 * @param $title
 * @param $description
 * @param $post_id_or_key
 * @param $tags
 */
function update_post($title, $description, $post_id_or_key, $tags){
    $conn = get_conn();

    $queryParam = null;
    if(gettype($post_id_or_key) == "integer" || gettype(intval($post_id_or_key)) == "integer"){
        $queryParam = 'id';
    }else if(gettype($post_id_or_key) == 'string'){
        $queryParam = 'post_key';
    }

    /* Prepare statement and prevent sqlinjection */
    $stmt = $conn->prepare('UPDATE posts SET title = ?, description = ? WHERE '.$queryParam.' = ?;');
    $stmt->bind_param('ssi', $title, $description, $post_id_or_key);

    /* Execute prepared statement */
    $stmt->execute();


    //Fetch all the current tags for the post
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

        //Check which given tags are new so they can be added
        foreach($tags as $tag){
            if(!in_array($tag, $old_tags)){
                $tags_to_add[] = $tag;
            }
        }

        //Check which tags that are removed in the given tag array
        foreach($old_tags as $tag){
            if(!in_array($tag, $tags)){
                $tags_to_delete[] = $tag;
            };
        }

        //Delete each tag that needs to be deleted
        foreach($tags_to_delete as $t){
            $tag_id_to_delete = array_search($t, $old_tags);
            delete_post_tag($tag_id_to_delete, $conn);
        }

        //Add each tag that needs to be removed
        foreach ($tags_to_add as $t){
            add_post_tag($post_id_or_key, $t, $conn);
        }

        //All the tags that were not new or removed are still in the database
    };

    $conn->commit();

    /* Close db connection and statement*/
    $stmt->close();
    $conn->close();
}

/**
 * This function fetches all the usernames of the users who
 * liked a post using the given post_id and joins the likes table.
 * The usernames are them returned as json so they can be presented
 * using an ajax(javascript) query.
 * @param $post_id
 * @return string
 */
function get_post_likes($post_id){
    //Get a database connection
    $conn = get_conn();
    /* Prepare statement and prevent sqlinjection */
    if($smnt = $conn->prepare('SELECT users.username FROM likes JOIN users ON (likes.user_id = users.id) WHERE likes.post_id = ?;')){
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
        return $json;
    }
}

/**
 * This functions first checks if a user with the given email exists
 * then checks if a token already exists, before inserting a new
 * token into the password_reset table. If a new token is successfully
 * added to the database, the server should try to send an email with
 * a reset link to the user.
 *
 * @param $email
 */
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

                //Sets unique to true to stop the loop
                $unique = true;
            }
        }
    }

    $conn->commit();
    /* Close db connection and statement*/
    $stmt1->close();
    $conn->close();
}

/**
 * This functions checks the password_reset table for a
 * record with a given token and returns true if it exists
 * and false if it does not.
 *
 * @param $token
 * @param null $conn
 * @return bool
 */
function token_exists($token, $conn = null){
    if(!isset($conn)){
        $conn = get_conn();
    }
    /* Prevent sqlinjection using prepared statement */
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

/**
 * This function selects the email and token from the password_reset table
 * and resets the password for the user with the given email and password
 * and updates the password_hash in the user table.
 *
 * @param $newPass - The new password for the user
 * @param $token - The token connect the user with
 */
function reset_password($newPass, $token){
    $conn = get_conn();
    /* Prevent sqlinjection using prepared statement */
    if($smnt = $conn->prepare('SELECT email, token FROM password_reset WHERE token = ? limit 1')){
        $smnt->bind_param('s', $token);

        $smnt->execute();

        $smnt->store_result();
        $smnt->bind_result($email, $token);

        if($smnt->fetch()){
            $pwhash = password_hash($newPass, PASSWORD_BCRYPT);
            /* Prevent sqlinjection using prepared statement */
            if($smnt = $conn->prepare('UPDATE users SET password_hash = ? WHERE email = ?;')){
                $smnt->bind_param('ss', $pwhash, $email);
                $smnt->execute();

                $smnt = $conn->prepare('DELETE FROM password_reset WHERE token = ?');
                $smnt->bind_param('s', $token);
                $smnt->execute();

                $smnt->close();
            }
        }
    }
}

/**
 * This function gets post info for a single post and gets the posts before
 * and after the post that is found using the post_key. Returns an array
 * containing all the post info and the post_key of the post before and after
 * to link to previous and next post.
 *
 * @param $post_key
 * @return array
 */
function get_posts_before_and_after($post_key){
    $conn = get_conn();
    $user_id = (isset($_SESSION['id']) ? $_SESSION['id'] : 0);

    /* Prevent sqlinjection using prepared statement */
    $smnt = $conn->prepare('SELECT posts.id, posts.title, posts.description, posts.likes, posts.favourites, posts.added, posts.extension, posts.post_key, posts.user_id, likes.user_id, favourites.user_id
                                   FROM posts
                                   LEFT JOIN likes ON (likes.post_id = posts.id AND likes.user_id = ?)
                                   LEFT JOIN favourites ON (favourites.post_id = posts.id AND favourites.user_id = ?)
                                   WHERE posts.post_key = ? limit 1;');
    $smnt->bind_param('iis', $user_id, $user_id, $post_key);
    $smnt->execute();

    $smnt->bind_result($id, $title, $description, $likes, $favourites, $added, $extension, $post_key, $user_id, $liked, $is_favourite);

    if($smnt->fetch()) {
        //TODO Create an object instead
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
        /* Prevent sqlinjection using prepared statement */
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

/**
 * This function inserts a new comment into the database using the
 * given post_key. If the insert is successful, a true boolean and
 * a comment object is returned on an array.
 *
 * @param $post_key
 * @param $comment
 * @param $parent_id
 * @return array|bool
 */
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
            //var_dump($conn->error);
            return false;
        }
    }else{
        //var_dump($conn->error);
        return false;
    }
}

/**
 * This function fetches all the comments for a post and
 * adds them all to a list. Once they are in a list, the child
 * comments are added to their parents child_array and then removed
 * from the first list. This is to create a nested comment tree structure.
 *
 * @param $post_key
 * @return array|bool
 */
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

        //Adds all the child comments to its parent child list
        foreach ($comment_arr as $com) {
            if ($com->parent_id != 0) {
                $comment_arr[$com->parent_id]->children[] = $com;
            }
        }

        //Remove all the child comments
        foreach ($comment_arr as $com) {
            if ($com->parent_id != 0) {
                unset($comment_arr[$com->id]);
            }
        }

        //
        return $comment_arr;
    }else{
        return false;
    }
}

/**
 * This function inserts a new avatar to the avatar table in the database,
 * and returns true if successful and false if not.
 *
 * @param $avatar_key
 * @param $extension
 * @param $user_id
 * @return bool
 */
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


/**
 * This function gets all the all the avatars from the avatars table and returns
 * them as objects in an array.
 *
 * @param $user_id
 * @return array
 */
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

/**
 * This function updates the current avatar for a user
 * in the avatar table and returns true if successful, an
 * false if not.
 *
 * @param $user_id
 * @param $avatar
 * @return bool
 */
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

/**
 * This function fetches all the favourites of a user and echo's them
 * on the index page as paragraphs.
 *
 * //TODO should just get the favourites and not echo them - seperate functionality
 * @return bool
 */
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

/**
 * This function gets all the current post tags for a given post
 * and returns them as an array of arrays.
 *
 * @param $post_id
 * @param bool $ids_only
 * @param null $con
 * @return array
 */
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

/**
 * This function fetches all the tags and echo's them. If
 * the selected parameter is given, the tags in the selected
 * array will be checked.
 *
 * @param array $selected
 */
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

/**
 * This function fetches all the tags
 * from the tags table and returns them
 * as an Array of Arrays.
 *
 * @return array
 */
function get_tags(){
    $conn = get_conn();
    $results = [];
    if($smnt = $conn->prepare("SELECT * FROM tags;")){
        $smnt->execute();
        $smnt->bind_result($id, $tag_name);

        while($smnt->fetch()){
            $tag_arr = Array();
            $tag_arr['id'] = $id;
            $tag_arr['tag_name'] = $tag_name;
            $results[] = $tag_arr;
        }
    }
    return $results;
}

/**
 * This function fetches and echo's all the
 * tahs for a single post.
 *
 * @param $post_id
 */
function echo_post_tags($post_id){
    $tags = get_post_tags($post_id, false);
    foreach($tags as $tag){
        $id = $tag['id'];
        $name = $tag['tag_name'];
        echo "<p id='$id' class='post-tag'>$name</p>";
    }
}

/**
 * This function deletes a single post tag from a post.
 *
 * @param $tag_id
 * @param null $con
 */
function delete_post_tag($tag_id, $con = null){
    $conn = (isset($con) ? get_conn() : $con);

    /* Prevent sqlinjection */
    if($stmt = $conn->prepare('DELETE FROM post_tags WHERE id = ?;')){
        $stmt->bind_param('i', $tag_id);

        /* Execute prepared statement */
        $stmt->execute();

        $conn->commit();

        /* Close db connection and statement*/
        $stmt->close();
        $conn->close();
    }
}

/**
 *
 * This function adds a new post tag to the database.
 *
 * @param $post_id - The post id to link a tag
 * @param $tag_id - The tag id to link a post_tag
 * @param null $con - Optional database connection
 */
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


/**
 *
 * This function searches through the database for
 * posts that matches on either their tags containing
 * the search query or their title and returns
 * a list of post objects.
 *
 * @param $query - The search query from the user
 * @param null $con - optional connection
 * @return array - An array of post objects
 */
function search($query, $con = null){
    //Use the give connection or get another one
    $conn = (isset($con) ? $con : get_conn());
    $logged_in_user_id = (isset($_SESSION['id']) ? $_SESSION['id'] : '');

    $posts = Array();
    $param = "%$query%";
    if($stmt = $conn->prepare("SELECT posts.id, posts.title, posts.description, posts.likes, posts.added, posts.extension, posts.post_key, users.username, posts.user_id, likes.user_id, favourites.user_id
                                      FROM post_tags
                                      JOIN tags ON  post_tags.tag_id = tags.id
                                      JOIN posts ON  (post_tags.post_id = posts.id OR posts.title LIKE ?)
                                      JOIN users ON (posts.user_id = users.id ) 
                                      LEFT JOIN likes ON (likes.user_id = ? AND posts.id = likes.post_id)
                                      LEFT JOIN favourites ON (favourites.user_id = ? AND posts.id = favourites.post_id)
                                      WHERE posts.title LIKE ? OR tags.tag_name LIKE ?;")){
        $stmt->bind_param('siiss', $param, $logged_in_user_id, $logged_in_user_id, $param, $param);
        $stmt->execute();
        $stmt->bind_result($id, $title, $description, $likes, $added, $extension, $post_key, $username, $user_id, $liked_id, $favourite_id);
        while($stmt->fetch()){
            if(!array_key_exists($id, $posts)){
                $posts[$id] = new Post($id, $title, $description, $likes, $added, $extension, $post_key, $user_id, $username, $liked_id, $favourite_id);
            }
        }
    }
    return $posts;
}

/**
 * This function fetches all the posts that have
 * a tag matching the given parameter tag_name.
 * Returns a list of posts objects.
 *
 * @param $tag_name
 * @param null $con
 * @return array
 */
function get_posts_by_tag_name($tag_name, $con = null){
    //Use the give connection or get another one
    $conn = (isset($con) ? $con : get_conn());
    $logged_in_user_id = (isset($_SESSION['id']) ? $_SESSION['id'] : '');

    $posts = Array();
    if($stmt = $conn->prepare("SELECT posts.id, posts.title, posts.description, posts.likes, posts.added, posts.extension, posts.post_key, users.username, posts.user_id, likes.user_id, favourites.user_id
                                      FROM post_tags
                                      JOIN tags ON  post_tags.tag_id = tags.id
                                      JOIN posts ON  (post_tags.post_id = posts.id)
                                      JOIN users ON (posts.user_id = users.id ) 
                                      LEFT JOIN likes ON (likes.user_id = ? AND posts.id = likes.post_id)
                                      LEFT JOIN favourites ON (favourites.user_id = ? AND posts.id = favourites.post_id)
                                      WHERE tags.tag_name = ?")){
        $stmt->bind_param('iis',  $logged_in_user_id, $logged_in_user_id, $tag_name);
        $stmt->execute();
        $stmt->bind_result($id, $title, $description, $likes, $added, $extension, $post_key, $username, $user_id, $liked_id, $favourite_id);
        while($stmt->fetch()){
            $posts[$id] = new Post($id, $title, $description, $likes, $added, $extension, $post_key, $user_id, $username, $liked_id, $favourite_id);
        }
    }
    return $posts;
}



?>
