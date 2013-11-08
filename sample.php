<?php

require_once '__autoloader.php';

$mongo = new MongoClient;
$mongoDB = $mongo->test;
MongoAR\ActiveRecord::setDatabase($mongoDB);

class CojenovePost extends MongoAR\ActiveRecord
{

}
foreach (CojenovePost::findAll()->limit(2, 1) as $article) {
    var_dump($article);
}
