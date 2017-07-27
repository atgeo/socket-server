<?php
define("HOST", "localhost");
define("PORT", 9000);
define("USERNAME", "username");
define("PASSWORD", "password");
define("DB_NAME", "database");

require_once "suggestTags.php";

function sig_handler($signo)
{
    global $spawn;
    echo "Handling $signo signal\n";
    echo "Closing socket and dying...\n";
    @socket_close($spawn);
    exit;
}

function fetchTagsFromDB()
{
    $mysqli = new mysqli(HOST, USERNAME, PASSWORD, DB_NAME);
    $mysqli->set_charset('utf8');
    $result = $mysqli->query('select id,tag from tags where status = 1');

    return $result;
}

function getTags()
{
    $result = fetchTagsFromDB();
    $db_tags = $db_tags_ids = array();

    //$i = 0;
    while ($row = $result->fetch_object()) {
        $tag = $row->tag;
        $id = $row->id;

        $db_tags[] = $tag;
        $db_tags_ids[] = $id;
    }

    return array('db_tags' => $db_tags, 'db_tags_ids' => $db_tags_ids);
}

function suggestTags($string, $db_tags, $db_tags_ids)
{
    $suggestTags = new suggestTags();
    $length = $suggestTags->setWords($string);

    $tags = $tag_ids = array();
    $altered_word = false;

    for ($i = 0; $i < $length; $i++) {
        $word = $suggestTags->getWordByIndex($i);

        if (!in_array($word, SuggestTags::$excluded_words)) {
            $suggestTags->query = $word;
            $suggestTags->j = 1;

            $promoted_result = '';
            $query = true;
            $results = array(); // Search results

            while ($suggestTags->j) {
                // First query into the $db_tags
                // it returns the number of the search results into variable $j
                // this section also strip linking characters if exists
                if ($query) {
                    $s = $suggestTags->query;

                    $results = $suggestTags->findQuery($db_tags, false);

                    $suggestTags->j = count($results);

                    // Reset $j if the tag found is not exactly the same we are looking for
                    SECTION_no_exact_match:
                    if ($suggestTags->j == 1 && !$suggestTags->findQuery($results)) {
                        $withNextWord = $suggestTags->query . " " . $suggestTags->getWordByIndex($i + 1);
                        if (!$suggestTags::findCustom($withNextWord, $results, true)) {
                            $suggestTags->j = 0;
                        }
                    }

                    //if didn't find a tag and it's a one word
                    //if it starts with "'و', 'ك', 'ب', 'ف', '"
                    if (!$suggestTags->j && !$suggestTags->getNumberOfAdditionalWords() && in_array(mb_substr($s, 0, 1), SuggestTags::$prefixes) && !$altered_word) {
                        $suggestTags->query = mb_substr($s, 1);

                        $query = true;
                        $suggestTags->j = 1;
                        $altered_word = true;
                        continue;
                    }
                } else {
                    $suggestTags->j = 0;

                    foreach ($results as $result) {
                        if (strpos($result, $suggestTags->query) !== false) {
                            $suggestTags->j++;
                        }
                    }

                    $query = true;
                }

                $altered_word = false;

                if (!$suggestTags->j && $promoted_result) {
                    if ($promoted_result) {
                        $tag_id = $db_tags_ids[array_search($promoted_result, $db_tags)];
                        $tags[] = array('id' => $tag_id, 'name' => $promoted_result);
                        $tag_ids[] = $tag_id;
                        if ($suggestTags->getNumberOfAdditionalWords() > 1) {
                            $i = $i - ($suggestTags->getNumberOfAdditionalWords() - 1);
                            continue;
                        }
                    } else
                        $promoted_result = '';
                }

                if (in_array($suggestTags->query, $results)) {
                    $promoted_result = $suggestTags->query;
                }

                if ($suggestTags->j == 1) {
                    if (($key = array_search($suggestTags->query, $results)) !== false) {
                        $tag_name = $results[$key];
                        $tag_id = $db_tags_ids[array_search($suggestTags->query, $db_tags)];
                        $tags[] = array('id' => $tag_id, 'name' => $tag_name);
                        $tag_ids[] = $tag_id;
                    } else {
                        goto SECTION_take_word_after;
                    }
                    $suggestTags->j = 0;
                } else if ($suggestTags->j > 1) {

                    SECTION_take_word_after:
                    if ($i < $length - 1 && !in_array($suggestTags->getWordByIndex($i + 1), $suggestTags::$excluded_words)) {
                        $suggestTags->query .= ' ' . $suggestTags->getWordByIndex(++$i);
                        $query = false;
                    } else {
                        if ($promoted_result) {
                            $tag_id = $db_tags_ids[array_search($promoted_result, $db_tags)];

                            $tags[] = array('id' => $tag_id, 'name' => $promoted_result);
                            $tag_ids[] = $tag_id;

                        } else { //if it was the last word and didn't find an exact match
                            $suggestTags->j = 1;
                            goto SECTION_no_exact_match;
                        }
                        break;
                    }
                } else {
                    SECTION_take_word_before:
                    // No results for word or combination of words. Go to next word (or second word in combination).
                    if ($i < $length - 1 && !in_array($suggestTags->getWordByIndex($i + 1), $suggestTags::$excluded_words)) {
                        $i = $i - $suggestTags->getNumberOfAdditionalWords();
                        $suggestTags->query = $suggestTags->getWordByIndex(++$i);
                        $query = true;
                        $suggestTags->j = 1;
                    }
                }
            }
        }
    }

    // Increment suggested in DB
    incrementSuggestedInDB($tag_ids);

    $tags = array_map("unserialize", array_unique(array_map("serialize", $tags)));

    return $tags;
}

function incrementSuggestedInDB($ids)
{
    $ids = implode(',', $ids);

    $mysqli = new mysqli(HOST, USERNAME, PASSWORD, DB_NAME);
    $mysqli->query("update tags set suggested = suggested + 1 where id in ($ids)");
}