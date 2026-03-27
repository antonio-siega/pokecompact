<?php

require_once('cache_operations.php');
require_once('../vendor/autoload.php');

//gets move details from a pokémon json
function getMoveDetails($apiHandler, $movesArray, $moveListOffset, $moveListLimit) {

    for ($i = 0; $i < $moveListOffset; $i++) {
        next($movesArray);
    }

    for ($i = 0; $i < $moveListLimit; $i++){
        if (key($movesArray) !== null){
            $data = current($movesArray);

            $moveName = $data['name'];
            $cachedMoveData = isCached("move", $moveName);

            if ($cachedMoveData) {
                $extractedData = $cachedMoveData;
            } else {
                $moveInfo = $apiHandler->sendRequest($data['url']);
                $moveInfo = json_decode($moveInfo, true);
                $extractedData = array(
                    'name' => $moveInfo['name'],
                    'effect' => isset($moveInfo['effect_entries'][1]) ?
                                $moveInfo['effect_entries'][1]['short_effect'] :
                                "",
                    'flavor_text' => "" //will be filled in later
                );

                //getting the first flavor text entry in english
                $flavorTextEntries = $moveInfo['flavor_text_entries'];
                $j = 0;
                while ($flavorTextEntries[$j]['language']['name'] !== "en"){
                    $j++;
                }
                $extractedData['flavor_text'] = $flavorTextEntries[$j]['flavor_text'];


                cacheRequest("move", json_encode($extractedData));
            }

            //if the actual effect info exists in english, get it
            if ($extractedData['effect'] !== ""){
                $moveDescription = $extractedData['effect'];
            
            //if not, the flavor text will have to do.
            } else {
                $moveDescription = $extractedData['flavor_text'];
            }

            $moveDescription = str_replace("\$effect_chance%", "", $moveDescription);

            echo "<tr>
                    <td class='fw-bold' style='vertical-align: middle'>" . ucwords(str_replace("-", " ", $moveName)) . "</td>
                    <td style='text-align: left'>" . $moveDescription . "</td>
                 </tr>";
            next($movesArray);
        } else {
            break;
        }
    }
}

//gets the sprites for the types of a pokémon from its "types" array
function getTypeSprites($apiHandler, $typesArray){
    $typeSprites = [];

    foreach($typesArray as $typeInfo) {
        $typeName = $typeInfo["type"]["name"];
        $cachedTypeData = isCached("type", $typeName);

        if ($cachedTypeData) {
            $typeSprites[] = $cachedTypeData["sprite"];
        } else {
            $typeResponse = $apiHandler->sendRequest($typeInfo["type"]["url"]);

            if ($typeResponse !== json_encode('An error has occured.')){
                $typeResponse = json_decode($typeResponse, true);

                if (isset($typeResponse["sprites"]["generation-iii"]["emerald"]["name_icon"])) {
                    $typeSprite = $typeResponse["sprites"]["generation-iii"]["emerald"]["name_icon"];
                } else {
                    $typeSprite = $typeResponse["sprites"]["generation-vi"]["x-y"]["name_icon"];
                }

                $typeData = array(
                    "name" => $typeInfo["type"]["name"],
                    "url" => $typeInfo["type"]["url"],
                    "sprite" => $typeSprite
                );

                cacheRequest("type", json_encode($typeData));
                $typeSprites[] = $typeSprite;
            }
        }
    }

    return $typeSprites;
}
?>