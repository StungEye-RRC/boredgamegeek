<?php

/* Finds all rows in the games table.
   Returns an array of hashes.
*/
function find_all_games() {
    $sql        = "SELECT * FROM games";
    $pdo_result = Database::prepare_and_execute($sql);
    $games      = $pdo_result->fetchAll();
    
    return $games;
}

/* Creates a new game in the games table by building the appropriate
   SQL INSERT statement.
   Usage:
   $new_game = ['name'             => 'New Game',
                'description'      => 'New Description',
                'min_num_players'  => 2,
                'max_num_players'  => 4,
                'min_play_minutes' => 60,
                'max_play_minutes' =>  120,
                'official_url'     => ''];   
   $success = create_game($new_game);
*/
function create_game($game) {
    $sql  = "INSERT INTO games";
    $sql .= "(name, description, min_num_players, max_num_players, min_play_minutes, max_play_minutes, official_url)";
    $sql .= " VALUES (:name, :description, :min_num_players, :max_num_players, :min_play_minutes, :max_play_minutes, :official_url)";
    echo $sql;
    return Database::prepare_and_execute($sql, $game);
}

/* Builds a hash of sanitized game form data from the POST super global.
 
   If nothing was provided for a particular input the default will be an empty string.

   Only adds the 'id' key if an id was POSTed.
*/
function sanitized_game() {
    $game = [
        'name'             => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        'description'      => filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        'min_num_players'  => filter_input(INPUT_POST, 'min_num_players', FILTER_SANITIZE_NUMBER_INT),
        'max_num_players'  => filter_input(INPUT_POST, 'max_num_players', FILTER_SANITIZE_NUMBER_INT),
        'min_play_minutes' => filter_input(INPUT_POST, 'min_play_minutes', FILTER_SANITIZE_NUMBER_INT),
        'max_play_minutes' => filter_input(INPUT_POST, 'max_play_minutes', FILTER_SANITIZE_NUMBER_INT),
        'official_url'     => filter_input(INPUT_POST, 'official_url', FILTER_SANITIZE_URL)
    ];
        
    if (isset($_POST['id'])) {
        $game['id'] = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    }
    
    return $game;
}

/* Returns a string describing the validation error if there is one.
 
   If the game is valid the function returns false.
*/
function validation_error($game) {
    if (strlen($game['name']) == 0) {
        return "A name must be provided.";
    } else if (strlen($game['description']) == 0) {
        return "A description must be provided.";
    } else if (!is_numeric($game['min_num_players']) || ($game['min_num_players'] < 1)) {
        return "The minimum number of players must be greater than 0.";
    } else if (!is_numeric($game['max_num_players']) || ($game['max_num_players'] < 1)) {
        return "The maximum number of players must be greater than 0.";
    } else if ($game['max_num_players'] < $game['min_num_players']) {
        return "The maximum number of players must be greater than or equal to the minimum number of players.";
    } else if (!is_numeric($game['min_play_minutes']) || ($game['min_play_minutes'] < 1)) {
        return "The minimum play time must be greater than 0 minutes.";
    } else if (!is_numeric($game['max_play_minutes']) || ($game['max_play_minutes'] < 1)) {
        return "The maximum play time must be greater than 0 minutes.";
    } else if ($game['max_play_minutes'] < $game['min_play_minutes']) {
        return "The maximum play time must be greater than or equal to the minimum play time.";
    } else {
        return false;
    }
}

/* Dynamically builds a PDO SQL update statement based on the provided $game hash.

   The hash keys must be valid games table columns.

   The $game hash must contain an id key to specific which row to update.

   Usage:

    $updated_game = ['name' => 'Changed',
                    'max_play_minutes' =>  180,
                    'official_url' => 'http://example.com',
                    'id' => 2];
    $pdo_result = update_game($new_game);

   This would build the following SQL statement:

    UPDATE games SET name = :name, max_play_minutes = :max_play_minutes, official_url = :official_url, id = :id WHERE id = :id

   Which would in turn be bound to the $updated_game data.
*/
function update_game($game) {
    // Being the SQL UPDATE statement
    $sql = "UPDATE games SET ";
    
    // Add all the "column = :placeholder" parts to the SQL statement
    foreach($game as $column => $value) {
        $sql .= "{$column} = :{$column}, ";
    }
    
    // Remove the last ", " added above so that we can add the final WHERE clause.
    $sql = substr($sql, 0, count($sql) - 3);
    $sql .= " WHERE id = :id";
    
    return Database::prepare_and_execute($sql, $game);
}

?>