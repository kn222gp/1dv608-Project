<?php

class NotFoundInDatabaseException extends \Exception{};

class SearchModel {
    
    private static $artistForAssocArray = 'ArtistName';
    private static $songForAssocArray = 'SongName';
    private static $chordsForAssocArray = 'Chords';
    private static $regexDollarSign = '/\$/';
    
    private $connection;
    private $artistNames;
    private $artistSongs;
    private $allrows;
    
    public function __construct(DeleteModel $deleteModel, ConnectionDAL $connectionDAL) {
        $this->deleteModel = $deleteModel;
        $this->connectionDAL = $connectionDAL;
    }
    
    public function checkDatabase($sField) {
        $this->sField = $sField;
        
        // Open a connection.
        $connection = $this->connectionDAL->OpenConnection();
        
        // The query that are going to be asked to the database.
        $query = $this->getEverything($connection);

        // Fetch from database using the selected query..
        $result = $this->fetchFromDatabase($connection, $query);
        
        // Close the connection.
        $this->connectionDAL->CloseConnection($connection);
        
        return $result;
    }
    
    public function listSongs(){
        
        $connection = $this->connectionDAL->OpenConnection();
        
        $query = $this->listSongsQuery($connection);

        $result = $this->fetchFromDatabase($connection, $query);
        
        $this->connectionDAL->CloseConnection($connection);
        
        return $result;
    }
    // Made it possible to add stuff with a '. For example Don't.
    private function unescapeString($text){
        $text = str_replace("\'", "'", $text);
        $text = str_replace('\"', '"', $text);
        return $text;
    }
    
    public function fetchFromDatabase($connection, $query) {
        
        // Save result that you got back from database.
        $result = $connection->query($query);
        
        // USE THIS SOLUTION. http://conctus.eu/example/6
        $allrows = array();
        $i = 0;
        while ($row = $result->fetch_assoc()) {
            // Used with unescapeString.
            if(isset($row[self::$artistForAssocArray])) {
                $row[self::$artistForAssocArray] = $this->unescapeString($row[self::$artistForAssocArray]);
            }
            if(isset($row[self::$songForAssocArray])) {
                $row[self::$songForAssocArray] = $this->unescapeString($row[self::$songForAssocArray]);
            }
            if(isset($row[self::$chordsForAssocArray])) {
                $row[self::$chordsForAssocArray] = $this->unescapeString($row[self::$chordsForAssocArray]);
            }
            array_push($allrows,$row);
            $i++;
            
        }
        
        // If there is set an array called $allrows return it, otherwise return null.
        $allrows;
        return isset($allrows) ? $allrows : null;
    }
    
    
    public function getEverything($connection) {
        return 'SELECT * '
                .'FROM Artists LEFT JOIN Songs '
                .'ON Songs.ArtistID = Artists.ArtistID '
                .'WHERE Artists.ArtistName LIKE  "%' . $connection->real_escape_string($this->sField) . '%" '
                .'OR Songs.SongName LIKE  "%' . $connection->real_escape_string($this->sField) . '%"'; 
    }
    
    public function listSongsQuery() {
        return 'SELECT * '
                .'FROM Artists LEFT JOIN Songs '
                .'ON Songs.ArtistID = Artists.ArtistID '; 
    }
    
    public function getArtistNames($result) {
        
         if(empty($result[0]['ArtistName'])) {
            throw new NotFoundInDatabaseException();
         }
         else {
            
            $artists = array();
            foreach ($result as $artist) {
                $artist['ArtistName'];
                array_push($artists, $artist);
            }
            
            $this->artistNames = $artists;
            return $this->artistNames;
         }
    }
    
    // Don't use the parameter anymore.
    public function getSongNames($result) {
        
        for($i = 0; $i < count($result); $i++) {
            if(empty($result[$i]['SongName'])) {
                $this->deleteModel->deleteArtist($result[$i]['ArtistName']);
            }
        }
    
        $artistSongs = array();
        foreach ($result as $songs) {
            $songs['SongName'];
            array_push($artistSongs, $songs);
        }
        
        $this->artistSongs = $artistSongs;
        return $this->artistSongs;
    }
    
    public function askForThisSong($songID){
        return 'SELECT * 
                FROM Songs 
                WHERE Songs.SongID = "' . $songID . '"'; 
    }
    
    public function getSpecificSong($songID){
        $connection = $this->connectionDAL->OpenConnection();

        $query = $this->askForThisSong($songID);
        $result = $this->fetchFromDatabase($connection, $query);
        
        $this->connectionDAL->CloseConnection($connection);
        
        return $result[0]['SongName'];
    }
    
    public function getChords($songID) {
        $connection = $this->connectionDAL->OpenConnection();

        $query = $this->askForThisSong($songID);
        $result = $this->fetchFromDatabase($connection, $query);
        
        $this->connectionDAL->CloseConnection($connection);
        
        return $result[0]['Chords'];
        
    }
    
    
    public function isUserAtResult() {
        if($this->artistNames != null || $this->artistSongs != null) {
            return true;
        }
        else {
            return false;
        }
    }
    
}