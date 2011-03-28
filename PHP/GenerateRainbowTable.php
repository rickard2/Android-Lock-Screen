<?php
/**
 * @author Rickard Andersson <h05rikan@du.se>
 * @package AndroidLockScreen
 * 
 * This script will generate every combination of the android lockscreen gesture, calculate
 * it's corresponding hash and save it to a database.
 *
 * This code is released without any license whatsoever, you're free to do what you want with it. 
 */

$dsn = "mysql:dbname=AndroidLockScreen;host=127.0.0.1"; // Change this if you want to
$user = ""; // Change this
$pass = ""; // Change this

try {
    $dbh = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    die();
}

$sth = $dbh->prepare('INSERT INTO RainbowTable (combination, hash) VALUES (?, ?)');

$total = 0;

// Generating all the combinations of gestures with length three, four, ..., eight and nine 
for ($x = 3; $x <= 9; $x++) {
  echo "==> Generating table for $x ... \n";

  $p = new Permutations(9, $x);
  
  $combinations = 0;
  
  $values = $p->getCurrent();
  
  do {
    $str = "";
    
    foreach ($values as $value) {
      $str .= chr($value);
    }
    
    $hash = sha1($str);
    
    if ($sth->execute( array( implode(",", $values) , $hash) )) {  
      $combinations++;
    } else {
      echo "Error: ";
      var_dump($sth->errorInfo());
    }
    
  } while (is_array($values = $p->getNext()));
  
  $total += $combinations;
    
  echo "==> Done! Inserted $combinations records!\n";
}

echo "==> All done with a total of $total records!\n";

/**
 * Class for generating permutations of numbers 
 * @author Rickard Andersson
 */ 
class Permutations {

  private $n; // How many numbers in each permutation to generate
  private $maxValue; // The maximum value in each number
  private $currentValues; // Array of current values

  /** 
   * Public constructor to initiate the class with number boundaries 
   * @param int $max  The highest number in this series
   * @param int $n    How many numbers to choose from the series of numbers
   */
  public function __construct($max, $n) {
    $this->n = $n;
    $this->max = $max;
    $this->currentValues = range(0, $n -1);
  }
  
  /**
   * Get the current set of values
   * @return array
   */
  public function getCurrent() {
    return $this->currentValues;
  }
  
  /**
   * Increase the number and return the resulting set of values
   * @return array|boolean   Returns false when all permutations have been generated.
   */
  public function getNext() {

    if ( $this->increase($this->n - 1) ) {
      return $this->currentValues;
    } else {
      return false;
    }
  }
  
  /**
   * Increases the value of the number in the index $index in the set of numbers and checks that
   * the set of numbers still is unique and within boundaries.
   * @param int $index  Which index to increas
   * @return int  Returns the new value on success and -1 on failure
   */
  private function findNext($index) {
  
    $newValue = $this->currentValues[ $index ];
    
    do {
      $newValue++;
    } while (in_array($newValue, $this->currentValues));
    
    if ($newValue >= $this->max) {
      return -1;
    } else {
      return $newValue;
    }    
  }  
  
  /**
   * Increases the value of the complete set of values until every value has been generated. This is a recursive function and a 
   * call to $index - 1 will be done if the value of the current index gets above the given boundary.
   * @param int $index 
   * @return bool 
   */
  private function increase($index) {
    
    $this->currentValues[ $index ] = $this->findNext($index);
    
    // findNext() returns -1 on failure and if $index == 0 all the numbers has been generated
    if ($this->currentValues[ $index ] == -1) {
      if ($index == 0) {    
        return false;
      } 
      
      // There might still be numbers to generate, call increase() and try to find a new set of numbers
      // by increasing the number of the index right below this one. 
      else {
        $success = $this->increase( $index - 1 );
        
        // Found a new set of values to work with. Since findNext has returned -1 in the first call, calling findNext again
        // from -1 and upwards will get the lowest available number for this index. 
        if ($success === true) {
          $this->currentValues[ $index ] = $this->findNext($index);
          return true;
        } else {
          return false;
        }
      }
    } else {
      return true;
    }
  }  
}