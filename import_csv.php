<?php

class CsvLib{
    protected $filename;
    protected $loaded_directory;
    protected $prefix;
    protected $is_ext_correct;
    protected $file_content = [];

    public function __construct($filename = "", $prefix = "", $loaded_directory = ""){
        $this->filename = $filename;
        $this->loaded_directory = $loaded_directory;
        $this->prefix = $prefix;
        $this->is_ext_correct = $this->check_extension($filename);
    }

    private function check_extension($filename){
        if(pathinfo($filename, PATHINFO_EXTENSION) == 'csv')
            return true;
        else
            return false;
    }

    public function open_file($_filename = null){
        if(!is_null($_filename)){
            $is_ext_correct = $this->check_extension($_filename);
            if($is_ext_correct){
                $this->filename = $_filename;
                $this->is_ext_correct = $is_ext_correct;
            }
        }
        if($this->is_ext_correct){
            $f_handle = fopen($this->filename, 'r');
            while($f_line = fgetcsv($f_handle)){
                array_push($this->file_content, $f_line);
            }
            fclose($f_handle);
            return true;
        }else{
            echo "Nieprawidłowe rozszerzenie pliku";
            return false;
        }
    }

    public function save_file($_directory = null, $_prefix = null){
        $directory = (is_null($_directory) ? $this->loaded_directory : $_directory);
        $prefix = (is_null($_prefix) ? $this->prefix : $_prefix);

        if(!is_dir($directory))
            mkdir($directory);

        $f_handle = fopen($directory.$prefix.$this->filename, 'w');

        foreach($this->file_content as $line){
            fputcsv($f_handle, $line);
        }

        fclose($f_handle);
    }

    public function add_first_col(){
        foreach($this->file_content as &$line){
            $length = count($line);
            for($i=1; $i < $length; $i++){
                $line[$i] += $line[0];
            }
        }
    }
}

class CsvLiv_db extends CsvLib{

    private $conn;

    public function __construct($filename = "", $prefix = "", $loaded_directory = ""){
        parent::__construct($filename, $prefix, $loaded_directory);
    }

    public function db_connect($host, $db_name, $username, $password){
        try {
            $this->conn = new PDO('mysql:host='.$host.';dbname='.$db_name, $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Connected successfully!";
        }catch(PDOException $e){
            echo "Connection failed: ".$e->getMessage();
        }
    }

    public function save_to_database(){
        $stmt = $this->conn->prepare("INSERT INTO files (id, name, upload_date) VALUES (NULL,?,NOW())");
        $stmt->execute(array($this->filename));

        $file_index = $this->conn->lastInsertId();

        $stmt = $this->conn->prepare("INSERT INTO content (id, file_id, f_row, sum_row) VALUES (:row_index, :index, :content, :row_sum)");
        $row_index = 0;

        foreach($this->file_content as $tab_row){
            $row_index++;
            $sum = array_sum($tab_row);
            $stmt->bindParam(':row_index', $row_index, PDO::PARAM_STR);
            $stmt->bindParam(':content', serialize($tab_row), PDO::PARAM_STR);
            $stmt->bindParam(':index', $file_index, PDO::PARAM_STR);
            $stmt->bindParam(':row_sum', $sum, PDO::PARAM_STR);
            $stmt->execute();
        }
    }

    public function show_table($file_id, $_sort_column, $_sort_direction)
    {
        switch($_sort_column){
            case 'id':
                $sort_column = 'content.id';
                break;
            case 'sum':
                $sort_column = 'content.sum_row';
                break;
            default:
                $sort_column = 'content.id';
        }

        switch($_sort_direction){
            case 'asc':
                $sort_direction = 'asc';
                break;
            case 'desc':
                $sort_direction = 'desc';
                break;
            default:
                $sort_direction = 'asc';
        }


        $stmt = $this->conn->prepare(
            "SELECT content.id, content.f_row, content.sum_row FROM files JOIN content ON files.id = content.file_id WHERE files.id = :id ORDER BY $sort_column $sort_direction"
        );

        $stmt->bindParam(':id', $file_id, PDO::PARAM_STR);
        $stmt->execute();
        if(!$rows = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
            echo "<br>Invalid parameter";
            exit();
        }
        $values = [];
        $longest_index = 0;

        foreach ($rows as $row) {
            $unserialized = unserialize($row['f_row']);
            array_unshift($unserialized, $row['sum_row']);
            array_push($values, $unserialized);
            end($unserialized);
            $temp = key($unserialized);
            $longest_index = ($temp > $longest_index) ? $temp : $longest_index;
        }

        echo "<table style=\"border-collapse: collapse;\">";
        foreach($values as $line){
            echo "<tr><td style=\"border-right: 1px dotted; border-bottom: 1px solid; color: red; padding: 5px\">".$line[0]."</td>";
            for($i=1; $i <= $longest_index; $i++){
                $val = (array_key_exists($i, $line)) ? $line[$i] : "";
                echo "<td style=\"border-right: 1px solid; border-bottom: 1px solid; padding: 5px\">".$val."</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }

    public function list_of_files(){
        $stmt = $this-> conn->prepare('SELECT * FROM files');
        $stmt->execute();
        $list_of_files = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($list_of_files as $file_link){
            echo "<option value=\"".$file_link['id']."\">".$file_link['name']."</option>";
        }
    }
}
// ---------------------------

$obj = new CsvLiv_db();
$obj->db_connect('localhost', 'lab02', 'test_usr', 'password');

if(isset($_GET['file_id'])){
    $obj->show_table($_GET['file_id'], $_GET['sort_col'], $_GET['sort_direction']);
}

if(isset($_POST['submit'])){

    $filename = $_POST['f_filename'];
    $loaded_directory = 'csv_uploaded/';
    $prefix = 'add_';

    if(($obj->open_file($filename))!=false)
        $obj->save_to_database();

}

?>

<form action="" method="POST">
    <label for="f_filename">Wybierz plik: </label>
    <input type="file" name="f_filename">
    <input type="submit" name="submit" value="Send">
</form>

<form action="" method="GET">
    <select name="file_id">
        <?php $obj->list_of_files(); ?>
    </select><br>
    <input type="radio" name="sort_col" value="id" checked>Id<br>
    <input type="radio" name="sort_col" value="sum">Sum<br>
    <input type="radio" name="sort_direction" value="asc" checked>ASC<br>
    <input type="radio" name="sort_direction" value="desc">DESC<br>
    <input type="submit" name="post" value="Show"/>
</form>
