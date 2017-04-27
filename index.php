<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 14/01/17
 * Time: 14:34
 */
session_start();
include 'dbHandling.php';
include_once 'functions.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $query = (isset($_POST["search-input"]) ? $_POST["search-input"] : '');
    header("Location: ./index.php?query=$query");
    exit();
}
?>


<!DOCTYPE html>
<html>

    <head>
        <?php
            echo_metadata();
        ?>
        <title>Picstr</title>
        <link href="https://use.fontawesome.com/db87107c26.css" media="all" rel="stylesheet">

        <link rel="stylesheet" href="css/menu.css">
        <link rel="stylesheet" href="css/index.css">
        <script type="text/javascript" src="./js/jquery.js"></script>
        <script type="text/javascript" src="./js/config.js"></script>
        <script type="text/javascript" src="./js/likes.js"></script>
        <script type="text/javascript" src="./js/favourite.js"></script>
        <script type="text/javascript" src="./js/script.js"></script>

    </head>

    <body>
        <header>
            <?php get_navigation(); ?>
        </header>


        <section id="search-results">
            <?php
            $query = (isset($_GET['query']) ? $_GET['query'] : '');
            $page = (isset($_GET['page']) ? intval($_GET['page']) : 1);
            $results = get_posts($page, $query);
            $posts = $results['posts'];

            if(!empty($query)){
                $numberOfResults = count($posts);
                echo "<h2>You searched for: $query</h2>";
                echo "Total results: $numberOfResults";
            }
            ?>
        </section>
        <main>

            <div id="post-container">
                <?php
                    echo_posts($posts);
                ?>

            </div>

            <div id="right-container">
                <div id="fixed-wrapper">
                    <section id="favourites" class="right-section">
                        <h1>Some of your favourites</h1>
                        <ul class="right-list">
                            <?php
                            echo (isset($_SESSION['id']) ? echo_user_favourites() : '<p>No favourites to show. Try <a href="./login.php">logging in</a> to add some.</p>');
                            ?>
                        </ul>
                    </section>

                    <section id="popular-categories" class="right-section">
                        <h1>Categories</h1>
                        <ul class="right-list">
                            <li class="right-list-item">
                                Work in progress..
                            </li>
                        </ul>
                    </section>
                </div>
            </div>

        </main>

        <section id="next-page-section">

            <?php
            if(empty($query)) {
                $totalPages = $results['totalPages'];
                $previous = $page - 1;
                $next = $page + 1;


                echo "<ul id='pages-list'>";
                $max = (($page + 5) > $totalPages ? $totalPages : ($page + 5));
                $start = (($page - 5) > 0 ? ($page - 5) : 0);


                if ($max == $totalPages && $max - 10 > 0) {
                    $start = $max - 10;
                } elseif ($start == 0 && $start + 10 < $totalPages) {
                    $max = 10;
                }
                echo($previous > 0 ? "<li class='page-list-element bottom-navigation'><a href='./?page=$previous'><-Prev</a></li>" : null);
                for ($i = $start; $i < $max; $i++) {
                    $p = ($i + 1);
                    if ($p <= $totalPages) {
                        if ($p != $page) {
                            echo "<li class='page-list-element'>[<a href='./?page=$p'>" . $p . "</a>]</li>";
                        } else {
                            echo "<li class='page-list-element'>[<b><a href='./?page=$p'>" . $p . "</a></b>]</li>";
                        }
                    } else {
                        break;
                    }
                };
                if ($max != $totalPages) {
                    echo "<li class='page-list-element'>..[<a href='./?page=$totalPages'>$totalPages</a>]</li>";
                }

                echo($next <= $totalPages ? "<li class='page-list-element bottom-navigation'><a href='./?page=$next'>next-></a></li>" : null);

                echo "</ul>";
            }
            ?>
        </section>


        <footer>

        </footer>
    </body>
</html>
