<?php
    function get_base_url($url) {
        $split_url = explode("/", $url);
        $base_url = $split_url[0] . "//" . $split_url[2] . "/";
        return $base_url;
    }

    function try_replace_with_frontend($url, $frontend, $original) {
        $config = require "config.php";

        if (isset($_COOKIE[$frontend]) || isset($_REQUEST[$frontend]) || !empty($config->$frontend))
        {
            if (isset($_COOKIE[$frontend]))
                $frontend = $_COOKIE[$frontend];
            else if (isset($_REQUEST[$frontend]))
                $frontend = $_REQUEST[$frontend];
            else if (!empty($config->$frontend))
                $frontend = $config->$frontend;

            if ($original == "instagram.com") 
            {
                if (!strpos($url, "/p/"))
                    $frontend .= "/u";
            }
           
            $url =  $frontend . explode($original, $url)[1];

            return $url;
        }

        return $url;
    }

    function check_for_privacy_frontend($url) {
        $frontends = array(
            "youtube.com" => "invidious",
            "instagram.com" => "bibliogram",
            "twitter.com" => "nitter",
            "reddit.com" => "libreddit",
            "tiktok.com" => "proxitok",
            "wikipedia.org" => "wikiless"
        );

        foreach($frontends as $original => $frontend)
        {
            if (strpos($url, $original))
            {
                $url = try_replace_with_frontend($url, $frontend, $original);
                break;
            }
        }

        return $url;
    }

    function check_ddg_bang($query) {

        $bangs_json = file_get_contents("static/misc/ddg_bang.json"); 
        $bangs = json_decode($bangs_json, true);
        
        $search_word = substr(explode(" ", $query)[0], 1);
        $bang_url = null;

        foreach($bangs as $bang)
        {
            if ($bang["t"] == $search_word)
            {
                $bang_url = $bang["u"];
                break;
            }
        }

        if ($bang_url)
        {
            $bang_query_array = explode("!" . $search_word, $query);
            $bang_query = trim(implode("", $bang_query_array));

            $request_url = str_replace("{{{s}}}", $bang_query, $bang_url);
            $request_url = check_for_privacy_frontend($request_url);

            header("Location: " . $request_url);
            die();
        }
    }

    function get_xpath($response) {
        if(!empty($response)) {
            $htmlDom = new DOMDocument;
            @$htmlDom->loadHTML($response);
            $xpath = new DOMXPath($htmlDom);
            return $xpath;
        } else {
            return null;
        }
    }

    function request($url) {
        global $config;

        $ch = curl_init($url);
        curl_setopt_array($ch, $config->curl_settings);
        $response = curl_exec($ch);

        return $response;
    }

    function human_filesize($bytes, $dec = 2) {
        $size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$dec}f ", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    function remove_special($string){
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    function print_elapsed_time($start_time) {
        $end_time = number_format(microtime(true) - $start_time, 2, '.', '');
        echo "<p class=\"mb-5\" id=\"time\">Fetched results in $end_time seconds</p>";
    }

    function print_next_page_button($text, $page, $query, $type) {
        echo "<form class=\"page\" action=\"search\" target=\"_top\" method=\"get\" autocomplete=\"off\">";
        echo "<input type=\"hidden\" name=\"p\" value=\"" . $page . "\" />";
        echo "<input type=\"hidden\" name=\"q\" value=\"$query\" />";
        echo "<input type=\"hidden\" name=\"type\" value=\"$type\" />";
        echo "<button type=\"submit\">$text</button>";
        echo "</form>";
    }

    function print_no_results($query) {
        echo "
        <div class=\"text-result-container\">
        <p>There are no results for your search - <b><em>$query</em></b></p>
        <p class='mt-2'>Suggestions:</p>
        <ul class='ml-3' style='color: var(--result-fg);'>
        <li>Make sure that all words are spelled correctly.</li>
        <li>Try different keywords.</li>
        <li>Try more general keywords.</li>
        <li>Try fewer keywords.</li>
        </ul>
        </div>";
    }

    function print_api_error($error, $code) {
        return array("code" => $code, "error" => $error);
    }
?>
