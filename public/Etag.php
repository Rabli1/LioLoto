<?php
header('Content-Type: application/json');
echo file_get_contents(__DIR__ . '/../database/json/Etag.json');