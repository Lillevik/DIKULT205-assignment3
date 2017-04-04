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
?>


<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="images/.jpg">
        <link rel="stylesheet" href="css/menu.css">
        <link rel="stylesheet" href="css/index.css">
        <script src="https://use.fontawesome.com/db87107c26.js"></script>
        <script type="text/javascript" src="./js/jquery.js"></script>
        <script type="text/javascript" src="./js/config.js"></script>
        <script type="text/javascript" src="./js/likes.js"></script>


        <script>
            function start_gif(element){
                var src = element.getAttribute("src");
                console.log(src);
                var arr = src.split('./uploadsfolder/')[1].split(".");
                console.log(arr);
                var current_src = arr[0]
                var new_src = current_src.substring(0, current_src.length - 1) + "." + arr[1];
                console.log(new_src);
                element.setAttribute("src", './uploadsfolder/' + new_src);
            }
        </script>
    </head>

    <body>
        <header>
            <?php get_navigation(); ?>
        </header>

        <main>

            <ul id="likes-window">

            </ul>

            <div id="post-container">
                <?php
                    if(!isset($_GET['page'])){
                        echo_posts(20);
                    }else{
                        echo_posts(20);
                    }
                ?>
            </div>

            <div id="right-container">
                <section class="right-section">
                    <h1>Favorites</h1>
                    <ul class="right-list">
                        <li class="right-list-item">
                            No favorites :(
                        </li>
                    </ul>
                </section>

                <section class="right-section">
                    <h1>Popular categories</h1>
                    <ul class="right-list">
                        <li class="right-list-item">
                            Nature
                        </li>
                        <li class="right-list-item">
                            Funny
                        </li>
                    </ul>
                </section>

            </div>

        </main>

        <footer>

        </footer>
    </body>
</html>
