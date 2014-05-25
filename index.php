<?php
require_once 'init.php';
require_once 'classification.php';

$epsilon = isset($_POST['epsilon']) ? $_POST['epsilon'] : 0;

if (isset($_GET['heartbeat'])) {
    echo $_GET['heartbeat'];
} elseif (isset($_POST['classify'])) {
    $raw_data_id = $_POST['raw_data_id'];
    $raw_draw_data = $_POST['classify'];

    // Classification
    if ($epsilon > 0) {
        $result_path = apply_douglas_peucker(pointLineList($raw_draw_data), $epsilon);
    } else {
        $result_path = pointLineList($raw_draw_data);
    }
    $A = scale_and_center(list_of_pointlists2pointlist($result_path));

    // Get the first 4000 known formulas
    $sql = "SELECT `wm_raw_draw_data`.`id`, `data`, `accepted_formula_id`, ".
           "`formula_in_latex`, `accepted_formula_id` as `formula_id`".
           "FROM `wm_raw_draw_data` ".
           "JOIN  `wm_formula` ON  `wm_formula`.`id` =  `accepted_formula_id` ".
           "LIMIT 4000";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $datasets = $stmt->fetchAll();

    $results = array();

    foreach ($datasets as $key => $dataset) {
        $B = $dataset['data'];
        if ($epsilon > 0) {
            $B = apply_douglas_peucker(pointLineList($B), $epsilon);
        } else {
            $B = pointLineList($B);
        }
        $B = scale_and_center(list_of_pointlists2pointlist($B));
        $results[] = array("dtw" => greedyMatchingDTW($A, $B),
                           "latex" => $dataset['accepted_formula_id'],
                           "id" => $dataset['id'],
                           "latex" => $dataset['formula_in_latex'],
                           "formula_id" => $dataset['formula_id']);
    }

    $dtw = array();
    foreach ($results as $key => $row) {
        $dtw[$key] = $row['dtw'];
    }
    array_multisort($dtw, SORT_ASC, $results);
    $results = array_filter($results, "maximum_dtw");

    // get only best match for each single symbol
    $results2 = array();
    foreach ($results as $key => $row) {
        if (array_key_exists($row['formula_id'], $results2)) {
            $results2[$row['formula_id']] = min($results2[$row['formula_id']], $row['dtw']);
            continue;
        } else {
            $results2[$row['formula_id']] = $row['dtw'];
        }
    }

    $results = $results2;
    $results = array_slice($results, 0, 10, true);

    $results = get_probability_from_distance($results);
    //$results = array("31" => 0.00000123);
    echo json_encode($results);
}

?>