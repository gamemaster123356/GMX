<?php

    $thepiratebay_url = "https://apibay.org/q.php?q=$query";

    function get_thepiratebay_results($response)
    {
        global $config;
        $results = array();
        $json_response = json_decode($response, true);

        if(!empty($json_response)) {
            foreach ($json_response as $response)
            {

                $size = human_filesize($response["size"]);
                $hash = $response["info_hash"]; 
                $name = $response["name"];
                $seeders = (int) $response["seeders"];
                $leechers = (int) $response["leechers"];
                $files = (int) $response["num_files"];

                $magnet = "magnet:?xt=urn:btih:$hash&dn=$name" . $config->bittorent_trackers;

                if ($name == "No results returned")
                    break;

                array_push($results, 
                    array (
                        "size" => htmlspecialchars($size),
                        "name" => htmlspecialchars($name),
                        "seeders" => htmlspecialchars($seeders),
                        "leechers" => htmlspecialchars($leechers),
                        "magnet" => htmlspecialchars($magnet),
                        "files" => htmlspecialchars($files),
                        "source" => "thepiratebay.org"
                    )
                );
            }
        }

        return $results;
       
    }
?>
