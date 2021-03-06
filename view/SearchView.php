<?php

class SearchView {
    
    private static $search = 'SearchView::Search';
    private static $postSearch = 'SearchView::PostSearch';
    private static $list = 'SearchView::List';
	private static $logout = 'LoginView::Logout';
	private static $delete = 'SearchView::Delete';
    
    private static $notFoundMessage = 'Not found in database!'; 
    
    private $errorMessage;
    
    public function __construct (SearchModel $searchModel, LoginModel $loginModel) {
        $this->searchModel = $searchModel;
        $this->loginModel = $loginModel;
        $this->wantsToList = false;
    }
    
    public function response() {
        $message = '';
        $response = '';
        
        if($this->errorMessage != null) {
            $message = $this->errorMessage;
        }
        
       
       	if($this->loginModel->isUserLoggedIn()){
			$response .= $this->generateLogoutButtonHTML($message);	
		}
		
        $response .= $this->generateHTML($message);
        
        if($this->isUserAtResult()) {
            if($this->wantsToList) {
                $response .= $this->generateListHTML();
            }
            else {
                $response .= $this->generateResultHTML();
            }
        }
        
        return $response;
    }
    
    public function generateListHTML() {
        return '
            <fieldset>
                <legend>Song list</legend>
                ' . $this->generateSongList() . '
            </fieldset>
        ';
    }
    
    public function generateResultHTML() {
        return '
            <fieldset>
                <legend>Search Result</legend>
                <p id=searchedFor>Searched for: ' . $this->searchedFor . '</p>
                
                <h4>Songs: </h4>' . $this->generateSongList() . '
            </fieldset>
        ';
    }
    
    public function generateHTML($message) {
        return '
        <h1>Welcome to Guitardo</h1>
        <p>Here you can search for different songs or artists to learn the guitar chords,<br> 
            making learning guitar easy!</p>
        <p>Search for <b>artist</b> or <b>song</b>.</p>
            <form method="post" id="searchForm">
                <input type="text" id="' . self::$search . '" name="' . self::$search . '" maxlength = "100" placeholder="Search..." />
            	<input type="submit" name="' . self::$postSearch . '" value="Search!" />
            	<p id=error>' . $message . '</p>
        	</form>
    	<p>Alternative click this button to see all the songs.</p>
    	<form method="post" id="listButton">
    	    <input type="submit" name="' . self::$list . '" value="List all songs" />
    	</form>
    	<p>Cannot find a song?<br>
    	    If you know the chords you can add them by clicking <a href="?add">here</a></p>
        ';
    }
    
	private function generateLogoutButtonHTML($message) {
	return '
		<form  method="post" >
			<input type="submit" name="' . self::$logout . '" value="Logout"/>
		</form>
	';
	}
	
	public function generateDeleteButtons($songID) {
	    if(isset($_SESSION['UserLoggedIn']) && $_SESSION['UserLoggedIn']) {
	        return '
                <form  method="post" id="deleteButtons">
			         <button name="'.self::$delete.'" type="submit" value="'. $songID .'">Delete</button>
		        </form>
		';
	    }
	}
	
	public function getSongToDelete()
	{
        return $_POST[self::$delete];
	}
    
    public function setErrorMessage($e) {
        $this->errorMessage = $e;
    }
    
    public function notFoundErrorMessage($e) {
        $e = self::$notFoundMessage;
        $this->errorMessage = $e;
    }
    
    
    public function isPosted() {
        if(isset($_POST[self::$postSearch])) {
                
            if(empty($_POST[self::$search])){
                $this->setErrorMessage("You have to write something in the search field.");
                return false;
            }
            else if($_POST[self::$search] != strip_tags($_POST[self::$search])) {
                $this->setErrorMessage("The text you've entered contains forbidden characters.");
                return false;
            }
            else {
                return true;
            }    
        }
    }
    
    public function isListPosted() {
        if(isset($_POST[self::$list])) {
            $this->wantsToList = true;
            return true;
        }
    }
    
    
    public function isUserAtResult() {
        if($this->searchModel->isUserAtResult()) {
            return true;
        }
        else {
            return false;
        }
    }
    
    public function logout(){
		if(isset($_POST[self::$logout])){
			return true;
		}
	}
	
	public function deletePost() {
	    if(isset($_POST[self::$delete])) {
	        return true;
	    }
	}
    
    
    public function getSearchField() {
        return $_POST[self::$search];
    }
    
    
    public function setSearchedArtistAndSongNames($artists, $songArray) {
            $this->searchedFor = $this->getSearchField();
            $this->artistNames = $artists;
            $this->songList = $songArray;
    }
    
    public function setSongNames($songArray) {
        $this->songList = $songArray;
    }
    
    
    public function generateSongList() {
        
        $songList = '<table id=songList>';
        foreach($this->songList as $song) {
            $songList .= '<tr>
                            <td>
                                <a href="?' . $song['SongID'] . '">' . $song['SongName'] . '</a> - ' . $song['ArtistName'] . $this->generateDeleteButtons($song['SongID']) . '
                            </td>
                        </tr>';
        }
        
        $songList .= '</table>';
        return $songList;
    }
    
    
    public function generateArtistList() {
        
        $artistList = '<table>';
        foreach($this->artistNames as $artist) {
            $artistList .= '<tr>
                                <td>
                                    ' . $artist['ArtistName'] != $artist['ArtistName'] . '
                                </td>
                            </tr>';
            
        }
        
        $artistList .= '</table>';
        return $artistList;
    }
}