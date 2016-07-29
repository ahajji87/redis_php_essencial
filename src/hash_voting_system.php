<?php
require "redis.php";

function saveLink($id, $author, $title, $link)
{
    client()->hmset('link:'.$id, 'author', $author, 'title', $title, 'link', $link, 'score', 0);
}


function upVote($id)
{
    $key = 'link:'.$id;
    client()->hincrby($key, 'score', 1);
}

function downVote($id)
{
    $key = 'link:'.$id;
    client()->hincrby($key, 'score', -1);
}

function showDetails($id)
{
    $data = client()->hgetall('link:'.$id);

    echo "Title: ", $data['title']."\n";
    echo "Author: ", $data['author']."\n";
    echo "Link: ", $data['link']."\n";
    echo "Score: ", $data['score']."\n";
    echo "-----------------------------\n";
}

saveLink(123, "dayvson", "Maxwell Dayvson's Github page", "https://github.com/dayvson");
upVote(123);
upVote(123);
saveLink(456, "hltbra", "Hugo Tavares's Github page", "https://github.com/hltbra");
upVote(456);
upVote(456);
upVote(456);
upVote(456);
downVote(456);
showDetails(123);
showDetails(456);

client()->quit();