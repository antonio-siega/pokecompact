<?php

function cacheRequest($fileNameWithoutExtension, $jsonData) {
    $path = getcwd() . "\\..\\cache\\$fileNameWithoutExtension.json";

    $file = fopen($path, "c+"); /*c+ so it doesn't truncate the file, but fseek still
                                affects the writing operations*/
    if (filesize($path) === 0) {
        fwrite($file, "["); //since the file is going to be a list of objects
    }

    if ($file && $jsonData !== json_encode('An error has occured.')) {
        fseek($file, -1, SEEK_END);

        /*since the file only starts with a "[", verifying that it ends in a "]" implies
        that it already has entries. if that's so, then we need to add a comma, append
        the new json data and then close the array again (since the comma overwrites it)*/
        if (fgets($file) === "]"){
            fseek($file, -1, SEEK_CUR);
            fwrite($file, ", ");
        }
        fwrite($file, $jsonData . "]");
        fclose($file);
        return true;
    } else {
        return false;
    }
}

function isCached($cacheFileNameWithoutExtension, $name) {
    $path = getcwd() . "\\..\\cache\\$cacheFileNameWithoutExtension.json";
    
    if (file_exists($path)) {
        $file = fopen($path, "r");

        if (filesize($path) !== 0){
            $jsonData = json_decode(fread($file, filesize($path)), true);

            foreach ($jsonData as $entry) {
                if ($entry['name'] === $name) {
                    fclose($file);
                    return $entry;
                }
            }
        }
    }

    return false;
}

function clearCache($cacheFileNameWithoutExtension) {
    $path = "\\..\\cache\\$cacheFileNameWithoutExtension.json";

    if ($file = fopen($path, "w")) {
        fclose($file);
        return true;
    } else {
        return false;
    }
}

?>