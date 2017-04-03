<?php
include '../dbHandling.php';
$conn = get_conn();

$result = $conn->query('SELECT * FROM comments;');

foreach ($result->fetch_array() as $i){
    echo $i . '\n';
}



$comments = array(
    1 => array('id' => 1, 'parent_id' => 0, 'childs' => array()),
    2 => array('id' => 2, 'parent_id' => 0, 'childs' => array()),
    3 => array('id' => 3, 'parent_id' => 0, 'childs' => array()),
    5 => array('id' => 5, 'parent_id' => 0, 'childs' => array()),
    11 => array('id' => 11, 'parent_id' => 0, 'childs' => array()),
    17 => array('id' => 17, 'parent_id' => 0, 'childs' => array()),
    23 => array('id' => 23, 'parent_id' => 0, 'childs' => array()),
    28 => array('id' => 28, 'parent_id' => 0, 'childs' => array()),

    4 => array('id' => 4, 'parent_id' => 1, 'childs' => array()),
    6 => array('id' => 6, 'parent_id' => 1, 'childs' => array()),
    8 => array('id' => 8, 'parent_id' => 2, 'childs' => array()),
    9 => array('id' => 9, 'parent_id' => 2, 'childs' => array()),
    7 => array('id' => 7, 'parent_id' => 3, 'childs' => array()),
    12 => array('id' =>12, 'parent_id' => 7, 'childs' => array()),
    13 => array('id' => 13, 'parent_id' => 12, 'childs' => array()),
);



/** Comment prepare start */
foreach ($comments as $k => &$v) {
    if ($v['parent_id'] != 0) {
        $comments[$v['parent_id']]['childs'][] =& $v;
    }
}
unset($v);

foreach ($comments as $k => $v) {
    if ($v['parent_id'] != 0) {
        unset($comments[$k]);
    }
}

/** Comment prepare end */

//Your indent pattern
function indent($size) {
    $string = "";
    for ($i = 0; $i < $size; $i++) {
        $string .= "#";
    }
    echo $string . '\n';
}


function printComments($comments, $indent = 0) {
    foreach ($comments as $comment) {
        echo indent($indent + 1).' I am comment '.$comment['id']."\n";
        if (!empty($comment['childs'])) {
            printComments($comment['childs'], $indent + 1);
        }
    }
}


printComments($comments);
?>