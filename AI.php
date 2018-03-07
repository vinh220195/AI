<?php
/* author : Nguyễn Thế Vinh .51303220 */
$receiveMethod = $_POST['value'];
$p = new Puzzle();
$p->readInput();
if($receiveMethod == "greedy"){
	$p->GreedyBestFirstSearch();
}
else if($receiveMethod == "astar"){
	$p->AStar();
}
//echo 'Current PHP version: ' . phpversion();
class Node{
	// declare
	var $from;
	var $arrayState;
	var $h;
	var $arrayWhiteTilePosition;
	var $previousStep;
	var $cost;
	var $g;
	
	//constructor
	public function __construct(){
		$this->h = 0;
		$this->previousStep = 0;
		$this->arrayWhiteTilePosition = array();
		$this->cost = 0;
	}
	public function constructWithNode($node){
		
		for($i = 0 ; $i < sizeof($node->arrayState); $i++){
			for($j = 0 ; $j < sizeof($node->arrayState); $j++){
				$this->arrayState[$i][$j] = $node->arrayState[$i][$j];
			}
		}
		$this->h = $node->h;
		for($i = 0 ; $i < sizeof($node->arrayWhiteTilePosition); $i++){
			$this->arrayWhiteTilePosition[$i] = $node->arrayWhiteTilePosition[$i];
		}
		$this->previousStep = $node->previousStep;
		$this->cost = $node->cost;
		$this->g = $node->g;
	}
}
class Tile{
	// declare
	var $value;
	var $positionX;
	var $positionY;
	var $enableSite;
	public function __construct(){
		$this->value = -2;
		$this->positionX = -2;
		$this->positionY = -2;
	}
	public function constructWithAll($value_in, $positionX_in, $positionY_in, $enableSite_in){
		$this->value = $value_in;
		$this->positionX = $positionX_in;
		$this->positionY = $positionY_in;
		$this->enableSite = $enableSite_in;
	}
	public function getValue(){
		return $this->value;
	}
}
class Puzzle{
	var $currentPos;
	var $tracking;
	var $initialState;
	var $goalState;
	var $MAX_ROW;
	var $MAX_COL;
	var $arrayPreviousState;
	var $posInArrayPreviousState;
	public function readInput(){
		$currentPos = 0;
		$this->tracking = array();
		$this->MAX_ROW = 3;
		$this->MAX_COL = 3;
		$this->posInArrayPreviousState = 0;
		$this->arrayPreviousState = array();
		$this->initialState = new Node();
		$initialStateTemp = $_POST['initialState'];
		$this->goalState = new Node();
		for($i = 0 ; $i < sizeof($initialStateTemp);$i++){
			$r = $initialStateTemp[$i][0];
			$c = $initialStateTemp[$i][1];
			$this->initialState->arrayState[$r][$c] = $i + 1;
			if($this->initialState->arrayState[$r][$c] == sizeof($initialStateTemp)){
				$this->initialState->arrayState[$r][$c] = 0;
			}
		}
		$this->initialState->arrayWhiteTilePosition = $this->getWhiteTilePosition($this->initialState->arrayState, 0);
		$tempPos = 0;
		for($r = 0; $r < $this->MAX_ROW; $r++){
			for($c = 0; $c < $this->MAX_COL; $c++){
				$this->goalState->arrayState[$r][$c] = $tempPos + 1;
				$tempPos++;
			}
		}
		$this->goalState->arrayState[$this->MAX_ROW - 1][$this->MAX_COL - 1] = 0;
		$this->initialState->h = $this->heuristic($this->initialState,$this->goalState);
	}
	// right checked 
	public function getWhiteTilePosition($state){
		$output = array();;
		for($i = 0; $i < $this->MAX_ROW; $i++){
			for( $j = 0; $j < $this->MAX_COL; $j++) {
				if($state[$i][$j] == 0){
					$output[0] = $i;
					$output[1] = $j;
					return $output;
				}
			}
		}
	}
	//right checked
	public function manhatanDistance($current, $target){
		return abs($target[0] - $current[0]) + abs($target[1] - $current[1]);
	}
	
	//right checked
	public function heuristic($currentState, $goalState){
		$output = 0;
		$x1 = -1;
		$y1 = -1;
		$x2 = -1;
		$y2 = -1;
		for ($i = 1; $i < $this->MAX_COL * $this->MAX_ROW; $i++) {
			for ($x = 0; $x < $this->MAX_ROW; $x++) {
				for ($y = 0; $y < $this->MAX_COL; $y++) {
					if ($currentState->arrayState[$x][$y] == $i) {
						$x1 = $x;
						$y1 = $y;
					}
					if ($this->goalState->arrayState[$x][$y] == $i) {
						$x2 = $x;
						$y2 = $y;
					}
				}
			}
			$current = array($x1, $y1);
			$target = array($x2, $y2);
			$output = $output + $this->manhatanDistance($current, $target);
		}
		$currentState->h = $output;
		return $output;
	}
	//right checked
	public function swapTile($a, $b) {
		$temp = new Tile();
		$temp->value = $a->value;
		$temp->positionX = $a->positionX;
		$temp->positionY = $a->positionY;
		$temp->enableSite = $a->enableSite;

		$a->value = $b->value;
		$a->positionX = $b->positionX;
		$a->positionY = $b->positionY;
		$a->enableSite = $b->enableSite;

		$b->value = $temp->value;
		$b->positionX = $temp->positionX;
		$b->positionY = $temp->positionY;
		$b->enableSite = $temp->enableSite;
	}
	//right checked
	public function getTileNearWhiteTilePosition($nodeState){
		$output = new Node();
		$output->constructWithNode($nodeState);
		$output->arrayWhiteTilePosition = $this->getWhiteTilePosition($output->arrayState, 0);
		$nearTilePosition = array();
		$wtp = $output->arrayWhiteTilePosition; 
		
		if($wtp[0] - 1 >= 0){
			$tileTemp01 = new Tile();
			$tileTemp01->constructWithAll($output->arrayState[$wtp[0] -	 1][$wtp[1]],$wtp[0] - 1,$wtp[1], 'u');
			$nearTilePosition[] = $tileTemp01;
		}
		if($wtp[0] + 1 < $this->MAX_ROW){
			$tileTemp02 = new Tile();
			$tileTemp02->constructWithAll($output->arrayState[$wtp[0] + 1][$wtp[1]],$wtp[0] + 1,$wtp[1], 'd');
			$nearTilePosition[] = $tileTemp02;
		}
		if($wtp[1] - 1 >= 0){
			$tileTemp03 = new Tile();
			$tileTemp03->constructWithAll($output->arrayState[$wtp[0]][$wtp[1] - 1],$wtp[0],$wtp[1] - 1, 'l');
			$nearTilePosition[] = $tileTemp03;
		}
		if($wtp[1] + 1 < $this->MAX_COL){
			$tileTemp04 = new Tile();
			$tileTemp04->constructWithAll($output->arrayState[$wtp[0]][$wtp[1] + 1],$wtp[0],$wtp[1] + 1, 'r');
			$nearTilePosition[] = $tileTemp04;
		}
		return $nearTilePosition;
	}

	public function getNextNodesPosition($nodeState){
		$arrayNearNodeState = array();
		
		$arrayNearTilePosition = array();
		$arrayNearTilePosition = $this->getTileNearWhiteTilePosition($nodeState);
		for($i = 0 ; $i < sizeof($arrayNearTilePosition); $i++){
				$output = new Node();
				$output->constructWithNode($nodeState);
				$output = $this->moveTile($output,$output->arrayWhiteTilePosition,$arrayNearTilePosition[$i]->enableSite,false);
				$arrayNearNodeState[] = $output;
		}
		
		
		for($i = 0 ; $i < sizeof($arrayNearNodeState); $i++){
			for($j = $i+1; $j < sizeof($arrayNearNodeState);$j++){
				if($arrayNearNodeState[$i]->h > $arrayNearNodeState[$j]->h){
					$nodeTemp = $arrayNearNodeState[$i];
					$arrayNearNodeState[$i] = $arrayNearNodeState[$j];
					$arrayNearNodeState[$j] = $nodeTemp;
				}
			}
		}
		return $arrayNearNodeState;
	}
	public function sortArrayNodeByCost($arrayNode){
		for($i = 0 ; $i < sizeof($arrayNode); $i++){
			for($j = $i+1; $j < sizeof($arrayNode);$j++){
				if($arrayNode[$i]->cost >= $arrayNode[$j]->cost){
					$nodeTemp = $arrayNode[$i];
					$arrayNode[$i] = $arrayNode[$j];
					$arrayNode[$j] = $nodeTemp;
				}
			}
		}
		
	}
	
	public function numOfWrongPosition($nodeState){
		$result = 0;
		for($i = 0 ; $i < $this->MAX_ROW;$i++){
			for($j = 0 ; $j < $this->MAX_ROW;$j++){
				if($nodeState->arrayState[$i][$j] != $this->goalState->arrayState[$i][$j]){
					if($nodeState->arrayState[$i][$j] != 0){
						$result++;
					}
				}
			}
		}
		return $result;
	}
	public function isSameState($nodeState1, $nodeState2){
		
		for($i = 0; $i < $this->MAX_ROW; $i++){
			for($j = 0; $j < $this->MAX_COL; $j++){
				if($nodeState1->arrayState[$i][$j] != $nodeState2->arrayState[$i][$j]){
					return false;
				}
			}
		}
		return true;
	}

	public function isVisitedState($nodeState){
		$this->posInArrayPreviousState = 0;
		for($i = 0; $i < sizeof($this->arrayPreviousState); $i++){
			if($this->isSameState($nodeState, $this->arrayPreviousState[$i])){
				$this->posInArrayPreviousState = $i;
				return true;
			}
		}
		return false;
	}
	//right checked
	public function moveTile($nodeState, $tile, $operator,$real) {
		$output = new Node();	
		$output->constructWithNode($nodeState);
		if ($operator == 'u') // Case-1: Move tile UP
		{
			// New postion of tile if move UP
			$row = $tile[0] - 1;
			$col = $tile[1];
			if ($row >= 0) // Tile stands inside the map
			{
				$output->previousStep = $output->arrayState[$row][$col];
				$tmp = $nodeState->arrayState[$row][$col];
				$output->arrayState[$row][$col] = 0;
				$output->arrayState[$tile[0]][$tile[1]] = $tmp;
				$output->arrayWhiteTilePosition = $this->getWhiteTilePosition($output->arrayState, 0);
				$output->h = $this->heuristic($output, $this->goalState);
				return $output;
			}
		}

		if ($operator == 'd') // Case-2: Move tile DOWN
		{
			// New postion of tile if move DOWN
			$row = $tile[0] + 1;
			$col = $tile[1];

			if ($row < $this->MAX_ROW) // Tile stands inside the map
			{
				$output->previousStep = $output->arrayState[$row][$col];
				$tmp = $nodeState->arrayState[$row][$col];
				$output->arrayState[$row][$col] = 0;
				$output->arrayState[$tile[0]][$tile[1]] = $tmp;
				$output->arrayWhiteTilePosition = $this->getWhiteTilePosition($output->arrayState, 0);
				$output->h = $this->heuristic($output, $this->goalState);
				return $output;
			}
		}

		if ($operator == 'l') // Case-3: Move tile LEFT
		{
			/* Enter your code here */

			// New postion of tile if move LEFT
			$row = $tile[0];
			$col = $tile[1] - 1;
			if ($col >= 0) // Tile stands inside the map
			{
				$output->previousStep = $output->arrayState[$row][$col];
				$tmp = $nodeState->arrayState[$row][$col];
				$output->arrayState[$row][$col] = 0;
				$output->arrayState[$tile[0]][$tile[1]] = $tmp;
				$output->arrayWhiteTilePosition = $this->getWhiteTilePosition($output->arrayState, 0);
				$output->h = $this->heuristic($output, $this->goalState);
				return $output;
			}
		}
		
		if ($operator == 'r') // Case-4: Move tile RIGHT
		{
			/* Enter your code here */
			$row = $tile[0];
			$col = $tile[1] + 1;
			
			
			if ($col < $this->MAX_COL) // Tile stands inside the map
			{
				$output->previousStep = $output->arrayState[$row][$col];
				$tmp = $nodeState->arrayState[$row][$col];
				$output->arrayState[$row][$col] = 0;
				$output->arrayState[$tile[0]][$tile[1]] = $tmp;
				$output->arrayWhiteTilePosition = $this->getWhiteTilePosition($output->arrayState, 0);
				$output->h = $this->heuristic($output, $this->goalState);
				return $output;
			}
		}

		return null;
	}
	public function isExist($state,$array){
		foreach($array as $elementkey => $state2){
			if($this->isSameState($state,$state2)){
				if($state->cost <= $state2->cost)
				unset($array[$elementkey]);
				return true;
			}
		}	
	
		return false;
	}
	public function GreedyBestFirstSearch(){
		$i = 0;
		$visited = false;
		while ($this->initialState->h != 0) {
			$arrayNearNodePosition = array();
			if(!$visited){
				$arrayNextStates = $this->getNextNodesPosition($this->initialState);
			}
			else{
				$arrayNextStates = $this->getNextNodesPosition($this->arrayPreviousState[count($this->arrayPreviousState)-1]);
				for($l = 0; $l < sizeof($arrayNextStates);$l++){
					if($this->isSameState($arrayNextStates[$l],$this->arrayPreviousState[$this->posInArrayPreviousState + 1])){
						array_splice($arrayNextStates,$l,1);
					}
				}
				$visited = false;
			}
			for($k = 0; $k < sizeof($arrayNextStates);$k++){
				$this->initialState = $arrayNextStates[$k];
				if(!$this->isVisitedState($this->initialState)){
					array_splice($arrayNextStates,$k,1);
					$this->initialState->arrayNextStates = $arrayNextStates;
					$this->arrayPreviousState[] = $this->initialState;
					break;
				}
				else
				 {
					if($k == sizeof($arrayNextStates) - 1)
					{
						for($j = 0; $j < sizeof($arrayNextStates); $j++){
							if($this->isVisitedState($arrayNextStates[$j])){
								if(!empty($this->arrayPreviousState[$this->posInArrayPreviousState]->arrayNextStates)){
									$this->arrayPreviousState[] = $arrayNextStates[$j];
									$visited = true;
									break;
								}
							}
						}
					}
				}
			}		
			$i++;
		}
		
		for($i = 0; $i < sizeof($this->arrayPreviousState);$i++){
			var_dump($this->arrayPreviousState[$i]->previousStep);
		}
	}
	public function distanceHeuristic3($current,$target){
		return (abs($target[0] - $current[0])*abs($target[0] - $current[0])) + (abs($target[1] - $current[1])*abs($target[1] - $current[1]));
	}
	public function heuristic3($currentState, $goalState){
		$output = 0;
		$x1 = -1;
		$y1 = -1;
		$x2 = -1;
		$y2 = -1;
		for ($i = 1; $i < $this->MAX_COL * $this->MAX_ROW; $i++) {
			for ($x = 0; $x < $this->MAX_ROW; $x++) {
				for ($y = 0; $y < $this->MAX_COL; $y++) {
					if ($currentState->arrayState[$x][$y] == $i) {
						$x1 = $x;
						$y1 = $y;
					}
					if ($this->goalState->arrayState[$x][$y] == $i) {
						$x2 = $x;
						$y2 = $y;
					}
				}
			}
			$current = array($x1, $y1);
			$target = array($x2, $y2);
			$output = $output + $this->distanceHeuristic3($current, $target);
		}
		return $output;
	}
	public function computeA($nodeState){
		$result = 0;
		for($i = 0; $i < $this->MAX_ROW;$i++){
			for($j = 0 ; $j < $this->MAX_COL;$j++){
				if($j <$this->MAX_COL - 1){
					if($nodeState->arrayState[$i][$j] == $this->goalState->arrayState[$i][$j + 1]){
						if($this->goalState->arrayState[$i][$j] == $nodeState->arrayState[$i][$j + 1]){
							$result += 2;
						}
					}
				}
				if($j < $this->MAX_ROW - 1){
					if($nodeState->arrayState[$j][$i] == $this->goalState->arrayState[$j + 1][$i]){
						if($this->goalState->arrayState[$j][$i] == $nodeState->arrayState[$j + 1][$i]){
							$result += 2;
						}
					}
				}
			}
		}
		return $result;
	}
	 public function AStar() {
		$i = 0;
		$valueG = 0;
		$open_list = array();
		$closed_list = array();
		$x = new Node();
		$childrenOfX = array();
		$this->initialState->g = 0;
		$this->initialState->from = -1;
		$this->initialState->currentPos = 0;
		$open_list[] = $this->initialState;
		$this->tracking[$i] = $this->initialState;
		
		while(!empty($open_list)){
			$x = $open_list[0];
			array_splice($open_list,0,1);
			
			$closed_list[] = $x;
			if($this->isSameState($x,$this->goalState)){
				break;
			}
			$childrenOfX = $this->getNextNodesPosition($x);
			$valueG++;
			foreach($childrenOfX as $state){
				$state->g = $x->g + 1;
				$state->cost = $this->heuristic($state,$this->goalState) + $this->numOfWrongPosition($state) + $this->computeA($state) + $state->g;
				
				if(!$this->isExist($state,$open_list) && !$this->isExist($state,$closed_list)){
					if($state->g < 20){
						$i++;
						$state->currentPos = $i;
						$state->from = $x->currentPos;
						$this->tracking[$i] = $state;
						$open_list[] = $state;	
					}
				}
			}
			$this->sortArrayNodeByCost($open_list);
		}
		var_dump(sizeof($closed_list));
		/* $result = array();
		$i = 1;
		do{
			$j = $this->tracking[sizeof($this->tracking) - $i];
			$i++;
		}while($j->h != 0);
		
		while($j->g != 0){
			$result[] = $j;
			$j = $this->tracking[$j->from];
		}
		for($i = sizeof($result) - 1; $i >= 0; $i--){
			var_dump($result[$i]->previousStep);
		} */
	}
}
?>