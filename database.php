<?php
interface AbstractRowType {
  public function validate($i):bool;
  public function getHTMLInput(string $n, string $v = ""):string;
  public function formatData($d);
  public function displayData($d);
  public function getCreateSQL();
}

class TextRowType implements AbstractRowType {
  protected $name;
  protected $maxlen = 65535;
  public function __construct($n) {
    $this->name = $n;
  }
  public function getHTMLInput($n, $v = ""):string {
    $n = htmlspecialchars($n);
    $v = htmlspecialchars($v);
    return "<input type=\"text\" name=\"$n\" value=\"$v\">\n";
  }
  public function validate($i):bool {
    return strlen($i) <= $this->maxlen;
  }
  public function formatData($d) {
    return $d;
  }
  public function displayData($d) {
    return htmlspecialchars($d);
  }
  public function getCreateSQL() {
    return "ALTER TABLE `student_grades` ADD `$this->name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'TextRowType';";
  }
}

class EnumRowType implements AbstractRowType {
  public function __construct($n) {
    $this->name = $n;
  }
  public function validate($i):bool {
    return in_array($i, $this->ENUM);
  }
  public function getHTMLInput($n, $v = ""):string {
    $n = htmlspecialchars($n);
    $v = htmlspecialchars($v);
    $html = "<select name=\"$n\">\n<option></option>\n";
    foreach ($this->ENUM as $g) {
      $html .= "<option";
      if ($v == $g)
        $html .= " selected=\"selected\"";
      $html .= ">$g</option>\n";
    }
    $html .= "</select>\n";
    return $html;
  }
  public function getCreateSQL() {
    // TODO add the enum options through a loop
    return "ALTER TABLE `student_grades` ADD `$this->name` enum('A', 'B', 'C', 'D', 'F', 'U') COMMENT 'GradeRowType' NOT NULL DEFAULT 'U'";
  }
  public function formatData($d) {
    return $d;
  }
  public function displayData($d) { return htmlspecialchars($d); }
  protected array $ENUM;
  private string $name;
}

class GradeRowType extends EnumRowType {
  protected array $ENUM = ["A", "B", "C", "D", "F", "U"];
}

class PhoneRowType extends TextRowType {
  protected $maxlen = 12;
  public function validate($i):bool {
    return strlen($i) <= $this->maxlen && preg_match("/(\d{3}).*?(\d{3}).*?(\d{4})/m", $i);
  }
  public function formatData($d):string {
    if (preg_match("/(\d{3}).*?(\d{3}).*?(\d{4})/m", $d, $m))
      return $m[1] . '-' . $m[2] . '-' . $m[3];
    else
      throw new Error("Could not format string");
  }
  public function getHTMLInput($n, $v = ""):string {
    $n = htmlspecialchars($n);
    $v = htmlspecialchars($v);
    return "<input type=\"text\" name=\"$n\" value=\"$v\" maxlength=\"15\" pattern=\"\d{3}.*?\d{3}.*?\d{4}\" placeholder=\"123-456-7890\">\n";
  }
  public function getCreateSQL() {
    return "ALTER TABLE `student_grades` ADD `$this->name` varchar(12) COMMENT 'PhoneRowType' NOT NULL DEFAULT '000-000-0000'";
  }
}

class FileRowType implements AbstractRowType {
  public function __construct($n) {
    $this->name = $n;
  }
  protected $maxlen = 16777215;
  public function validate($i):bool {
    return strlen($i) <= $this->maxlen;
  }
  public function getHTMLInput($n, $v = ""):string {
    $n = htmlspecialchars($n);
    $v = htmlspecialchars($v);
    return "<input type=\"file\" name=\"$n\">\n";
  }
  public function formatData($d) { return $d; }
  public function getCreateSQL() {
    $class = get_class($this);
    return "ALTER TABLE student_grades ADD $this->name MEDIUMBLOB COMMENT '$class'";
  }
  public function displayData($d) {
    return '<a href="data:application/octet-stream;filename=file.txt;base64, ' . @base64_encode($d). '">Download</a>';
  }
  protected string $name;
}

class PhotoRowType extends FileRowType {
  public function displayData($d) {
    return '<a href="data:application/octet-stream;base64, ' . @base64_encode($d) . '"><img src="data:image;base64, ' . @base64_encode($d) . '"></a>';
  }
}

class GradesDatabase {
  const Ascending = "ASC";
  const Descending = "DESC";

  function __construct() {
    $this->dbh = new PDO('mysql:dbname=school;host=localhost', 'lsevcik', 'asdfghjkl');
    $this->dbh->prepare("CREATE TABLE IF NOT EXISTS `student_grades` ( `name` varchar(255) NOT NULL COMMENT 'TextRowType', PRIMARY KEY (`name`) ) DEFAULT CHARSET=utf8mb4")->execute();
  }

  public function selectAll($sortBy = NULL, string $order = GradesDatabase::Descending):PDOStatement {
    $stmt = "SELECT * FROM `student_grades`";
    if (isset($sortBy))
      $stmt .= " ORDER BY " . $sortBy . " " . $order;
    $sth = $this->dbh->prepare($stmt);
    if (!$sth->execute())
      throw new Exception(print_r($sth->errorInfo(), true), $sth->errorInfo()[0]);
    return $sth;
  }

  public function getRowTypeByName(string $s):AbstractRowType {
    $stmt = "SELECT CHARACTER_MAXIMUM_LENGTH,COLUMN_COMMENT FROM `information_schema`.`columns` WHERE `TABLE_NAME` = 'student_grades' AND `COLUMN_NAME` = :column_name;";
    $sth = $this->dbh->prepare($stmt);
    $sth->bindParam(":column_name", $s);
    if (!$sth->execute())
      throw new RuntimeException("Unknown Column");
    $vals = $sth->fetch();
    $maxlen = $vals[0];
    $type = $vals[1];
    try {
      return new $type($s);
    } catch (Error $e) {
      return new TextRowType($s);
    }
  }

  public function createRow(array $o) {
    $keys = array_keys($o);
    foreach ($keys as $col) {
      $t = $this->getRowTypeByName($col);
      if (!$t->validate($o[$col]))
        throw new Exception("Failed to validate data for column $col");
      $o[$col] = $t->formatData($o[$col]);
    }
    $stmt = "INSERT INTO student_grades (";
    $stmt .= implode(',', $keys);
    $stmt .= ") VALUES (";
    foreach ($keys as $key)
      $stmt .= ":$key, ";
    $stmt = substr($stmt, 0, -2);
    $stmt .= ");";
    $sth = $this->dbh->prepare($stmt);
    foreach ($keys as $key)
      $sth->bindParam(":$key", $o[$key]);
    $res = $sth->execute();
    if (!$res)
      throw new Exception(print_r($sth->errorInfo(), true), $sth->errorInfo()[0]);
    return $res;
  }

  public function readRow(string $name) {
    $stmt = "SELECT * FROM students_grades WHERE `name` = :name LIMIT 1;";
    $sth = $this->dbh->prepare($stmt);
    $sth->bindParam(":name", $name);
    $res = $sth->execute();
    if (!$res)
      throw new Exception(print_r($sth->errorInfo(),true), $sth->errorInfo()[0]);
    return $sth->fetch();
  }

  public function updateRow(string $name, array $o) {
    $keys = array_keys($o);
    foreach ($keys as $col) {
      $t = $this->getRowTypeByName($col);
      if (!$t->validate($o[$col]))
        throw new Exception("Failed to validate data for column $col");
      $o[$col] = $t->formatData($o[$col]);
    }
    $stmt = "UPDATE `student_grades` SET ";
    foreach ($keys as $key)
      $stmt .= "$key = :$key, ";
    $stmt = substr($stmt, 0, -2);
    $stmt .= " WHERE `name` = :unique;";
    $sth = $this->dbh->prepare($stmt);
    foreach ($keys as $key)
      $sth->bindParam(":$key", $o[$key]);
    $sth->bindParam(":unique", $name);
    $res = $sth->execute();
    if (!$res)
      throw new Exception(print_r($sth->errorInfo(), true), $sth->errorInfo()[0]);
    return $res;
  }

  public function deleteRow($o) {
    $name = $o["name"];
    $stmt = "DELETE FROM `student_grades` WHERE `name` = :name;";
    $sth = $this->dbh->prepare($stmt);
    $sth->bindParam(":name", $name);
    $res = $sth->execute();
    if (!$res)
      throw new Exception(print_r($sth->errorInfo(), true), $sth->errorInfo()[0]);
    return $res;
  }
/*
  public static function extractPhone(string $mixed): string {
    preg_match("/(\d{3}).*?(\d{3}).*?(\d{4})/m", $mixed, $m);
    return ($m[1] . "-" . $m[2] . "-" . $m[3]);
  }
 */

  public function getCols() {
    $stmt = "SELECT COLUMN_NAME as name,COLUMN_COMMENT as type FROM `information_schema`.`COLUMNS` WHERE `TABLE_NAME` = 'student_grades';";
    $sth = $this->dbh->prepare($stmt);
    if (!$sth->execute())
      throw new RuntimeException("Failed to fetch");
    return $sth;
  }

  public function addCol($n, $t) {
    $col = new $t($n);
    $sth = $this->dbh->prepare($col->getCreateSQL());
    if (!$sth->execute())
      throw new Exception(print_r($sth->errorInfo(), true), $sth->errorInfo()[0]);
  }

  public function deleteCol($d) {
    $col = $d['name'];
    if ($col == 'name')
      return;
    $stmt = "ALTER TABLE `student_grades` DROP `$col`;";
    $sth = $this->dbh->prepare($stmt);
    if (!$sth->execute())
      throw new Exception(print_r($sth->errorInfo(), true), $sth->errorInfo()[0]);
  }

  public function exportCSV() {
    $sth = $this->selectAll();
    $csv = fopen("php://temp", 'w');

    fputcsv($csv, $this->getCols()->fetchAll(PDO::FETCH_COLUMN, 0));

    while ($data = $sth->fetch(PDO::FETCH_ASSOC))
      fputcsv($csv, $data);
    rewind($csv);
    $s = stream_get_contents($csv);
    fclose($csv);
    return $s;
  }

  public function importCSV() {

  }

  private PDO $dbh;
}
