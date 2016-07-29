<?php
require "redis.php";

client()->set('article:123:headline', 'Google wants to turn your clothes into a computer');
client()->set('article:1202:headline', 'For millennials, the end is comming!');
client()->set('string:34:headline', 'What about SWAT, service when advance T');


function upVote($id)
{
    $key = 'article:'.$id.':votes';
    client()->incr($key);
}

function downVote($id)
{
    $key = 'article:'.$id.':votes';
    client()->decr($key);
}

function showResults($id)
{
    $headlineKey = 'article:'.$id.':headline';
    $voteKey = 'article:'.$id.':votes';

    $data = client()->mget([
        $headlineKey,
        $voteKey
    ]);

    print 'The article '.$data[0].' has '.$data[1].' votes!';
}

upVote(1202);
upVote(123);
upVote(123);
upVote(1202);
upVote(34);
upVote(34);
downVote(1202);
downVote(123);

showResults(123);
showResults(1202);
showResults(34);

client()->quit();