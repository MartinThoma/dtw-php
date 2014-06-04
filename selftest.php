<?php
set_time_limit (2*60*60);
require_once 'init.php';
require_once 'classification.php';

// Prepare crossvalidation data set
$crossvalidation = array(
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
        array()
    );

$sql = "SELECT id, formula_in_latex FROM `wm_formula`";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$datasets = $stmt->fetchAll();

foreach ($datasets as $key => $dataset) {
    $id = $dataset['id'];
    $sql = "SELECT id, data FROM `wm_raw_draw_data` WHERE `accepted_formula_id` = :fid";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':fid', $id, PDO::PARAM_INT);
    $stmt->execute();
    $raw_datasets = $stmt->fetchAll();
    if (count($raw_datasets) >= 10) {
        echo $dataset['formula_in_latex']." (".count($raw_datasets).")<br/>";
        $i = 0;
        foreach ($raw_datasets as $key => $raw_data) {
            $crossvalidation[$i][] = array('data' => $raw_data['data'],
                                           'id' => $raw_data['id'],
                                           'formula_id' => $dataset['id'],
                                           'accepted_formula_id' => $id,
                                           'formula_in_latex' => $dataset['formula_in_latex']
                                          );
            $i = ($i + 1) % 10;
        }
    }
}

for ($i=0; $i < 10; $i++) { 
    print_r(count($crossvalidation[0]));
    echo "<br/>";
}

// Set parameters for classification
$epsilon = 0;
// Start getting validation results
$classification_accuracy = array();
echo "\n\n";
$execution_time = array();

function is_in_top_ten($id, $data) {
    $keys = array();
    foreach ($data as $key => $value) {
        $keys[] = key($value);
    }
    return in_array($id, $keys);
}

for ($testset=0; $testset < 10; $testset++) {
    $classification_accuracy[] = array('correct' => 0,
                                       'wrong' => 0,
                                       'c10' => 0,
                                       'w10' => 0);
    foreach ($crossvalidation[$testset] as $testdata) {
        $start = microtime (true);
        $raw_draw_data = $testdata['data'];
        if ($epsilon > 0) {
            $result_path = apply_douglas_peucker(pointLineList($raw_draw_data), $epsilon);
        } else {
            $result_path = pointLineList($raw_draw_data);
        }
        $A = scale_and_center(list_of_pointlists2pointlist($result_path));

        // Prepare datasets the algorithm may use
        $datasets = array();
        foreach ($crossvalidation as $key => $value) {
            if ($key == $testset) {
                continue;
            } else {
                $datasets = array_merge($datasets, $value);
            }
        }

        $results = classify($datasets, $A, $epsilon);
        $end = microtime (true);
        $execution_time[] = $end - $start;

        reset($results);
        $answer_id = 0;
        if(is_null($results[0])) {
            # That should not happen
            echo "\nRaw_data_id = ".$testdata['id']."\n";
            $answer_id = key($results);
        } else {
            $answer_id = key($results[0]);
        }

        if ($answer_id == $testdata['formula_id']) {
            $classification_accuracy[$testset]['correct'] += 1;
        } else {
            $classification_accuracy[$testset]['wrong'] += 1;
        }

        if (is_in_top_ten($testdata['formula_id'], $results)) {
            $classification_accuracy[$testset]['c10'] += 1;
        } else {
            $classification_accuracy[$testset]['w10'] += 1;
        }
        echo "|";
    }

    $classification_accuracy[$testset]['accuracy'] = $classification_accuracy[$testset]['correct'] / 
        ($classification_accuracy[$testset]['correct'] + $classification_accuracy[$testset]['wrong']);
    $classification_accuracy[$testset]['a10'] = $classification_accuracy[$testset]['c10'] /
        ($classification_accuracy[$testset]['c10'] + $classification_accuracy[$testset]['w10']);
    var_dump($classification_accuracy);
    echo "\n";
    echo "Average time:";
    echo array_sum($execution_time)/count($execution_time);
}

var_dump($classification_accuracy);

?>