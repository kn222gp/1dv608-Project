<?php

class SearchController {
    
    public function __construct(RenderView $renderView, SearchView $searchView, SearchModel $searchModel, LoginModel $loginModel, DeleteModel $deleteModel, ResultView $resultView) {
        $this->renderView = $renderView;
        $this->searchView = $searchView;
        $this->searchModel = $searchModel;
        $this->loginModel = $loginModel;
        $this->deleteModel = $deleteModel;
        $this->resultView = $resultView;
    }
    
    public function Start() {
        try {
            if($this->searchView->isPosted()) {
                $this->userWantsToSearch();
            }
            else if ($this->searchView->isListPosted()) {
                $this->userWantsToList();
            }
            
            else if($this->searchView->logout()) {
                $this->loginModel->logout();
            }
            
            else if($this->searchView->deletePost()) {
               $this->deleteModel->deleteSong($this->searchView->getSongToDelete());
            }
        }
        catch (Exception $e) {
            $this->searchView->notFoundErrorMessage($e);
        }
        $this->renderSearchView();
    }
    
    public function userWantsToSearch() {
        $searchField = $this->searchView->getSearchField();
        
        $result = $this->searchModel->checkDatabase($searchField); // Returns an array from database.
        
        $artistNames = $this->searchModel->getArtistNames($result); // Forward the array to another function in model that use the array to get the artist name.
        $artistSongs = $this->searchModel->getSongNames($result); // Forward the array to another function in model that use the array to get an array with song names.
    
        $this->searchView->setSearchedArtistAndSongNames($artistNames, $artistSongs);
    }
    
    public function userWantsToList(){
        $result = $this->searchModel->listSongs();
        $songs = $this->searchModel->getSongNames($result);
        
        $this->searchView->setSongNames($songs);
    }
    
    public function renderSearchView() {
        $this->renderView->render($this->loginModel->isUserLoggedIn(), $this->searchView, false);
    }
    
    public function renderChordsView() {
        $this->renderView->render(true, $this->resultView, true);
    }
    
    public function Chords($songID) {

        $songName = $this->searchModel->getSpecificSong($songID);
        $chords = $this->searchModel->getChords($songID);
        
        $this->resultView->setSongAndChords($songName, $chords);
        $this->renderChordsView();
    }
    
}